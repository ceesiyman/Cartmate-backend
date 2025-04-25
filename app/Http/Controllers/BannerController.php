<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/banners",
     *     summary="Get all active banners",
     *     tags={"Banners"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of active banners",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="subtitle", type="string"),
     *                     @OA\Property(property="cta", type="string"),
     *                     @OA\Property(property="image", type="string"),
     *                     @OA\Property(property="color", type="string"),
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
        $banners = Banner::where('active', true)
            ->paginate($perPage);

        return response()->json($banners);
    }

    /**
     * @OA\Post(
     *     path="/api/banners",
     *     summary="Create a new banner",
     *     tags={"Banners"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="subtitle", type="string"),
     *             @OA\Property(property="cta", type="string"),
     *             @OA\Property(property="image", type="string"),
     *             @OA\Property(property="color", type="string"),
     *             @OA\Property(property="active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Banner created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'cta' => 'required|string|max:50',
            'image' => 'required|string',
            'color' => 'required|string|max:50',
            'active' => 'boolean'
        ]);

        $banner = Banner::create($validated);

        return response()->json($banner, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/banners/{id}",
     *     summary="Update a banner",
     *     tags={"Banners"},
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
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="subtitle", type="string"),
     *             @OA\Property(property="cta", type="string"),
     *             @OA\Property(property="image", type="string"),
     *             @OA\Property(property="color", type="string"),
     *             @OA\Property(property="active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Banner updated successfully"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'subtitle' => 'string|max:255',
            'cta' => 'string|max:50',
            'image' => 'string',
            'color' => 'string|max:50',
            'active' => 'boolean'
        ]);

        $banner->update($validated);

        return response()->json($banner);
    }

    /**
     * @OA\Delete(
     *     path="/api/banners/{id}",
     *     summary="Delete a banner",
     *     tags={"Banners"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Banner deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return response()->json(null, 204);
    }
} 