<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CountriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class CountryTest extends TestCase
{
    use RefreshDatabase;

    public function testCountriesCanBeFetched()
    {
        $this->seed(CountriesSeeder::class);

        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->getJson('/api/get-countries')
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('countries')->has('countries.0'));
    }
}
