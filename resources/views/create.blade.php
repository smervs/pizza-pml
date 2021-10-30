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
            <button type="submit" class="ml-1 bg-red-500 text-white px-6 py-2 rounded-full mt-4 shadow-xl hover:bg-red-600">Save</button>
        </form>
        {{-- /Form --}}
    </div>
@endsection