<?php

namespace Tests\Feature;

use App\Models\Quintessential;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuintessentialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed the quintessentials
        $this->seed(\Database\Seeders\QuintessentialSeeder::class);
    }

    public function test_quintessentials_index_page_can_be_rendered(): void
    {
        $response = $this->get(route('quintessentials.index'));

        $response->assertStatus(200);
        $response->assertSee('The Ten Quintessentials');
    }

    public function test_quintessentials_index_displays_all_ten_quintessentials(): void
    {
        $response = $this->get(route('quintessentials.index'));

        $response->assertStatus(200);
        
        // Check that all 10 quintessentials are displayed
        $quintessentials = Quintessential::all();
        $this->assertCount(10, $quintessentials);
        
        foreach ($quintessentials as $quintessential) {
            $response->assertSee($quintessential->name);
            $response->assertSee($quintessential->description);
        }
    }

    public function test_quintessential_show_page_can_be_rendered(): void
    {
        $quintessential = Quintessential::where('slug', 'reflection')->first();

        $response = $this->get(route('quintessentials.show', $quintessential->slug));

        $response->assertStatus(200);
        $response->assertSee($quintessential->name);
        $response->assertSee($quintessential->description);
    }

    public function test_all_ten_quintessentials_exist_in_correct_order(): void
    {
        $expectedQuintessentials = [
            1 => 'Truth',
            2 => 'Justice',
            3 => 'Beauty',
            4 => 'Love',
            5 => 'Balance',
            6 => 'Reflection',
            7 => 'Harmonic',
            8 => 'Integration',
            9 => 'Transformation',
            10 => 'Unification',
        ];

        foreach ($expectedQuintessentials as $number => $name) {
            $quintessential = Quintessential::where('number', $number)->first();
            
            $this->assertNotNull($quintessential, "Quintessential #{$number} should exist");
            $this->assertEquals($name, $quintessential->name, "Quintessential #{$number} should be named '{$name}'");
        }
    }

    public function test_quintessential_six_through_ten_have_correct_attributes(): void
    {
        $newQuintessentials = [
            6 => ['name' => 'Reflection', 'slug' => 'reflection'],
            7 => ['name' => 'Harmonic', 'slug' => 'harmonic'],
            8 => ['name' => 'Integration', 'slug' => 'integration'],
            9 => ['name' => 'Transformation', 'slug' => 'transformation'],
            10 => ['name' => 'Unification', 'slug' => 'unification'],
        ];

        foreach ($newQuintessentials as $number => $attributes) {
            $quintessential = Quintessential::where('number', $number)->first();
            
            $this->assertNotNull($quintessential);
            $this->assertEquals($attributes['name'], $quintessential->name);
            $this->assertEquals($attributes['slug'], $quintessential->slug);
            $this->assertNotNull($quintessential->description);
            $this->assertNotNull($quintessential->content);
            $this->assertNotNull($quintessential->icon);
            $this->assertNotNull($quintessential->color);
        }
    }

    public function test_quintessential_navigation_links_work(): void
    {
        $reflection = Quintessential::where('slug', 'reflection')->first();
        
        $response = $this->get(route('quintessentials.show', $reflection->slug));
        
        $response->assertStatus(200);
        
        // Should have link back to index
        $response->assertSee(route('quintessentials.index'));
    }
}
