<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    // public function home()
    // {
    //     $products = Product::where('is_active', true)
    //         ->with('category')
    //         ->latest()
    //         ->take(10)
    //         ->get();
        
    //     return view('home', compact('products'));
    // }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }
}
