<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PlantController extends Controller
{
    public function index(Request $request)
    {
        $query = Plant::with(['category', 'plantType']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

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

        // Filter by stock availability
        if ($request->has('in_stock') && $request->in_stock == 1) {
            $query->where('stock', '>', 0);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = strtolower($request->get('sort_order', 'desc'));

        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        if (in_array($sortBy, ['name', 'price', 'created_at', 'stock'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $perPage = min(max((int) $request->get('per_page', 12), 1), 100);
        $plants = $query->paginate($perPage);

        return response()->json([
            'plants' => $plants,
        ]);
    }

    public function show(Plant $plant)
    {
        $plant->load(['category', 'plantType', 'reviews.user']);

        return response()->json([
            'plant' => $plant,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:plants,name',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'plant_type_id' => 'required|exists:plant_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $count = 1;

            // Ensure unique slug
            while (Plant::where('slug', $slug)->exists()) {
                $slug = $originalSlug.'-'.$count;
                $count++;
            }

            $data = [
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'category_id' => $request->category_id,
                'plant_type_id' => $request->plant_type_id,
            ];

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $filename = $slug.'-'.time().'.'.$extension;
                $imagePath = $image->storeAs('plants', $filename, 'public');
                $data['image'] = $imagePath;
            }

            $plant = Plant::create($data);

            return response()->json([
                'message' => 'Plant created successfully',
                'plant' => $plant->load(['category', 'plantType']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create plant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Plant $plant)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:plants,name,'.$plant->id,
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'plant_type_id' => 'sometimes|required|exists:plant_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['name', 'description', 'price', 'stock', 'category_id', 'plant_type_id']);

            // Update slug if name changed
            if ($request->has('name') && $request->name !== $plant->name) {
                $slug = Str::slug($request->name);
                $originalSlug = $slug;
                $count = 1;

                while (Plant::where('slug', $slug)->where('id', '!=', $plant->id)->exists()) {
                    $slug = $originalSlug.'-'.$count;
                    $count++;
                }

                $data['slug'] = $slug;
            }

            if ($request->hasFile('image')) {
                // Delete old image
                if ($plant->image && Storage::disk('public')->exists($plant->image)) {
                    Storage::disk('public')->delete($plant->image);
                }

                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                // Use new slug if name changed, otherwise use existing slug
                $slugForImage = isset($data['slug']) ? $data['slug'] : $plant->slug;
                $filename = $slugForImage.'-'.time().'.'.$extension;
                $imagePath = $image->storeAs('plants', $filename, 'public');
                $data['image'] = $imagePath;
            }

            $plant->update($data);

            return response()->json([
                'message' => 'Plant updated successfully',
                'plant' => $plant->fresh()->load(['category', 'plantType']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update plant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Plant $plant)
    {
        try {
            // Check if plant has orders
            if ($plant->orderDetails()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete plant with existing orders. Consider setting stock to 0 instead.',
                ], 400);
            }

            // Delete image
            if ($plant->image && Storage::disk('public')->exists($plant->image)) {
                Storage::disk('public')->delete($plant->image);
            }

            $plant->delete();

            return response()->json([
                'message' => 'Plant deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete plant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
