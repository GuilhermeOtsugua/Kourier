<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows a Koúrier landing page with auth actions and demo credentials', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Koúrier')
        ->assertSee('Secure dataset tooling')
        ->assertSee('Log in')
        ->assertSee('Register')
        ->assertSee('otsugua@example.com')
        ->assertSee('pass');
});

it('points authenticated users to their current team dashboard', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $this
        ->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Open dashboard')
        ->assertSee(route('dashboard', $team), false);
});
