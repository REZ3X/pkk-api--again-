<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return response()->json(new ProductCollection($products), Response::HTTP_OK, [], JSON_UNESCAPED_SLASHES);
    }

    public function store(ProductRequest $request)
    {
        $product = $request->validated();

        if ($request->hasFile('image_url')) {
            $imagePath = $request->file('image_url')->store('products', 'public');
            $product['image_url'] = $imagePath;
        }

        $product = Product::create($product);

        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'data' => new ProductResource($product),
        ], Response::HTTP_CREATED, [], JSON_UNESCAPED_SLASHES);
    }

    public function show(Product $product)
    {
        return response()->json(
            [
                'status' => true,
                'message' => 'Product retrieved successfully',
                'data' => new ProductResource($product)
            ],
            Response::HTTP_OK,
            [],
            JSON_UNESCAPED_SLASHES
        );
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        if ($request->hasFile('image_url')) {

            if ($product->image_url) {
                Storage::disk('public')->delete($product->image_url);
            }

            $imagePath = $request->file('image_url')->store('products', 'public');
            $product->update(['image_url' => $imagePath]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product->refresh()),
        ], Response::HTTP_OK, [], JSON_UNESCAPED_SLASHES);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully',
        ], Response::HTTP_OK, [], JSON_UNESCAPED_SLASHES);
    }
}
