<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderRefund;
use App\Models\OrderRefundStatus;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\PaymentType;
use App\Models\ProductImage;
use App\Models\ShippingType;
use App\Models\UserProfile;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Nette\Schema\ValidationException;

class OrderController extends Controller
{
    public function updateOrder(Request $request, $id)
    {
        try {
            $order = Order::query()->where('order_id', $id)->first();
            Order::query()->where('order_id', $id)->update([
                'order_id' => $id,
                'user_id' => $request->user_id,
                'carrier_id' => $request->carrier_id,
                'cart_id' => $request->cart_id,
                'status_id' => $request->status_id,
                'shipping_address_id' => $request->shipping_address_id,
                'billing_address_id' => $request->billing_address_id,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'comment' => $request->comment,
                'shipping_number' => $request->shipping_number,
                'shipping_date' => $request->shipping_date,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'total' => $request->total,
                'is_partial' => $request->is_partial,
                'is_paid' => $request->is_paid
            ]);
            if ($order->status_id != $request->status_id) {
                OrderStatusHistory::query()->insert([
                    'status_id' => $request->status_id,
                    'order_id' => $order->order_id
                ]);
            }
            return response(['message' => 'Sipariş güncelleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'e' => $throwable->getMessage()]);
        }
    }

    public function getOnGoingOrders()
    {
        try {
            $orders = Order::query()
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status_id')
                ->where('order_statuses.run_on', 1)
                ->get(['orders.id', 'orders.order_id', 'orders.created_at as order_date', 'orders.total', 'orders.status_id',
                    'orders.shipping_type', 'orders.user_id', 'orders.payment_type'
                ]);
            foreach ($orders as $order) {
                $product_count = OrderProduct::query()->where('order_id', $order->order_id)->get()->count();
                $product = OrderProduct::query()->where('order_id', $order->order_id)->first();
                $product_image = ProductImage::query()->where('variation_id', $product->variation_id)->first()->image;
                $status_name = OrderStatus::query()->where('id', $order->status_id)->first()->name;
                $shipping_type = ShippingType::query()->where('id', $order->shipping_type)->first()->name;
                $user_profile = UserProfile::query()->where('user_id', $order->user_id)->first(['name', 'surname']);
                $payment_type = PaymentType::query()->where('id', $order->payment_type)->first()->name;

                $order['product_count'] = $product_count;
                $order['product_image'] = $product_image;
                $order['payment_type'] = $order->payment_type;
                $order['status_name'] = $status_name;
                $order['shipping_number'] = $order->shipping_number;
                $order['shipping_type_name'] = $shipping_type;
                $order['user_profile'] = $user_profile;
                $order['payment_type_name'] = $payment_type;
            }
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['orders' => $orders]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'â' => $queryException->getMessage()]);
        }
    }

    public function getCompletedOrders()
    {
        try {
            $orders = Order::query()
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status_id')
                ->where('order_statuses.run_on', 0)
                ->get(['orders.id', 'orders.order_id', 'orders.created_at as order_date', 'orders.total', 'orders.status_id',
                    'orders.shipping_type', 'orders.user_id', 'orders.payment_type'
                ]);
            foreach ($orders as $order) {
                $product_count = OrderProduct::query()->where('order_id', $order->order_id)->get()->count();
                $product = OrderProduct::query()->where('order_id', $order->order_id)->first();
                $product_image = ProductImage::query()->where('variation_id', $product->variation_id)->first()->image;
                $status_name = OrderStatus::query()->where('id', $order->status_id)->first()->name;
                $shipping_type = ShippingType::query()->where('id', $order->shipping_type)->first()->name;
                $user_profile = UserProfile::query()->where('user_id', $order->user_id)->first(['name', 'surname']);
                $payment_type = PaymentType::query()->where('id', $order->payment_type)->first()->name;

                $order['product_count'] = $product_count;
                $order['product_image'] = $product_image;
                $order['payment_type'] = $order->payment_type;
                $order['status_name'] = $status_name;
                $order['shipping_number'] = $order->shipping_number;
                $order['shipping_type_name'] = $shipping_type;
                $order['user_profile'] = $user_profile;
                $order['payment_type_name'] = $payment_type;
            }
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['orders' => $orders]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    public function getOrderStatusHistoriesById($order_id)
    {
        try {
            $order_status_histories = OrderStatusHistory::query()->where('order_id', $order_id)->get();
            return response(['message' => 'İşlem başarılı.', 'status' => 'success', 'order_status_histories' => $order_status_histories]);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'er' => $throwable->getMessage()]);
        }
    }

