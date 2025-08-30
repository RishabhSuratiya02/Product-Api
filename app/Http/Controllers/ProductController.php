<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $fileName = 'products.json';

 //for getting products by id
    private function getProducts()
    {
      
        if (!Storage::exists($this->fileName)) {
            Storage::put($this->fileName, json_encode([]));
        }

       
        return json_decode(Storage::get($this->fileName), true);
    }


    private function saveProducts($products)
    {
        Storage::put($this->fileName, json_encode($products, JSON_PRETTY_PRINT));
    }

 
    public function store(Request $request)
    {
        
        $validatedData = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
        ]);

       
        $products = $this->getProducts();

        $id = count($products) + 1;

        $newProduct = array_merge(['id' => $id], $validatedData);

        $products[$id] = $newProduct;
        $this->saveProducts($products);

        return response()->json($newProduct, 201);
    }

   
    public function show($id)
    {
        $products = $this->getProducts();

        if (!isset($products[$id])) {
            return response()->json([
                'error' => 'Product not found'
            ], 404);
        }

        return response()->json($products[$id]);
    }

    public function update(Request $request, $id)
    {
        $products = $this->getProducts();

        if (!isset($products[$id])) {
            return response()->json([
                'error' => 'Product not found'
            ], 404);
        }

        $validatedData = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes|string|nullable',
            'price'       => 'sometimes|numeric|min:0',
            'quantity'    => 'sometimes|integer|min:0',
        ]);

        $products[$id] = array_merge($products[$id], $validatedData);

        $this->saveProducts($products);

        return response()->json($products[$id], 200);
    }
}
