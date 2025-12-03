<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCityTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_creation_creates_city_and_country()
    {
        $vendor = User::factory()->create(['type' => 'vendor']);
        $category = Category::create(['name' => ['en' => 'Test Category'], 'slug' => 'test-category']);

        $response = $this->actingAs($vendor)->withoutExceptionHandling()->postJson('/api/services', [
            'category_id' => $category->id,
            'title' => ['en' => 'Test Service'],
            'description' => ['en' => 'Test Description'],
            'price_type' => 'fixed',
            'price' => 100,
            'status' => 'active',
            'city' => 'New City',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('countries', ['id' => 1]);
        // Note: Spatie Translatable stores as JSON, so exact match might be tricky in assertDatabaseHas depending on implementation
        // But we can check the model.

        $country = Country::find(1);
        $this->assertNotNull($country);

        $city = City::where('country_id', 1)->first();
        $this->assertNotNull($city);
        $this->assertEquals('New City', $city->getTranslation('name', 'en'));

        $service = Service::first();
        $this->assertNotNull($service->city_id);
        $this->assertEquals($city->id, $service->city_id);
        $this->assertEquals('New City', $service->cityRelationship->getTranslation('name', 'en'));
    }

    public function test_service_creation_uses_existing_city()
    {
        $vendor = User::factory()->create(['type' => 'vendor']);
        $category = Category::create(['name' => ['en' => 'Test Category'], 'slug' => 'test-category']);

        $country = Country::create(['id' => 1, 'name' => ['en' => 'Saudi Arabia']]);
        $city = City::create(['country_id' => 1, 'name' => ['en' => 'Existing City']]);

        $response = $this->actingAs($vendor)->withoutExceptionHandling()->postJson('/api/services', [
            'category_id' => $category->id,
            'title' => ['en' => 'Test Service'],
            'description' => ['en' => 'Test Description'],
            'price_type' => 'fixed',
            'price' => 100,
            'status' => 'active',
            'city' => 'Existing City',
        ]);

        $response->assertStatus(201);

        $this->assertCount(1, City::all());

        $service = Service::first();
        $this->assertEquals($city->id, $service->city_id);
    }

    public function test_service_update_updates_city()
    {
        $vendor = User::factory()->create(['type' => 'vendor']);
        $category = Category::create(['name' => ['en' => 'Test Category'], 'slug' => 'test-category']);

        $service = Service::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => ['en' => 'Test Service'],
            'price_type' => 'fixed',
            'status' => 'active',
        ]);

        // Initial state: Old City might not be linked if created directly, but let's assume we want to test the update logic.
        // The update logic should create 'Updated City' and link it.

        $response = $this->actingAs($vendor)->withoutExceptionHandling()->putJson("/api/services/{$service->id}", [
            'category_id' => $category->id,
            'title' => ['en' => 'Test Service'],
            'price_type' => 'fixed',
            'price' => 150,
            'status' => 'active',
            'city' => 'Updated City',
        ]);

        $response->assertStatus(200);

        $service->refresh();
        $this->assertNotNull($service->city_id);
        $this->assertEquals('Updated City', $service->cityRelationship->getTranslation('name', 'en'));
    }
}
