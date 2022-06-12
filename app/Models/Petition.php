<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Petition extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'userId',
        'title',
        'directedTo',
        'description',
        'imageUrl',
        'goal',
        'isGoalCompleted',
        'registerDate',
        'status'
    ];
}
