<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Members Management System')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        :root {
            --sidebar-width: 260px;
            --navbar-height: 60px;
            --primary-color: #1e3a5f;
            --secondary-color: #2c5282;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, #0f2440 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
        }
        
        .sidebar .brand {
            padding: 20px;
            font-size: 1.3rem;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar .nav-item {
            padding: 0;
        }
        
        .sidebar .menu-header {
            padding: 12px 20px 6px;
            color: #63b3ed;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-top: 15px;
            margin-bottom: 5px;
        }
        
        .sidebar .menu-header:first-child {
            margin-top: 0;
        }
        
        .sidebar .nav-link {
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #63b3ed;
        }
        
        .sidebar .nav-link i {
            width: 25px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding-top: var(--navbar-height);
        }
        
        .navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--navbar-height);
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            z-index: 999;
            padding: 0 20px;
        }
        
        .navbar .dropdown-toggle::after {
            display: none;
        }
        
        .page-header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: white;
            border-bottom: 2px solid #f3f4f6;
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .table th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
            font-size: 0.875rem;
        }
        
        .badge {
            padding: 5px 10px;
            font-weight: 500;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 58, 95, 0.15);
        }
        
        .dataTables_wrapper {
            padding: 20px;
        }
        
        .dataTables_length select {
            border-radius: 4px;
        }
        
        .dt-buttons {
            margin-bottom: 10px;
        }
        
        .dt-buttons .btn {
            margin-right: 5px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6b7280;
            padding: 12px 20px;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            background: transparent;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .navbar {
                left: 0;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="sidebar">
        <div class="brand">
            <i class="fas fa-users me-2"></i>Members System
        </div>
        <nav class="nav flex-column mt-3">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            
            @can('members.menu')
            <a href="{{ route('admin.members.index') }}" class="nav-link {{ request()->routeIs('admin.members.*') ? 'active' : '' }}">
                <i class="fas fa-user-friends"></i> Members
            </a>
            @endcan
            
            @can('membership_types.manage')
            <a href="{{ route('admin.membership-types.index') }}" class="nav-link {{ request()->routeIs('admin.membership-types.*') ? 'active' : '' }}">
                <i class="fas fa-id-card"></i> Membership Types
            </a>
            @endcan
            
            @can('payments.manage')
            <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave"></i> Payments
            </a>
            @endcan
            
            @can('reports.view')
            <a href="{{ route('admin.reports.members') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            @endcan
            
            @can('import.export')
            <a href="{{ route('admin.import-export.index') }}" class="nav-link {{ request()->routeIs('admin.import-export.*') ? 'active' : '' }}">
                <i class="fas fa-file-import"></i> Import/Export
            </a>
            @endcan
            
            @can('events.menu')
            <div class="menu-header">Events</div>
            
            <a href="{{ route('admin.events.dashboard') }}" class="nav-link {{ request()->routeIs('admin.events.dashboard') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i> Event Dashboard
            </a>
            
            <a href="{{ route('admin.events.index') }}" class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i> Events
            </a>
            
            @can('event_types.manage')
            <a href="{{ route('admin.event-types.index') }}" class="nav-link {{ request()->routeIs('admin.event-types.*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i> Event Types
            </a>
            @endcan
            @endcan
            
            @role('Super Admin')
            <a href="{{ route('admin.receipts.index') }}" class="nav-link {{ request()->routeIs('admin.receipts.*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i> Receipts
            </a>
            @endrole
            
            @role('Super Admin')
            <div class="menu-header">User Maintenance</div>
            
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i> Users
            </a>
            
            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i> Roles
            </a>
            
            <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                <i class="fas fa-key"></i> Permissions
            </a>
            
            <a href="{{ route('admin.roles.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.roles.permissions.*') ? 'active' : '' }}">
                <i class="fas fa-assignments"></i> Assign Permissions
            </a>
            
            <div class="menu-header">System Admin</div>
            
            <a href="{{ route('admin.office-bearers.index') }}" class="nav-link {{ request()->routeIs('admin.office-bearers.*') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i> Office Bearers
            </a>
            
            <a href="{{ route('admin.organizations.index') }}" class="nav-link {{ request()->routeIs('admin.organizations.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i> Organizations
            </a>
            @endrole
        </nav>
    </div>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid px-0">
            <div class="d-flex align-items-center">
                <span class="text-muted me-4">
                    <i class="fas fa-calendar-alt me-1"></i>
                    {{ now()->format('l, F j, Y') }}
                </span>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=1e3a5f&color=fff" 
                             class="rounded-circle me-2" width="32" height="32" alt="User">
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user me-2"></i>Profile
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">@yield('page-title', 'Dashboard')</h4>
            @yield('page-actions')
        </div>
        
        <div class="container-fluid px-4">
            @yield('content')
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        @if(session('success'))
        toastr.success('{{ session('success') }}', 'Success');
        @endif
        
        @if(session('error'))
        toastr.error('{{ session('error') }}', 'Error');
        @endif
        
        function deleteItem(url, id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        success: function(response) {
                            toastr.success(response.success || 'Deleted successfully');
                            if (typeof dataTable !== 'undefined') {
                                dataTable.ajax.reload();
                            } else {
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.error || 'Delete failed');
                        }
                    });
                }
            });
        }
        
        $(document).on('click', 'a[href="#"]', function(e) {
            e.preventDefault();
        });
        
        let hasUnsavedChanges = false;
        
        $('form input, form select, form textarea').on('change', function() {
            hasUnsavedChanges = true;
        });
        
        window.onbeforeunload = function() {
            if (hasUnsavedChanges) {
                return 'You have unsaved changes. Are you sure you want to leave?';
            }
        };
        
        $('form').on('submit', function() {
            hasUnsavedChanges = false;
        });
        
        $('input[type="text"]').on('keyup', function() {
            if ($(this).data('uppercase')) {
                this.value = this.value.toUpperCase();
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
