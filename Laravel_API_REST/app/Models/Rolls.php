<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rolls extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'die1_value',
        'die2_value',
        'result',
        'roll_date',
    ];
}
