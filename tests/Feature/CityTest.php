<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_all_cities()
    {
        $country = Country::create(['id' => 1, 'name' => ['en' => 'Saudi Arabia', 'ar' => 'السعودية']]);

        City::create(['country_id' => 1, 'name' => ['en' => 'Riyadh', 'ar' => 'الرياض']]);
        City::create(['country_id' => 1, 'name' => ['en' => 'Jeddah', 'ar' => 'جدة']]);

        $response = $this->getJson('/api/cities');

        $response->assertStatus(200);
        $response->assertJSONStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                ],
            ],
        ]);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_filter_cities_by_country()
    {
        $country1 = Country::create(['id' => 1, 'name' => ['en' => 'Saudi Arabia']]);
        $country2 = Country::create(['id' => 2, 'name' => ['en' => 'UAE']]);

        City::create(['country_id' => 1, 'name' => ['en' => 'Riyadh']]);
        City::create(['country_id' => 2, 'name' => ['en' => 'Dubai']]);

        $response = $this->getJson('/api/cities?country_id=1');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Riyadh', $response->json('data.0.name'));
    }

    public function test_can_search_cities_by_name()
    {
        $country = Country::create(['id' => 1, 'name' => ['en' => 'Saudi Arabia']]);

        City::create(['country_id' => 1, 'name' => ['en' => 'Riyadh', 'ar' => 'الرياض']]);
        City::create(['country_id' => 1, 'name' => ['en' => 'Jeddah', 'ar' => 'جدة']]);

        $response = $this->getJson('/api/cities?search=Riyadh');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Riyadh', $response->json('data.0.name'));
    }
}
