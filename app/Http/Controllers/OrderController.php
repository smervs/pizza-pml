<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PmlService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchText = $request->search ?? '';
        $orders = Order::search($searchText);

        return view('index', [
            'search' => $searchText,
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if (! $request->order) {
                throw new \Exception('Invalid PML');
            }

            $pmlService = new PmlService($request->order);
            $pmlService->save();

            return redirect()->route('orders');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
