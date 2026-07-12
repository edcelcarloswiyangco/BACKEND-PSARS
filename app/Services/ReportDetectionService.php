<?php

namespace App\Services;

use App\Models\AnimalReport;
use App\Models\ReportDetectionCase;
use App\Models\ReportGroupExclusion;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportDetectionService
{
    public const GROUP_TYPE_USER_MULTIPLE = 'user_multiple';

    public const GROUP_TYPE_GROUP_RELATED = 'group_related';

    public const MAX_DISTANCE_METERS = 50;

    public const MATCHING_WINDOW_DAYS = 7;

    public function syncGroupRelatedCases(): void
    {
        if (! Schema::hasTable('report_detection_cases') || ! Schema::hasTable('report_detection_case_reports')) {
            return;
        }

        DB::transaction(function (): void {
            $cases = ReportDetectionCase::query()
                ->with(['reports.user'])
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $reports = AnimalReport::query()
                ->with('user')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $attachedReportIds = $cases->flatMap(fn (ReportDetectionCase $case) => $case->reports->pluck('id'))->unique()->flip();

            foreach ($reports as $report) {
                if (isset($attachedReportIds[$report->id])) {
                    continue;
                }

                $matchingCase = $this->findMatchingCaseForReport($cases, $report);

                if ($matchingCase) {
                    $matchingCase->reports()->syncWithoutDetaching([$report->id]);
                    $matchingCase->load('reports.user');
                    $attachedReportIds[$report->id] = true;

                    continue;
                }

                $candidateReports = $this->findCandidateReportsForCase($reports, $report, $attachedReportIds);

                if ($candidateReports->isEmpty()) {
                    continue;
                }

                $case = $this->createCaseFromReports($report, $candidateReports);
                $cases->push($case->load('reports.user'));
                $attachedReportIds[$report->id] = true;

                foreach ($candidateReports as $candidateReport) {
                    $attachedReportIds[$candidateReport->id] = true;
                }
            }

            $this->closeExpiredCases();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildUserMultipleGroups(iterable $reports): array
    {
        $reportsCollection = $reports instanceof Collection ? $reports : collect($reports);
        $eligibleReports = $reportsCollection->filter(fn (AnimalReport $report) => $report->created_at && $report->latitude !== null && $report->longitude !== null)->values();

        $clusters = [];

        foreach ($eligibleReports as $report) {
            $clusters[$this->userMultipleClusterKey($report)][] = $report;
        }

        $groups = [];

        foreach ($clusters as $reportsByCriteria) {
            foreach ($this->clusterReportsByConditions($reportsByCriteria, true) as $cluster) {
                if (count($cluster) < 2) {
                    continue;
                }

                $groupKey = $this->userMultipleGroupKey($cluster);
                $cluster = $this->applyExclusions($cluster, self::GROUP_TYPE_USER_MULTIPLE, $groupKey);

                if (count($cluster) < 2) {
                    continue;
                }

                $groups[] = $this->formatUserMultipleGroup($cluster, $groupKey);
            }
        }

        return $groups;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildGroupRelatedCases(): array
    {
        if (! Schema::hasTable('report_detection_cases')) {
            return [];
        }

        $cases = ReportDetectionCase::query()
            ->with(['reports.user'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return $cases->map(fn (ReportDetectionCase $case) => $this->formatGroupRelatedCase($case))->all();
    }

    public function excludeReportFromGroup(string $groupType, string $groupKey, int $reportId): void
    {
        ReportGroupExclusion::query()->updateOrCreate(
            [
                'group_type' => $groupType,
                'group_key' => $groupKey,
                'report_id' => $reportId,
            ],
            []
        );

        if ($groupType === self::GROUP_TYPE_GROUP_RELATED && Schema::hasTable('report_detection_case_reports')) {
            $case = ReportDetectionCase::query()->whereKey($groupKey)->first();

            if ($case) {
                $case->reports()->detach($reportId);
            }
        }
    }

    private function closeExpiredCases(): void
    {
        ReportDetectionCase::query()
            ->where('matching_state', ReportDetectionCase::MATCHING_STATE_OPEN)
            ->whereNotNull('matching_window_ends_at')
            ->where('matching_window_ends_at', '<', now())
            ->update(['matching_state' => ReportDetectionCase::MATCHING_STATE_CLOSED]);
    }

    /**
     * @param  EloquentCollection<int, ReportDetectionCase>  $cases
     */
    private function findMatchingCaseForReport(EloquentCollection $cases, AnimalReport $report): ?ReportDetectionCase
    {
        return $cases->first(function (ReportDetectionCase $case) use ($report): bool {
            if ($case->report_type !== $report->report_type || $case->animal_type !== $report->animal_type) {
                return false;
            }

            if (! $case->matching_window_started_at || ! $case->matching_window_ends_at || ! $report->created_at) {
                return false;
            }

            if ($report->created_at->lt($case->matching_window_started_at) || $report->created_at->gt($case->matching_window_ends_at)) {
                return false;
            }

            return $case->reports->contains(function (AnimalReport $existingReport) use ($report): bool {
                if ($existingReport->user_id === $report->user_id) {
                    return false;
                }

                return $this->isWithinMatchingRules($existingReport, $report, false);
            });
        });
    }

    /**
     * @param  Collection<int, AnimalReport>  $reports
     * @param  Collection<int, int>  $attachedReportIds
     */
    private function findCandidateReportsForCase(Collection $reports, AnimalReport $report, Collection $attachedReportIds): Collection
    {
        return $reports
            ->filter(function (AnimalReport $candidate) use ($report, $attachedReportIds): bool {
                if ($candidate->id === $report->id || isset($attachedReportIds[$candidate->id])) {
                    return false;
                }

                return $this->isWithinMatchingRules($report, $candidate, true);
            })
            ->values();
    }

    /**
     * @param  array<int, AnimalReport>  $reports
     * @return array<int, array<int, AnimalReport>>
     */
    private function clusterReportsByConditions(array $reports, bool $requireSameUser): array
    {
        $indexedReports = array_values($reports);
        $count = count($indexedReports);

        if ($count < 2) {
            return [];
        }

        $parents = range(0, $count - 1);

        $find = function (int $index) use (&$parents, &$find): int {
            if ($parents[$index] !== $index) {
                $parents[$index] = $find($parents[$index]);
            }

            return $parents[$index];
        };

        $union = function (int $first, int $second) use (&$parents, $find): void {
            $rootFirst = $find($first);
            $rootSecond = $find($second);

            if ($rootFirst !== $rootSecond) {
                $parents[$rootSecond] = $rootFirst;
            }
        };

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($this->isWithinMatchingRules($indexedReports[$i], $indexedReports[$j], $requireSameUser)) {
                    $union($i, $j);
                }
            }
        }

        $clusterMap = [];

        foreach ($indexedReports as $index => $report) {
            $clusterMap[$find($index)][] = $report;
        }

        return array_values($clusterMap);
    }

    private function isWithinMatchingRules(AnimalReport $firstReport, AnimalReport $secondReport, bool $requireSameUser): bool
    {
        if ($firstReport->report_type !== $secondReport->report_type) {
            return false;
        }

        if ($firstReport->animal_type !== $secondReport->animal_type) {
            return false;
        }

        if ($requireSameUser && $firstReport->user_id !== $secondReport->user_id) {
            return false;
        }

        if (! $firstReport->created_at || ! $secondReport->created_at) {
            return false;
        }

        $timeDifference = abs($firstReport->created_at->diffInDays($secondReport->created_at));

        if ($timeDifference > self::MATCHING_WINDOW_DAYS) {
            return false;
        }

        if ($firstReport->latitude === null || $firstReport->longitude === null || $secondReport->latitude === null || $secondReport->longitude === null) {
            return false;
        }

        return $this->distanceInMeters($firstReport, $secondReport) <= self::MAX_DISTANCE_METERS;
    }

    private function distanceInMeters(AnimalReport $firstReport, AnimalReport $secondReport): float
    {
        $latitudeOne = (float) $firstReport->latitude;
        $longitudeOne = (float) $firstReport->longitude;
        $latitudeTwo = (float) $secondReport->latitude;
        $longitudeTwo = (float) $secondReport->longitude;

        $earthRadius = 6371000;
        $latitudeDelta = deg2rad($latitudeTwo - $latitudeOne);
        $longitudeDelta = deg2rad($longitudeTwo - $longitudeOne);
        $latOneRadians = deg2rad($latitudeOne);
        $latTwoRadians = deg2rad($latitudeTwo);

        $a = sin($latitudeDelta / 2) ** 2
            + cos($latOneRadians) * cos($latTwoRadians) * sin($longitudeDelta / 2) ** 2;

        return 2 * $earthRadius * asin(min(1, sqrt($a)));
    }

    private function createCaseFromReports(AnimalReport $seedReport, Collection $candidateReports): ReportDetectionCase
    {
        return DB::transaction(function () use ($seedReport, $candidateReports): ReportDetectionCase {
            $case = ReportDetectionCase::query()->create([
                'case_number' => $this->nextCaseNumber(),
                'report_type' => $seedReport->report_type,
                'animal_type' => $seedReport->animal_type,
                'matching_state' => ReportDetectionCase::MATCHING_STATE_OPEN,
                'matching_window_started_at' => $seedReport->created_at,
                'matching_window_ends_at' => $seedReport->created_at?->copy()->addDays(self::MATCHING_WINDOW_DAYS),
                'primary_location_text' => $seedReport->location_text,
                'center_latitude' => $seedReport->latitude,
                'center_longitude' => $seedReport->longitude,
            ]);

            $case->reports()->attach([$seedReport->id]);

            foreach ($candidateReports as $candidateReport) {
                $case->reports()->attach([$candidateReport->id]);
            }

            return $case;
        });
    }

    private function nextCaseNumber(): string
    {
        $year = now()->format('Y');
        $latestCase = ReportDetectionCase::query()
            ->where('case_number', 'like', "CASE-{$year}-%")
            ->orderByDesc('id')
            ->first();

        $nextSequence = 1;

        if ($latestCase) {
            $parts = explode('-', $latestCase->case_number);
            $nextSequence = isset($parts[2]) ? ((int) $parts[2]) + 1 : 1;
        }

        return sprintf('CASE-%s-%05d', $year, $nextSequence);
    }

    private function userMultipleClusterKey(AnimalReport $report): string
    {
        return implode('|', [
            'user-multiple',
            (string) $report->user_id,
            mb_strtolower(trim((string) $report->report_type)),
            mb_strtolower(trim((string) $report->animal_type)),
        ]);
    }

    /**
     * @param  array<int, AnimalReport>  $reports
     */
    private function userMultipleGroupKey(array $reports): string
    {
        $sortedReports = array_values($reports);
        usort($sortedReports, static function (AnimalReport $left, AnimalReport $right): int {
            return ($left->created_at?->timestamp ?? $left->id) <=> ($right->created_at?->timestamp ?? $right->id);
        });

        return 'user-multiple:' . ($sortedReports[0]->id ?? '0');
    }

    /**
     * @param  array<int, AnimalReport>  $reports
     * @return array<int, AnimalReport>
     */
    private function applyExclusions(array $reports, string $groupType, string $groupKey): array
    {
        $excludedReportIds = ReportGroupExclusion::query()
            ->where('group_type', $groupType)
            ->where('group_key', $groupKey)
            ->pluck('report_id')
            ->all();

        if ($excludedReportIds === []) {
            return $reports;
        }

        return array_values(array_filter($reports, static fn (AnimalReport $report) => ! in_array($report->id, $excludedReportIds, true)));
    }

    /**
     * @param  array<int, AnimalReport>  $reports
     * @return array<string, mixed>
     */
    private function formatUserMultipleGroup(array $reports, string $groupKey): array
    {
        usort($reports, static function (AnimalReport $left, AnimalReport $right): int {
            return ($left->created_at?->timestamp ?? $left->id) <=> ($right->created_at?->timestamp ?? $right->id);
        });

        $reportCount = count($reports);
        $uniqueLocations = collect($reports)->pluck('location_text')->filter()->unique()->values();
        $matchingStart = $reports[0]->created_at;
        $matchingEnd = $reports[$reportCount - 1]->created_at;

        return [
            'group_key' => $groupKey,
            'report_count' => $reportCount,
            'user_name' => optional($reports[0]->user)->full_name ?? optional($reports[0]->user)->name ?? '-',
            'user_code' => optional($reports[0]->user)->registration_code,
            'user_email' => optional($reports[0]->user)->email,
            'report_type' => $reports[0]->report_type,
            'animal_type' => $reports[0]->animal_type,
            'location_summary' => $this->buildLocationSummary($uniqueLocations->all(), $reports),
            'matching_date_range' => $matchingStart && $matchingEnd
                ? sprintf('%s - %s', $matchingStart->format('M d, Y'), $matchingEnd->format('M d, Y'))
                : 'Unknown',
            'reports' => array_map(fn (AnimalReport $report): array => $this->formatReportForDetection($report), $reports),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatGroupRelatedCase(ReportDetectionCase $case): array
    {
        $reports = $case->reports->sortBy(fn (AnimalReport $report) => $report->created_at?->timestamp ?? $report->id)->values();
        $uniqueUsers = $reports->pluck('user_id')->filter()->unique()->count();
        $uniqueLocations = $reports->pluck('location_text')->filter()->unique()->values();

        return [
            'case_id' => $case->id,
            'case_number' => $case->case_number,
            'report_count' => $reports->count(),
            'report_type' => $case->report_type,
            'animal_type' => $case->animal_type,
            'matching_state' => $case->matching_state,
            'matching_window_start' => optional($case->matching_window_started_at)->format('M d, Y'),
            'matching_window_end' => optional($case->matching_window_ends_at)->format('M d, Y'),
            'location_summary' => $this->buildLocationSummary($uniqueLocations->all(), $reports->all()),
            'reports' => $reports->map(fn (AnimalReport $report): array => $this->formatReportForDetection($report))->all(),
            'relationship_label' => $uniqueUsers > 1 ? 'Different Users' : 'Same User',
        ];
    }

    /**
     * @param  array<int, string>  $locations
     * @param  array<int, AnimalReport>  $reports
     */
    private function buildLocationSummary(array $locations, array $reports): string
    {
        $locationSummary = collect($locations)->take(2)->implode(', ');

        if ($locationSummary === '') {
            $locationSummary = 'General area near the matched coordinates';
        } elseif (count($locations) > 2) {
            $locationSummary .= ' + ' . (count($locations) - 2) . ' more';
        }

        $latitude = round(collect($reports)->avg(fn (AnimalReport $report) => (float) $report->latitude), 5);
        $longitude = round(collect($reports)->avg(fn (AnimalReport $report) => (float) $report->longitude), 5);

        return sprintf('%s • Approx. center: %.5f, %.5f', $locationSummary, $latitude, $longitude);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatReportForDetection(AnimalReport $report): array
    {
        $reportCode = sprintf('R%s-%05d', optional($report->created_at)->format('y') ?? '00', $report->id);

        return [
            'id' => $report->id,
            'report_code' => $reportCode,
            'report_type' => $report->report_type,
            'animal_type' => $report->animal_type,
            'user_id' => optional($report->user)->id,
            'user_code' => optional($report->user)->registration_code,
            'user_name' => optional($report->user)->full_name ?? optional($report->user)->name,
            'user_email' => optional($report->user)->email,
            'created_at' => optional($report->created_at)->format('M d, Y H:i'),
            'location_text' => $report->location_text,
            'latitude' => $report->latitude,
            'longitude' => $report->longitude,
            'status' => $report->status,
        ];
    }
}