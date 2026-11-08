<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\FeeType;
use App\Models\Menu;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_account_comes_from_config(): void
    {
        config(['school.admin' => ['name' => 'Principal', 'email' => 'principal@school.test', 'password' => 'Secret@2026']]);

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'principal@school.test')->firstOrFail();
        $this->assertSame('Principal', $admin->name);
        $this->assertSame(Role::SuperAdmin, $admin->role);
        $this->assertTrue(Hash::check('Secret@2026', $admin->password));
        $this->assertNotNull($admin->code);
    }

    public function test_seeding_twice_does_not_duplicate_or_fail(): void
    {
        $this->seed(DatabaseSeeder::class);
        $counts = [User::count(), Menu::count(), FeeType::count(), SchoolClass::count(), Section::count()];

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($counts, [User::count(), Menu::count(), FeeType::count(), SchoolClass::count(), Section::count()]);
        $this->assertSame([1, 3, 9], [User::count(), SchoolClass::count(), Section::count()]);
    }

    public function test_reseeding_keeps_a_changed_admin_password(): void
    {
        $this->seed(DatabaseSeeder::class);
        User::where('email', config('school.admin.email'))->update(['password' => Hash::make('Changed@123')]);

        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Hash::check('Changed@123', User::where('email', config('school.admin.email'))->value('password')));
    }

    public function test_school_name_defaults_to_app_name(): void
    {
        config(['app.name' => 'Angkor High School']);

        $this->assertSame('Angkor High School', Setting::value('school_name'));

        Setting::store(['school_name' => 'Bayon College']);
        $this->assertSame('Bayon College', Setting::value('school_name'));
    }
}
