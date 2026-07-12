<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
    </div>
    <div style="margin-bottom:16px">
        <div><strong>Admin</strong></div>
        <div class="muted">{{ auth('admin')->user()->email }}</div>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button class="btn" type="submit">Logout</button>
    </form>
</div>
