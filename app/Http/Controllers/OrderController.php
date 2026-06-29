<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_method' => 'required|in:standard,express',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $totalPrice = 0;
            $items = [];
            $requestedItems = collect($validator->validated()['items'])
                ->groupBy('plant_id')
                ->map(fn ($items) => $items->sum('quantity'));

            // Check stock and calculate total price
            foreach ($requestedItems as $plantId => $quantity) {
                $plant = Plant::whereKey($plantId)->lockForUpdate()->firstOrFail();

                if ($plant->stock < $quantity) {
                    DB::rollBack();

                    return response()->json([
                        'message' => "Insufficient stock for {$plant->name}. Available: {$plant->stock}",
                    ], 400);
                }

                $itemPrice = $plant->price * $quantity;
                $totalPrice += $itemPrice;

                $items[] = [
                    'plant' => $plant,
                    'quantity' => $quantity,
                    'price' => $plant->price,
                ];
            }

            // Calculate shipping cost
            $shippingCost = $request->shipping_method === 'express' ? 50000 : 20000;

            // Generate invoice number
            $invoiceNumber = Order::generateInvoiceNumber();

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'invoice_number' => $invoiceNumber,
                'recipient_name' => $request->recipient_name,
                'recipient_phone' => $request->recipient_phone,
                'total_price' => $totalPrice,
                'shipping_address' => $request->shipping_address,
                'shipping_method' => $request->shipping_method,
                'shipping_cost' => $shippingCost,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Create order details and update stock
            foreach ($items as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'plant_id' => $item['plant']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Update stock
                $item['plant']->decrement('stock', $item['quantity']);
            }

            // Create notification
            Notification::create([
                'user_id' => $request->user()->id,
                'message' => 'Your order '.$invoiceNumber.' has been placed and is waiting for confirmation.',
                'type' => 'order_placed',
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
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if ($user->isAdmin()) {
            $order = Order::findOrFail($id);
        } else {
            $order = Order::where('user_id', $user->id)->findOrFail($id);
        }

        if (! $order->canCancel()) {
            return response()->json(['message' => 'This order cannot be canceled. Only pending or confirmed orders can be canceled.'], 400);
        }

        try {
            DB::beginTransaction();

            $order->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'cancel_reason' => $request->reason,
            ]);

            // Return stock to inventory
            foreach ($order->orderDetails as $detail) {
                Plant::where('id', $detail->plant_id)
                    ->increment('stock', $detail->quantity);
            }

            // Create notification
            Notification::create([
                'user_id' => $order->user_id,
                'message' => 'Your order '.$order->invoice_number.' has been canceled. Reason: '.$request->reason,
                'type' => 'order_canceled',
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

        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $order = Order::with('orderDetails')->findOrFail($id);

        if (in_array($order->status, ['completed', 'canceled']) && $order->status !== $request->status) {
            return response()->json([
                'message' => 'Completed or canceled orders cannot be changed.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $oldStatus = $order->status;
            $updateData = ['status' => $request->status];

            // Set timestamp based on status
            if ($request->status === 'completed') {
                $updateData['completed_at'] = now();
            } elseif ($request->status === 'canceled') {
                $updateData['canceled_at'] = now();
            }

            $order->update($updateData);

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
                    $message = 'Your order '.$order->invoice_number.' has been confirmed and is being processed.';
                    break;
                case 'completed':
                    $message = 'Your order '.$order->invoice_number.' has been completed. Thank you for your purchase!';
                    break;
                case 'canceled':
                    $message = 'Your order '.$order->invoice_number.' has been canceled by admin.';
                    break;
            }

            Notification::create([
                'user_id' => $order->user_id,
                'message' => $message,
                'type' => 'order_status_changed',
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
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%'.$request->search.'%')
                    ->orWhereHas('user', function ($query) use ($request) {
                        $query->where('name', 'like', '%'.$request->search.'%')
                            ->orWhere('email', 'like', '%'.$request->search.'%');
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

            // Kembalikan stok produk jika order belum canceled
            if (in_array($order->status, ['pending', 'confirmed'])) {
                foreach ($order->orderDetails as $detail) {
                    Plant::where('id', $detail->plant_id)
                        ->increment('stock', $detail->quantity);
                }
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
            'recipient_name' => 'sometimes|required|string|max:255',
            'recipient_phone' => 'sometimes|required|string|max:20',
            'shipping_address' => 'sometimes|required|string',
            'shipping_method' => 'sometimes|required|in:standard,express',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = $request->only(['recipient_name', 'recipient_phone', 'shipping_address', 'notes']);

        // Update shipping cost if method changed
        if ($request->has('shipping_method')) {
            $updateData['shipping_method'] = $request->shipping_method;
            $updateData['shipping_cost'] = $request->shipping_method === 'express' ? 50000 : 20000;
        }

        $order->update($updateData);

        return response()->json([
            'message' => 'Order details updated successfully',
            'order' => $order->fresh(),
        ]);
    }

    // Get invoice
    public function getInvoice($id)
    {
        $user = request()->user();

        if ($user->isAdmin()) {
            $order = Order::with(['user', 'orderDetails.plant'])->findOrFail($id);
        } else {
            $order = Order::with(['orderDetails.plant'])
                ->where('user_id', $user->id)
                ->findOrFail($id);
        }

        return response()->json(['invoice' => $order]);
    }
}
