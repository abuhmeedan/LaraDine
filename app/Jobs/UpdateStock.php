<?php

namespace App\Jobs;

use App\Models\Ingredients;
use App\Models\Products;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UpdateStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $consumedIngredients = null;

    public function __construct($consumedIngredients)
    {
        $this->consumedIngredients = $consumedIngredients;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // All Ordered Products by ID to get the Product Ingredients Array
        $products = Products::findMany(array_keys($this->consumedIngredients));

        // Caculate the Consumed Ingredients and Prepare the Array for Email Notification
        $ingredients = [];
        foreach ($products as $product) {
            foreach ($product->ingredients as $ingredient) {
                $ingredientId = $ingredient->id;
                $totalWeight = $ingredient->weight * $this->consumedIngredients[$product->id];
                $ingredients[$ingredientId] = isset($ingredients[$ingredientId]) ? $ingredients[$ingredientId] + $totalWeight : $totalWeight;
            }
        }

        $toBeNotifiedIngredients = [];
        foreach ($ingredients as $ingredientId => $weight) {
            // Calculate the Remainning in the Stock for each Ingredient
            $ingredient = Ingredients::find($ingredientId);
            $remainingWeight = $ingredient->remaining_weight - $weight;
            $ingredient->remaining_weight = $remainingWeight;

            if ($remainingWeight / $ingredient->total_weight * 100 < 50 && $ingredient->stock_status != 'NOTIFIED') {
                $ingredient->stock_status = 'NOTIFIED';
                $toBeNotifiedIngredients[] = $ingredient->name;
            }
            // Update the stock with the Remainning Ingredients and the warning Email status
            $ingredient->save();
        }

        // Send Warning Email for all Ingredients below 50%
        if (!empty($toBeNotifiedIngredients)) {
            $ingredient->stock_status = 'NOTIFIED';
            $details = [
                'title' => implode(', ', $toBeNotifiedIngredients),
            ];
            \Mail::to('abuhmeedan@gmail.com')->send(new \App\Mail\StockStatusMailer($details));
        }
    }
}