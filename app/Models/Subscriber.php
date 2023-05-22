<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $table = 'category_subscribers';
    protected $fillable = ['category_id', 'user_id'];
    use HasFactory;
}
