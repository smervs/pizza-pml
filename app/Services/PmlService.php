<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PizzaTopping;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PmlService {
    const MAX_PIZZA_PER_ORDER = 24;
    const MAX_TOPPINGS_PER_PIZZA = 3;
    const MAX_TOPPING_ITEMS = 12;

    private $order;

    public function __construct(string $pml)
    {
        $this->order = $this->parse($pml);
    }

    /**
     * Parse PML to array
     * @param $pml string
     * @return array
     */
    public function parse(string $pml)
    {
        try {
            $xml = preg_replace('/{/', '<', $pml);
            $xml = preg_replace('/}/', '>', $xml);
            $xml = preg_replace('/\\\\/', '/', $xml);

            return json_decode(json_encode(simplexml_load_string($xml)), true);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid PML.');
        }
    }

    /**
     * Save order to database
     */
    public function save()
    {
        DB::beginTransaction();

        try {
            // throws exception if invalid
            $this->validateOrder($this->order);

            // save order
            $order = new Order;
            $order->order_number = $this->order['@attributes']['number'];
            $order->save();
            $this->savePizzas($order);

            DB::commit();
            return $order->id;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Validate Order
     * @param array $order
     * @return bool | Exception
     */
    private function validateOrder($order)
    {
        // check if order is not empty
        if (!$order) {
            throw new \Exception('No order found');
        }

        $validator = Validator::make($order, [
            '@attributes.number' => 'required|integer',
            'pizza' => 'required|array|between:0,' . self::MAX_PIZZA_PER_ORDER
        ], [
            '@attributes.number.integer' => 'Invalid Order number',
            'array' => 'Invalid :attribute',
            'between' => 'Invalid number of :attribute'
        ], [
            '@attributes.number' => 'Order Number',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return true;
    }

    /**
     * Validate pizza inside order
     * @param array $pizza
     * @return bool | Exception
     */
    private function validatePizza($pizza)
    {
        // check if pizza is not empty
        if (!$pizza) {
            throw new \Exception('Invalid pizza.');
        }

        $validator = Validator::make($pizza, [
            '@attributes.number' => 'required|integer',
            'toppings' => [
                Rule::requiredIf($this->isCustomPizza($pizza)),
                'between:0,' . self::MAX_TOPPINGS_PER_PIZZA
            ]
        ], [
            'integer' => 'Invalid :attribute',
            'between' => 'Invalid number of toppings'
        ], [
            '@attributes.number' => 'Pizza Number',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return true;
    }

    /**
     * Validate toppings of pizza
     * @param array $topping
     * @return bool | Exception
     */
    private function validateTopping($topping)
    {
        // check if topping is not empty
        if (!$topping) {
            throw new \Exception('Invalid topping.');
        }

        $validator = Validator::make($topping, [
            '@attributes.area' => [
                'required',
                'integer',
                Rule::in(array_keys(PizzaTopping::TOPPING_AREAS))
            ],
            'item' => 'required|' . (
                is_array($topping['item']) ?
                    ('between:0,' . self::MAX_TOPPING_ITEMS) : ''
            )
        ], [
            'integer' => 'Invalid :attribute',
            'between' => 'Invalid number of :attribute',
            'in' => 'Invalid :attribute'
        ], [
            '@attributes.area' => 'topping area'
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return true;
    }

    /**
     * Save list of pizza of an order
     * @param Order $order
     * @return void
     */
    private function savePizzas($order)
    {
        // check if there's only one pizza
        if (isset($this->order['pizza']['@attributes'])) {
            $pizza = $this->savePizza($order, $this->order['pizza']);
            $this->saveToppingList($pizza, $this->order['pizza']);
            return;
        }

        foreach ($this->order['pizza'] as $pizzaXml) {
            $pizza = $this->savePizza($order, $pizzaXml);
            $this->saveToppingList($pizza, $pizzaXml);
        }
    }

    /**
     * Save pizza of an order
     * @param Order $order
     * @param array $pizza array from xml
     * @return OrderPizza
     */
    private function savePizza($order, $pizza)
    {
        // throw exception if invalid
        $this->validatePizza($pizza);

        return $order->pizzas()->create([
            'sequence' => $pizza['@attributes']['number'],
            'size' => $pizza['size'],
            'crust' => $pizza['crust'],
            'type' => $pizza['type'],
            'total_toppings' => $this->getTotalToppings($this->getToppings($pizza))
        ]);
    }

    /**
     * Save list of toppings in a pizza
     * @param OrderPizza $pizza
     * @param array $pizzaXml array from xml
     * @return void
     */
    private function saveToppingList($pizza, $pizzaXml)
    {
        if (! ($this->isCustomPizza($pizzaXml) && $pizza->total_toppings > 0)) {
            return;
        }

        // check if there's only on topping
        if (isset($pizzaXml['toppings']['@attributes'])) {
            $this->saveTopping($pizza, $pizzaXml['toppings']);
            return;
        }

        foreach ($pizzaXml['toppings'] as $toppingXml) {
            $this->saveTopping($pizza, $toppingXml);
        }
    }

    /**
     * Save topping of pizza
     * @param OrderPizza $pizza
     * @param array $topping array from xml
     * @return PizzaTopping
     */
    private function saveTopping($pizza, $topping)
    {
        if (!$pizza || !$topping) return;

        // throw exception if invalid
        $this->validateTopping($topping);

        return $pizza->toppings()->create([
            'area' => $topping['@attributes']['area'],
            'item' => is_array($topping['item']) ?
                        implode(',', $topping['item']) :
                        $topping['item']
        ]);
    }

    /**
     * Check if pizza type is custom
     * @param array $pizza
     * @return bool
     */
    private function isCustomPizza($pizza)
    {
        if ($pizza && isset($pizza['type'])
            && strtolower(trim($pizza['type'])) === 'custom') {
            return true;
        }

        return false;
    }

    /**
     * Get list of toppings in a pizza
     * @param array $pizza array from XML
     * @return array | null
     */
    private function getToppings($pizza)
    {
        if ($pizza
            && isset($pizza['toppings'])
            && count($pizza['toppings']) > 0) {
            // check if there's only one topping
            if (isset($pizza['toppings']['@attributes'])) {
                return [$pizza['toppings']];
            }

            return $pizza['toppings'];
        }

        return null;
    }

    /**
     * Sum of items in a toppings array
     * @param array $toppings
     * @return integer
     */
    private function getTotalToppings($toppings)
    {
        if (! $toppings) return 0;

        return array_reduce($toppings, function($total, $topping) {
            return $total + $this->getNumberOfToppingItems($topping);
        }, 0);
    }

    /**
     * Get total number of items in a topping
     * @param array $topping Array from xml
     * @return integer
     */
    private function getNumberOfToppingItems($topping)
    {
        if (! $topping) return 0;

        if (is_array($topping['item'])) {
            return count($topping['item']);
        }

        return 1;
    }
}