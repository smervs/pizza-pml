@extends('layouts.default')
@section('content')
    <div class="m-10 flex w-full flex-col sm:flex-row">
        {{-- Form --}}
        <form method="POST" action="/orders" class="flex-1">
            <div class="flex flex-col w-full md:w-3/6">
                @csrf
                <label class="text-lg text-gray-700 font-semibold">PML Order</label>
                <textarea name="order" required rows="20" class="p-2 border border-gray-300 rounded-md w-full">{{ old('order') }}</textarea>
                @if ($errors)
                <div class="flex justify-end text-red-600">
                    {{ $errors->first() }}
                </div>
                @endif
            </div>
            <button class="border border-red-500 text-red-500 active:border-red-600 font-bold uppercase text-sm px-6 py-3 rounded-full shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="button" onclick="toggleModal('modal-id')">
                View sample PML
            </button>
            <button type="submit" class="ml-1 mt-4 bg-red-500 text-white active:bg-red-600 font-bold uppercase text-sm px-6 py-3 rounded-full shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150">Save</button>
        </form>
        {{-- /Form --}}
    </div>
    <div class="hidden overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center" id="modal-id">
        <div class="relative w-auto my-6 mx-auto max-w-3xl">
            <!--content-->
            <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                <!--header-->
                <div class="flex items-start justify-between p-5 border-b border-solid border-blueGray-200 rounded-t">
                    <h3 class="text-3xl font-semibold">
                    Sample Pizza Markup Language
                    </h3>
                </div>
                <!--body-->
                <div class="relative p-6 flex-auto">
                    <pre class="whitespace-pre-wrap">
{order number="123"}
    {pizza number="1"}
        {size}large{\size}
        {crust}hand-tossed{\crust}
        {type}custom{\type}
        {toppings area="0"}
            {item}pepperoni{\item}
            {item}extra cheese{\item}
        {\toppings}
        {toppings area="1"}
            {item}sausage{\item}
        {\toppings}
        {toppings area="2"}
            {item}mushrooms{\item}
        {\toppings}
    {\pizza}
    {pizza number="2"}
        {size}medium{\size}
        {crust}deep dish{\crust}
        {type}pepperoni feast hathsts{\type}
    {\pizza}
{\order}
                    </pre>
                </div>
                <!--footer-->
                <div class="flex items-center justify-end p-6 border-t border-solid border-blueGray-200 rounded-b">
                    <button class="text-red-500 background-transparent font-bold uppercase px-6 py-2 text-sm outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="button" onclick="toggleModal('modal-id')">
                    Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="hidden opacity-25 fixed inset-0 z-40 bg-black" id="modal-id-backdrop"></div>
    <script type="text/javascript">
    function toggleModal(modalID){
        document.getElementById(modalID).classList.toggle("hidden");
        document.getElementById(modalID + "-backdrop").classList.toggle("hidden");
        document.getElementById(modalID).classList.toggle("flex");
        document.getElementById(modalID + "-backdrop").classList.toggle("flex");
    }
    </script>
@endsection