    public function updateOrderStatus(Request $request)
    {
        try {
            OrderStatusHistory::query()->insert([
                'order_id' => $request->order_id,
                'status_id' => $request->status_id
            ]);
            Order::query()->where('order_id', $request->order_id)->update([
                'status_id' => $request->status_id
            ]);
            return response(['message' => 'İşlem başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'er' => $throwable->getMessage()]);
        }
    }

    public function updateOrderInfo(Request $request, $order_id)
    {
        /**sipariş durumu teslimat türü update olacak**/

        try {
            Order::query()->where('order_id', $order_id)->update([
                'status_id' => $request->status_id,
                'shipping_type' => $request->shipping_type
            ]);
            return response(['message' => 'Sipariş durumu güncelleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'e' => $throwable->getMessage()]);
        }
    }

    public function updateOrderShipment(Request $request, $order_id)
    {
        /**firma gönderi takip kodu update olacak**/

        try {
            Order::query()->where('order_id', $order_id)->update([
                'shipping_number' => $request->shipping_number,
                'carrier_id' => $request->carrier_id
            ]);
            return response(['message' => 'Sipariş numarası güncelleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'e' => $throwable->getMessage()]);
        }
    }

    public function updateOrderBilling(Request $request, $order_id)
    {
        /**ad soyad telefon adees posta kodu ilçe il ülke corporate adrestekiler güncellenecek**/
        try {
            $billing_address = $request->name . " - " . $request->address . " - " . $request->postal_code . " - " . $request->phone . " - " . $request->district . " / " . $request->city . " / " . $request->country;
            if ($request->company_name != ''){
                $billing_address = $billing_address." - ".$request->tax_number." - ".$request->tax_office." - ".$request->company_name;
            }
            Order::query()->where('order_id', $order_id)->update([
                'billing_address' => $billing_address
            ]);
            return response(['message' => 'Sipariş adresi güncelleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'e' => $throwable->getMessage()]);
        }
    }

    public function updateOrderShipping(Request $request, $order_id)
    {
        try {
            $shipping_address = $request->name . " - " . $request->address . " - " . $request->postal_code . " - " . $request->phone . " - " . $request->district . " / " . $request->city . " / " . $request->country;
            if ($request->company_name != ''){
                $shipping_address = $shipping_address." - ".$request->tax_number." - ".$request->tax_office." - ".$request->company_name;
            }
            Order::query()->where('order_id', $order_id)->update([
                'shipping_address' => $shipping_address
            ]);
            return response(['message' => 'Sipariş adresi güncelleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'e' => $throwable->getMessage()]);
        }
    }

    public function getRefundOrders(){
        try {
            $order_refunds = OrderRefund::query()
                ->leftJoin('order_refund_statuses','order_refund_statuses.id','=','order_refunds.status')
                ->leftJoin('user_profiles','user_profiles.user_id','=','order_refunds.user_id')
                ->where('order_refunds.active',1)
                ->selectRaw('order_refunds.*, user_profiles.name, user_profiles.surname,order_refund_statuses.name as status_name')
                ->get();

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['order_refunds' => $order_refunds]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','err' => $queryException->getMessage()]);
        }
    }

    public function getOrderRefundStatuses(){
        try {
            $order_refund_statuses = OrderRefundStatus::query()
                ->where('active',1)
                ->get();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['order_refund_statuses' => $order_refund_statuses]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','err' => $queryException->getMessage()]);
        }
    }

    public function updateRefundStatus(Request $request, $order_id){
        try {
            OrderRefund::query()->where('order_id',$order_id)->update([
                'status' => $request->status,
            ]);
            return response(['message' => 'İade durumu güncelleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001']);
        }
    }

}
