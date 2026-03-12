<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CartController extends Controller
{
    /**
     * Display products in the authenticated user's cart.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $items = CartItem::with('product')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'data' => CartItemResource::collection($items),
        ], Response::HTTP_OK);
    }

    /**
     * Add a product to the cart or update quantity.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $product = Product::findOrFail($validated['product_id']);

        $quantity = min($validated['quantity'], $product->stock);

        $item = CartItem::firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $item->quantity = $quantity;
        $item->save();

        return response()->json([
            'message' => 'Product added to cart',
            'data' => new CartItemResource($item->load('product')),
        ], Response::HTTP_OK);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, CartItem $cartItem)
    {
        $user = $request->user();
        if ($cartItem->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        $cartItem->delete();
        return response()->json(['message' => 'Item removed'], Response::HTTP_OK);
    }
}
