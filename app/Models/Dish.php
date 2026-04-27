<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dish extends Model
{
    protected $fillable = ['name', 'description', 'price', 'available', 'allergens', 'category_id'];
    protected $casts = ['available' => 'boolean', 'price' => 'decimal:2'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Um prato pode aparecer em várias reservas
    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class)->withPivot('quantity');
    }
}