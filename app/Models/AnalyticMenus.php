<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticMenus extends Model
{
    use HasFactory;

    const NAME = 'name';

    protected $table = 'analytic_menus';
    protected $fillable = [self::NAME];
}
