<?php

namespace Database\Seeders;

use App\Models\AnimalReport;
use App\Models\User;
use App\Services\ReportDetectionService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->seedTestUsers();

        foreach ($this->reportDefinitions() as $definition) {
            $this->seedReport($definition, $users[$definition['user_email']]);
        }

        app(ReportDetectionService::class)->syncGroupRelatedCases();
    }

    /**
     * @return array<string, User>
     */
    private function seedTestUsers(): array
    {
        $definitions = [
            'report.seed.alpha@example.com' => [
                'name' => 'report.seed.alpha',
                'full_name' => 'Report Seed Alpha',
                'contact_number' => '09170000001',
                'address' => 'Test Seed Barangay Alpha, Angeles City',
            ],
            'report.seed.bravo@example.com' => [
                'name' => 'report.seed.bravo',
                'full_name' => 'Report Seed Bravo',
                'contact_number' => '09170000002',
                'address' => 'Test Seed Barangay Bravo, Angeles City',
            ],
            'report.seed.charlie@example.com' => [
                'name' => 'report.seed.charlie',
                'full_name' => 'Report Seed Charlie',
                'contact_number' => '09170000003',
                'address' => 'Test Seed Barangay Charlie, Angeles City',
            ],
            'report.seed.delta@example.com' => [
                'name' => 'report.seed.delta',
                'full_name' => 'Report Seed Delta',
                'contact_number' => '09170000004',
                'address' => 'Test Seed Barangay Delta, Angeles City',
            ],
            'report.seed.echo@example.com' => [
                'name' => 'report.seed.echo',
                'full_name' => 'Report Seed Echo',
                'contact_number' => '09170000005',
                'address' => 'Test Seed Barangay Echo, Angeles City',
            ],
            'report.seed.foxtrot@example.com' => [
                'name' => 'report.seed.foxtrot',
                'full_name' => 'Report Seed Foxtrot',
                'contact_number' => '09170000006',
                'address' => 'Test Seed Barangay Foxtrot, Angeles City',
            ],
        ];

        $users = [];

        foreach ($definitions as $email => $attributes) {
            $users[$email] = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $attributes['name'],
                    'full_name' => $attributes['full_name'],
                    'email_verified_at' => now(),
                    'status' => 'active',
                    'password' => Hash::make('ReportSeed123!'),
                    'contact_number' => $attributes['contact_number'],
                    'address' => $attributes['address'],
                ]
            );
        }

        return $users;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function reportDefinitions(): array
    {
        return [
            // Test Case 1: Same user with nearby coordinates. Expected result: These reports should appear under User Multiple Reports.
            [
                'user_email' => 'report.seed.alpha@example.com',
                'description' => 'TEST CASE 1A: Same user with nearby coordinates for a stray CAT report. Expected result: These reports should appear under User Multiple Reports.',
                'report_type' => 'stray animal',
                'animal_type' => 'CAT',
                'location_text' => 'Balibago, Angeles City',
                'latitude' => 15.1234500,
                'longitude' => 120.6000000,
                'created_at' => '2026-01-05 08:00:00',
            ],
            [
                'user_email' => 'report.seed.alpha@example.com',
                'description' => 'TEST CASE 1B: Same user with nearby coordinates for a stray CAT report. Expected result: These reports should appear under User Multiple Reports.',
                'report_type' => 'stray animal',
                'animal_type' => 'CAT',
                'location_text' => 'Balibago, Angeles City',
                'latitude' => 15.1237600,
                'longitude' => 120.6000000,
                'created_at' => '2026-01-06 09:00:00',
            ],

            // Test Case 2: Different users with nearby coordinates. Expected result: These reports should be grouped under Group Related Reports.
            [
                'user_email' => 'report.seed.alpha@example.com',
                'description' => 'TEST CASE 2A: Support report for the Group Related Reports case. Expected result: This report helps anchor the detected case so the different-user reports can be grouped.',
                'report_type' => 'injured animal',
                'animal_type' => 'DOG',
                'location_text' => 'Sapang Bato, Angeles City',
                'latitude' => 14.6500000,
                'longitude' => 120.9800000,
                'created_at' => '2026-02-10 10:00:00',
            ],
            [
                'user_email' => 'report.seed.alpha@example.com',
                'description' => 'TEST CASE 2B: Same matching window and nearby coordinates for a different-user injured DOG report. Expected result: These reports should be grouped under Group Related Reports.',
                'report_type' => 'injured animal',
                'animal_type' => 'DOG',
                'location_text' => 'Sapang Bato, Angeles City',
                'latitude' => 14.6502500,
                'longitude' => 120.9800000,
                'created_at' => '2026-02-11 11:00:00',
            ],
            [
                'user_email' => 'report.seed.bravo@example.com',
                'description' => 'TEST CASE 2C: Different user with nearby coordinates for an injured DOG report. Expected result: These reports should be grouped under Group Related Reports.',
                'report_type' => 'injured animal',
                'animal_type' => 'DOG',
                'location_text' => 'Sapang Bato, Angeles City',
                'latitude' => 14.6503000,
                'longitude' => 120.9800000,
                'created_at' => '2026-02-12 12:00:00',
            ],

            // Test Case 3: Same report and animal type but outside the 50-meter radius. Expected result: These reports should NOT be matched.
            [
                'user_email' => 'report.seed.charlie@example.com',
                'description' => 'TEST CASE 3A: Reference dead CAT report inside the same matching period. Expected result: This report should remain unmatched because the paired report is outside the 50-meter radius.',
                'report_type' => 'dead animal',
                'animal_type' => 'CAT',
                'location_text' => 'Santo Cristo, Angeles City',
                'latitude' => 14.7000000,
                'longitude' => 120.9500000,
                'created_at' => '2026-03-12 09:00:00',
            ],
            [
                'user_email' => 'report.seed.charlie@example.com',
                'description' => 'TEST CASE 3B: Same report and animal type but outside the 50-meter radius. Expected result: These reports should NOT be matched.',
                'report_type' => 'dead animal',
                'animal_type' => 'CAT',
                'location_text' => 'Santo Cristo, Angeles City',
                'latitude' => 14.7007000,
                'longitude' => 120.9500000,
                'created_at' => '2026-03-13 09:00:00',
            ],

            // Test Case 4: Nearby reports but different animal types. Expected result: These reports should NOT be matched.
            [
                'user_email' => 'report.seed.delta@example.com',
                'description' => 'TEST CASE 4A: Nearby aggressive animal report with CAT as the animal type. Expected result: These reports should NOT be matched because the paired report uses DOG.',
                'report_type' => 'aggressive animal',
                'animal_type' => 'CAT',
                'location_text' => 'Malabanias, Angeles City',
                'latitude' => 14.6100000,
                'longitude' => 120.9900000,
                'created_at' => '2026-04-14 14:00:00',
            ],
            [
                'user_email' => 'report.seed.echo@example.com',
                'description' => 'TEST CASE 4B: Nearby aggressive animal report with DOG as the animal type. Expected result: These reports should NOT be matched because the paired report uses CAT.',
                'report_type' => 'aggressive animal',
                'animal_type' => 'DOG',
                'location_text' => 'Malabanias, Angeles City',
                'latitude' => 14.6102800,
                'longitude' => 120.9900000,
                'created_at' => '2026-04-14 15:00:00',
            ],

            // Test Case 5: Same animal type and nearby location but different report types. Expected result: These reports should NOT be matched.
            [
                'user_email' => 'report.seed.alpha@example.com',
                'description' => 'TEST CASE 5A: Nearby stray CAT report. Expected result: These reports should NOT be matched because the paired report uses a different report type.',
                'report_type' => 'stray animal',
                'animal_type' => 'CAT',
                'location_text' => 'Pampang, Angeles City',
                'latitude' => 14.5300000,
                'longitude' => 120.9700000,
                'created_at' => '2026-05-18 08:30:00',
            ],
            [
                'user_email' => 'report.seed.bravo@example.com',
                'description' => 'TEST CASE 5B: Nearby injured CAT report. Expected result: These reports should NOT be matched because the paired report uses a different report type.',
                'report_type' => 'injured animal',
                'animal_type' => 'CAT',
                'location_text' => 'Pampang, Angeles City',
                'latitude' => 14.5302600,
                'longitude' => 120.9700000,
                'created_at' => '2026-05-18 09:00:00',
            ],

            // Test Case 6: Testing the 1-week matching window. Expected result: Only reports inside the allowed matching window should be related.
            [
                'user_email' => 'report.seed.delta@example.com',
                'description' => 'TEST CASE 6A: Reference dead DOG report for the 1-week matching window test. Expected result: This report should relate only to the report inside the allowed window.',
                'report_type' => 'dead animal',
                'animal_type' => 'DOG',
                'location_text' => 'Cutcut, Angeles City',
                'latitude' => 14.5600000,
                'longitude' => 120.9600000,
                'created_at' => '2026-06-01 10:00:00',
            ],
            [
                'user_email' => 'report.seed.delta@example.com',
                'description' => 'TEST CASE 6B: Inside the 1-week matching window for a dead DOG report. Expected result: This report should be related to the reference report.',
                'report_type' => 'dead animal',
                'animal_type' => 'DOG',
                'location_text' => 'Cutcut, Angeles City',
                'latitude' => 14.5602400,
                'longitude' => 120.9600000,
                'created_at' => '2026-06-07 10:00:00',
            ],
            [
                'user_email' => 'report.seed.delta@example.com',
                'description' => 'TEST CASE 6C: Outside the 1-week matching window for a dead DOG report. Expected result: This report should remain unrelated.',
                'report_type' => 'dead animal',
                'animal_type' => 'DOG',
                'location_text' => 'Cutcut, Angeles City',
                'latitude' => 14.5604800,
                'longitude' => 120.9600000,
                'created_at' => '2026-06-09 10:00:00',
            ],

            // Test Case 7: Multiple matching reports. Expected result: All matching reports should belong to the same related group/case.
            [
                'user_email' => 'report.seed.alpha@example.com',
                'description' => 'TEST CASE 7A: Same-case anchor report for multiple matching injured CAT reports. Expected result: This report should belong to the same related group/case.',
                'report_type' => 'injured animal',
                'animal_type' => 'CAT',
                'location_text' => 'Clark, Angeles City',
                'latitude' => 14.6200000,
                'longitude' => 120.9400000,
                'created_at' => '2026-07-03 07:30:00',
            ],
            [
                'user_email' => 'report.seed.alpha@example.com',
                'description' => 'TEST CASE 7B: Same user matching injured CAT report. Expected result: This report should belong to the same related group/case.',
                'report_type' => 'injured animal',
                'animal_type' => 'CAT',
                'location_text' => 'Clark, Angeles City',
                'latitude' => 14.6202000,
                'longitude' => 120.9400000,
                'created_at' => '2026-07-04 07:30:00',
            ],
            [
                'user_email' => 'report.seed.bravo@example.com',
                'description' => 'TEST CASE 7C: Different user matching injured CAT report. Expected result: This report should belong to the same related group/case.',
                'report_type' => 'injured animal',
                'animal_type' => 'CAT',
                'location_text' => 'Clark, Angeles City',
                'latitude' => 14.6203000,
                'longitude' => 120.9400000,
                'created_at' => '2026-07-05 07:30:00',
            ],
            [
                'user_email' => 'report.seed.charlie@example.com',
                'description' => 'TEST CASE 7D: Different user matching injured CAT report. Expected result: This report should belong to the same related group/case.',
                'report_type' => 'injured animal',
                'animal_type' => 'CAT',
                'location_text' => 'Clark, Angeles City',
                'latitude' => 14.6203500,
                'longitude' => 120.9400000,
                'created_at' => '2026-07-06 07:30:00',
            ],

            // Test Case 8: Reports have identical coordinates. Expected result: These reports should be matched.
            [
                'user_email' => 'report.seed.echo@example.com',
                'description' => 'TEST CASE 8A: Exact same coordinates for a stray DOG report. Expected result: These reports should be matched.',
                'report_type' => 'stray animal',
                'animal_type' => 'DOG',
                'location_text' => 'Mabalacat, Pampanga',
                'latitude' => 14.5805000,
                'longitude' => 120.9305000,
                'created_at' => '2026-08-10 16:00:00',
            ],
            [
                'user_email' => 'report.seed.echo@example.com',
                'description' => 'TEST CASE 8B: Exact same coordinates for a stray DOG report. Expected result: These reports should be matched.',
                'report_type' => 'stray animal',
                'animal_type' => 'DOG',
                'location_text' => 'Mabalacat, Pampanga',
                'latitude' => 14.5805000,
                'longitude' => 120.9305000,
                'created_at' => '2026-08-11 16:00:00',
            ],

            // Test Case 9: Testing the 50-meter boundary. Expected result: The 49-meter report should match and the 51-meter report should not.
            [
                'user_email' => 'report.seed.foxtrot@example.com',
                'description' => 'TEST CASE 9A: Reference dead CAT report for the 50-meter boundary test. Expected result: The nearby 49-meter report should match this report.',
                'report_type' => 'dead animal',
                'animal_type' => 'CAT',
                'location_text' => 'San Fernando, Pampanga',
                'latitude' => 14.6400000,
                'longitude' => 120.9100000,
                'created_at' => '2026-09-15 09:15:00',
            ],
            [
                'user_email' => 'report.seed.foxtrot@example.com',
                'description' => 'TEST CASE 9B: Approximately 49 meters from the reference report. Expected result: SHOULD MATCH.',
                'report_type' => 'dead animal',
                'animal_type' => 'CAT',
                'location_text' => 'San Fernando, Pampanga',
                'latitude' => 14.6404400,
                'longitude' => 120.9100000,
                'created_at' => '2026-09-15 09:20:00',
            ],
            [
                'user_email' => 'report.seed.foxtrot@example.com',
                'description' => 'TEST CASE 9C: Approximately 51 meters from the reference report. Expected result: SHOULD NOT MATCH.',
                'report_type' => 'dead animal',
                'animal_type' => 'CAT',
                'location_text' => 'San Fernando, Pampanga',
                'latitude' => 14.6404600,
                'longitude' => 120.9100000,
                'created_at' => '2026-09-15 09:25:00',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function seedReport(array $definition, User $user): AnimalReport
    {
        $createdAt = Carbon::parse($definition['created_at']);

        $report = AnimalReport::query()->updateOrCreate(
            ['description' => $definition['description']],
            [
                'user_id' => $user->id,
                'report_type' => $definition['report_type'],
                'animal_type' => $definition['animal_type'],
                'location_text' => $definition['location_text'],
                'latitude' => $definition['latitude'],
                'longitude' => $definition['longitude'],
                'description' => $definition['description'],
                'image_path' => '',
                'video_path' => null,
                'status' => 'pending',
                'resolved_at' => null,
            ]
        );

        $report->timestamps = false;
        $report->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $report;
    }
}