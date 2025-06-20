<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filter berdasarkan role jika dibutuhkan
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter berdasarkan kata kunci pencarian
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->paginate(10);

        return response()->json(['users' => $users]);
    }

    public function show($id)
    {
        $user = User::with(['orders' => function($query) {
            $query->latest()->limit(5);
        }])->findOrFail($id);

        return response()->json(['user' => $user]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'role' => 'sometimes|required|in:admin,user',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($id);

        $data = $request->only(['name', 'email', 'role', 'phone', 'address']);

        // Update password jika disediakan
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Mencegah menghapus diri sendiri
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete your own account'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
