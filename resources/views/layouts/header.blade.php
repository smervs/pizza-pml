<div class="flex bg-yellow-50 p-10">
    <div class="flex flex-grow">
        <div class="text-md font-bold">PIZZA MARKUP LANGUAGE</div>
    </div>
    <div>
        <a href="{{ route('orders')}}" class="{{ request()->routeIs('orders') ? 'py-4 px-6 bg-red-500 rounded-full text-white shadow-xl' : '' }}">Orders</a>
        <a href="{{ route('create-order') }}" class="ml-6 {{ request()->routeIs('create-order') ? 'py-4 px-6 bg-red-500 rounded-full text-white shadow-xl' : '' }}">Create Order</a>
    </div>
</div>