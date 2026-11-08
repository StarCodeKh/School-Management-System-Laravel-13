<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * The sidebar menu. Each top-level entry may have children; "routes" lists the
     * route names that mark an item active, "pattern" adds URL patterns.
     */
    private const MENU = [
        ['title' => 'Dashboard', 'icon' => 'home', 'children' => [
            ['title' => 'Admin Dashboard', 'route' => 'home'],
            ['title' => 'Teacher Dashboard', 'route' => 'teacher/dashboard'],
            ['title' => 'Student Dashboard', 'route' => 'student/dashboard'],
        ]],
        ['title' => 'User Management', 'icon' => 'shield', 'ability' => 'manage-users', 'pattern' => 'view/user/edit/*', 'children' => [
            ['title' => 'List Users', 'route' => 'list/users'],
        ]],
        ['title' => 'Students', 'icon' => 'academic-cap', 'pattern' => 'student/edit/*|student/profile/*', 'children' => [
            ['title' => 'Student List', 'route' => 'student/list', 'routes' => ['student/list', 'student/grid']],
            ['title' => 'Student Add', 'route' => 'student/add/page'],
        ]],
        ['title' => 'Teachers', 'icon' => 'users', 'pattern' => 'teacher/edit/*', 'children' => [
            ['title' => 'Teacher List', 'route' => 'teacher/list/page', 'routes' => ['teacher/list/page', 'teacher/grid/page']],
            ['title' => 'Teacher Add', 'route' => 'teacher/add/page'],
        ]],
        ['title' => 'Departments', 'icon' => 'building', 'pattern' => 'department/edit/*', 'children' => [
            ['title' => 'Department List', 'route' => 'department/list/page'],
            ['title' => 'Department Add', 'route' => 'department/add/page'],
        ]],
        ['title' => 'Classes', 'icon' => 'squares', 'route' => 'class/list/page'],
        ['title' => 'Subjects', 'icon' => 'book', 'pattern' => 'subject/edit/*', 'children' => [
            ['title' => 'Subject List', 'route' => 'subject/list/page'],
            ['title' => 'Subject Add', 'route' => 'subject/add/page'],
        ]],
        ['title' => 'Invoices', 'icon' => 'document', 'pattern' => 'invoice/edit/*|invoice/view/*', 'children' => [
            ['title' => 'Invoices List', 'route' => 'invoice/list/page', 'routes' => [
                'invoice/list/page', 'invoice/paid/page', 'invoice/overdue/page', 'invoice/draft/page',
                'invoice/recurring/page', 'invoice/cancelled/page', 'invoice/grid/page',
            ]],
            ['title' => 'Add Invoice', 'route' => 'invoice/add/page'],
        ]],
        ['title' => 'Accounts', 'icon' => 'banknotes', 'children' => [
            ['title' => 'Fees Collection', 'route' => 'account/fees/collections/page'],
            ['title' => 'Add Fees', 'route' => 'add/fees/collection/page'],
        ]],
        ['title' => 'Settings', 'icon' => 'cog', 'ability' => 'manage-settings', 'children' => [
            ['title' => 'General', 'route' => 'setting/page'],
            ['title' => 'Invoice', 'route' => 'invoice/settings/page'],
            ['title' => 'Tax', 'route' => 'invoice/settings/tax/page'],
            ['title' => 'Bank', 'route' => 'invoice/settings/bank/page'],
        ]],
    ];

    public function run(): void
    {
        Menu::query()->delete();

        foreach (self::MENU as $order => $item) {
            $parent = $this->create($item, $order + 1);

            foreach ($item['children'] ?? [] as $childOrder => $child) {
                $this->create($child + ['ability' => $item['ability'] ?? null], $childOrder + 1, $parent);
            }
        }
    }

    private function create(array $item, int $order, ?Menu $parent = null): Menu
    {
        return Menu::create([
            'parent_id' => $parent?->id,
            'title' => $item['title'],
            'icon' => $item['icon'] ?? null,
            'route' => $item['route'] ?? null,
            'active_routes' => $item['routes'] ?? array_filter([$item['route'] ?? null]),
            'pattern' => $item['pattern'] ?? null,
            'ability' => $item['ability'] ?? null,
            'sort_order' => $order,
            'is_active' => true,
        ]);
    }
}
