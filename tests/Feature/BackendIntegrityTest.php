<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Plant;
use App\Models\PlantType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BackendIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_marked_as_read(): void
    {
        $user = User::factory()->create();
        $notification = Notification::create([
            'user_id' => $user->id,
            'message' => 'Order placed',
            'type' => 'order_placed',
        ]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('notification.is_read', true);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_order_rejects_duplicate_items_when_combined_quantity_exceeds_stock(): void
    {
        $user = User::factory()->create();
        $plant = $this->createPlant(['stock' => 3]);

        Sanctum::actingAs($user);

        $this->postJson('/api/orders', [
            'items' => [
                ['plant_id' => $plant->id, 'quantity' => 2],
                ['plant_id' => $plant->id, 'quantity' => 2],
            ],
            'recipient_name' => 'Customer',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jalan Test No. 1',
            'shipping_method' => 'standard',
        ])->assertStatus(400);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(3, $plant->fresh()->stock);
    }

    public function test_plant_catalog_handles_invalid_sort_order_and_per_page(): void
    {
        $this->createPlant();

        $this->getJson('/api/plants?sort_by=name&sort_order=random&per_page=999')
            ->assertOk()
            ->assertJsonPath('plants.per_page', 100);
    }

    public function test_deleting_completed_order_does_not_restore_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $plant = $this->createPlant(['stock' => 5]);
        $order = Order::create([
            'user_id' => $user->id,
            'invoice_number' => 'INV-20260629-0001',
            'recipient_name' => 'Customer',
            'recipient_phone' => '081234567890',
            'total_price' => 100000,
            'status' => 'completed',
            'shipping_address' => 'Jalan Test No. 1',
            'shipping_method' => 'standard',
            'shipping_cost' => 20000,
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'plant_id' => $plant->id,
            'quantity' => 2,
            'price' => 50000,
        ]);

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/admin/orders/{$order->id}")
            ->assertOk();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertSame(5, $plant->fresh()->stock);
    }

    private function createPlant(array $attributes = []): Plant
    {
        $category = Category::create(['name' => 'Tanaman Hias', 'slug' => 'tanaman-hias']);
        $plantType = PlantType::create(['name' => 'Indoor', 'slug' => 'indoor']);

        return Plant::create(array_merge([
            'name' => 'Monstera',
            'slug' => 'monstera',
            'description' => 'Tanaman hias indoor.',
            'price' => 50000,
            'stock' => 10,
            'category_id' => $category->id,
            'plant_type_id' => $plantType->id,
        ], $attributes));
    }
}
