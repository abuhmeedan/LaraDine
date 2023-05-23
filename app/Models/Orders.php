<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Traits\Uuids;

class Orders extends Model
{
    use HasFactory, Uuids;
    /**
     * Get the phone associated with the user.
     */
    public function userId(): HasOne
    {
        return $this->hasOne(Users::class, 'foreign_key');
    }
    /**
     * Get the phone associated with the user.
     */
    public function productId(): HasOne
    {
        return $this->hasOne(Products::class, 'foreign_key');
    }
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];
}