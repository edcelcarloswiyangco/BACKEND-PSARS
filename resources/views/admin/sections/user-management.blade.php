<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
        <div><strong>Total users</strong><div class="muted">{{ $users->count() }} accounts registered</div></div>
        <div class="muted">Last updated: {{ now()->format('M d, Y') }}</div>
    </div>

    <div class="user-search-wrap" style="margin-bottom:14px">
        <input
            id="userSearchInput"
            type="search"
            class="user-search"
            placeholder="Search user ID, name, or contact number..."
            autocomplete="off"
            aria-label="Search user ID, name, or contact number"
        >
    </div>

    @if ($users->isEmpty())
        <div class="muted">No users have registered yet.</div>
    @else
        <div style="overflow:auto">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Full Name</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th># Reports</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        @php
                            $userSearchText = collect([
                                $user->registration_code,
                                $user->full_name ?? $user->name,
                                $user->email,
                                $user->contact_number,
                            ])->filter()->implode(' ');
                        @endphp
                        <tr
                            data-user-id="{{ $user->id }}"
                            data-user-name="{{ e($user->full_name ?? $user->name) }}"
                            data-user-status="{{ $user->status ?? 'deactivated' }}"
                            data-user-search="{{ strtolower(e($userSearchText)) }}"
                        >
                            <td>{{ $user->registration_code }}</td>
                            <td><strong>{{ $user->full_name ?? $user->name }}</strong><div class="muted">{{ $user->email }}</div></td>
                            <td>{{ $user->contact_number ?? '-' }}</td>
                            <td><span class="user-status-badge {{ $user->status ?? 'deactivated' }}">{{ ucfirst(str_replace('_', ' ', $user->status ?? 'deactivated')) }}</span></td>
                            <td>{{ $user->reports_count ?? 0 }}</td>
                            <td>
                                <button class="btn-ghost" type="button" onclick="openUser({{ $user->id }})">View</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="userNoResultsTable" class="user-no-results">No users match your search.</div>
    @endif
</div>
