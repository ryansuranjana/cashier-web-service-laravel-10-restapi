<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::with(['orders'])->paginate(10);
            $response = new UserCollection($users);
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
            DB::beginTransaction();
            $validate = Validator::make($request->all(), [
                'email' => 'required|email|unique:users,email',
                'name' => 'required|max:100',
                'password' => 'required|min:8|max:16',
                'role' => 'required'
            ]);

            if($validate->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'Bad Request',
                    'errors' => $validate->errors()
                ], 400);
            }

            User::create([
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'password' => bcrypt($request->input('password')),
                'role' => $request->input('role')
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
            $response = new UserResource(User::findOrFail($id));
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
    public function update(Request $request, User $user)
    {
        try {
            DB::beginTransaction();
            $validate = Validator::make($request->all(), [
                'email' => 'required|email|unique:users,email,' . $user->id,
                'name' => 'required|max:100',
                'role' => 'required'
            ]);

            if($validate->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'Bad Request',
                    'errors' => $validate->errors()
                ], 400);
            }

            $user->update([
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'role' => $request->input('role')
            ]);

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
    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();
            $user->delete();

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
