# School Management System (Laravel 13)

A school administration app built with Laravel 13, Tailwind CSS 4 and Alpine.js,
with role-based dashboards for admins, teachers, students and parents.

## Features

**People and structure**
- **Students**: profiles with photo, class and section, login account, parents/guardians.
  The profile shows fees, attendance, exam results, library loans, transport and hostel.
- **Teachers**: linked to a user account and a department.
- **Departments, classes and sections, subjects** (subjects per class).
- **Users**: roles Super Admin, Admin, Teacher, Staff, Student, Parent, Client, Normal User.

**Academics**
- **Attendance**: daily register per section (present, absent, late, excused) and a date-range report.
- **Exams**: marks entry per subject, result sheet with grades and ranks, printable report cards.
- **Timetable**: weekly timetable per section; a teacher cannot be booked twice in one period.

**Finance**
- **Fee structures** per class, **fee balances** per student, **fee collection**.
- **Invoices**: line items, charges, discounts and tax (totals calculated on the server),
  status tabs, filters, printable invoice.
- **Expenses**, **payroll** (net salary calculated, copy to next month) and a monthly **finance overview**.

**Communication**
- **Notice board** and **events calendar**, each targeted at everyone, staff, teachers, students or parents.
- **Parent dashboard**: each linked child's fees, attendance, results and school services.

**Operations**
- **Library**: books, issue and return, overdue list, daily late fine.
- **Transport**: vehicles, routes with stops, students per route within seat capacity.
- **Hostel**: rooms and bed allocations.

**Settings**: school name and contact, currency, invoice defaults, tax, bank details, library rules.

## Who can do what

| Role | Access |
|------|--------|
| Super Admin, Admin | Everything |
| Teacher | Teacher dashboard; view students, teachers, departments, subjects; take attendance; enter marks |
| Student | Own dashboard: class, subjects, attendance, results, fees, timetable |
| Parent | Dashboard with each linked child |
| Everyone | Notice board, events, timetable, own profile |

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js 20 or newer and npm
- MySQL 8 (or MariaDB / SQLite)

## Installation

```bash
git clone https://github.com/StarCodeKh/School-Management-System-Laravel-13.git
cd School-Management-System-Laravel-13

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Create a database (default name `school_sm_db`) and set these values in `.env`:

| Key | Purpose | Default |
|-----|---------|---------|
| `APP_NAME` | School name until it is changed under Settings | `School Management System` |
| `DB_*` | Database connection | MySQL, `school_sm_db` |
| `ADMIN_NAME` | Name of the first Super Admin | `Super Admin` |
| `ADMIN_EMAIL` | Login email of the first Super Admin | `admin@system.com` |
| `ADMIN_PASSWORD` | Password of the first Super Admin | `Password@123` |

Then:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

Open http://127.0.0.1:8000 and log in with `ADMIN_EMAIL` / `ADMIN_PASSWORD`
(by default `admin@system.com` / `Password@123`). Change the password after the first login
(Profile → Change password).

The seeder also creates the sidebar menu, classes 10–12 with sections A–C and the default fee types.
It is safe to run again (`php artisan db:seed`): existing records are kept and only missing ones are added.

### Development

```bash
composer dev
```

Runs the PHP server, queue worker, log viewer and Vite (hot reload) together.

## Testing

```bash
php artisan test
```

Feature tests cover access control, every page rendering, validation rules,
invoice total calculation, settings and dashboards.

## Project structure

| Path | Contents |
|------|----------|
| `app/Enums` | Fixed value lists: roles, user status, gender, blood group, religion, invoice status |
| `app/Http/Controllers` | One controller per area (students, teachers, invoices, settings, ...) |
| `app/Models` | Eloquent models; `Setting` holds school-wide settings |
| `database/migrations` | One table per migration file |
| `database/seeders` | Admin user, sidebar menu, classes and fee types |
| `resources/views/components` | Shared Blade components: layouts, sidebar, form fields, tables, buttons |
| `resources/js/app.js` | Alpine.js setup and the invoice form component |
| `resources/css/app.css` | Tailwind CSS entry point and theme |

## Tech stack

- [Laravel 13](https://laravel.com)
- [Tailwind CSS 4](https://tailwindcss.com) with `@tailwindcss/forms`
- [Alpine.js 3](https://alpinejs.dev)
- [Heroicons](https://heroicons.com) (MIT) as inline SVG
- Vite for asset bundling
