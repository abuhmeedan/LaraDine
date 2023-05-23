<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateStock;
use App\Models\Ingredients;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Orders;
use Illuminate\Support\Str;


class OrdersController extends Controller
{
    public function order(Request $request)
    {
        // validate the order payload 
        $validator = Validator::make($request->all(), [
            'products.*.product_id' => 'required|uuid|exists:products,id',
            'products.*.quantity' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['code' => 40001, 'message' => $validator->errors()->all()], 400);
        }

        // Create Order and save them in database
        $consumedIngredients = [];
        foreach ($request->products as $order) {
            $productId = $order['product_id'];
            Orders::insert([
                'id' => Str::uuid()->toString(),
                'product_id' => $productId,
                'quantity' => $order['quantity'],
                'user_id' => auth('sanctum')->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $consumedIngredients[$productId] = isset($consumedIngredients[$productId]) ? $consumedIngredients[$productId] + $order['quantity'] : $order['quantity'];
        }

        // Run the Stock update Job
        UpdateStock::dispatch($consumedIngredients);

        return response(['code' => 20101, 'message' => 'Order Created'], 201);
    }
}