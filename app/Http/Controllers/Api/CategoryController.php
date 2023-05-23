<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = Category::paginate(10);
            $response = new CategoryCollection($categories);
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
                'name' => 'required|max:50|unique:categories,name',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'Bad Request',
                    'errors' => $validate->errors()
                ], 400);
            }

            Category::create([
                'name' => $request->input('name')
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
            $response = new CategoryResource(Category::findOrFail($id));
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
    public function update(Request $request, Category $category)
    {
        try {
            DB::beginTransaction();
            $validate = Validator::make($request->all(), [
                'name' => 'required|max:50|unique:categories,name,' . $category->id,
            ]);

            if($validate->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'Bad Request',
                    'errors' => $validate->errors()
                ], 400);
            }

            $category->update([
                'name' => $request->input('name')
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
    public function destroy(Category $category)
    {
        try {
            DB::beginTransaction();
            $category->delete();

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
