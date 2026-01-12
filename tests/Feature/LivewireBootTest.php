<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LivewireBootTest extends TestCase
{
    public function test_home_contains_livewire_component(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(\App\Livewire\Pages\Home::class);
    }
}
