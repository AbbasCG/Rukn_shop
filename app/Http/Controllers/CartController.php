<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cartItems = Cart::where('user_id', auth()->id())
            ->with('product')
            ->get();
        
        return view('cart.index', compact('cartItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $existingCartItem = Cart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->where('status', 'active')
            ->first();

        if ($existingCartItem) {
            // Handle both 'quantity' and 'quanttty' column names
            $quantityColumn = Schema::hasColumn('carts', 'quantity') ? 'quantity' : 'quanttty';
            $existingCartItem->$quantityColumn = ($existingCartItem->$quantityColumn ?? 0) + $request->quantity;
            $existingCartItem->save();
        } else {
            $cartData = [
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'status' => 'active',
            ];
            
            // Handle both 'quantity' and 'quanttty' column names
            $quantityColumn = Schema::hasColumn('carts', 'quantity') ? 'quantity' : 'quanttty';
            $cartData[$quantityColumn] = $request->quantity;
            
            Cart::create($cartData);
        }

        return redirect()->back()->with('success', 'Product added to cart!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cart $cart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $cart)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart)
    {
        //
    }
}
