<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin · StrayConnect</title>
    <style>
        :root{--bg:#f4f7f7;--panel:#fff;--text:#102a43;--muted:#627d98;--accent:#0f766e;--danger:#b42318;--border:#d9e2ec}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,sans-serif;background:linear-gradient(180deg,#f8fbfc,var(--bg));color:var(--text)}
        .app{display:flex;min-height:100vh;align-items:flex-start}
        .sidebar{width:260px;background:var(--panel);border-right:1px solid var(--border);padding:22px;display:flex;flex-direction:column;gap:18px;position:sticky;top:0;height:100vh;overflow-y:auto;align-self:flex-start}
        .brand{font-weight:900;color:var(--accent);font-size:18px}
        .nav{display:flex;flex-direction:column;gap:6px}
        .nav a{display:block;padding:10px 12px;border-radius:10px;color:var(--text);text-decoration:none;font-weight:700}
        .nav a.active{background:#ecfdf3;color:var(--accent)}
        .content{flex:1;min-width:0;padding:28px}
        .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
        .card{background:var(--panel);border:1px solid rgba(15,23,42,.06);border-radius:12px;padding:18px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:12px 14px;border-bottom:1px solid var(--border);text-align:left}
        th{font-size:12px;color:var(--muted);text-transform:uppercase}
        .btn{background:var(--accent);color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none;font-weight:700;border:none;cursor:pointer}
        .btn-ghost{background:transparent;border:1px solid var(--border);padding:8px 12px;border-radius:8px;cursor:pointer}
        .btn-danger{background:var(--danger);color:#fff;border:none}
        .muted{color:var(--muted)}
        .status.active{color:#027a48;font-weight:700}
        .status.inactive{color:#b45309;font-weight:700}
        .user-status-badge{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:800;letter-spacing:.02em;text-transform:uppercase}
        .user-status-badge.active{background:#ecfdf3;color:#027a48}
        .user-status-badge.suspended{background:#fffaeb;color:#b54708}
        .user-status-badge.deactivated{background:#fef2f2;color:#b42318}
        .summary-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:16px;margin-bottom:18px}
        .stat-card{background:#f8fffc;border:1px solid #d9f7ec;border-radius:14px;padding:18px}
        .stat-card strong{display:block;margin-bottom:6px}
        .analytics-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-bottom:18px}
        .metric-card{color:#fff;border:none;min-height:128px;display:flex;flex-direction:column;justify-content:space-between}
        .metric-card strong{display:block;font-size:15px;letter-spacing:.02em;text-transform:uppercase;margin-bottom:6px}
        .metric-value{font-size:34px;font-weight:900;line-height:1}
        .metric-note{font-size:13px;opacity:.88;margin-top:10px}
        .metric-card.pending{background:linear-gradient(135deg,#ef4444,#b91c1c)}
        .metric-card.in-progress{background:linear-gradient(135deg,#f59e0b,#d97706)}
        .metric-card.resolved{background:linear-gradient(135deg,#10b981,#047857)}
        .charts-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-top:18px}
        .chart-card{background:var(--panel);border:1px solid rgba(15,23,42,.06);border-radius:16px;padding:18px;box-shadow:0 10px 25px rgba(15,23,42,.04)}
        .chart-card.wide{grid-column:1 / -1}
        .chart-card h3{margin:0 0 4px 0;font-size:17px}
        .chart-card .muted{font-size:13px}
        .chart-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px}
        .chart-select{border:1px solid var(--border);border-radius:10px;background:#fff;padding:10px 12px;font:inherit;color:var(--text)}
        .chart-box{min-height:320px}
        .chart-box.tall{min-height:360px}
        .report-toolbar{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:16px}
        .report-filters{display:flex;flex-direction:column;gap:12px;flex:1;min-width:min(100%,560px)}
        .report-status-filters{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
        .report-status-label{font-size:12px;font-weight:800;letter-spacing:.02em;text-transform:uppercase;color:var(--muted);margin-right:4px}
        .status-checkbox{display:inline-flex;align-items:center;gap:8px;padding:9px 12px;border:1px solid var(--border);border-radius:999px;background:#fff;cursor:pointer;font-weight:700;color:var(--text)}
        .status-checkbox input{accent-color:var(--accent)}
        .report-management-card{display:flex;flex-direction:column;gap:16px}
        .report-tabs{margin-bottom:0}
        .report-tab-panel{display:flex;flex-direction:column;gap:16px}
        .report-search-wrap{min-width:240px;max-width:360px;flex:1}
        .report-search{width:100%;border:1px solid var(--border);border-radius:10px;background:#fff;padding:10px 12px;font:inherit;color:var(--text)}
        .user-search-wrap{min-width:240px;max-width:420px;flex:1}
        .user-search{width:100%;border:1px solid var(--border);border-radius:10px;background:#fff;padding:10px 12px;font:inherit;color:var(--text)}
        .user-no-results{display:none;margin-top:6px;padding:14px;border:1px dashed var(--border);border-radius:12px;background:#fbfdff}
        .report-no-results{display:none;margin-top:6px;padding:14px;border:1px dashed var(--border);border-radius:12px;background:#fbfdff}
        .related-report-groups{display:flex;flex-direction:column;gap:16px}
        .related-group-card{border:1px solid var(--border);border-radius:16px;background:#f8fbfd;padding:18px;box-shadow:0 8px 22px rgba(15,23,42,.04)}
        .related-group-header{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px}
        .related-group-header h4{margin:0;font-size:16px}
        .related-group-badge{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;background:#ecfdf3;color:var(--accent);font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.02em}
        .related-group-meta{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:14px;color:#475569;line-height:1.5}
        .related-group-meta span{display:inline-flex;align-items:center;gap:4px;padding:8px 10px;border:1px solid #d9e2ec;border-radius:999px;background:#fff}
        .related-group-meta strong{color:var(--text)}
        .related-report-list{display:flex;flex-direction:column;gap:12px}
        .related-report-item{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:14px 16px;border:1px solid var(--border);border-radius:12px;background:#fff}
        .related-report-summary{flex:1;min-width:0}
        .related-report-summary h5{margin:0 0 4px 0;font-size:15px}
        .related-report-summary .muted{display:block;font-size:13px;margin-top:4px;color:var(--muted)}
        .report-status-pill{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.02em}
        .report-status-pill.pending{background:#fef3f2;color:#b42318}
        .report-status-pill.in_progress{background:#fffaeb;color:#b54708}
        .report-status-pill.resolved{background:#ecfdf3;color:#027a48}
        .report-actions{white-space:nowrap;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .report-status-row-action,.btn-status{background:var(--accent);color:#fff;border:none;padding:8px 12px;border-radius:8px;font-weight:700;cursor:pointer}
        .btn-status.secondary,.report-status-row-action.secondary{background:#f59e0b}
        .report-status-row-action[disabled],.btn-status[disabled]{opacity:.55;cursor:not-allowed}
        .modal-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px}
        .modal-summary-card{border:1px solid var(--border);border-radius:14px;background:#f8fafc;padding:14px}
        .modal-summary-card strong{display:block;margin-bottom:6px}
        .modal-section-title{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
        .modal-section-title strong{font-size:15px}
        .modal-section-copy{margin:8px 0 0;color:var(--muted);line-height:1.5}
        .account-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
        .account-actions .btn,.account-actions .btn-ghost,.account-actions .btn-danger{min-width:160px;justify-content:center}
        .tabs{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
        .tab{background:transparent;border:1px solid var(--border);border-radius:999px;padding:10px 16px;color:var(--text);cursor:pointer;font-weight:700}
        .tab.active{background:#ecfdf3;color:var(--accent);border-color:#86efac}
        .tab-panel.hidden,.hidden{display:none !important}
        .report-card{border:1px solid #d9e2ec;border-radius:12px;padding:16px;margin-bottom:16px;background:#f9fbfd}
        .report-card h4{margin:0 0 8px 0;font-size:16px}
        .report-row{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border:1px solid var(--border);border-radius:12px;background:#fff;margin-bottom:12px;gap:16px}
        .report-summary{flex:1;min-width:0}
        .report-summary h4{margin:0 0 4px 0;font-size:15px}
        .report-summary .muted{display:block;font-size:13px;margin-top:4px;color:var(--muted)}
        .report-meta{margin-bottom:10px;color:#475569;line-height:1.5}
        .report-media{display:flex;flex-wrap:wrap;gap:12px;margin-top:12px}
        .report-media img{width:calc(50% - 6px);max-width:220px;max-height:220px;border-radius:12px;object-fit:cover;border:1px solid #d9e2ec}
        .report-media video{width:100%;max-width:420px;border-radius:12px;border:1px solid #d9e2ec}
        .announcement-header{display:flex;flex-direction:column;align-items:flex-start;gap:12px}
        .announcement-header-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
        .announcement-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px}
        .announcement-card{border:1px solid var(--border);border-radius:16px;overflow:hidden;background:#fff;box-shadow:0 10px 25px rgba(15,23,42,.04)}
        .announcement-card img{width:100%;height:180px;object-fit:cover;display:block;background:#eef2f7}
        .announcement-body{padding:16px}
        .announcement-meta{display:flex;justify-content:space-between;gap:10px;align-items:center;margin-top:10px;color:var(--muted);font-size:13px;flex-wrap:wrap}
        .announcement-actions{display:flex;gap:8px;flex-wrap:nowrap;align-items:center;justify-content:flex-start;margin-top:14px}
        .announcement-actions form{margin:0;display:flex;align-items:center}
        .announcement-actions > *{flex:0 0 auto}
        .announcement-actions .btn-ghost{white-space:nowrap}
        .announcement-tabs{margin-top:8px}
        .announcement-tab-panels{margin-top:16px}
        .announcement-preview{display:none;align-items:center;justify-content:center;min-height:180px;border:1px dashed var(--border);border-radius:14px;background:#f8fafc;overflow:hidden}
        .announcement-preview img{width:100%;height:100%;max-height:260px;object-fit:cover;display:block}
        .announcement-preview.has-image{display:flex}
        .announcement-form{display:grid;grid-template-columns:1fr;gap:12px;margin-bottom:18px}
        .modal-card.narrow{max-width:680px}
        .modal-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-top:18px}
        .modal-actions .btn,.modal-actions .btn-ghost{min-width:108px;justify-content:center}
        .toast{position:fixed;top:24px;right:24px;z-index:1200;max-width:min(420px,calc(100vw - 32px));background:#fff;border:1px solid var(--border);border-radius:16px;box-shadow:0 20px 40px rgba(15,23,42,.16);padding:16px;display:flex;gap:14px;align-items:flex-start}
        .toast.success{border-color:#bbf7d0;background:#f0fdf4}
        .toast.error{border-color:#fecaca;background:#fef2f2}
        .toast-content{flex:1;min-width:0}
        .toast-title{font-size:13px;font-weight:800;letter-spacing:.02em;text-transform:uppercase;margin-bottom:4px}
        .toast-message{margin:0;color:var(--text);line-height:1.45}
        .toast-actions{display:flex;gap:8px;align-items:center;flex-shrink:0}
        .toast-close{background:transparent;border:none;color:var(--muted);font-size:18px;line-height:1;cursor:pointer;padding:4px 6px}
        .toast-ok{padding:8px 14px;white-space:nowrap}
        .field{display:flex;flex-direction:column;gap:6px}
        .field label{font-weight:700;font-size:13px}
        .field input,.field textarea,.field select{width:100%;border:1px solid var(--border);border-radius:12px;background:#fff;padding:12px 14px;font:inherit;color:var(--text)}
        .field textarea{min-height:140px;resize:vertical}
        .field-help{font-size:12px;color:var(--muted);line-height:1.4}
        .modal-section{display:flex;flex-direction:column;gap:14px}
        .modal-section + .modal-section{margin-top:14px;padding-top:14px;border-top:1px solid var(--border)}
        .suspension-summary-text{font-weight:700;color:var(--text)}
        .checkbox-row{display:flex;align-items:center;gap:8px;font-weight:700;color:var(--text)}
        .modal{position:fixed;inset:0;background:rgba(2,6,23,.5);display:flex;align-items:center;justify-content:center;padding:20px;visibility:hidden;opacity:0;transition:opacity .15s ease,visibility .15s}
        .modal.show{visibility:visible;opacity:1}
        .modal-card{background:#fff;border-radius:12px;max-width:880px;width:100%;padding:18px;max-height:calc(100vh - 60px);overflow:auto}
        .modal-card.narrow{max-width:720px}
        .modal-card.wide{max-width:980px}
        @media (max-width:800px){.sidebar{display:none}.content{padding:16px}}
    </style>
</head>
<body>
    <div class="app">
        <?php $section = request('section', 'dashboard'); ?>
        <aside class="sidebar">
            <div class="brand">STRAYCONNECT</div>
            <nav class="nav">
                <a href="{{ route('admin.dashboard', ['section' => 'dashboard']) }}" class="{{ $section === 'dashboard' ? 'active' : '' }}">DASHBOARD</a>
                <a href="{{ route('admin.dashboard', ['section' => 'user-management']) }}" class="{{ $section === 'user-management' ? 'active' : '' }}">USER MANAGEMENT</a>
                <a href="{{ route('admin.dashboard', ['section' => 'pet-directory']) }}" class="{{ $section === 'pet-directory' ? 'active' : '' }}">PET DIRECTORY</a>
                <a href="{{ route('admin.dashboard', ['section' => 'report-management']) }}" class="{{ $section === 'report-management' ? 'active' : '' }}">REPORT MANAGEMENT</a>
                <a href="{{ route('admin.dashboard', ['section' => 'announcements']) }}" class="{{ $section === 'announcements' ? 'active' : '' }}">ANNOUNCEMENTS</a>
                <a href="{{ route('admin.dashboard', ['section' => 'settings']) }}" class="{{ $section === 'settings' ? 'active' : '' }}">SETTINGS</a>
            </nav>
            <div class="muted" style="margin-top:auto">Signed in as<br><strong>{{ auth('admin')->user()->email }}</strong></div>
        </aside>

        <main class="content">
            <div class="topbar">
                <div>
                    @if ($section === 'dashboard')
                        <h2 style="margin:0">Dashboard</h2>
                        
                    @elseif ($section === 'user-management')
                        <h2 style="margin:0">User Management</h2>
                        
                    @elseif ($section === 'pet-directory')
                        <h2 style="margin:0">Pet Directory</h2>
                       
                    @elseif ($section === 'report-management')
                        <h2 style="margin:0">Report Management</h2>
                        
                    @elseif ($section === 'announcements')
                        <div class="announcement-header">
                            <h2 style="margin:0">Announcements</h2>
                            <div class="announcement-header-actions">
                                <div class="tabs" role="tablist" aria-label="Announcement tabs">
                                    <button class="tab active" type="button" data-announcement-tab="create">Creation</button>
                                    <button class="tab" type="button" data-announcement-tab="feed">Feed List</button>
                                </div>
                            </div>
                        </div>
                    @else
                        <h2 style="margin:0">Settings</h2>
                    @endif
                </div>
            </div>

            @php
                $toastType = null;
                $toastMessage = null;

                if (session('success')) {
                    $toastType = 'success';
                    $toastMessage = session('success');
                } elseif (session('error')) {
                    $toastType = 'error';
                    $toastMessage = session('error');
                } elseif ($errors->any()) {
                    $toastType = 'error';
                    $toastMessage = $errors->first();
                }
            @endphp

            @if ($toastMessage)
                <div class="toast {{ $toastType }}" role="alert" aria-live="assertive" data-toast>
                    <div class="toast-content">
                        <div class="toast-title">{{ $toastType === 'success' ? 'Success' : 'Action failed' }}</div>
                        <p class="toast-message">{{ $toastMessage }}</p>
                    </div>
                    <div class="toast-actions">
                        
                        <button class="btn toast-ok" type="button" data-toast-close>OK</button>
                    </div>
                </div>
            @endif

            @if ($section === 'dashboard')
                @include('admin.sections.dashboard')
            @elseif ($section === 'user-management')
                @include('admin.sections.user-management')
            @elseif ($section === 'pet-directory')
                @include('admin.sections.pet-directory')
            @elseif ($section === 'report-management')
                @include('admin.sections.report-management')
            @elseif ($section === 'announcements')
                @include('admin.sections.announcements')
            @else
                @include('admin.sections.settings')
            @endif
        </main>
    </div>

    <div id="userModal" class="modal" role="dialog" aria-hidden="true">
        <div class="modal-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <div>
                    <h3 id="modalName" style="margin:0">User Details</h3>
                    <div class="muted" id="modalSubtitle">Overview</div>
                </div>
                <button onclick="closeModal()" class="btn-ghost">Close</button>
            </div>
            <div id="tabOverview" class="tab-panel">
                <div><strong>Email:</strong> <span id="modalEmail"></span></div>
                <div><strong>Contact:</strong> <span id="modalContact"></span></div>
                <div><strong>Address:</strong> <span id="modalAddress"></span></div>
                <div><strong>Status:</strong> <span id="modalStatus" class="user-status-badge"></span></div>
                <div><strong>Registered:</strong> <span id="modalRegistered"></span></div>
                <div><strong>Pets count:</strong> <span id="modalPetsCount"></span></div>
                <div><strong>Reports count:</strong> <span id="modalReportsCount"></span></div>
            </div>

            <div class="modal-section" style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
                <div class="modal-section-title">
                    <strong>Action History</strong>
                </div>
                <div id="modalActionHistory" class="modal-section-copy">No action history yet.</div>
            </div>

            <div class="modal-section">
                <div class="modal-section-title">
                    <strong>Account Actions</strong>
                </div>
                <div id="modalAccountActions" class="account-actions"></div>
            </div>
        </div>
    </div>

    <div id="reportModal" class="modal" role="dialog" aria-hidden="true">
        <div class="modal-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <div>
                    <h3 id="reportModalTitle" style="margin:0">Report Details</h3>
                    <div class="muted" id="reportModalSubtitle"></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <button id="reportStatusAction" class="btn-status" type="button" disabled>Resolved</button>
                    <button onclick="closeReportModal()" class="btn-ghost">Close</button>
                </div>
            </div>
            <div id="reportDetails"></div>
        </div>
    </div>

    <div id="suspensionModal" class="modal" role="dialog" aria-hidden="true">
        <div class="modal-card narrow">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px">
                <div>
                    <h3 id="suspensionModalTitle" style="margin:0">Suspend Account</h3>
                    <div class="muted" id="suspensionModalSubtitle">Choose a reason and duration before continuing.</div>
                </div>
                <button type="button" class="btn-ghost" data-suspension-close>Close</button>
            </div>

            <div class="modal-grid" style="margin-bottom:14px">
                <div class="modal-summary-card">
                    <strong>Account</strong>
                    <div id="suspensionUserName" class="suspension-summary-text">-</div>
                    <div id="suspensionUserMeta" class="muted">-</div>
                </div>
                <div class="modal-summary-card">
                    <strong>Status</strong>
                    <div id="suspensionUserStatus" class="suspension-summary-text">-</div>
                    <div id="suspensionUserSummary" class="muted">-</div>
                </div>
            </div>

            <form id="suspensionForm" class="announcement-form" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" id="suspensionTargetUserId" name="user_id" value="">

                <div class="field">
                    <label for="suspensionReason">Reason</label>
                    <textarea id="suspensionReason" name="suspension_reason" required placeholder="Enter the reason for this suspension."></textarea>
                </div>

                <div class="field">
                    <label for="suspensionType">Duration type</label>
                    <select id="suspensionType" name="suspension_type" required>
                        <option value="days">Days</option>
                        <option value="weeks">Weeks</option>
                        <option value="months">Months</option>
                        <option value="permanent">Indefinite suspension</option>
                    </select>
                </div>

                <div class="field" id="suspensionValueField">
                    <label for="suspensionValue">Duration value</label>
                    <input id="suspensionValue" name="suspension_value" type="number" min="1" step="1" value="1">
                    <div class="field-help">Choose the number of days, weeks, or months.</div>
                </div>

                <div class="field">
                    <label for="suspensionNote">Optional notes</label>
                    <textarea id="suspensionNote" name="suspension_note" placeholder="Add any extra context or internal notes."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-ghost" data-suspension-close>Cancel</button>
                    <button class="btn" type="submit">Suspend Account</button>
                </div>
            </form>
        </div>
    </div>

    <div id="suspensionConfirmModal" class="modal" role="dialog" aria-hidden="true">
        <div class="modal-card narrow">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px">
                <div>
                    <h3 style="margin:0">Confirm Suspension</h3>
                    <div class="muted">Are you sure you want to suspend this account?</div>
                </div>
                <button type="button" class="btn-ghost" data-suspension-confirm-cancel>Close</button>
            </div>

            <div class="modal-summary-card" style="margin-bottom:14px">
                <strong id="suspensionConfirmUserName">-</strong>
                <div id="suspensionConfirmSummary" class="muted">-</div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-ghost" data-suspension-confirm-cancel>Cancel</button>
                <button id="confirmSuspensionButton" class="btn btn-danger" type="button">Confirm Suspension</button>
            </div>
        </div>
    </div>

    <div id="announcementEditModal" class="modal" role="dialog" aria-hidden="true">
        <div class="modal-card narrow">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px">
                <div>
                    <h3 id="announcementEditTitle" style="margin:0">Edit Announcement</h3>
                    
                </div>
                
            </div>
            <form id="announcementEditForm" class="announcement-form" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="field">
                    <label for="announcement-edit-title">Title</label>
                    <input id="announcement-edit-title" name="title" type="text" maxlength="255" required>
                </div>

                <div class="field">
                    <label for="announcement-edit-description">Description</label>
                    <textarea id="announcement-edit-description" name="description" required></textarea>
                </div>

                <div class="field">
                    <label for="announcement-edit-image">Replace image</label>
                    <input id="announcement-edit-image" name="image" type="file" accept="image/*" data-image-preview-input="modal-edit">
                </div>

                <div id="announcement-edit-preview" class="announcement-preview" aria-live="polite">
                    <img alt="Selected announcement preview" hidden>
                    <div class="muted">Current announcement image will show here.</div>
                </div>

                <label class="checkbox-row">
                    <p id="announcement-edit-published" type="" name="is_published" >
                    
                </label>

                <div class="modal-actions">
                    <button type="button" class="btn-ghost" data-announcement-edit-close>Cancel</button>
                    <button class="btn" type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="announcementDeleteModal" class="modal" role="dialog" aria-hidden="true">
        <div class="modal-card narrow">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px">
                <div>
                    <h3 style="margin:0">Delete Announcement</h3>
                    <div class="muted">This action cannot be undone.</div>
                </div>
                <button type="button" class="btn-ghost" data-announcement-delete-close>Close</button>
            </div>
            <p class="muted" id="announcementDeleteMessage" style="margin-top:0">Are you sure you want to delete this announcement?</p>
            <form id="announcementDeleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="button" class="btn-ghost" data-announcement-delete-close>Cancel</button>
                    <button class="btn" type="submit" style="background:var(--danger)">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const analytics = @json($analytics ?? []);
        const trendData = analytics.trend_data || { today: { labels: [], data: [] }, seven_days: { labels: [], data: [] }, month: { labels: [], data: [] } };
        const statusBreakdown = analytics.status_breakdown || { pending: 0, in_progress: 0, resolved: 0 };
        const animalTypeDistribution = analytics.animal_type_distribution || {};
        const petVaccinationDistribution = analytics.pet_vaccination_distribution || { vaccinated: 0, unvaccinated: 0 };
        const registeredPetTypeDistribution = analytics.registered_pet_type_distribution || {};

        let trendChart;
        let statusBarChart;
        let animalTypeChart;
        let vaccinationChart;
        let registeredPetTypeChart;

        function buildTrendOptions(seriesData, labels) {
            return {
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    fontFamily: 'Inter, system-ui, -apple-system, Segoe UI, sans-serif',
                },
                series: [{ name: 'Reports', data: seriesData }],
                stroke: { curve: 'smooth', width: 5 },
                colors: ['#0f766e'],
                markers: { size: 6, colors: ['#0f766e'], strokeColors: '#fff', strokeWidth: 2, hover: { size: 8 } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
                xaxis: { categories: labels, labels: { style: { colors: '#64748b' } } },
                yaxis: { labels: { style: { colors: '#64748b' } } },
                tooltip: { theme: 'light' },
                fill: { type: 'solid', opacity: 0.2 },
            };
        }

        function renderTrendChart(range) {
            const dataset = trendData[range] || trendData.seven_days;
            const options = buildTrendOptions(dataset.data || [], dataset.labels || []);

            if (!trendChart) {
                trendChart = new ApexCharts(document.querySelector('#trendChart'), options);
                trendChart.render();
                return;
            }

            trendChart.updateOptions({ xaxis: { categories: options.xaxis.categories } });
            trendChart.updateSeries([{ name: 'Reports', data: options.series[0].data }]);
        }

        function renderStatusBarChart() {
            const options = {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false },
                    fontFamily: 'Inter, system-ui, -apple-system, Segoe UI, sans-serif',
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '50%',
                        borderRadius: 8,
                    },
                },
                colors: ['#ef4444', '#f59e0b', '#10b981'],
                series: [{
                    name: 'Reports',
                    data: [
                        Number(statusBreakdown.pending || 0),
                        Number(statusBreakdown.in_progress || 0),
                        Number(statusBreakdown.resolved || 0),
                    ],
                }],
                xaxis: {
                    categories: ['Pending', 'In Progress', 'Resolved'],
                    labels: { style: { colors: '#64748b' } },
                },
                dataLabels: { enabled: true },
                grid: { borderColor: '#e2e8f0' },
                tooltip: { theme: 'light' },
            };

            statusBarChart = new ApexCharts(document.querySelector('#statusBarChart'), options);
            statusBarChart.render();
        }

        function renderAnimalTypeChart() {
            const labels = Object.keys(animalTypeDistribution);
            const values = labels.map(label => Number(animalTypeDistribution[label] || 0));
            const hasData = labels.length > 0;

            const options = {
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'Inter, system-ui, -apple-system, Segoe UI, sans-serif',
                },
                labels: hasData ? labels : ['No data'],
                series: hasData ? values : [1],
                colors: ['#0f766e', '#0284c7', '#8b5cf6', '#f59e0b', '#ef4444', '#14b8a6', '#64748b'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true },
                tooltip: { theme: 'light' },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: () => values.reduce((sum, value) => sum + value, 0).toString(),
                                },
                            },
                        },
                    },
                },
                responsive: [{
                    breakpoint: 768,
                    options: { legend: { position: 'bottom' } },
                }],
            };

            animalTypeChart = new ApexCharts(document.querySelector('#animalTypeChart'), options);
            animalTypeChart.render();
        }

        function renderVaccinationChart() {
            const options = {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false },
                    fontFamily: 'Inter, system-ui, -apple-system, Segoe UI, sans-serif',
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 8,
                    },
                },
                colors: ['#22c55e', '#ef4444'],
                series: [{
                    name: 'Pets',
                    data: [
                        Number(petVaccinationDistribution.vaccinated || 0),
                        Number(petVaccinationDistribution.unvaccinated || 0),
                    ],
                }],
                xaxis: {
                    categories: ['Vaccinated', 'Unvaccinated'],
                    labels: { style: { colors: '#64748b' } },
                },
                dataLabels: { enabled: true },
                grid: { borderColor: '#e2e8f0' },
                tooltip: { theme: 'light' },
            };

            vaccinationChart = new ApexCharts(document.querySelector('#vaccinationChart'), options);
            vaccinationChart.render();
        }

        function renderRegisteredPetTypeChart() {
            const labels = Object.keys(registeredPetTypeDistribution);
            const values = labels.map(label => Number(registeredPetTypeDistribution[label] || 0));
            const hasData = labels.length > 0;

            const options = {
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'Inter, system-ui, -apple-system, Segoe UI, sans-serif',
                },
                labels: hasData ? labels : ['No data'],
                series: hasData ? values : [1],
                colors: ['#0891b2', '#4f46e5', '#16a34a', '#f59e0b', '#ef4444', '#9333ea', '#0f766e'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true },
                tooltip: { theme: 'light' },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: () => values.reduce((sum, value) => sum + value, 0).toString(),
                                },
                            },
                        },
                    },
                },
            };

            registeredPetTypeChart = new ApexCharts(document.querySelector('#registeredPetTypeChart'), options);
            registeredPetTypeChart.render();
        }

        function reportLabel(status) {
            return status ? status.replace('_', ' ') : 'unknown';
        }

        function nextStatus(status) {
            if (status === 'pending') {
                return 'in_progress';
            }

            if (status === 'in_progress') {
                return 'resolved';
            }

            return null;
        }

        function updateReportModalAction(status) {
            const actionButton = document.getElementById('reportStatusAction');
            if (!actionButton) {
                return;
            }

            const next = nextStatus(status);
            if (!next) {
                actionButton.textContent = 'Resolved';
                actionButton.disabled = true;
                actionButton.dataset.nextStatus = '';
                return;
            }

            actionButton.disabled = false;
            actionButton.textContent = next === 'in_progress' ? 'Mark In Progress' : 'Mark Resolved';
            actionButton.dataset.nextStatus = next;
        }

        function syncReportRow(id, status) {
            const row = document.querySelector(`[data-report-id="${id}"]`);
            if (!row) {
                return;
            }

            const statusPill = row.querySelector('.report-status-pill');
            if (statusPill) {
                statusPill.className = `report-status-pill ${status}`;
                statusPill.textContent = reportLabel(status);
            }

            const button = row.querySelector('.report-status-row-action');
            if (button) {
                const next = nextStatus(status);
                if (!next) {
                    button.textContent = 'Resolved';
                    button.disabled = true;
                    button.dataset.nextStatus = '';
                } else {
                    button.disabled = false;
                    button.textContent = next === 'in_progress' ? 'Mark In Progress' : 'Mark Resolved';
                    button.dataset.nextStatus = next;
                }
            }
        }

        function submitReportStatusUpdate(reportId, status) {
            return fetch(`{{ url('/admin/reports') }}/${reportId}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status }),
            }).then(async response => {
                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload.message || 'Unable to update report status');
                }
                return payload.data;
            });
        }

        function changeReportStatus(reportId, status) {
            if (!status) {
                return;
            }

            submitReportStatusUpdate(reportId, status)
                .then(data => {
                    const report = reportData.find(item => item.id === reportId);
                    if (report) {
                        report.status = data.status;
                    }
                    syncReportRow(reportId, data.status);
                    updateReportModalAction(data.status);
                    const subtitle = document.getElementById('reportModalSubtitle');
                    if (subtitle) {
                        subtitle.textContent = `${report?.created_at || ''} • ${reportLabel(data.status)}`;
                    }
                })
                .catch(error => {
                    alert(error.message || 'Unable to update report status');
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Only render charts if they exist on the page
            if (document.getElementById('trendChart')) renderTrendChart('today');
            if (document.getElementById('statusChart')) renderStatusBarChart();
            if (document.getElementById('animalTypeChart')) renderAnimalTypeChart();
            if (document.getElementById('vaccinationChart')) renderVaccinationChart();
            if (document.getElementById('registeredPetTypeChart')) renderRegisteredPetTypeChart();
            initToast();
            initReportManagementTabs();
            initAnnouncementTabs();
            initImagePreviews();
            initAnnouncementActionModals();

            const trendRange = document.getElementById('trendRange');
            if (trendRange) {
                trendRange.addEventListener('change', (event) => {
                    renderTrendChart(event.target.value);
                });
            }
        });

        function openUser(id) {
            fetch('/admin/users/' + id + '/details')
                .then(r => r.json())
                .then(data => {
                    renderUserModal(data);
                    document.getElementById('tabOverview').classList.remove('hidden');
                    userModal.classList.add('show');
                })
                .catch(err => {
                    alert('Unable to fetch user details');
                });
        }

        @php
            $reportData = $reports->map(function ($report) {
                $reportCode = sprintf('R%s-%05d', optional($report->created_at)->format('y') ?? '00', $report->id);
                $userCode = optional($report->user)
                    ? $report->user->registration_code
                    : null;

                return [
                    'id' => $report->id,
                    'report_code' => $reportCode,
                    'report_type' => $report->report_type,
                    'animal_type' => $report->animal_type,
                    'user_id' => optional($report->user)->id,
                    'user_code' => $userCode,
                    'description' => $report->description,
                    'location_text' => $report->location_text,
                    'latitude' => $report->latitude,
                    'longitude' => $report->longitude,
                    'status' => $report->status,
                    'created_at' => optional($report->created_at)->format('M d, Y H:i'),
                    'image_paths' => $report->image_paths,
                    'video_path' => $report->video_path,
                    'media_version' => optional($report->updated_at ?? $report->created_at)->timestamp ?? $report->id,
                    'user_name' => optional($report->user)->full_name ?? optional($report->user)->name,
                    'user_email' => optional($report->user)->email,
                    'user_contact' => optional($report->user)->contact_number,
                ];
            })->toArray();
        @endphp

        const reportData = @json($reportData);
        const reportRows = Array.from(document.querySelectorAll('#allReportsPanel .report-row[data-report-id]'));
        const reportSearchInput = document.getElementById('reportSearchInput');
        const reportStatusDropdown = document.getElementById('reportStatusDropdown');
        const reportNoResults = document.getElementById('reportNoResults');
        const reportStatusInputs = reportStatusDropdown
            ? Array.from(reportStatusDropdown.querySelectorAll('input[data-status-option]'))
            : [];
        const userSearchInput = document.getElementById('userSearchInput');
        const userRows = Array.from(document.querySelectorAll('tr[data-user-search]'));
        const userNoResults = document.getElementById('userNoResultsTable');
        const userModal = document.getElementById('userModal');
        const modalName = document.getElementById('modalName');
        const modalSubtitle = document.getElementById('modalSubtitle');
        const modalEmail = document.getElementById('modalEmail');
        const modalContact = document.getElementById('modalContact');
        const modalAddress = document.getElementById('modalAddress');
        const modalStatus = document.getElementById('modalStatus');
        const modalRegistered = document.getElementById('modalRegistered');
        const modalPetsCount = document.getElementById('modalPetsCount');
        const modalReportsCount = document.getElementById('modalReportsCount');
        const modalActionHistory = document.getElementById('modalActionHistory');
        const modalAccountActions = document.getElementById('modalAccountActions');
        const suspensionModal = document.getElementById('suspensionModal');
        const suspensionConfirmModal = document.getElementById('suspensionConfirmModal');
        const suspensionForm = document.getElementById('suspensionForm');
        const suspensionTargetUserId = document.getElementById('suspensionTargetUserId');
        const suspensionReasonInput = document.getElementById('suspensionReason');
        const suspensionTypeInput = document.getElementById('suspensionType');
        const suspensionValueField = document.getElementById('suspensionValueField');
        const suspensionValueInput = document.getElementById('suspensionValue');
        const suspensionNoteInput = document.getElementById('suspensionNote');
        const suspensionUserName = document.getElementById('suspensionUserName');
        const suspensionUserMeta = document.getElementById('suspensionUserMeta');
        const suspensionUserStatus = document.getElementById('suspensionUserStatus');
        const suspensionUserSummary = document.getElementById('suspensionUserSummary');
        const suspensionConfirmUserName = document.getElementById('suspensionConfirmUserName');
        const suspensionConfirmSummary = document.getElementById('suspensionConfirmSummary');
        const confirmSuspensionButton = document.getElementById('confirmSuspensionButton');

        let currentUserModalData = null;
        let pendingSuspensionPayload = null;
        let pendingSuspensionUser = null;
        let toastTimer = null;

        function normalizeReportText(value) {
            return String(value || '').toLowerCase().trim();
        }

        function normalizeUserText(value) {
            return String(value || '').toLowerCase().trim();
        }

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        }

        function formatUserStatusLabel(status) {
            const normalizedStatus = status || 'deactivated';

            return normalizedStatus.charAt(0).toUpperCase() + normalizedStatus.slice(1).replace('_', ' ');
        }

        function showAccountActionToast(message, type = 'success') {
            if (toastTimer) {
                window.clearTimeout(toastTimer);
            }

            const existingToast = document.querySelector('[data-action-toast]');
            if (existingToast) {
                existingToast.remove();
            }

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('data-action-toast', '1');
            toast.innerHTML = `
                <div class="toast-content">
                    <div class="toast-title">${type === 'success' ? 'Success' : 'Action failed'}</div>
                    <p class="toast-message"></p>
                </div>
                <div class="toast-actions">
                    <button class="toast-close" type="button" aria-label="Close notification" data-toast-close>×</button>
                </div>
            `;

            toast.querySelector('.toast-message').textContent = message;
            toast.querySelector('[data-toast-close]').addEventListener('click', () => toast.remove());
            document.body.appendChild(toast);

            toastTimer = window.setTimeout(() => {
                toast.remove();
            }, 4000);
        }

        function suspensionLabel(type) {
            if (type === 'days') {
                return 'Days';
            }

            if (type === 'weeks') {
                return 'Weeks';
            }

            if (type === 'months') {
                return 'Months';
            }

            if (type === 'permanent') {
                return 'Indefinite';
            }

            return 'Unknown';
        }

        function syncSuspensionDurationField() {
            if (!suspensionTypeInput || !suspensionValueField || !suspensionValueInput) {
                return;
            }

            const isPermanent = suspensionTypeInput.value === 'permanent';
            suspensionValueField.classList.toggle('hidden', isPermanent);
            suspensionValueInput.required = !isPermanent;

            if (isPermanent) {
                suspensionValueInput.value = '';
            } else if (!suspensionValueInput.value) {
                suspensionValueInput.value = '1';
            }
        }

        function openModal(modalElement) {
            if (modalElement) {
                modalElement.classList.add('show');
            }
        }

        function closeModalElement(modalElement) {
            if (modalElement) {
                modalElement.classList.remove('show');
            }
        }

        function formatUserSummary(data) {
            const name = data.full_name || data.name || `User ${data.id}`;
            const meta = [data.email || '-', data.contact_number || '-'].join(' • ');
            const summary = data.suspension_summary || 'No active suspension.';
            const status = data.status || 'deactivated';

            if (suspensionUserName) suspensionUserName.textContent = name;
            if (suspensionUserMeta) suspensionUserMeta.textContent = meta;
            if (suspensionUserStatus) suspensionUserStatus.textContent = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
            if (suspensionUserSummary) suspensionUserSummary.textContent = summary;
        }

        function updateUserTableStatus(userId, status) {
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            if (!row) {
                return;
            }

            row.dataset.userStatus = status;

            const statusBadge = row.querySelector('.user-status-badge');
            if (statusBadge) {
                statusBadge.className = `user-status-badge ${status}`;
                statusBadge.textContent = formatUserStatusLabel(status);
            }
        }

        function renderUserAccountActions(data) {
            if (!modalAccountActions) {
                return;
            }

            const status = data.status || 'deactivated';

            if (status === 'active') {
                modalAccountActions.innerHTML = `
                    <button class="btn btn-danger" type="button" onclick="openSuspensionModal(${data.id})">Suspend Account</button>
                `;
                return;
            }

            if (status === 'suspended') {
                modalAccountActions.innerHTML = `
                    <button class="btn" type="button" onclick="unsuspendUser(${data.id})">Unsuspend Account</button>
                `;
                return;
            }

            modalAccountActions.innerHTML = '<div class="muted">No suspension actions are available for deactivated accounts.</div>';
        }

        function renderUserModal(data) {
            currentUserModalData = data;

            const status = data.status || 'deactivated';

            if (modalName) modalName.textContent = data.full_name || `User ${data.id}`;
            if (modalSubtitle) modalSubtitle.textContent = 'Overview';
            if (modalEmail) modalEmail.textContent = data.email || '-';
            if (modalContact) modalContact.textContent = data.contact_number || '-';
            if (modalAddress) modalAddress.textContent = data.address || '-';
            if (modalStatus) {
                modalStatus.className = `user-status-badge ${status}`;
                modalStatus.textContent = formatUserStatusLabel(status);
            }
            if (modalRegistered) modalRegistered.textContent = data.registered_at || '-';
            if (modalPetsCount) modalPetsCount.textContent = data.pets_count ?? 0;
            if (modalReportsCount) modalReportsCount.textContent = data.reports_count ?? 0;
            if (modalActionHistory) modalActionHistory.textContent = 'No action history yet.';

            renderUserAccountActions(data);
            updateUserTableStatus(data.id, status);
        }

        function refreshUserModalData(userId) {
            if (!userId) {
                return Promise.resolve();
            }

            return fetch(`/admin/users/${userId}/details`)
                .then(response => response.json())
                .then(data => {
                    renderUserModal(data);
                    return data;
                });
        }

        function resetSuspensionDraft() {
            pendingSuspensionPayload = null;
            pendingSuspensionUser = null;

            if (suspensionForm) {
                suspensionForm.reset();
            }

            if (suspensionTargetUserId) {
                suspensionTargetUserId.value = '';
            }

            syncSuspensionDurationField();
        }

        function populateSuspensionModal(data) {
            pendingSuspensionUser = data;

            if (suspensionTargetUserId) {
                suspensionTargetUserId.value = data.id;
            }

            if (suspensionForm) {
                suspensionForm.action = `/admin/users/${data.id}/suspension`;
            }

            if (suspensionReasonInput) {
                suspensionReasonInput.value = '';
            }

            if (suspensionTypeInput) {
                suspensionTypeInput.value = 'days';
            }

            if (suspensionValueInput) {
                suspensionValueInput.value = '1';
            }

            if (suspensionNoteInput) {
                suspensionNoteInput.value = '';
            }

            formatUserSummary(data);
            syncSuspensionDurationField();
        }

        function openSuspensionModal(userId) {
            fetch(`/admin/users/${userId}/details`)
                .then(response => response.json())
                .then(data => {
                    const currentStatus = data.status || 'deactivated';

                    if (currentStatus === 'suspended') {
                        unsuspendUser(userId);
                        return;
                    }

                    populateSuspensionModal(data);
                    openModal(suspensionModal);
                })
                .catch(() => {
                    alert('Unable to load user details');
                });
        }

        function closeSuspensionModal() {
            closeModalElement(suspensionModal);
            resetSuspensionDraft();
        }

        function showSuspensionConfirmation(payload) {
            pendingSuspensionPayload = payload;

            const durationText = payload.suspension_type === 'permanent'
                ? 'Indefinite suspension'
                : `${payload.suspension_value} ${suspensionLabel(payload.suspension_type).toLowerCase()}`;

            if (suspensionConfirmUserName) {
                suspensionConfirmUserName.textContent = pendingSuspensionUser?.full_name || pendingSuspensionUser?.name || `User ${pendingSuspensionUser?.id || ''}`;
            }

            if (suspensionConfirmSummary) {
                suspensionConfirmSummary.textContent = `${payload.suspension_reason} • ${durationText}`;
            }

            closeModalElement(suspensionModal);
            openModal(suspensionConfirmModal);
        }

        function buildSuspensionPayload() {
            const formData = new FormData(suspensionForm);
            const suspensionType = String(formData.get('suspension_type') || 'days');
            const payload = {
                suspension_reason: String(formData.get('suspension_reason') || '').trim(),
                suspension_type: suspensionType,
                suspension_note: String(formData.get('suspension_note') || '').trim(),
            };

            if (suspensionType !== 'permanent') {
                payload.suspension_value = String(formData.get('suspension_value') || '').trim();
            }

            return payload;
        }

        function submitSuspensionPayload() {
            if (!pendingSuspensionUser || !pendingSuspensionPayload) {
                return;
            }

            const userId = pendingSuspensionUser.id;

            fetch(`/admin/users/${pendingSuspensionUser.id}/suspension`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(pendingSuspensionPayload),
            })
                .then(async response => {
                    const payload = await response.json();
                    if (!response.ok) {
                        throw new Error(payload.message || 'Unable to suspend account');
                    }

                    closeModalElement(suspensionConfirmModal);
                    closeSuspensionModal();
                    showAccountActionToast('Account suspended successfully.', 'success');

                    return refreshUserModalData(userId);
                })
                .catch(error => {
                    showAccountActionToast('Failed to suspend the account. Please try again.', 'error');
                });
        }

        function unsuspendUser(userId) {
            fetch(`/admin/users/${userId}/suspension`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
                .then(async response => {
                    const payload = await response.json();
                    if (!response.ok) {
                        throw new Error(payload.message || 'Unable to unsuspend account');
                    }

                    closeModalElement(suspensionConfirmModal);
                    closeSuspensionModal();
                    showAccountActionToast('Account unsuspended successfully.', 'success');

                    return refreshUserModalData(userId);
                })
                .catch(error => {
                    showAccountActionToast('Failed to unsuspend the account. Please try again.', 'error');
                });
        }

        function getSelectedReportStatuses() {
            const allInput = reportStatusInputs.find(input => input.value === 'all');
            const selected = reportStatusInputs
                .filter(input => input.value !== 'all' && input.checked)
                .map(input => input.value);

            if (allInput && allInput.checked) {
                return ['pending', 'in_progress', 'resolved'];
            }

            return selected.length ? selected : ['pending', 'in_progress', 'resolved'];
        }

        function applyReportFilters() {
            if (!reportRows.length) {
                return;
            }

            const searchQuery = normalizeReportText(reportSearchInput ? reportSearchInput.value : '');
            const selectedStatuses = getSelectedReportStatuses();

            let visibleCount = 0;

            reportRows.forEach(row => {
                const rowStatus = row.dataset.reportStatus || '';
                const rowSearch = row.dataset.reportSearch || '';
                const matchesSearch = !searchQuery || rowSearch.includes(searchQuery);
                const matchesStatus = selectedStatuses.includes(rowStatus);
                const visible = matchesSearch && matchesStatus;

                row.classList.toggle('hidden', !visible);
                if (visible) {
                    visibleCount += 1;
                }
            });

            if (reportNoResults) {
                reportNoResults.style.display = visibleCount === 0 ? 'block' : 'none';
                reportNoResults.textContent = searchQuery
                    ? 'No reports match your search and status filters.'
                    : 'No reports match the selected status filters.';
            }
        }

        function initReportManagementTabs() {
            const tabButtons = Array.from(document.querySelectorAll('[data-report-tab]'));
            const panels = {
                all: document.getElementById('allReportsPanel'),
                related: document.getElementById('relatedReportsPanel'),
            };

            if (!tabButtons.length || !panels.all || !panels.related) {
                return;
            }

            const activateTab = (tabName) => {
                tabButtons.forEach(button => {
                    const isActive = button.dataset.reportTab === tabName;
                    button.classList.toggle('active', isActive);
                    button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                Object.entries(panels).forEach(([name, panel]) => {
                    panel.classList.toggle('hidden', name !== tabName);
                });
            };

            tabButtons.forEach(button => {
                button.addEventListener('click', () => activateTab(button.dataset.reportTab || 'all'));
            });

            activateTab('all');
        }

        function syncReportStatusInputs(changedInput) {
            const allInput = reportStatusInputs.find(input => input.value === 'all');
            if (!allInput) {
                return;
            }

            if (changedInput.value === 'all') {
                reportStatusInputs.forEach(input => {
                    input.checked = changedInput.checked;
                });
            } else {
                if (changedInput.checked) {
                    allInput.checked = false;
                }

                const activeCount = reportStatusInputs.filter(input => input.value !== 'all' && input.checked).length;
                if (activeCount === 0) {
                    allInput.checked = true;
                    reportStatusInputs.forEach(input => {
                        if (input.value !== 'all') {
                            input.checked = true;
                        }
                    });
                }
            }

            applyReportFilters();
        }

        function initReportManagementFilters() {
            if (!reportSearchInput || !reportStatusDropdown) {
                return;
            }

            const allInput = reportStatusInputs.find(input => input.value === 'all');
            if (allInput) {
                allInput.checked = true;
            }
            reportStatusInputs.forEach(input => {
                if (input.value !== 'all') {
                    input.checked = true;
                }
                input.addEventListener('change', () => syncReportStatusInputs(input));
            });

            reportSearchInput.addEventListener('input', () => {
                applyReportFilters();
            });

            applyReportFilters();
        }

        function initUserSuspensionWorkflow() {
            if (suspensionTypeInput) {
                suspensionTypeInput.addEventListener('change', syncSuspensionDurationField);
            }

            if (suspensionForm) {
                suspensionForm.addEventListener('submit', event => {
                    event.preventDefault();
                    showSuspensionConfirmation(buildSuspensionPayload());
                });
            }

            if (confirmSuspensionButton) {
                confirmSuspensionButton.addEventListener('click', submitSuspensionPayload);
            }

            document.querySelectorAll('[data-suspension-close]').forEach(button => {
                button.addEventListener('click', closeSuspensionModal);
            });

            document.querySelectorAll('[data-suspension-confirm-cancel]').forEach(button => {
                button.addEventListener('click', () => {
                    closeModalElement(suspensionConfirmModal);
                    openModal(suspensionModal);
                });
            });

            if (suspensionModal) {
                suspensionModal.addEventListener('click', event => {
                    if (event.target === suspensionModal) {
                        closeSuspensionModal();
                    }
                });
            }

            if (suspensionConfirmModal) {
                suspensionConfirmModal.addEventListener('click', event => {
                    if (event.target === suspensionConfirmModal) {
                        closeModalElement(suspensionConfirmModal);
                    }
                });
            }

            syncSuspensionDurationField();
        }

        function applyUserFilters() {
            if (!userRows.length) {
                return;
            }

            const searchQuery = normalizeUserText(userSearchInput ? userSearchInput.value : '');
            let visibleCount = 0;

            userRows.forEach(row => {
                const rowSearch = row.dataset.userSearch || '';
                const visible = !searchQuery || rowSearch.includes(searchQuery);

                row.classList.toggle('hidden', !visible);
                if (visible) {
                    visibleCount += 1;
                }
            });

            if (userNoResults) {
                userNoResults.style.display = visibleCount === 0 ? 'block' : 'none';
                userNoResults.textContent = searchQuery
                    ? 'No users match your search.'
                    : 'No users are available.';
            }
        }

        function initUserManagementSearch() {
            if (!userSearchInput || !userRows.length) {
                return;
            }

            userSearchInput.addEventListener('input', () => {
                applyUserFilters();
            });

            applyUserFilters();
        }

        document.addEventListener('DOMContentLoaded', initReportManagementFilters);
        document.addEventListener('DOMContentLoaded', initUserManagementSearch);
        document.addEventListener('DOMContentLoaded', initUserSuspensionWorkflow);

        const mediaBaseUrl = @json(url('/api/media'));

        function mediaUrl(path, mediaVersion = '') {
            const version = mediaVersion ? `&v=${encodeURIComponent(mediaVersion)}` : '';
            return `${mediaBaseUrl}?path=${encodeURIComponent(path)}${version}`;
        }

        function openReport(id) {
            const report = reportData.find(r => r.id === id);
            if (!report) {
                alert('Report not found');
                return;
            }

            const mediaVersion = report.media_version || '';

            document.getElementById('reportModalTitle').textContent = `Report ID ${report.report_code || report.id} — ${report.report_type} (${report.animal_type})`;
            document.getElementById('reportModalSubtitle').textContent = `${report.created_at} • ${reportLabel(report.status)}`;

            let mediaHtml = '';
            if (Array.isArray(report.image_paths) && report.image_paths.length) {
                mediaHtml += '<div class="report-media">' + report.image_paths.map(path => `<img src="${mediaUrl(path, mediaVersion)}" alt="Report image">`).join('') + '</div>';
            }
            if (report.video_path) {
                mediaHtml += `<div class="report-media"><video controls><source src="${mediaUrl(report.video_path, mediaVersion)}" type="video/mp4"></video></div>`;
            }

            document.getElementById('reportDetails').innerHTML = `
                <div class="report-card">
                    <div class="report-meta"><strong>Report ID:</strong> ${report.report_code || report.id}<br><strong>User ID:</strong> ${report.user_code || report.user_id || '-'}<br><strong>Submitted by:</strong> ${report.user_name || '-'}<br><strong>Email:</strong> ${report.user_email || '-'}<br><strong>Contact:</strong> ${report.user_contact || '-'}<br><strong>Location:</strong> ${report.location_text || '-'}<br><strong>Coordinates:</strong> ${report.latitude || '-'}, ${report.longitude || '-'}<br><strong>Status:</strong> ${reportLabel(report.status)}</div>
                    <div class="report-meta" style="margin-top:12px"><strong>Description:</strong> ${report.description || '-'}</div>
                    ${mediaHtml || '<div class="muted">No media attached.</div>'}
                </div>
            `;

            updateReportModalAction(report.status);

            const statusActionButton = document.getElementById('reportStatusAction');
            if (statusActionButton) {
                statusActionButton.onclick = () => changeReportStatus(report.id, statusActionButton.dataset.nextStatus);
            }

            document.getElementById('reportModal').classList.add('show');
        }

        function closeModal() { document.getElementById('userModal').classList.remove('show'); }
        function closeReportModal() { document.getElementById('reportModal').classList.remove('show'); }

        function initUserSuspensionControls() {
            if (suspensionTypeInput) {
                suspensionTypeInput.addEventListener('change', syncSuspensionDurationField);
                syncSuspensionDurationField();
            }
        }

        function initAnnouncementTabs() {
            const tabButtons = Array.from(document.querySelectorAll('[data-announcement-tab]'));
            const createPanel = document.getElementById('announcement-create-panel');
            const feedPanel = document.getElementById('announcement-feed-panel');

            if (!tabButtons.length || !createPanel || !feedPanel) {
                return;
            }

            const panels = {
                create: createPanel,
                feed: feedPanel,
            };

            function showAnnouncementTab(tabName) {
                tabButtons.forEach(button => {
                    button.classList.toggle('active', button.dataset.announcementTab === tabName);
                });

                Object.entries(panels).forEach(([name, panel]) => {
                    panel.classList.toggle('hidden', name !== tabName);
                });
            }

            tabButtons.forEach(button => {
                button.addEventListener('click', () => showAnnouncementTab(button.dataset.announcementTab));
            });

            showAnnouncementTab('create');
        }

        function initImagePreviews() {
            const previewInputs = Array.from(document.querySelectorAll('[data-image-preview-input]'));

            previewInputs.forEach(input => {
                const previewKey = input.dataset.imagePreviewInput;
                const previewId = previewKey === 'create'
                    ? 'announcement-create-preview'
                    : previewKey === 'modal-edit'
                        ? 'announcement-edit-preview'
                        : `announcement-edit-preview-${previewKey.replace('edit-', '')}`;
                const preview = document.getElementById(previewId);

                if (!preview) {
                    return;
                }

                const previewImage = preview.querySelector('img');
                const previewText = preview.querySelector('.muted');

                input.addEventListener('change', () => {
                    const file = input.files && input.files[0] ? input.files[0] : null;

                    if (!file) {
                        if (previewImage) {
                            previewImage.removeAttribute('src');
                            previewImage.hidden = true;
                        }
                        if (previewText) {
                            previewText.textContent = previewId === 'announcement-create-preview'
                                ? 'Image preview will appear here before publishing.'
                                : 'Current announcement image will stay unchanged unless you choose a new one.';
                            previewText.hidden = false;
                        }
                        preview.classList.toggle('has-image', false);
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = event => {
                        if (previewImage && event.target?.result) {
                            previewImage.src = String(event.target.result);
                            previewImage.hidden = false;
                        }
                        if (previewText) {
                            previewText.hidden = true;
                        }
                        preview.classList.add('has-image');
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        function openAnnouncementEditModal(announcementData) {
            const modal = document.getElementById('announcementEditModal');
            const form = document.getElementById('announcementEditForm');
            const titleInput = document.getElementById('announcement-edit-title');
            const descriptionInput = document.getElementById('announcement-edit-description');
            const publishedInput = document.getElementById('announcement-edit-published');
            const preview = document.getElementById('announcement-edit-preview');
            const previewImage = preview ? preview.querySelector('img') : null;
            const previewText = preview ? preview.querySelector('.muted') : null;

            if (!modal || !form || !titleInput || !descriptionInput || !publishedInput || !preview) {
                return;
            }

            form.action = announcementData.updateUrl || form.action;
            titleInput.value = announcementData.title || '';
            descriptionInput.value = announcementData.description || '';
            publishedInput.checked = Boolean(Number(announcementData.isPublished || 0));
            preview.dataset.currentImage = announcementData.imageUrl || '';

            if (previewImage) {
                if (announcementData.imageUrl) {
                    previewImage.src = announcementData.imageUrl;
                    previewImage.hidden = false;
                    preview.classList.add('has-image');
                } else {
                    previewImage.removeAttribute('src');
                    previewImage.hidden = true;
                    preview.classList.remove('has-image');
                }
            }

            if (previewText) {
                previewText.textContent = announcementData.imageUrl
                    ? 'Current announcement image is shown here. Choose a new file to replace it.'
                    : 'No image currently set. Choose a file to add one.';
                previewText.hidden = Boolean(announcementData.imageUrl);
            }

            modal.classList.add('show');
        }

        function closeAnnouncementEditModal() {
            const modal = document.getElementById('announcementEditModal');
            const form = document.getElementById('announcementEditForm');

            if (form) {
                form.reset();
            }

            if (modal) {
                modal.classList.remove('show');
            }
        }

        function openAnnouncementDeleteModal(deleteUrl, title) {
            const modal = document.getElementById('announcementDeleteModal');
            const form = document.getElementById('announcementDeleteForm');
            const message = document.getElementById('announcementDeleteMessage');

            if (!modal || !form || !message) {
                return;
            }

            form.action = deleteUrl;
            message.textContent = title
                ? `Are you sure you want to delete "${title}"? This action cannot be undone.`
                : 'Are you sure you want to delete this announcement? This action cannot be undone.';
            modal.classList.add('show');
        }

        function closeAnnouncementDeleteModal() {
            const modal = document.getElementById('announcementDeleteModal');

            if (modal) {
                modal.classList.remove('show');
            }
        }

        function initToast() {
            const toast = document.querySelector('[data-toast]');
            if (!toast) {
                return;
            }

            const closeButtons = Array.from(toast.querySelectorAll('[data-toast-close]'));
            closeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    toast.remove();
                });
            });
        }

        function initAnnouncementActionModals() {
            const editButtons = Array.from(document.querySelectorAll('[data-announcement-edit-open]'));
            editButtons.forEach(button => {
                button.addEventListener('click', () => {
                    openAnnouncementEditModal({
                        updateUrl: button.dataset.announcementEditOpen ? button.dataset.announcementUpdateUrl : button.dataset.announcementUpdateUrl,
                        title: button.dataset.announcementTitle || '',
                        description: button.dataset.announcementDescription || '',
                        imageUrl: button.dataset.announcementImage || '',
                        isPublished: button.dataset.announcementPublished || '0',
                    });
                });
            });

            const deleteButtons = Array.from(document.querySelectorAll('[data-announcement-delete-open]'));
            deleteButtons.forEach(button => {
                button.addEventListener('click', () => {
                    openAnnouncementDeleteModal(
                        button.dataset.announcementDeleteUrl || '',
                        button.dataset.announcementDeleteTitle || ''
                    );
                });
            });

            Array.from(document.querySelectorAll('[data-announcement-edit-close]')).forEach(button => {
                button.addEventListener('click', closeAnnouncementEditModal);
            });

            Array.from(document.querySelectorAll('[data-announcement-delete-close]')).forEach(button => {
                button.addEventListener('click', closeAnnouncementDeleteModal);
            });

            const editModal = document.getElementById('announcementEditModal');
            const deleteModal = document.getElementById('announcementDeleteModal');

            if (editModal) {
                editModal.addEventListener('click', event => {
                    if (event.target === editModal) {
                        closeAnnouncementEditModal();
                    }
                });
            }

            if (deleteModal) {
                deleteModal.addEventListener('click', event => {
                    if (event.target === deleteModal) {
                        closeAnnouncementDeleteModal();
                    }
                });
            }
        }

        function filterPets(status) {
            // Hide all tabs
            ['all', 'vaccinated', 'not_vaccinated', 'unknown'].forEach(s => {
                const panel = document.getElementById('pets-' + s);
                if (panel) panel.classList.add('hidden');
                const tab = document.getElementById('tab-' + s.replace('_', '-'));
                if (tab) tab.classList.remove('active');
            });

            // Show selected tab
            const selectedPanel = document.getElementById('pets-' + status);
            if (selectedPanel) selectedPanel.classList.remove('hidden');
            const selectedTab = document.getElementById('tab-' + status.replace('_', '-'));
            if (selectedTab) selectedTab.classList.add('active');
        }

        @if ($section === 'user-management')
            setInterval(() => {
                if (!document.hidden) {
                    window.location.reload();
                }
            }, 600000);
        @endif
    </script>
</body>
</html>
