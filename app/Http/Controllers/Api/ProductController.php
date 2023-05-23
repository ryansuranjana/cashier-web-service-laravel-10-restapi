<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $products = Product::with(['category']);

            if ($request->has(['category', 'name'])) {
                $products = $products->where('category_id', $request->category)->where('name', $request->name);
            } else if ($request->has('name')) {
                $products = $products->where('name', $request->name);
            } else if ($request->has('category')) {
                $products = $products->where('category_id', $request->category);
            }

            $response = new ProductCollection($products->paginate(10)->appends($request->query()));
            return $response->additional([
                'code' => 200,
                'status' => 'OK'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'status' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'name' => 'required|max:100|unique:products,name',
                'sku' => 'required|max:20|unique:products,sku',
                'stock' => 'required|integer',
                'price' => 'required|integer',
                'image' => 'required|image|mimes:jpg,jpeg,png|max:1024',
                'category_id' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'Bad Request',
                    'errors' => $validate->errors()
                ], 400);
            }

            Product::create([
                'name' => $request->input('name'),
                'sku' => $request->input('sku'),
                'stock' => $request->input('stock'),
                'price' => $request->input('price'),
                'image' => $request->file('image')->store('products_image'),
                'category_id' => $request->input('category_id')
            ]);

            return response()->json([
                'code' => 201,
                'status' => 'Created',
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'status' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $response = new ProductResource(Product::findOrFail($id));
            return $response->additional([
                'code' => 200,
                'status' => 'OK'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'status' => 'Not Found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'status' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            $validate = Validator::make($request->all(), [
                'name' => 'required|max:100|unique:products,name,' . $product->id,
                'sku' => 'required|max:20|unique:products,sku,' . $product->id,
                'stock' => 'required|integer',
                'price' => 'required|integer',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
                'category_id' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'Bad Request',
                    'errors' => $validate->errors()
                ], 400);
            }

            $data = [
                'name' => $request->input('name'),
                'sku' => $request->input('sku'),
                'stock' => $request->input('stock'),
                'price' => $request->input('price'),
                'category_id' => $request->input('category_id')
            ];

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products_image');
            }

            $product->update($data);

            return response()->json([
                'code' => 200,
                'status' => 'OK',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'status' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            Storage::disk('public')->delete($product->image);
            $product->delete();

            return response()->json([
                'code' => 200,
                'status' => 'OK',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'status' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
