<?php

namespace Tests\Feature\Orders;

use App\Http\Controllers\OrdersController;
use App\Jobs\UpdateStock;
use App\Models\Ingredients;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class OrdersControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function it_can_create_an_order()
    {
        $user = Users::factory()->create();
        $this->actingAs($user, 'sanctum');
        $quantity = 3;
        $beefId = Ingredients::factory()->create(['name' => 'BEEF', 'total_weight' => 10000, 'remaining_weight' => 10000, 'weight_unit' => 'GRAM'])->id;
        $cheeseId = Ingredients::factory()->create(['name' => 'CHEESE', 'total_weight' => 10000, 'remaining_weight' => 10000, 'weight_unit' => 'GRAM'])->id;
        $onionId = Ingredients::factory()->create(['name' => 'ONION', 'total_weight' => 10000, 'remaining_weight' => 10000, 'weight_unit' => 'GRAM'])->id;

        $product = Products::factory()->create([
            'name' => 'Burger',
            'ingredients' => [['id' => $beefId, 'weight' => 150], ['id' => $cheeseId, 'weight' => 30], ['id' => $onionId, 'weight' => 20]]
        ]);
        $request = Request::create('api/order', 'POST', [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                ],
            ],
        ]);

        $controller = new OrdersController();
        $response = $controller->order($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(['code' => 20101, 'message' => 'Order Created'], json_decode($response->getContent(), true));

        $order = Orders::latest('id')->first();
        $this->assertEquals($product->id, $order->product_id);
        $this->assertEquals($quantity, $order->quantity);
    }


    /**
     * @test
     */
    public function job_will_update_the_stock()
    {
        $user = Users::factory()->create();
        $this->actingAs($user, 'sanctum');

        //total and remainning weight are same cause still nothing consumed at this step and the the UpdateStock job will update the remainning
        $totalWeight = 10000;
        $beefId = Ingredients::factory()->create(['name' => 'BEEF', 'total_weight' => $totalWeight, 'remaining_weight' => $totalWeight, 'weight_unit' => 'GRAM'])->id;
        $cheeseId = Ingredients::factory()->create(['name' => 'CHEESE', 'total_weight' => $totalWeight, 'remaining_weight' => $totalWeight, 'weight_unit' => 'GRAM'])->id;
        $onionId = Ingredients::factory()->create(['name' => 'ONION', 'total_weight' => $totalWeight, 'remaining_weight' => $totalWeight, 'weight_unit' => 'GRAM'])->id;

        $product = Products::factory()->create([
            'name' => 'Burger',
            'ingredients' => [['id' => $beefId, 'weight' => 150], ['id' => $cheeseId, 'weight' => 30], ['id' => $onionId, 'weight' => 20]]
        ]);

        // Create an instance of the UpdateStock job
        $quantity = 2;
        $job = new UpdateStock([$product->id => $quantity]);

        // Call the handle method
        $job->handle();

        // Calculate the consumed Ingredients
        foreach ($product->ingredients as $ingredient) {
            $updatedIngredients = Ingredients::find($ingredient->id);
            $calcWeight = $totalWeight - ($ingredient->weight * $quantity);
            $this->assertEquals($calcWeight, $updatedIngredients->remaining_weight);
        }
    }
}