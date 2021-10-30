<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PizzaTopping extends Model
{
    protected $table = 'pizza_topping';
    protected $fillable = ['area', 'item'];

    const TOPPING_AREAS = [
        0 => 'Whole',
        1 => 'First-Half',
        2 => 'Second-Half'
    ];

    public function getAreaNameAttribute() {
        return self::TOPPING_AREAS[$this->area];
    }
}
