<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Ingredients;
use App\Models\Products;
use App\Models\Users;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $user = Users::factory(10)->create();
        $this->assertEquals(10, Users::get()->count());
        $user = Users::first();
        $this->actingAs($user);
        $quantity = 3;
        $beefId = Ingredients::factory()->create(['name' => 'BEEF', 'total_weight' => 10000, 'remaining_weight' => 10000, 'weight_unit' => 'GRAM'])->id;
        $cheeseId = Ingredients::factory()->create(['name' => 'CHEESE', 'total_weight' => 10000, 'remaining_weight' => 10000, 'weight_unit' => 'GRAM'])->id;
        $onionId = Ingredients::factory()->create(['name' => 'ONION', 'total_weight' => 10000, 'remaining_weight' => 10000, 'weight_unit' => 'GRAM'])->id;

        $product = Products::factory()->create([
            'name' => 'Burger',
            'ingredients' => [['id' => $beefId, 'weight' => 150], ['id' => $cheeseId, 'weight' => 30], ['id' => $onionId, 'weight' => 20]]
        ]);
        $response = $this->post('/api/order', [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                ],
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(['code' => 20101, 'message' => 'Order Created'], json_decode($response->getContent(), true));
    }

    public function test_the_application_returns_a_unauthorized_response(): void
    {

        $quantity = 3;
        $beefId = Ingredients::first()->id;
        $cheeseId = Ingredients::first()->id;
        $onionId = Ingredients::first()->id;

        $product = Products::factory()->create([
            'name' => 'Burger',
            'ingredients' => [['id' => $beefId, 'weight' => 150], ['id' => $cheeseId, 'weight' => 30], ['id' => $onionId, 'weight' => 20]]
        ]);
        $response = $this->post('/api/order', [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                ],
            ],
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(["message" => "Unauthenticated."], json_decode($response->getContent(), true));
    }
}