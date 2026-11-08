# School Management System (Laravel 13)

A school administration app built with Laravel 13, Tailwind CSS 4 and Alpine.js.
It manages students, teachers, departments, classes, subjects, invoices and fee payments,
with role-based dashboards for admins, teachers and students.

## Features

- **Dashboards**: admin overview, a teacher dashboard (own profile, department, colleagues)
  and a student dashboard (class, subjects, fee payments, invoices).
- **Students**: profiles with photo, class and section, optional link to a login account.
- **Teachers**: profiles linked to a user account and a department.
- **Departments**: head of department chosen from the teachers.
- **Classes and sections**: managed on the Classes page; used by student and subject forms.
- **Subjects**: per class.
- **Invoices**: line items, extra charges, discounts and tax with live totals
  (recalculated on the server), status tabs (paid, overdue, draft, recurring, cancelled),
  filters, list and grid views, printable invoice page.
- **Fees collection**: record payments per student and fee type.
- **User management**: roles (Super Admin, Admin, Teacher, Student, Staff, Client, Normal User)
  and status; admin-only menus are hidden from other roles.
- **Settings**: school name and contact, currency symbol, invoice defaults, tax and bank details.

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

Create a database (default name `school_sm_db`), set the `DB_*` values in `.env`, then:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

Open http://127.0.0.1:8000 and log in with the seeded admin account:

| Email               | Password       |
|---------------------|----------------|
| `admin@system.com`  | `Password@123` |

Change this password after the first login (Profile → Change password).

The seeder also creates the sidebar menu, classes 10–12 with sections A–C and the default fee types.

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
