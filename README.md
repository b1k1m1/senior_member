<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Members Management System

A complete Laravel 12 based Members Management System with role-based access control, membership tracking, payment management, and comprehensive reporting.

## Features

- **Authentication**: Laravel Breeze (Blade)
- **Authorization**: Spatie laravel-permission (roles + permissions)
- **Database**: MySQL (utf8mb4)
- **UI**: Bootstrap 5, Blade templates
- **DataTables**: jQuery DataTables for listing pages
- **Notifications**: Toastr for success/error messages
- **Confirmation**: SweetAlert2 for delete confirmations and unsaved changes warnings
- **Export/Import**: CSV, XLSX, PDF support via Maatwebsite Excel and DomPDF

## Modules

1. **Dashboard** - Overview statistics and recent activity
2. **Members** - Full CRUD with photo upload, DataTables listing
3. **Membership Types** - Manage membership categories
4. **Payments** - Track member fee payments
5. **Reports** - Member lists, active/inactive, dues summary
6. **Import/Export** - CSV/XLSX import, PDF/XLSX/CSV export

## Installation

### Prerequisites

- PHP 8.2+
- Composer
- Node.js (for building assets)
- MySQL 5.7+

### Step 1: Clone and Install Dependencies

```bash
composer install
npm install
```

### Step 2: Environment Configuration

```bash
cp .env.example .env
```

Update `.env` with your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=members_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 3: Generate Application Key

```bash
php artisan key:generate
```

### Step 4: Run Migrations and Seeders

```bash
php artisan migrate:fresh --seed
```

### Step 5: Create Storage Link

```bash
php artisan storage:link
```

### Step 6: Build Assets

```bash
npm run build
```

### Step 7: Start the Server

```bash
php artisan serve
```

Access the application at `http://localhost:8000`

## Default Credentials

After seeding, you can log in with:

- **Email**: admin@membersystem.com
- **Password**: password

## Roles & Permissions

| Role | Permissions |
|------|-------------|
| Super Admin | All permissions |
| Admin | All except system-level (delete members) |
| Clerk | View/Create/Edit members + payments |
| Viewer | View only |

### Permission List

- `members.menu` - Access members section
- `members.view` - View member list/details
- `members.create` - Create new members
- `members.edit` - Edit members
- `members.delete` - Delete members
- `membership_types.manage` - Manage membership types
- `payments.manage` - Manage payments
- `reports.view` - View reports
- `import.export` - Import/Export data

## Customization

### PDF Header Configuration

To customize the company name and address in PDF reports, edit:
`resources/views/admin/reports/exports/members-pdf.blade.php`

```php
// Update these lines:
<div class="company-name">Your Organization Name</div>
<div class="company-address">123 Main Street, City, State 12345 | Phone: (555) 123-4567</div>
```

### Database Schema

- `members` - Member information with profile details
- `membership_types` - Membership categories (LIFE, ANNUAL, SENIOR)
- `payments` - Payment records linked to members

## Import/Export

### Import Format

The import file should contain these columns:
- member_no (required)
- first_name (required)
- last_name (required)
- spouse_first_name
- spouse_last_name
- email
- phone
- address1
- address2
- city
- state
- zip
- membership_type_name (auto-creates if not found)
- membership_start_date
- status (ACTIVE/INACTIVE)
- receipt_no

### Export Formats

- CSV - Plain comma-separated values
- XLSX - Excel spreadsheet
- PDF - Professional formatted document

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
