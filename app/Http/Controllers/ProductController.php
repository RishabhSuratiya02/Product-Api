<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // File name where products will be stored
    private $fileName = 'products.json';

    /**
     * Helper function to read products from JSON file
     */
    private function getProducts()
    {
        // If file does not exist, create an empty JSON array
        if (!Storage::exists($this->fileName)) {
            Storage::put($this->fileName, json_encode([]));
        }

        // Decode JSON into PHP array
        return json_decode(Storage::get($this->fileName), true);
    }

    /**
     * Helper function to save products into JSON file
     */
    private function saveProducts($products)
    {
        Storage::put($this->fileName, json_encode($products, JSON_PRETTY_PRINT));
    }

    /**
     * API 1: Create a new product
     * Method: POST /api/products
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
        ]);

        // Load existing products
        $products = $this->getProducts();

        // Generate product ID (auto increment style)
        $id = count($products) + 1;

        // Merge ID with validated data
        $newProduct = array_merge(['id' => $id], $validatedData);

        // Save to products list
        $products[$id] = $newProduct;
        $this->saveProducts($products);

        // Return response with 201 Created
        return response()->json($newProduct, 201);
    }

    /**
     * API 2: Get a product by ID
     * Method: GET /api/products/{id}
     */
    public function show($id)
    {
        $products = $this->getProducts();

        // Check if product exists
        if (!isset($products[$id])) {
            return response()->json([
                'error' => 'Product not found'
            ], 404);
        }

        return response()->json($products[$id]);
    }

    /**
     * API 3: Update product by ID
     * Method: PUT /api/products/{id}
     */
    public function update(Request $request, $id)
    {
        $products = $this->getProducts();

        // If product does not exist
        if (!isset($products[$id])) {
            return response()->json([
                'error' => 'Product not found'
            ], 404);
        }

        // Validate only the fields provided (partial update allowed)
        $validatedData = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes|string|nullable',
            'price'       => 'sometimes|numeric|min:0',
            'quantity'    => 'sometimes|integer|min:0',
        ]);

        // Update only given fields
        $products[$id] = array_merge($products[$id], $validatedData);

        // Save back to file
        $this->saveProducts($products);

        // Return updated product
        return response()->json($products[$id], 200);
    }
}
