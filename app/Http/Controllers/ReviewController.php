<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;
use App\Models\Order;
use App\Models\Plant;

class ReviewController extends Controller
{
    public function index($plantId)
    {
        $reviews = Review::where('plant_id', $plantId)
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        return response()->json(['reviews' => $reviews]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plant_id' => 'required|exists:plants,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Check if order belongs to user
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found or does not belong to you.'], 404);
        }

        // Check if order is completed
        if ($order->status !== 'completed') {
            return response()->json(['message' => 'You can only review completed orders.'], 400);
        }

        // Check if user bought this plant in this order
        $orderDetail = $order->orderDetails()
            ->where('plant_id', $request->plant_id)
            ->first();

        if (!$orderDetail) {
            return response()->json(['message' => 'You did not purchase this plant in this order.'], 400);
        }

        // Check if review already exists
        $existingReview = Review::where('user_id', $user->id)
            ->where('plant_id', $request->plant_id)
            ->where('order_id', $request->order_id)
            ->first();

        if ($existingReview) {
            return response()->json(['message' => 'You have already reviewed this plant for this order.'], 400);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'plant_id' => $request->plant_id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review->load('user:id,name')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $review = Review::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found.'], 404);
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review->load('user:id,name')
        ]);
    }

    public function destroy($id)
    {
        $user = request()->user();

        $review = Review::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found.'], 404);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }

    public function userReviews(Request $request)
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with(['plant:id,name,image', 'order:id'])
            ->latest()
            ->paginate(10);

        return response()->json(['reviews' => $reviews]);
    }
}
