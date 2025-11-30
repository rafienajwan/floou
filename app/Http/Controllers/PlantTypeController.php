<?php

namespace App\Http\Controllers;

use App\Models\PlantType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PlantTypeController extends Controller
{
    public function index()
    {
        $plantTypes = PlantType::withCount('plants')->get();
        return response()->json(['plant_types' => $plantTypes]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:plant_types,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $count = 1;

            while (PlantType::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $plantType = PlantType::create([
                'name' => $request->name,
                'slug' => $slug,
            ]);

            return response()->json([
                'message' => 'Plant type created successfully',
                'plant_type' => $plantType
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create plant type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, PlantType $plantType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:plant_types,name,' . $plantType->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $count = 1;

            while (PlantType::where('slug', $slug)->where('id', '!=', $plantType->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $plantType->update([
                'name' => $request->name,
                'slug' => $slug,
            ]);

            return response()->json([
                'message' => 'Plant type updated successfully',
                'plant_type' => $plantType
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update plant type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(PlantType $plantType)
    {
        try {
            if ($plantType->plants()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete plant type with existing plants'
                ], 400);
            }

            $plantType->delete();

            return response()->json([
                'message' => 'Plant type deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete plant type',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
