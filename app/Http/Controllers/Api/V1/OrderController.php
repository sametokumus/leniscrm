<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Brand;
use App\Models\Carrier;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\City;
use App\Models\CorporateAddresses;
use App\Models\Country;
use App\Models\DeliveryPrice;
use App\Models\District;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderRefund;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRule;
use App\Models\ProductVariation;
use App\Models\ShippingType;
use App\Models\User;
use DateTime;
use Faker\Provider\Uuid;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Nette\Schema\ValidationException;

class OrderController extends Controller
{

    public function addOrder(Request $request)
    {
        try {
            $cart = Cart::query()->where('cart_id', $request->cart_id)->where('active', 1)->first();

            if(isset($cart)) {

                $order_status = OrderStatus::query()->where('is_default', 1)->first();
                $order_quid = Uuid::uuid();
                $shipping_id = $request->shipping_address_id;
                $billing_id = $request->billing_address_id;
                $shipping = Address::query()->where('id', $shipping_id)->first();
                $country = Country::query()->where('id', $shipping->country_id)->first();
                $city = City::query()->where('id', $shipping->city_id)->first();
                $district = District::query()->where('id', $shipping->district_id)->first();

                $shipping_address = $shipping->name . " " . $shipping->surname . " - " . $shipping->address_1 . " " . $shipping->address_2 . " - " . $shipping->postal_code . " - " . $shipping->phone . " - " . $district->name . " / " . $city->name . " / " . $country->name;
                if ($shipping->type == 2){
                    $shipping_corporate_address = CorporateAddresses::query()->where('address_id',$shipping_id)->first();
                    $shipping_address = $shipping_address." - ".$shipping_corporate_address->tax_number." - ".$shipping_corporate_address->tax_office." - ".$shipping_corporate_address->company_name;
                }


                $billing = Address::query()->where('id', $billing_id)->first();
                $billing_country = Country::query()->where('id', $billing->country_id)->first();
                $billing_city = City::query()->where('id', $billing->city_id)->first();
                $billing_district = District::query()->where('id', $billing->district_id)->first();
                $billing_address = $billing->name . " " . $billing->surname . " - " . $billing->address_1 . " " . $billing->address_2 . " - " . $billing->postal_code . " - " . $billing->phone . " - " . $billing_district->name . " / " . $billing_city->name . " / " . $billing_country->name;

                if ($shipping->type == 2){
                    $billing_corporate_address = CorporateAddresses::query()->where('address_id',$billing_id)->first();
                    $shipping_address = $shipping_address." - ".$billing_corporate_address->tax_number." - ".$billing_corporate_address->tax_office." - ".$billing_corporate_address->company_name;
                }
                $order_id = Order::query()->insertGetId([
                    'order_id' => $order_quid,
                    'user_id' => $request->user_id,
                    'carrier_id' => $request->carrier_id,
                    'cart_id' => $request->cart_id,
                    'status_id' => $order_status->id,
                    'shipping_address_id' => $request->shipping_address_id,
                    'billing_address_id' => $request->billing_address_id,
                    'shipping_address' => $shipping_address,
                    'billing_address' => $billing_address,
                    'comment' => $request->comment,
                    'shipping_type' => $request->delivery_type,
                    'payment_type' => $request->payment_type,
                    'shipping_price' => $request->shipping_price,
                    'subtotal' => $request->subtotal,
                    'total' => $request->total,
                    'is_partial' => $request->is_partial,
                    'is_paid' => $request->is_paid
                ]);

                Cart::query()->where('cart_id', $request->cart_id)->update([
                    'user_id' => $request->user_id,
                    'is_order' => 1,
                    'active' => 0
                ]);
                $user_discount = User::query()->where('id', $request->user_id)->first()->user_discount;
                $carts = CartDetail::query()->where('cart_id', $request->cart_id)->get();
                foreach ($carts as $cart) {
                    $product = Product::query()->where('id', $cart->product_id)->first();
                    $variation = ProductVariation::query()->where('id', $cart->variation_id)->first();
                    $rule = ProductRule::query()->where('variation_id', $variation->id)->first();
                    if ($rule->discounted_price == null || $rule->discount_rate == 0){
                        $price = $rule->regular_price - ($rule->regular_price / 100 * $user_discount);
                        $tax = $price / 100 * $rule->tax_rate;
                        $total = ($price + $tax) * $request->quantity;
                    }else{
                        $price = $rule->regular_price - ($rule->regular_price / 100 * ($user_discount + $rule->discount_rate));
                        $tax = $price / 100 * $rule->tax_rate;
                        $total = ($price + $tax) * $request->quantity;
                    }
                    OrderProduct::query()->insert([
                        'order_id' => $order_quid,
                        'product_id' => $product->id,
                        'variation_id' => $variation->id,
                        'name' => $product->name,
                        'sku' => $variation->sku,
                        'regular_price' => $rule->regular_price,
                        'regular_tax' => $rule->regular_tax,
                        'discounted_price' => $rule->discounted_price,
                        'discounted_tax' => $rule->discounted_tax,
                        'discount_rate' => $rule->discount_rate,
                        'tax_rate' => $rule->tax_rate,
                        'user_discount' => $user_discount,
                        'quantity' => $cart->quantity,
                        'total' => $total
                    ]);
                }

                OrderStatusHistory::query()->insert([
                    'order_id' => $order_quid,
                    'status_id' => $order_status->id
                ]);

                return response(['message' => 'Sipariş ekleme işlemi başarılı.', 'status' => 'success', 'object' => ['order_id' => $order_quid]]);
            }else{
                return response(['message' => 'Sepet Bulunamadı.', 'status' => 'cart-001']);
            }

        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'e' => $throwable->getMessage()]);
        }
    }

    public function getOrdersByUserId($user_id){
        try {
            $orders = Order::query()->where('user_id',$user_id)->get(['id', 'order_id', 'created_at as order_date', 'total', 'status_id','payment_type']);
            foreach ($orders as $order){
                $product_count = OrderProduct::query()->where('order_id', $order->order_id)->get()->count();
                $product = OrderProduct::query()->where('order_id', $order->order_id)->first();
                $product_image = ProductImage::query()->where('variation_id', $product->variation_id)->first()->image;
                $status_name = OrderStatus::query()->where('id', $order->status_id)->first()->name;
                $payment_type = PaymentType::query()->where('id',$order->payment_type)->first()->name;

                $order['product_count'] = $product_count;
                $order['product_image'] = $product_image;
                $order['payment_type'] = $payment_type;
                $order['status_name'] = $status_name;

                $created_at = $order->order_date;

                $start = new DateTime($created_at);
                $end = Carbon::now();

                $interval = $end->diff($start);
                $final = $interval->format('%a');

                if ($final <= 15){
                    $order['is_refundable'] = 1;
                    $refund = OrderRefund::query()->where('order_id',$order->id)->first();
                    if (isset($refund)){
                        $order['is_refundable'] = 0;

                    }
                }else{
                    $order['is_refundable'] = 0;
                }
            }
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['orders' => $orders]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    public function getOrderById($order_id){
        try {
            $order = Order::query()->where('order_id',$order_id)->first();
            $order['status_name'] = OrderStatus::query()->where('id', $order->status_id)->first()->name;
            $order['carrier_name'] = Carrier::query()->where('id', $order->carrier_id)->first()->name;
            $order['shipping_name'] = ShippingType::query()->where('id', $order->shipping_type)->first()->name;
            $order['payment_name'] = PaymentType::query()->where('id', $order->payment_type)->first()->name;
            $order_details = OrderProduct::query()->where('order_id', $order_id)->get();
            $order_price = 0;
            $order_tax = 0;
            $weight = 0;
            foreach ($order_details as $order_detail){
                $product = Product::query()->where('id',$order_detail->product_id)->first();
                $brand_name = Brand::query()->where('id',$product->brand_id)->first()->name;
                $variation = ProductVariation::query()->where('id',$order_detail->variation_id)->first();
                $rule = ProductRule::query()->where('product_id',$order_detail->product_id)->first();
                $image = ProductImage::query()->where('product_id',$order_detail->product_id)->first();

                $variation['rule'] = $rule;
                $variation['image'] = $image;
                $product['variation'] = $variation;
                $product['brand_name'] = $brand_name;
                $order_detail['product'] = $product;
                if ($order_detail->discounted_price == null || $order_detail->discount_rate == 0){
                    $order_detail_price = $order_detail->regular_price * $order_detail->quantity;
                    $order_detail_tax = $order_detail->regular_tax * $order_detail->quantity;
                }else{
                    $order_detail_price = $order_detail->discounted_price * $order_detail->quantity;
                    $order_detail_tax = $order_detail->discounted_tax * $order_detail->quantity;
                }
                $weight = $weight + $rule->weight;
//                if($product->is_free_shipping == 1){
//                    $order_detail_delivery_price = 0.00;
//                }
                $order_detail['sub_total_price'] = $order_detail_price;
                $order_detail['sub_total_tax'] = $order_detail_tax;
                $order_price += $order_detail_price;
                $order_tax += $order_detail_tax;

            }
            $order['order_details'] = $order_details;
            $order['total_price'] = $order_price;
            $order['total_tax'] = $order_tax;

            $delivery_price = DeliveryPrice::query()->where('min_value', '<=', $weight)->where('max_value', '>', $weight)->first();
            $order['total_delivery'] = $delivery_price;
            $order['total_weight'] = $weight;

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['order' => $order]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','e' => $queryException->getMessage()]);
        }
    }

    public function addPayment(Request $request)
    {
        try {

            Payment::query()->insert([
                'order_id' => $request->order_id,
                'type' => $request->type,
                'bank_id' => $request->bank_id,
                'installment' => $request->installment_count
            ]);

            return response(['message' => 'Ödeme oluşturuldu.', 'status' => 'success']);

        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'e' => $throwable->getMessage()]);
        }
    }

    public function updatePayment(Request $request)
    {
        try {

            Payment::query()->where('order_id', $request->order_id)->update([
                'return_code' => $request->return_code,
                'response' => $request->response,
                'transaction_id' => $request->transaction_id,
                'transaction_date' => $request->transaction_date,
                'hostrefnum' => $request->hostrefnum,
                'authcode' => $request->authcode,
                'is_preauth' => 1,
                'is_paid'=> 1
            ]);
            Order::query()->where('order_id', $request->order_id)->update([
                'is_paid' => 1,
                'status_id' => 3
            ]);

            return response(['message' => 'Ödeme güncellendi.', 'status' => 'success']);

        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'e' => $throwable->getMessage()]);
        }
    }


}
