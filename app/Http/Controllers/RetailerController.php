<?php

namespace App\Http\Controllers;

use App\Models\Retailer;
use Illuminate\Http\Request;

class RetailerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/retailers",
     *     summary="Get all active retailers",
     *     tags={"Retailers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of active retailers",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="logo", type="string"),
     *                     @OA\Property(property="active", type="boolean"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $retailers = Retailer::where('active', true)
            ->paginate($perPage);

        return response()->json($retailers);
    }

    /**
     * @OA\Post(
     *     path="/api/retailers",
     *     summary="Create a new retailer",
     *     tags={"Retailers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="logo", type="string"),
     *             @OA\Property(property="active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Retailer created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|string',
            'active' => 'boolean'
        ]);

        $retailer = Retailer::create($validated);

        return response()->json($retailer, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/retailers/{id}",
     *     summary="Update a retailer",
     *     tags={"Retailers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="logo", type="string"),
     *             @OA\Property(property="active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Retailer updated successfully"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $retailer = Retailer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'logo' => 'string',
            'active' => 'boolean'
        ]);

        $retailer->update($validated);

        return response()->json($retailer);
    }

    /**
     * @OA\Delete(
     *     path="/api/retailers/{id}",
     *     summary="Delete a retailer",
     *     tags={"Retailers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Retailer deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        $retailer = Retailer::findOrFail($id);
        $retailer->delete();

        return response()->json(null, 204);
    }
} 