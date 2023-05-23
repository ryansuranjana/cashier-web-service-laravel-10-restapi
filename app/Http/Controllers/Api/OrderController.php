<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderCollection;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $orders = Order::with([
                'user',
                'products',
                'payment'
            ])->paginate(10);

            $response = new OrderCollection($orders);
            return $response->additional([
                'code' => 200,
                'status' => 'OK',
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
            DB::beginTransaction();

            $validate = Validator::make($request->all(), [
                'payment_id' => 'required',
                'total_paid' => 'required|integer',
                'products' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'Bad Request',
                    'errors' => $validate->errors()
                ], 400);
            }

            $products = collect($request->products)->map(function ($product) {
                $productData = Product::find($product['product_id']);
                $productData->update([
                    'stock' => $productData['stock'] - 1
                ]);
                return [
                    'product' => $productData,
                    'qty' => $product['qty'],
                    'total_price' => $product['qty'] * $productData['price']
                ];
            });
            $total_price = collect($products)->map(fn($product) => $product['total_price'])->reduce(fn($carry, $item) => $carry + $item);
            $total_return = $total_price - $request->total_paid;

            $order = Order::create([
                'user_id' => Auth::user()->id,
                'payment_type_id' => $request->payment_id,
                'total_price' => $total_price,
                'total_paid' => $request->total_paid,
                'total_return' => $total_return,
                'receipt_code' => generateReceiptCode(8)
            ]);

            foreach($products as $product) {
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product['product']['id'],
                    'qty' => $product['qty'],
                    'total_price' => $product['total_price']
                ]);
            }

            DB::commit();
            return response()->json([
                'code' => 201,
                'status' => 'Created'
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
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
            $response = new OrderResource(Order::findOrFail($id));
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
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
