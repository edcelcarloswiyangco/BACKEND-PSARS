<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Dashboard - PSARS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            font-size: 28px;
        }
        
        .badge {
            background: rgba(255, 255, 255, 0.3);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid white;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        h2 {
            margin-bottom: 20px;
            color: #667eea;
            font-size: 22px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #555;
        }
        
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        button {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #5568d3;
        }
        
        .admin-list {
            list-style: none;
        }
        
        .admin-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #667eea;
        }
        
        .admin-email {
            font-weight: 500;
        }
        
        .delete-btn {
            background: #d32f2f;
            padding: 6px 12px;
            font-size: 13px;
        }
        
        .delete-btn:hover {
            background: #b71c1c;
        }
        
        .success-message {
            background: #c8e6c9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error-message {
            background: #ffcdd2;
            color: #c62828;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>PSARS Developer Dashboard</h1>
            <div class="badge">DEVELOPER MODE</div>
        </div>
        <form method="POST" action="{{ route('developer.logout') }}" style="display: inline;">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
    
    <div class="container">
        @if (session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif
        
        @if ($errors->has('message'))
            <div class="error-message">{{ $errors->first('message') }}</div>
        @endif
        
        <div class="grid">
            <!-- Create Admin Card -->
            <div class="card">
                <h2>➕ Create Admin Account</h2>
                
                @if ($errors->any())
                    <div class="error-message" style="margin-bottom: 20px;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                
                <form method="POST" action="{{ route('developer.admin.store') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email">Admin Email</label>
                        <input type="email" id="email" name="email" required value="{{ old('email') }}">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Admin Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit">Create Admin</button>
                </form>
            </div>
            
            <!-- Admin List Card -->
            <div class="card">
                <h2>👥 Admin Accounts ({{ count($admins) }})</h2>
                
                @if (count($admins) > 0)
                    <ul class="admin-list">
                        @foreach ($admins as $admin)
                            <li class="admin-item">
                                <span class="admin-email">{{ $admin->email }}</span>
                                <form method="POST" action="{{ route('developer.admin.destroy', $admin) }}" style="display: inline;" onsubmit="return confirm('Delete this admin account?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p style="color: #999; text-align: center; padding: 20px;">No admin accounts created yet.</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
