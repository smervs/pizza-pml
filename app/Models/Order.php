<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = ['order_number'];

    public function pizzas()
    {
        return $this->hasMany(OrderPizza::class);
    }

    public static function search(string $search)
    {
        return self::with('pizzas.toppings')->where(function($query) use ($search) {
            if ($search) {
                $query->whereHas('pizzas', function ($query) use ($search) {
                    $query->where('size', 'LIKE', $search . '%')
                        ->orWhere('crust', 'LIKE', $search . '%')
                        ->orWhere('type', 'LIKE', $search . '%');

                    if (is_numeric($search)) {
                        $query->orWhere('total_toppings', $search);
                    }
                });
            }
        })->orderBy('id', 'DESC')->get();
    }
}
