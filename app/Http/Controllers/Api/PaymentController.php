<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\PaymentCollection;
use App\Http\Resources\Payment\PaymentResource;
use App\Models\Payment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $payments = Payment::paginate(10);
            $response = new PaymentCollection($payments);
            return $response->additional([
                'code' => 200,
                'status' => 'OK'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'status' => 'Internal Server Error'
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
                'name' => 'required|max:50',
                'type' => 'required|max:50',
                'logo' => 'required|image|mimes:jpg,jpeg,png|max:1024'
            ]);

            if($validate->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'Bad Request',
                    'errors' => $validate->errors()
                ], 400);
            }

            Payment::create([
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'logo' => $request->file('logo')->store('payments_logo')
            ]);

            DB::commit();
            return response()->json([
                'code' => 201,
                'status' => 'Created',
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
            $response = new PaymentResource(Payment::findOrFail($id));
            return $response->additional([
                'code' => 200,
                'status' => 'OK'
            ]);
        } catch(ModelNotFoundException $e) {
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
    public function update(Request $request, Payment $payment)
    {
        try {
            DB::beginTransaction();
            $validate = Validator::make($request->all(), [
                'name' => 'required|max:50',
                'type' => 'required|max:50',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:1024'
            ]);

            if($validate->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'Bad Request',
                    'errors' => $validate->errors()
                ], 400);
            }

            $data = [
                'name' => $request->input('name'),
                'type' => $request->input('type'),
            ];

            if($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('payments_logo');
            }
            $payment->update($data);

            DB::commit();
            return response()->json([
                'code' => 200,
                'status' => 'OK',
            ], 200);
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
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        try {
            DB::beginTransaction();
            Storage::disk('public')->delete($payment->logo);
            $payment->delete();

            DB::commit();
            return response()->json([
                'code' => 200,
                'status' => 'OK',
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'status' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
