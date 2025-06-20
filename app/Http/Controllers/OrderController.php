<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Plant;
use App\Models\Notification;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $orders = Order::with(['user', 'orderDetails.plant'])->latest()->paginate(10);
        } else {
            $orders = $user->orders()->with(['orderDetails.plant'])->latest()->paginate(10);
        }

        return response()->json(['orders' => $orders]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.plant_id' => 'required|exists:plants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $totalPrice = 0;
            $items = [];

            // Check stock and calculate total price
            foreach ($request->items as $item) {
                $plant = Plant::findOrFail($item['plant_id']);

                if ($plant->stock < $item['quantity']) {
                    return response()->json([
                        'message' => "Insufficient stock for {$plant->name}. Available: {$plant->stock}"
                    ], 400);
                }

                $itemPrice = $plant->price * $item['quantity'];
                $totalPrice += $itemPrice;

                $items[] = [
                    'plant' => $plant,
                    'quantity' => $item['quantity'],
                    'price' => $plant->price
                ];
            }

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_price' => $totalPrice,
                'shipping_address' => $request->shipping_address,
                'notes' => $request->notes,
                'status' => 'pending'
            ]);

            // Create order details and update stock
            foreach ($items as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'plant_id' => $item['plant']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Update stock
                $item['plant']->decrement('stock', $item['quantity']);
            }

            // Create notification
            Notification::create([
                'user_id' => $request->user()->id,
                'message' => 'Your order #' . $order->id . ' has been placed and is waiting for confirmation.',
                'type' => 'order_placed'
            ]);

            DB::commit();

            return response()->json(['order' => $order->load('orderDetails.plant')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while placing your order.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $order = Order::with(['user', 'orderDetails.plant'])->findOrFail($id);
        } else {
            $order = Order::with(['orderDetails.plant'])
                ->where('user_id', $user->id)
                ->findOrFail($id);
        }

        return response()->json(['order' => $order]);
    }

    public function cancel(Request $request, $id)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $order = Order::findOrFail($id);
        } else {
            $order = Order::where('user_id', $user->id)->findOrFail($id);
        }

        if (!$order->canCancel()) {
            return response()->json(['message' => 'This order cannot be canceled.'], 400);
        }

        try {
            DB::beginTransaction();

            $order->update(['status' => 'canceled']);

            // Return stock to inventory
            foreach ($order->orderDetails as $detail) {
                Plant::where('id', $detail->plant_id)
                    ->increment('stock', $detail->quantity);
            }

            // Create notification
            Notification::create([
                'user_id' => $order->user_id,
                'message' => 'Your order #' . $order->id . ' has been canceled.',
                'type' => 'order_canceled'
            ]);

            DB::commit();

            return response()->json(['message' => 'Order canceled successfully', 'order' => $order]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while canceling your order.'], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:confirmed,completed,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $order = Order::findOrFail($id);

        try {
            DB::beginTransaction();

            $oldStatus = $order->status;
            $order->update(['status' => $request->status]);

            // If canceled, return stock
            if ($request->status === 'canceled' && $oldStatus !== 'canceled') {
                foreach ($order->orderDetails as $detail) {
                    Plant::where('id', $detail->plant_id)
                        ->increment('stock', $detail->quantity);
                }
            }

            // Create notification
            $message = '';
            switch ($request->status) {
                case 'confirmed':
                    $message = 'Your order #' . $order->id . ' has been confirmed.';
                    break;
                case 'completed':
                    $message = 'Your order #' . $order->id . ' has been completed.';
                    break;
                case 'canceled':
                    $message = 'Your order #' . $order->id . ' has been canceled.';
                    break;
            }

            Notification::create([
                'user_id' => $order->user_id,
                'message' => $message,
                'type' => 'order_status_changed'
            ]);

            DB::commit();

            return response()->json(['message' => 'Order status updated successfully', 'order' => $order]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while updating order status.'], 500);
        }
    }

    // Method untuk menampilkan semua pesanan (admin view)
    public function adminIndex(Request $request)
    {
        $query = Order::with(['user', 'orderDetails.plant']);

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan tanggal
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Pencarian
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('user', function($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                          ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            });
        }

        $orders = $query->latest()->paginate(10);

        return response()->json(['orders' => $orders]);
    }

    // Method untuk menghapus pesanan (admin only)
    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        try {
            DB::beginTransaction();

            // Kembalikan stok produk
            foreach ($order->orderDetails as $detail) {
                Plant::where('id', $detail->plant_id)
                    ->increment('stock', $detail->quantity);
            }

            // Hapus detail pesanan dan pesanan
            $order->orderDetails()->delete();
            $order->delete();

            DB::commit();

            return response()->json(['message' => 'Order deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while deleting the order.'], 500);
        }
    }

    // Method untuk mengubah detail pesanan (admin only)
    public function updateOrderDetails(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'shipping_address' => 'sometimes|required|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order->update($request->only(['shipping_address', 'notes']));

        return response()->json([
            'message' => 'Order details updated successfully',
            'order' => $order->fresh()
        ]);
    }
}
