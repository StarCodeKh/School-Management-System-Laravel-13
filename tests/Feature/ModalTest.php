<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_failed_save_reopens_only_its_own_modal(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin]);

        $this->actingAs($admin)->from('/transport')->followingRedirects()
            ->post(route('transport.vehicles.store'), ['_modal' => 'add-vehicle', 'registration_number' => 'TEST-1', 'capacity' => 999])
            ->assertOk()
            ->assertSee("modal({ name: 'add-vehicle', open: true })", false)
            ->assertSee("modal({ name: 'add-route', open: false })", false)
            ->assertSee('The capacity field must not be greater than 200.');
    }

    public function test_add_forms_live_in_modals(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::SuperAdmin]));

        foreach (['/transport' => 'add-route', '/hostel' => 'allocate', '/class/list/page' => 'add-class', '/fee-structures' => 'set-fee'] as $uri => $modal) {
            $this->get($uri)->assertOk()
                ->assertSee("\$dispatch('open-modal', '{$modal}')", false)
                ->assertSee("modal({ name: '{$modal}'", false);
        }
    }
}
