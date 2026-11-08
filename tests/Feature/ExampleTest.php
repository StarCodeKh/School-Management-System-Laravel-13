<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_sends_guests_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect('/home');
        $this->get('/home')->assertRedirect('/login');
        $this->get('/login')->assertOk();
    }
}
