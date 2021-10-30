<div class="flex bg-yellow-50 p-10">
    <div class="flex flex-grow items-center">
        <div class="text-md font-bold">
            <a href="/">PIZZA (PML)</a>
        </div>
    </div>
    <div class="flex items-center">
        <a href="{{ route('orders')}}" class="{{ request()->routeIs('orders') ? 'py-4 px-6 bg-red-500 rounded-full text-white shadow-xl hover:bg-red-600' : '' }}">Orders</a>
        <a href="{{ route('create-order') }}" class="ml-6 {{ request()->routeIs('create-order') ? 'py-4 px-6 bg-red-500 rounded-full text-white shadow-xl  hover:bg-red-600' : '' }}">Create Order</a>
    </div>
</div>