<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PizzaTopping;
use Illuminate\Support\Facades\DB;

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
            // throw exception if invalid
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

    private function validateOrder($order)
    {
        // check if order is not empty
        if (!$order) {
            throw new \Exception('No order found.');
        }

        // check if id is a number
        if (! (isset($order['@attributes']['number'])
            && is_numeric($order['@attributes']['number']))) {
            throw new \Exception('Invalid Order Number.');
        }

        // check if has pizza and count is less than or equal to max pizza per order
        $pizzaCount = (isset($order['pizza']) && is_array($order['pizza'])) ? count($order['pizza']) : 0;
        if ($pizzaCount == 0 || $pizzaCount > self::MAX_PIZZA_PER_ORDER) {
            throw new \Exception('Invalid number of pizza.');
        }

        return true;
    }

    private function validatePizza($pizza)
    {
        // check if pizza is not empty
        if (!$pizza) {
            throw new \Exception('Invalid pizza.');
        }

        // check if id is a number
        if (!(isset($pizza['@attributes']['number'])
            && is_numeric($pizza['@attributes']['number']))) {
            throw new \Exception('Invalid Pizza Number.');
        }

        // check number of toppings for custom pizza
        // should be less than the max toppings per pizza
        if ($this->isCustomPizza($pizza)
            && isset($pizza['toppings'])
            && count($pizza['toppings']) > self::MAX_TOPPINGS_PER_PIZZA) {
            throw new \Exception('Invalid Number of Toppings.');
        }

        return true;
    }

    private function validateTopping($topping)
    {
        // check if topping is not empty
        if (!$topping) {
            throw new \Exception('Invalid topping.');
        }

        // check if area is valid
        if (! (isset($topping['@attributes']['area'])
            && isset(PizzaTopping::TOPPING_AREAS[$topping['@attributes']['area']]))) {
            throw new \Exception('Invalid Topping Area.');
        }

        // check topping items if exist
        // and less than the max number of toppings
        if (!isset($topping['item'])
            || $this->getNumberOfToppingItems($topping) === 0
            || $this->getNumberOfToppingItems($topping) > self::MAX_TOPPING_ITEMS) {
            throw new \Exception('Invalid Number of Topping Items.');
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
        foreach ($this->order['pizza'] as $pizzaXml) {
            // throw exception if invalid
            $this->validatePizza($pizzaXml);
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

        foreach ($pizzaXml['toppings'] as $toppingXml) {
            // throw exception if invalid
            $this->validateTopping($toppingXml);
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
        if ($pizza && isset($pizza['type']) && strtolower($pizza['type']) === 'custom') {
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
        if ($pizza && isset($pizza['toppings']) && count($pizza['toppings']) > 0) {
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