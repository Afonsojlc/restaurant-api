<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reservation extends Model
{
    protected $fillable = ['user_id', 'reserved_at', 'guests', 'notes', 'status'];
    protected $casts = ['reserved_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Uma reserva pode incluir vários pratos (com quantidade)
    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class)->withPivot('quantity');
    }
}