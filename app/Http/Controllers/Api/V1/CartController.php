<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\DeliveryPrice;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRule;
use App\Models\ProductVariation;
use Faker\Provider\Uuid;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Nette\Schema\ValidationException;

class CartController extends Controller
{
    public function addCart(Request $request){
        try {

            if(!empty($request->cart_id)){
                $cart_id = $request->cart_id;
            }else{
                $added_cart_id = Cart::query()->insertGetId([
                    'cart_id' => Uuid::uuid()
                ]);
                $cart_id = Cart::query()->where('id',$added_cart_id)->first()->cart_id;
            }
            $rule = ProductRule::query()->where('product_id',$request->product_id)->first();
            if ($rule->discount_rate > 0){
                $price = $rule->discounted_price;
            }else{
                $price = $rule->regular_price;
            }
            $cart_detail = CartDetail::query()->where('variation_id',$request->variation_id)
                ->where('cart_id',$cart_id)
                ->where('product_id',$request->product_id)
                ->where('active',1)
                ->first();
            if (isset($cart_detail)){
                $quantity = $cart_detail->quantity+$request->quantity;
                CartDetail::query()->where('cart_id',$cart_id)
                    ->where('variation_id',$request->variation_id)
                    ->where('product_id',$request->product_id)
                    ->update([
                    'quantity' => $quantity
                ]);
            }else{
                CartDetail::query()->insert([
                    'cart_id' => $cart_id,
                    'product_id' => $request->product_id,
                    'variation_id' => $request->variation_id,
                    'quantity' => $request->quantity,
                    'price' => $price,
                ]);
            }

            if (!empty($request->user_id)){
             Cart::query()->where('cart_id',$cart_id)->update([
                 'user_id' => $request->user_id
             ]);
            }

            return response(['message' => 'Sepet ekleme işlemi başarılı.', 'status' => 'success','cart' => $cart_id]);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001','e'=> $throwable->getMessage()]);
        }
    }

    public function updateCartProduct(Request $request){
        try {
            CartDetail::query()->where('cart_id',$request->cart_id)
                ->where('product_id',$request->product_id)
                ->where('variation_id',$request->variation_id)
                ->update([
                'product_id' => $request->product_id,
                'variation_id' => $request->variation_id,
                'cart_id' => $request->cart_id,
                'quantity' => $request->quantity
            ]);
            if ($request->quantity == 0){
                CartDetail::query()->where('cart_id',$request->cart_id)->where('product_id',$request->product_id)->update([
                    'active' => 0
                ]);
                $cart_product_count = CartDetail::query()->where('cart_id',$request->cart_id)->where('active',1)->count();
                if ($cart_product_count == 0){
                    Cart::query()->where('cart_id',$request->cart_id)->update([
                        'active' => 0
                    ]);
                    return response(['message' => 'Sepet silme işlemi başarılı.','status' => 'success']);
                }

            }
            return response(['message' => 'Sepet güncelleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001','e'=> $throwable->getMessage()]);
        }
    }

    public function deleteCartProduct(Request $request){
        try {
            CartDetail::query()->where('cart_id',$request->cart_id)
                ->where('product_id',$request->product_id)
                ->where('variation_id',$request->variation_id)
                ->update([
                'active' => 0
            ]);
            $cart_details = CartDetail::query()->where('cart_id',$request->cart_id)->get();
            if (isset($cart_details)){
                return response(['message' => 'Sepet silme işlemi başarılı.', 'status' => 'success', 'cart_status' => true]);
            }else{
                Cart::query()->where('cart_id',$request->cart_id)->update([
                    'active' => 0
                ]);
                return response(['message' => 'Sepet silme işlemi başarılı.', 'status' => 'success', 'cart_status' => false]);
            }
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001','e'=> $throwable->getMessage()]);
        }
    }

    public function getCartById($cart_id){
        try {
            $cart = Cart::query()->where('cart_id',$cart_id)->first();
            $cart_details = CartDetail::query()->where('cart_id',$cart->cart_id)->where('active',1)->get();
            $cart_price = 0;
            $cart_tax = 0;
            $weight = 0;
            foreach ($cart_details as $cart_detail){
                $product = Product::query()->where('id',$cart_detail->product_id)->first();
                $variation = ProductVariation::query()->where('id',$cart_detail->variation_id)->first();
                $rule = ProductRule::query()->where('product_id',$cart_detail->product_id)->first();
                $image = ProductImage::query()->where('product_id',$cart_detail->product_id)->first();

                $variation['rule'] = $rule;
                $variation['image'] = $image;
                $product['variation'] = $variation;
                $cart_detail['product'] = $product;
                if ($rule->discounted_price == null || $rule->discount_rate == 0){
                    $cart_detail_price = $rule->regular_price * $cart_detail->quantity;
                    $cart_detail_tax = $rule->regular_tax * $cart_detail->quantity;
                }else{
                    $cart_detail_price = $rule->discounted_price * $cart_detail->quantity;
                    $cart_detail_tax = $rule->discounted_tax * $cart_detail->quantity;
                }
                $weight = $weight + $rule->weight;
//                if($product->is_free_shipping == 1){
//                    $cart_detail_delivery_price = 0.00;
//                }
                $cart_detail['sub_total_price'] = $cart_detail_price;
                $cart_detail['sub_total_tax'] = $cart_detail_tax;
                $cart_price += $cart_detail_price;
                $cart_tax += $cart_detail_tax;

            }
            $cart['cart_details'] = $cart_details;
            $cart['total_price'] = $cart_price;
            $cart['total_tax'] = $cart_tax;

            $delivery_price = DeliveryPrice::query()->where('min_value', '<=', $weight)->where('max_value', '>', $weight)->first();
            $cart['total_delivery'] = $delivery_price;
            $cart['total_weight'] = $weight;

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['cart' => $cart]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','a' => $queryException->getMessage()]);
        }
    }

    public function getUserAllCartById($user_id){
        try {
            $user_cart = Cart::query()->where('user_id',$user_id)->get();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['user_cart' => $user_cart]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    public function getClearCartById($cart_id){
        try {
            CartDetail::query()->where('cart_id', $cart_id)
                ->update([
                    'active' => 0
                ]);

            Cart::query()->where('cart_id', $cart_id)->update([
                'active' => 0
            ]);

            return response(['message' => 'Sepet silme işlemi başarılı.','status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001','e'=> $throwable->getMessage()]);
        }
    }

    public function getUserToCart($user_id, $cart_id){
        try {

            Cart::query()->where('cart_id', $cart_id)->update([
                'user_id' => $user_id
            ]);

            return response(['message' => 'Güncelleme işlemi başarılı.','status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001','e'=> $throwable->getMessage()]);
        }
    }

}
