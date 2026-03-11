<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Address;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderRepository
{
    public function createOrder(array $params): Order
    {
        return Order::create(array_merge($params, [
            'placed_at' => now()
        ]));
    }

    public function createOrderItem(array $params): void
    {
        OrderItem::create($params);
    }

    /**
     * Mengambil detail order lengkap dengan Customer dan Items.
     * Menggantikan SQL manual jsonb_build_object.
     */
    public function getById(string $id): ?Order
    {
        return Order::with(['user:id,email,name', 'items.product:id,slug,image_url'])
            ->find($id);
    }

    public function getItems(string $orderId): Collection
    {
        return OrderItem::with('product:id,slug,image_url')
            ->where('order_id', $orderId)
            ->get();
    }

    public function updateStatus(string $id, string $status): ?Order
    {
        $order = Order::find($id);
        if (!$order) return null;

        $update = ['status' => $status];

        if ($status === 'COMPLETED') $update['completed_at'] = now();
        if ($status === 'CANCELLED') $update['cancelled_at'] = now();

        $order->update($update);
        return $order;
    }

    public function updateOrderSnapToken(array $params): ?Order
    {
        $order = Order::find($params['id']);
        if ($order) {
            $order->update([
                'snap_token' => $params['snap_token'],
                'snap_redirect_url' => $params['snap_redirect_url'],
                'snap_token_expired_at' => $params['snap_token_expired_at'],
            ]);
        }
        return $order;
    }

    /**
     * List order untuk customer (Public/Mobile App)
     */
    public function list(array $params): LengthAwarePaginator
    {
        $limit = (int) ($params['limit'] ?? 10);
        $offset = (int) ($params['offset'] ?? 0);

        return Order::query()
            ->with(['items.product:id,slug,image_url'])
            ->where('user_id', $params['user_id'])
            ->when(!empty($params['status']), fn($q) => $q->where('status', $params['status']))
            ->when(!empty($params['search']), fn($q) => $q->where('order_number', 'ilike', "%{$params['search']}%"))
            ->orderByDesc('placed_at')
            ->paginate($limit, ['*'], 'page', (int)($offset / $limit) + 1);
    }

    /**
     * List order untuk Admin Panel
     */
    public function listAdmin(array $params): LengthAwarePaginator
    {
        $limit = (int) ($params['limit'] ?? 10);
        $offset = (int) ($params['offset'] ?? 0);

        return Order::query()
            ->with('user:id,name')
            ->when(!empty($params['status']), fn($q) => $q->where('status', $params['status']))
            ->when(!empty($params['search']), fn($q) => $q->where('order_number', 'ilike', "%{$params['search']}%"))
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', (int)($offset / $limit) + 1);
    }

    /**
     * Lock for Update (Penting untuk integrasi Payment Gateway/Webhook)
     */
    public function getOrderPaymentForUpdateById(string $id): ?Order
    {
        return Order::where('id', $id)->lockForUpdate()->first();
    }

    public function getOrderPaymentForUpdateByOrderNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)->lockForUpdate()->first();
    }

    public function updateOrderPaymentStatus(array $params): ?Order
    {
        $order = Order::find($params['id']);
        if ($order) {
            $order->update(array_filter([
                'payment_status' => $params['payment_status'],
                'paid_at'        => $params['paid_at'],
                'cancelled_at'   => $params['cancelled_at'],
                'status'         => $params['status'],
                'payment_method' => $params['payment_method'] ?? $order->payment_method,
                'note'           => $params['note'] ?? $order->note,
            ]));
        }
        return $order;
    }

    public function getOrderSummaryByOrderNumber(string $orderNumber): ?Order
    {
        return Order::select(['id', 'order_number', 'subtotal_price', 'discount_price', 'shipping_price'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function getUserById(string $id): ?User
    {
        return User::find($id);
    }

    public function getAddressById(array $params): ?Address
    {
        return Address::where('id', $params['id'])
            ->where('user_id', $params['user_id'])
            ->first();
    }
}
