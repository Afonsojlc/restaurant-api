<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration and token generation.
     */
    public function test_user_can_register_and_receive_sanctum_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'phone' => '912345678',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'token', 'user' => ['id', 'name', 'email', 'role']]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'client',
        ]);
    }

    /**
     * Test public access to categories and dishes menu.
     */
    public function test_public_can_view_active_categories_and_dishes(): void
    {
        $category = Category::create([
            'name' => 'Appetizers',
            'active' => true,
            'description' => 'Starters'
        ]);

        Dish::create([
            'name' => 'Garlic Bread',
            'price' => 4.50,
            'available' => true,
            'category_id' => $category->id,
        ]);

        $responseCategories = $this->getJson('/api/categories');
        $responseCategories->assertStatus(200)
            ->assertJsonFragment(['name' => 'Appetizers']);

        $responseDishes = $this->getJson('/api/dishes');
        $responseDishes->assertStatus(200)
            ->assertJsonFragment(['name' => 'Garlic Bread']);
    }

    /**
     * Test customer creating a reservation with dishes linked via pivot.
     */
    public function test_authenticated_client_can_book_reservation_with_dishes(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $category = Category::create(['name' => 'Mains', 'active' => true]);
        $dish = Dish::create([
            'name' => 'Grilled Salmon',
            'price' => 19.50,
            'available' => true,
            'category_id' => $category->id
        ]);

        $response = $this->actingAs($client, 'sanctum')->postJson('/api/reservations', [
            'reserved_at' => now()->addDays(2)->toDateTimeString(),
            'guests' => 4,
            'notes' => 'Quiet table please',
            'dishes' => [
                ['id' => $dish->id, 'quantity' => 2]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['guests' => 4, 'status' => 'pending']);

        $this->assertDatabaseHas('reservations', [
            'user_id' => $client->id,
            'guests' => 4,
            'status' => 'pending'
        ]);
    }

    /**
     * Test admin-only authorization guard on category creation.
     */
    public function test_non_admin_cannot_create_category(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client, 'sanctum')->postJson('/api/categories', [
            'name' => 'Unauthorized Category',
            'active' => true,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin can update reservation status.
     */
    public function test_admin_can_confirm_reservation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);

        $reservation = Reservation::create([
            'user_id' => $client->id,
            'reserved_at' => now()->addDays(1),
            'guests' => 2,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin, 'sanctum')->patchJson("/api/reservations/{$reservation->id}/status", [
            'status' => 'confirmed'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'confirmed']);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'confirmed'
        ]);
    }
}
