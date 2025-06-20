<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Plant;
use App\Http\Resources\PlantResource;

class PlantController extends Controller
{
    public function index(Request $request)
    {
        $query = Plant::with(['category', 'plantType']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by plant type
        if ($request->has('plant_type_id')) {
            $query->where('plant_type_id', $request->plant_type_id);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%');
        }

        // Sort by price
        if ($request->has('sort_price')) {
            $query->orderBy('price', $request->sort_price);
        }

        $plants = $query->paginate(10);

        return response()->json([
            'plants' => PlantResource::collection($plants),
            'pagination' => [
                'total' => $plants->total(),
                'per_page' => $plants->perPage(),
                'current_page' => $plants->currentPage(),
                'last_page' => $plants->lastPage(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'plant_type_id' => 'required|exists:plant_types,id',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('plants', 'public');
        }

        $plant = Plant::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'plant_type_id' => $request->plant_type_id,
            'image' => $imagePath,
        ]);

        return response()->json(['plant' => new PlantResource($plant)], 201);
    }

    public function show(Plant $plant)
    {
        $plant->load(['category', 'plantType']);
        return response()->json(['plant' => new PlantResource($plant)]);
    }

    public function update(Request $request, Plant $plant)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'plant_type_id' => 'required|exists:plant_types,id',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'plant_type_id' => $request->plant_type_id,
        ];

        if ($request->hasFile('image')) {
            // Delete old image
            if ($plant->image) {
                Storage::disk('public')->delete($plant->image);
            }

            $data['image'] = $request->file('image')->store('plants', 'public');
        }

        $plant->update($data);

        return response()->json(['plant' => new PlantResource($plant)]);
    }

    public function destroy(Plant $plant)
    {
        if ($plant->image) {
            Storage::disk('public')->delete($plant->image);
        }

        $plant->delete();

        return response()->json(['message' => 'Plant deleted successfully']);
    }
}
