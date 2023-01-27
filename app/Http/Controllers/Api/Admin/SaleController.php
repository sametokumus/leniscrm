<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferProduct;
use App\Models\OfferRequest;
use App\Models\OfferRequestProduct;
use App\Models\Sale;
use App\Models\SaleOffer;
use Faker\Provider\Uuid;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Nette\Schema\ValidationException;

class SaleController extends Controller
{

    public function addSale(Request $request)
    {
        try {
            $request->validate([
                'request_id' => 'required',
            ]);
            $sale_id = Uuid::uuid();
            $customer_id = OfferRequest::query()->where('request_id', $request->request_id)->first()->company_id;
            Sale::query()->insert([
                'sale_id' => $sale_id,
                'request_id' => $request->request_id,
                'customer_id' => $customer_id,
                'status_id' => 1
            ]);

            foreach ($request->offers as $offer){
                SaleOffer::query()->insert([
                    'sale_id' => $sale_id,
                    'offer_id' => $offer->offer_id,
                    'offer_product_id' => $offer->offer_product_id,
                    'product_id' => $offer->product_id,
                    'supplier_id' => $offer->product_id,
                    'date_code' => $offer->product_id,
                    'package_type' => $offer->product_id,
                    'request_quantity' => $offer->product_id,
                    'offer_quantity' => $offer->product_id,
                    'pcs_price' => $offer->product_id,
                    'total_price' => $offer->product_id,
                    'discount_rate' => $offer->product_id,
                    'discounted_price' => $offer->product_id,
                ]);
            }

            return response(['message' => __('Satış ekleme işlemi başarılı.'), 'status' => 'success', 'object' => ['sale_id' => $sale_id]]);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

}
