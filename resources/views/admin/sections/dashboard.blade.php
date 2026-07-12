<div class="summary-grid">
    <div class="stat-card">
        <strong>Total Users</strong>
        <div class="muted">{{ $summary['total_users'] ?? 0 }} accounts</div>
    </div>
    <div class="stat-card">
        <strong>Total Reports</strong>
        <div class="muted">{{ $summary['total_reports'] ?? 0 }} reports submitted</div>
    </div>
    <div class="stat-card">
        <strong>Total Pets</strong>
        <div class="muted">{{ $summary['total_pets'] ?? 0 }} registered pets</div>
    </div>
</div>

<div class="analytics-grid">
    <div class="card metric-card pending">
        <div>
            <strong>Pending Today</strong>
            <div class="metric-value">{{ $analytics['today_status_counts']['pending'] ?? 0 }}</div>
        </div>
        <div class="metric-note">Reports created today</div>
    </div>
    <div class="card metric-card in-progress">
        <div>
            <strong>In Progress Today</strong>
            <div class="metric-value">{{ $analytics['today_status_counts']['in_progress'] ?? 0 }}</div>
        </div>
        <div class="metric-note">Reports created today</div>
    </div>
    <div class="card metric-card resolved">
        <div>
            <strong>Resolved Today</strong>
            <div class="metric-value">{{ $analytics['today_status_counts']['resolved'] ?? 0 }}</div>
        </div>
        <div class="metric-note">Reports created today</div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card wide">
        <div class="chart-toolbar">
            <div>
                <h3>Report Trend</h3>
                <div class="muted">Daily report volume for the last 7 days, this month, or today</div>
            </div>
            <select id="trendRange" class="chart-select" aria-label="Select trend range">
                <option value="today" selected>Today</option>
                <option value="seven_days">Last 7 Days</option>
                <option value="month">This Month</option>
            </select>
        </div>
        <div id="trendChart" class="chart-box"></div>
    </div>

    <div class="chart-card">
        <h3>Animal Type Distribution</h3>
        <div id="animalTypeChart" class="chart-box tall"></div>
    </div>

    <div class="chart-card">
        <h3>Report status</h3>
        <div id="statusBarChart" class="chart-box tall"></div>
    </div>

    <div class="chart-card">
        <h3>Vaccinated vs Unvaccinated</h3>
        <div id="vaccinationChart" class="chart-box tall"></div>
    </div>

    <div class="chart-card">
        <h3>Most Registered Animal Type</h3>
        <div id="registeredPetTypeChart" class="chart-box tall"></div>
    </div>
</div>
