<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/stores",
     *     summary="Get all stores",
     *     tags={"Stores"},
     *     @OA\Response(
     *         response=200,
     *         description="List of stores",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Store")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $stores = Store::all();
        return response()->json([
            'success' => true,
            'data' => $stores
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/stores",
     *     summary="Create a new store",
     *     tags={"Stores"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Amazon"),
     *             @OA\Property(property="url", type="string", example="https://amazon.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Store created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Store")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Implementation of store method
    }

    /**
     * @OA\Get(
     *     path="/api/stores/{id}",
     *     summary="Get store by ID",
     *     tags={"Stores"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Store ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Store")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store not found"
     *     )
     * )
     */
    public function show($id)
    {
        // Implementation of show method
    }

    /**
     * @OA\Put(
     *     path="/api/stores/{id}",
     *     summary="Update store",
     *     tags={"Stores"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Store ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Amazon"),
     *             @OA\Property(property="url", type="string", example="https://amazon.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Store")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Implementation of update method
    }

    /**
     * @OA\Delete(
     *     path="/api/stores/{id}",
     *     summary="Delete store",
     *     tags={"Stores"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Store ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Store deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        // Implementation of destroy method
    }
} 