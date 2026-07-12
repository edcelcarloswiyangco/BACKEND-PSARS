<div class="card report-management-card">
    <div class="tabs report-tabs" role="tablist" aria-label="Report management tabs">
        <button class="tab active" type="button" data-report-tab="all" aria-selected="true">All Reports</button>
        <button class="tab" type="button" data-report-tab="related" aria-selected="false">Related Reports</button>
    </div>

    <div id="allReportsPanel" class="report-tab-panel">
        <div class="report-toolbar" >
            <div>
                
            </div>
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

    <div id="relatedReportsPanel" class="report-tab-panel hidden">
        @if (empty($related_report_groups))
            <div class="muted">No related reports were detected yet.</div>
        @else
            <div class="related-report-groups">
                @foreach ($related_report_groups as $group)
                    <div class="related-group-card">
                        <div class="related-group-header">
                            <div>
                                <h4>Related Reports Group</h4>
                                <div class="muted">{{ $group['report_count'] }} reports detected on {{ $group['date'] }}</div>
                            </div>
                            <span class="related-group-badge">{{ $group['relationship_label'] }}</span>
                        </div>

                        <div class="related-group-meta">
                            <span><strong>Report Type:</strong> {{ $group['report_type'] }}</span>
                            <span><strong>Animal Type:</strong> {{ $group['animal_type'] }}</span>
                            <span><strong>Date:</strong> {{ $group['date'] }}</span>
                            <span><strong>General Location:</strong> {{ $group['location_summary'] }}</span>
                            <span><strong>Relationship:</strong> {{ $group['relationship_label'] }}</span>
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
