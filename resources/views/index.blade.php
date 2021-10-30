@extends('layouts.default')
@section('content')
    <div class="m-10 w-full">
        <div class="flex justify-end">
            <form method="GET" action="/orders">
                <input name="search" class="border border-gray-500 rounded-md h-10 p-2" placeholder="Search" value="{{ $search }}" />
                <button type="submit" class="border bg-red-500 text-white px-6 py-2 rounded-full">Search</button>
            </form>
        </div>
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 w-full gap-6">
            @foreach ($orders as $order)
            <div class="border border-red-400 shadow-xl rounded-xl p-6 cursor-pointer hover:bg-red-500 hover:text-white">
                <div>
                    Order #
                    <span class="text-lg font-semibold">{{ $order->order_number}}</span>
                </div>
                @foreach ($order->pizzas as $pizza)
                <div class="mt-4">
                    <span class="text-lg font-semibold">Pizza {{ $pizza->sequence }}</span> -
                    {{ $pizza->size }}, {{ $pizza->crust }}, {{ $pizza->type }}
                    <div class="ml-4">
                        @foreach ($pizza->toppings as $topping)
                            <div>Toppings {{ $topping->areaName }}</div>
                            <div class="ml-8">
                                @foreach (explode(',', $topping->item) as $item)
                                    <div>{{ $item }}</div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
@stop