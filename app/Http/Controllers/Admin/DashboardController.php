<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnimalReport;
use App\Models\Pet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $hasReportsTable = Schema::hasTable('animal_reports');
        $hasPetsTable = Schema::hasTable('pets');

        $users = User::query()->latest()->get();

        foreach ($users as $user) {
            $user->reports_count = $hasReportsTable
                ? DB::table('animal_reports')->where('user_id', $user->id)->count()
                : 0;
            $user->status = $this->resolveUserStatus($user);
            $user->suspension_summary = $this->buildSuspensionSummary($user);
        }

        $reports = $hasReportsTable
            ? AnimalReport::query()->with('user')->latest('id')->get()
            : collect();
        $relatedReportGroups = $hasReportsTable
            ? $this->buildRelatedReportGroups($reports)
            : [];

        $todayStatusCounts = [
            'pending' => 0,
            'in_progress' => 0,
            'resolved' => 0,
        ];

        $statusBreakdown = [
            'pending' => 0,
            'in_progress' => 0,
            'resolved' => 0,
        ];

        $trendData = [
            'today' => ['labels' => [], 'data' => []],
            'seven_days' => ['labels' => [], 'data' => []],
            'month' => ['labels' => [], 'data' => []],
        ];

        $animalTypeDistribution = [];
        $petVaccinationDistribution = [
            'vaccinated' => 0,
            'unvaccinated' => 0,
        ];
        $registeredPetTypeDistribution = [];
        $announcements = Schema::hasTable('announcements')
            ? Announcement::query()->latest('published_at')->latest('id')->get()
            : collect();

        if ($hasReportsTable) {
            $totalReports = AnimalReport::query()->count();

            $todayRows = DB::table('animal_reports')
                ->select('status', DB::raw('count(*) as total'))
                ->whereDate('created_at', Carbon::today())
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();

            foreach ($todayStatusCounts as $status => $defaultCount) {
                $todayStatusCounts[$status] = (int) ($todayRows[$status] ?? $defaultCount);
            }

            $statusRows = DB::table('animal_reports')
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();

            foreach ($statusBreakdown as $status => $defaultCount) {
                $statusBreakdown[$status] = (int) ($statusRows[$status] ?? $defaultCount);
            }

            $todayHourRows = DB::table('animal_reports')
                ->select(DB::raw('HOUR(created_at) as report_hour'), DB::raw('count(*) as total'))
                ->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->pluck('total', 'report_hour')
                ->all();

            for ($hour = 0; $hour < 24; $hour++) {
                $trendData['today']['labels'][] = Carbon::today()->startOfDay()->addHours($hour)->format('g A');
                $trendData['today']['data'][] = (int) ($todayHourRows[$hour] ?? 0);
            }

            $sevenDaysStart = Carbon::today()->subDays(6);
            $sevenDayRows = DB::table('animal_reports')
                ->select(DB::raw('DATE(created_at) as report_date'), DB::raw('count(*) as total'))
                ->whereBetween('created_at', [$sevenDaysStart->copy()->startOfDay(), Carbon::today()->endOfDay()])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('total', 'report_date')
                ->all();

            for ($index = 0; $index < 7; $index++) {
                $date = $sevenDaysStart->copy()->addDays($index);
                $trendData['seven_days']['labels'][] = $date->format('D');
                $trendData['seven_days']['data'][] = (int) ($sevenDayRows[$date->toDateString()] ?? 0);
            }

            $monthStart = Carbon::now()->startOfMonth();
            $monthRows = DB::table('animal_reports')
                ->select(DB::raw('DATE(created_at) as report_date'), DB::raw('count(*) as total'))
                ->whereBetween('created_at', [$monthStart->copy()->startOfDay(), Carbon::now()->endOfMonth()])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('total', 'report_date')
                ->all();

            for ($date = $monthStart->copy(); $date->lte(Carbon::today()); $date->addDay()) {
                $trendData['month']['labels'][] = (string) $date->day;
                $trendData['month']['data'][] = (int) ($monthRows[$date->toDateString()] ?? 0);
            }

            $animalTypeDistribution = DB::table('animal_reports')
                ->select('animal_type', DB::raw('count(*) as total'))
                ->groupBy('animal_type')
                ->orderByDesc('total')
                ->pluck('total', 'animal_type')
                ->all();
        } else {
            $totalReports = 0;
        }

        if ($hasPetsTable) {
            $petVaccinationRows = DB::table('pets')
                ->select('rabies_status', DB::raw('count(*) as total'))
                ->groupBy('rabies_status')
                ->pluck('total', 'rabies_status')
                ->all();

            $petVaccinationDistribution['vaccinated'] = (int) ($petVaccinationRows['vaccinated'] ?? 0);
            $petVaccinationDistribution['unvaccinated'] = (int) ($petVaccinationRows['not_vaccinated'] ?? 0);

            $registeredPetTypeDistribution = DB::table('pets')
                ->select('animal_type', DB::raw('count(*) as total'))
                ->groupBy('animal_type')
                ->orderByDesc('total')
                ->pluck('total', 'animal_type')
                ->all();
        }

        $totalPets = $hasPetsTable ? DB::table('pets')->count() : 0;

        // Fetch pets grouped by rabies vaccination status
        $petsByStatus = [
            'vaccinated' => [],
            'not_vaccinated' => [],
            'unknown' => [],
        ];

        if ($hasPetsTable) {
            $petsByStatus['vaccinated'] = Pet::where('rabies_status', 'vaccinated')->with('user')->get();
            $petsByStatus['not_vaccinated'] = Pet::where('rabies_status', 'not_vaccinated')->with('user')->get();
            $petsByStatus['unknown'] = Pet::where('rabies_status', 'unknown')->with('user')->get();
        }

        return view('admin.dashboard', [
            'users' => $users,
            'summary' => [
                'total_users' => User::query()->count(),
                'total_reports' => $totalReports,
                'total_pets' => $totalPets,
            ],
            'reports' => $reports,
            'related_report_groups' => $relatedReportGroups,
            'pets_by_status' => $petsByStatus,
            'analytics' => [
                'today_status_counts' => $todayStatusCounts,
                'status_breakdown' => $statusBreakdown,
                'trend_data' => $trendData,
                'animal_type_distribution' => $animalTypeDistribution,
                'pet_vaccination_distribution' => $petVaccinationDistribution,
                'registered_pet_type_distribution' => $registeredPetTypeDistribution,
            ],
            'announcements' => $announcements,
        ]);
    }

    public function show(User $user)
    {
        $status = $this->resolveUserStatus($user);

        $data = [
            'id' => $user->id,
            'full_name' => $user->full_name ?? $user->name,
            'email' => $user->email,
            'contact_number' => $user->contact_number,
            'address' => $user->address,
            'registered_at' => $user->created_at ? $user->created_at->format('M d, Y') : null,
            'status' => $status,
            'suspension_type' => $user->suspension_type,
            'suspension_value' => $user->suspension_value,
            'suspension_reason' => $user->suspension_reason,
            'suspension_note' => $user->suspension_note,
            'suspended_at' => optional($user->suspended_at)->format('M d, Y h:i A'),
            'suspension_ends_at' => optional($user->suspension_ends_at)->format('M d, Y h:i A'),
            'suspension_summary' => $this->buildSuspensionSummary($user),
        ];

        if (Schema::hasTable('pets')) {
            $data['pets'] = DB::table('pets')->where('user_id', $user->id)->get();
        } else {
            $data['pets'] = [];
        }

        if (Schema::hasTable('animal_reports')) {
            $data['reports'] = DB::table('animal_reports')->where('user_id', $user->id)->get();
        } else {
            $data['reports'] = [];
        }

        $data['pets_count'] = is_countable($data['pets']) ? count($data['pets']) : 0;
        $data['reports_count'] = is_countable($data['reports']) ? count($data['reports']) : 0;

        return response()->json($data);
    }

    private function resolveUserStatus(User $user): string
    {
        return $user->accountStatus();
    }

    private function buildSuspensionSummary(User $user): ?string
    {
        return $user->suspensionSummary();
    }

    public function suspendUser(Request $request, User $user): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'suspension_type' => ['required', 'in:days,weeks,months,permanent'],
            'suspension_value' => ['nullable', 'integer', 'min:1', 'required_if:suspension_type,days,weeks,months'],
            'suspension_reason' => ['required', 'string', 'max:255'],
            'suspension_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $suspensionEndsAt = null;

        if ($validated['suspension_type'] === 'days') {
            $suspensionEndsAt = now()->addDays((int) $validated['suspension_value']);
        } elseif ($validated['suspension_type'] === 'weeks') {
            $suspensionEndsAt = now()->addWeeks((int) $validated['suspension_value']);
        } elseif ($validated['suspension_type'] === 'months') {
            $suspensionEndsAt = now()->addMonthsNoOverflow((int) $validated['suspension_value']);
        }

        $user->forceFill([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspension_type' => $validated['suspension_type'],
            'suspension_value' => $validated['suspension_type'] === 'permanent' ? null : (int) $validated['suspension_value'],
            'suspension_reason' => $validated['suspension_reason'],
            'suspension_note' => $validated['suspension_note'] ?? null,
            'suspension_ends_at' => $suspensionEndsAt,
        ])->save();

        return $this->suspensionResponse($request, 'User suspended successfully.');
    }

    public function unsuspendUser(Request $request, User $user): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user->forceFill([
            'status' => 'active',
            'suspended_at' => null,
            'suspension_type' => null,
            'suspension_value' => null,
            'suspension_reason' => null,
            'suspension_note' => null,
            'suspension_ends_at' => null,
        ])->save();

        return $this->suspensionResponse($request, 'User suspension removed successfully.');
    }

    private function suspensionResponse(Request $request, string $message): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function updateReportStatus(AnimalReport $report)
    {
        if ($report->status === 'resolved') {
            return response()->json([
                'message' => 'Resolved reports cannot be changed.',
                'status' => $report->status,
            ], 422);
        }

        $nextStatus = match ($report->status) {
            'pending' => 'in_progress',
            'in_progress' => 'resolved',
            default => 'pending',
        };

        $report->status = $nextStatus;
        if ($nextStatus === 'resolved' && !$report->resolved_at) {
            $report->resolved_at = now();
        }
        $report->save();

        return response()->json([
            'message' => 'Report status updated successfully.',
            'data' => [
                'id' => $report->id,
                'status' => $report->status,
                'resolved_at' => optional($report->resolved_at)->toIso8601String(),
            ],
        ]);
    }

    /**
     * @param  iterable<int, AnimalReport>  $reports
     * @return array<int, array<string, mixed>>
     */
    private function buildRelatedReportGroups(iterable $reports): array
    {
        $candidates = [];

        foreach ($reports as $report) {
            if (! $report->created_at || $report->latitude === null || $report->longitude === null) {
                continue;
            }

            $candidates[$this->relatedReportKey($report)][] = $report;
        }

        $groups = [];

        foreach ($candidates as $reportsByCriteria) {
            foreach ($this->clusterReportsByLocation($reportsByCriteria) as $cluster) {
                if (count($cluster) < 2) {
                    continue;
                }

                $groups[] = $this->formatRelatedReportGroup($cluster);
            }
        }

        return $groups;
    }

    /**
     * @param  array<int, AnimalReport>  $reports
     * @return array<int, array<int, AnimalReport>>
     */
    private function clusterReportsByLocation(array $reports): array
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
                if ($this->distanceInMeters($indexedReports[$i], $indexedReports[$j]) <= 50) {
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

    private function relatedReportKey(AnimalReport $report): string
    {
        return implode('|', [
            mb_strtolower(trim((string) $report->report_type)),
            mb_strtolower(trim((string) $report->animal_type)),
            optional($report->created_at)->toDateString() ?? '',
        ]);
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

    /**
     * @param  array<int, AnimalReport>  $reports
     * @return array<string, mixed>
     */
    private function formatRelatedReportGroup(array $reports): array
    {
        usort($reports, static function (AnimalReport $left, AnimalReport $right): int {
            return ($right->created_at?->timestamp ?? $right->id) <=> ($left->created_at?->timestamp ?? $left->id);
        });

        $uniqueUserIds = collect($reports)
            ->map(fn (AnimalReport $report) => $report->user_id)
            ->filter(fn ($userId) => $userId !== null)
            ->unique()
            ->values();

        $uniqueLocations = collect($reports)
            ->map(fn (AnimalReport $report) => trim((string) $report->location_text))
            ->filter()
            ->unique()
            ->values();

        $averageLatitude = round(collect($reports)->avg(fn (AnimalReport $report) => (float) $report->latitude), 5);
        $averageLongitude = round(collect($reports)->avg(fn (AnimalReport $report) => (float) $report->longitude), 5);

        return [
            'report_count' => count($reports),
            'report_type' => $reports[0]->report_type,
            'animal_type' => $reports[0]->animal_type,
            'date' => optional($reports[0]->created_at)->format('M d, Y'),
            'relationship_label' => $this->resolveRelationshipLabel($uniqueUserIds->count()),
            'location_summary' => $this->buildRelatedLocationSummary($uniqueLocations->all(), $averageLatitude, $averageLongitude),
            'reports' => array_map(function (AnimalReport $report): array {
                $reportCode = sprintf('R%s-%05d', optional($report->created_at)->format('y') ?? '00', $report->id);

                return [
                    'id' => $report->id,
                    'report_code' => $reportCode,
                    'report_type' => $report->report_type,
                    'animal_type' => $report->animal_type,
                    'user_id' => $report->user_id,
                    'user_code' => optional($report->user)->registration_code,
                    'user_name' => optional($report->user)->full_name ?? optional($report->user)->name,
                    'user_email' => optional($report->user)->email,
                    'created_at' => optional($report->created_at)->format('M d, Y H:i'),
                    'location_text' => $report->location_text,
                    'latitude' => $report->latitude,
                    'longitude' => $report->longitude,
                    'status' => $report->status,
                ];
            }, $reports),
        ];
    }

    /**
     * @param  array<int, string>  $locations
     */
    private function buildRelatedLocationSummary(array $locations, float $latitude, float $longitude): string
    {
        $locationSummary = collect($locations)->take(2)->implode(', ');

        if ($locationSummary === '') {
            $locationSummary = 'General area near the matched coordinates';
        } elseif (count($locations) > 2) {
            $locationSummary .= ' + ' . (count($locations) - 2) . ' more';
        }

        return sprintf('%s • Approx. center: %.5f, %.5f', $locationSummary, $latitude, $longitude);
    }

    private function resolveRelationshipLabel(int $uniqueUserCount): string
    {
        if ($uniqueUserCount <= 1) {
            return 'Same User';
        }

        if ($uniqueUserCount === 2) {
            return 'Different Users';
        }

        return 'Multiple Users';
    }
}
