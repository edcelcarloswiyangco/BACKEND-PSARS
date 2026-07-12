<div class="card report-management-card">
    <div class="tabs report-tabs" role="tablist" aria-label="Report management tabs">
        <button class="tab active" type="button" data-report-tab="all" aria-selected="true">All Reports</button>
        <button class="tab" type="button" data-report-tab="user-multiple" aria-selected="false">User Multiple Reports</button>
        <button class="tab" type="button" data-report-tab="group-related" aria-selected="false">Group Related Reports</button>
    </div>

    <div id="allReportsPanel" class="report-tab-panel">
        <div class="report-toolbar">
            <div></div>
            <div class="report-filters">
                <div class="report-search-wrap">
                    <input
                        id="reportSearchInput"
                        type="search"
                        class="report-search"
                        placeholder="Search reports..."
                        autocomplete="off"
                        aria-autocomplete="list"
                        aria-expanded="false"
                    >
                </div>
                <div id="reportStatusDropdown" class="report-status-filters" aria-label="Filter reports by status">
                    <span class="report-status-label">Status</span>
                    <label class="status-checkbox">
                        <input type="checkbox" value="all" checked data-status-option>
                        <span>All Statuses</span>
                    </label>
                    <label class="status-checkbox">
                        <input type="checkbox" value="pending" checked data-status-option>
                        <span>Pending</span>
                    </label>
                    <label class="status-checkbox">
                        <input type="checkbox" value="in_progress" checked data-status-option>
                        <span>In Progress</span>
                    </label>
                    <label class="status-checkbox">
                        <input type="checkbox" value="resolved" checked data-status-option>
                        <span>Resolved</span>
                    </label>
                </div>
            </div>
        </div>

        @if ($reports->isEmpty())
            <div class="muted">No reports have been submitted yet.</div>
        @else
            <div>
                @foreach ($reports as $report)
                    @php
                        $reportCode = sprintf('R%s-%05d', optional($report->created_at)->format('y') ?? '00', $report->id);
                        $userCode = optional($report->user)
                            ? $report->user->registration_code
                            : null;
                    @endphp
                    @php
                        $reportSearchText = collect([
                            $reportCode,
                            $report->report_type,
                            $report->animal_type,
                            $userCode,
                            optional($report->user)->email,
                        ])->filter()->implode(' ');
                    @endphp
                    @php
                        $reportNextStatus = match ($report->status) {
                            'pending' => 'in_progress',
                            'in_progress' => 'resolved',
                            default => null,
                        };
                    @endphp
                    <div class="report-row" data-report-id="{{ $report->id }}" data-report-status="{{ $report->status }}" data-report-search="{{ strtolower(e($reportSearchText)) }}">
                        <div class="report-summary">
                            <h4>Report ID {{ $reportCode }} — {{ ucfirst($report->report_type) }} ({{ $report->animal_type }})</h4>
                            <span class="muted">Submitted: {{ $report->created_at->format('M d, Y') }}</span>
                            <span class="muted">User ID: {{ $userCode ?? '-' }}</span>
                            <div style="margin-top:8px">
                                <span class="report-status-pill {{ $report->status }}">{{ str_replace('_', ' ', $report->status) }}</span>
                            </div>
                        </div>
                        <div class="report-actions">
                            <button class="btn-ghost" type="button" onclick="openReport({{ $report->id }})">View</button>
                            <button
                                class="report-status-row-action"
                                type="button"
                                @if (!$reportNextStatus) disabled @endif
                                data-next-status="{{ $reportNextStatus }}"
                                onclick="changeReportStatus({{ $report->id }}, this.dataset.nextStatus)"
                            >
                                {{ $reportNextStatus === 'in_progress' ? 'Mark In Progress' : ($reportNextStatus === 'resolved' ? 'Mark Resolved' : 'Resolved') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div id="reportNoResults" class="report-no-results muted">No reports match your search and status filters.</div>
        @endif
    </div>

    <div id="userMultipleReportsPanel" class="report-tab-panel hidden">
        @if (empty($user_multiple_report_groups))
            <div class="muted">No user multiple reports were detected yet.</div>
        @else
            <div class="related-report-groups">
                @foreach ($user_multiple_report_groups as $group)
                    <div class="related-group-card detection-group-card">
                        <div class="related-group-header">
                            <div>
                                <h4>User Multiple Reports</h4>
                                <div class="muted">{{ $group['report_count'] }} matching reports for {{ $group['user_name'] }}</div>
                            </div>
                            <span class="related-group-badge">Same User</span>
                        </div>

                        <div class="related-group-meta">
                            <span><strong>User/Reporter:</strong> {{ $group['user_code'] ?? '-' }}{{ !empty($group['user_name']) ? ' · ' . $group['user_name'] : '' }}</span>
                            <span><strong>Report Type:</strong> {{ ucfirst($group['report_type']) }}</span>
                            <span><strong>Animal Type:</strong> {{ $group['animal_type'] }}</span>
                            <span><strong>Location/Area:</strong> {{ $group['location_summary'] }}</span>
                            <span><strong>Matching Date Range:</strong> {{ $group['matching_date_range'] }}</span>
                        </div>

                        <div class="related-report-list">
                            @foreach ($group['reports'] as $report)
                                <div class="related-report-item">
                                    <div class="related-report-summary">
                                        <h5>Report ID {{ $report['report_code'] }} — {{ ucfirst($report['report_type']) }} ({{ $report['animal_type'] }})</h5>
                                        <span class="muted">Reporter: {{ $report['user_code'] ?? '-' }}{{ !empty($report['user_name']) ? ' · ' . $report['user_name'] : '' }}</span>
                                        <span class="muted">Submitted: {{ $report['created_at'] }}</span>
                                        <span class="muted">Location: {{ $report['location_text'] ?? 'Unknown' }}</span>
                                        <span class="muted">Current Status: {{ str_replace('_', ' ', $report['status']) }}</span>
                                    </div>
                                    <div class="report-actions">
                                        <button class="btn-ghost" type="button" onclick="openReport({{ $report['id'] }})">View</button>
                                        <button class="btn-ghost" type="button" onclick="viewReportInAllReports({{ $report['id'] }})">View in All Reports</button>
                                        <button class="btn-status secondary" type="button" onclick="confirmRemoveFromGroup('user_multiple', '{{ $group['group_key'] }}', {{ $report['id'] }}, '{{ addslashes($report['report_code']) }}')">Remove from Group</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div id="groupRelatedReportsPanel" class="report-tab-panel hidden">
        @if (empty($group_related_cases))
            <div class="muted">No group related reports were detected yet.</div>
        @else
            <div class="related-report-groups">
                @foreach ($group_related_cases as $case)
                    <div class="related-group-card detection-group-card">
                        <div class="related-group-header">
                            <div>
                                <h4>Case {{ $case['case_number'] }}</h4>
                                <div class="muted">{{ $case['report_count'] }} potentially related reports</div>
                            </div>
                            <span class="report-case-badge {{ $case['matching_state'] }}">{{ ucwords(str_replace('_', ' ', $case['matching_state'])) }}</span>
                        </div>

                        <div class="related-group-meta">
                            <span><strong>Case Number:</strong> {{ $case['case_number'] }}</span>
                            <span><strong>Relationship:</strong> Potentially Related</span>
                            <span><strong>Report Type:</strong> {{ ucfirst($case['report_type']) }}</span>
                            <span><strong>Animal Type:</strong> {{ $case['animal_type'] }}</span>
                            <span><strong>Location/Area:</strong> {{ $case['location_summary'] }}</span>
                            <span><strong>Matching Window:</strong> {{ $case['matching_window_start'] }} - {{ $case['matching_window_end'] }}</span>
                        </div>

                        <div class="related-report-list">
                            @foreach ($case['reports'] as $report)
                                <div class="related-report-item">
                                    <div class="related-report-summary">
                                        <h5>Report ID {{ $report['report_code'] }} — {{ ucfirst($report['report_type']) }} ({{ $report['animal_type'] }})</h5>
                                        <span class="muted">Reporter: {{ $report['user_code'] ?? '-' }}{{ !empty($report['user_name']) ? ' · ' . $report['user_name'] : '' }}</span>
                                        <span class="muted">Submitted: {{ $report['created_at'] }}</span>
                                        <span class="muted">Location: {{ $report['location_text'] ?? 'Unknown' }}</span>
                                        <span class="muted">Current Status: {{ str_replace('_', ' ', $report['status']) }}</span>
                                    </div>
                                    <div class="report-actions">
                                        <button class="btn-ghost" type="button" onclick="openReport({{ $report['id'] }})">View</button>
                                        <button class="btn-ghost" type="button" onclick="viewReportInAllReports({{ $report['id'] }})">View in All Reports</button>
                                        <button class="btn-status secondary" type="button" onclick="confirmRemoveFromGroup('group_related', '{{ $case['case_id'] }}', {{ $report['id'] }}, '{{ addslashes($report['report_code']) }}')">Remove from Group</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>