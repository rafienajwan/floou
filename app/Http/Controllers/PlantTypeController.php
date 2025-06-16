<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\PlantType;

class PlantTypeController extends Controller
{
    public function index()
    {
        $plantTypes = PlantType::all();
        return response()->json(['plant_types' => $plantTypes]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:plant_types',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plantType = PlantType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['plant_type' => $plantType], 201);
    }

    public function show(PlantType $plantType)
    {
        return response()->json(['plant_type' => $plantType]);
    }

    public function update(Request $request, PlantType $plantType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:plant_types,name,' . $plantType->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plantType->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['plant_type' => $plantType]);
    }

    public function destroy(PlantType $plantType)
    {
        $plantType->delete();
        return response()->json(['message' => 'Plant type deleted successfully']);
    }
}
