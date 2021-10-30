<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPizza extends Model
{
    protected $table = 'order_pizza';
    protected $fillable = ['sequence', 'size', 'crust', 'type', 'total_toppings'];

    public function toppings()
    {
        return $this->hasMany(PizzaTopping::class);
    }
}
