<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MembershipTypeController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ImportExportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\Events\EventDashboardController;
use App\Http\Controllers\Admin\Events\EventTypeController;
use App\Http\Controllers\Admin\Events\EventController;
use App\Http\Controllers\Admin\Events\EventAttendeeController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\OfficeBearerController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // AJAX endpoints (inside auth, outside perm middleware)
    Route::get('members/ajax', [MemberController::class, 'ajax'])->name('admin.members.ajax');
    Route::get('members/search', [MemberController::class, 'search'])->name('admin.members.search');
    Route::get('membership-types/ajax', [MembershipTypeController::class, 'ajax'])->name('admin.membership-types.ajax');
    Route::get('payments/ajax', [PaymentController::class, 'ajax'])->name('admin.payments.ajax');

    // Members
    Route::middleware(['perm:members.menu'])->group(function () {

        Route::get('members/import/excel', [MemberController::class, 'importForm'])
        ->name('admin.members.import.form');

        Route::post('members/import/excel/preview', [MemberController::class, 'importPreview'])
        ->name('admin.members.import.preview');

        Route::post('members/import/excel/confirm', [MemberController::class, 'importConfirm'])
        ->name('admin.members.import.confirm');

        Route::resource('members', MemberController::class)->names('admin.members');
    });

    // Membership Types
    Route::middleware(['perm:membership_types.manage'])->group(function () {
        Route::resource('membership-types', MembershipTypeController::class)->names('admin.membership-types');
    });

    // Payments
    Route::middleware(['perm:payments.manage'])->group(function () {
        Route::resource('payments', PaymentController::class)->names('admin.payments');
        Route::get('members/{member}/payments/create', [PaymentController::class, 'createForMember'])->name('admin.members.payments.create');
        Route::post('members/{member}/payments', [PaymentController::class, 'storeForMember'])->name('admin.members.payments.store');
    });

    // Reports
    Route::middleware(['perm:reports.view'])->group(function () {
        Route::get('reports/members', [ReportController::class, 'memberList'])->name('admin.reports.members');
        Route::get('reports/active-inactive', [ReportController::class, 'activeInactive'])->name('admin.reports.active-inactive');
        Route::get('reports/dues-summary', [ReportController::class, 'duesSummary'])->name('admin.reports.dues-summary');
        Route::get('reports/export-members', [ReportController::class, 'exportMembers'])->name('admin.reports.export-members');
    });

    // Import/Export
    Route::middleware(['perm:import.export'])->group(function () {
        Route::get('import-export', [ImportExportController::class, 'index'])->name('admin.import-export.index');
        Route::get('import-export/template', [ImportExportController::class, 'downloadTemplate'])->name('admin.import-export.template');
        Route::post('import-export/import', [ImportExportController::class, 'import'])->name('admin.import-export.import');
        Route::get('import-export/export', [ImportExportController::class, 'export'])->name('admin.import-export.export');
    });

    // Events
    Route::middleware(['perm:events.menu'])->group(function () {
        // Event Dashboard
        Route::get('events-dashboard', [EventDashboardController::class, 'index'])->name('admin.events.dashboard');

        // Event Types
        Route::get('event-types/ajax', [EventTypeController::class, 'ajax'])->name('admin.event-types.ajax');
        Route::resource('event-types', EventTypeController::class)->names('admin.event-types');

        // Events
        Route::get('events/ajax', [EventController::class, 'ajax'])->name('admin.events.ajax');
        Route::resource('events', EventController::class)->names('admin.events');

        // Event Attendees
        Route::get('events/{event}/attendees/ajax', [EventAttendeeController::class, 'ajax'])->name('admin.event-attendees.ajax');
        Route::get('events/{event}/attendees', [EventAttendeeController::class, 'index'])->name('admin.event-attendees.index');
        Route::get('events/{event}/attendees/create', [EventAttendeeController::class, 'create'])->name('admin.event-attendees.create');
        Route::post('events/{event}/attendees', [EventAttendeeController::class, 'store'])->name('admin.event-attendees.store');
        Route::delete('events/{event}/attendees/{attendee}', [EventAttendeeController::class, 'destroy'])->name('admin.event-attendees.destroy');
        Route::put('events/{event}/attendees/{attendee}/status', [EventAttendeeController::class, 'updateStatus'])->name('admin.event-attendees.update-status');

        // Documents (Receipts & Welcome Letters)
        Route::get('payments/{payment}/receipt', [DocumentController::class, 'generateReceipt'])->name('admin.payments.receipt');
        Route::get('members/{member}/welcome-letter', [DocumentController::class, 'generateWelcomeLetter'])->name('admin.members.welcome-letter');
    });

    // User Maintenance - Super Admin Only
    Route::middleware(['role:Super Admin'])->group(function () {
        // Users
        Route::get('users/ajax', [UserController::class, 'ajax'])->name('admin.users.ajax');
        Route::resource('users', UserController::class)->names('admin.users');

        // Roles
        Route::get('roles/ajax', [RoleController::class, 'ajax'])->name('admin.roles.ajax');
        Route::resource('roles', RoleController::class)->names('admin.roles');

        // Permissions
        Route::get('permissions/ajax', [PermissionController::class, 'ajax'])->name('admin.permissions.ajax');
        Route::resource('permissions', PermissionController::class)->names('admin.permissions');

        // Assign Permissions to Roles
        Route::get('roles-permissions', [RolePermissionController::class, 'index'])->name('admin.roles.permissions.index');
        Route::get('roles-permissions/{role?}', [RolePermissionController::class, 'index'])->name('admin.roles.permissions.show');
        Route::put('roles-permissions/{role}', [RolePermissionController::class, 'update'])->name('admin.roles.permissions.update');

        // System Admin - Office Bearers
        Route::get('office-bearers/ajax', [OfficeBearerController::class, 'ajax'])->name('admin.office-bearers.ajax');
        Route::resource('office-bearers', OfficeBearerController::class)->names('admin.office-bearers');

        // System Admin - Organizations
        Route::get('organizations/ajax', [OrganizationController::class, 'ajax'])->name('admin.organizations.ajax');
        Route::resource('organizations', OrganizationController::class)->names('admin.organizations');

        // Receipts
        Route::get('receipts/ajax', [ReceiptController::class, 'ajax'])->name('admin.receipts.ajax');
        Route::resource('receipts', ReceiptController::class)->names('admin.receipts');
        Route::get('receipts/{receipt}/print', [ReceiptController::class, 'print'])->name('admin.receipts.print');
    });
});

require __DIR__.'/auth.php';
