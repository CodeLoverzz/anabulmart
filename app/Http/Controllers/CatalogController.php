<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('variants');

        if ($request->has('q') && !empty($request->q)) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $products = $query->latest()->get();

        return view('catalog.index', compact('products'));
    }

    public function show($id)
    {
        $product = Product::with('variants')->where('id', $id)->orWhere('slug', $id)->firstOrFail();
        
        return view('catalog.show', compact('product'));
    }
}