<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\CurrencyHelper;
use App\Helpers\StatusHistoryHelper;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Employee;
use App\Models\Measurement;
use App\Models\Offer;
use App\Models\OfferProduct;
use App\Models\OfferRequest;
use App\Models\OfferRequestProduct;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleOffer;
use App\Models\StatusHistory;
use Faker\Provider\Uuid;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Nette\Schema\ValidationException;

class OfferController extends Controller
{
    public function getNewOffersByRequestId($request_id)
    {
        try {
            $sale = Sale::query()->where('request_id', $request_id)->where('active', 1)->first();
            if ($sale->status_id <= 3) {

                $companies = Offer::query()
                    ->leftJoin('companies', 'companies.id', '=', 'offers.supplier_id')
                    ->selectRaw('companies.id as company_id, companies.name as company_name')
                    ->where('offers.request_id', $request_id)
                    ->where('offers.active', 1)
                    ->distinct()
                    ->get();

                $products = Offer::query()
                    ->leftJoin('offer_products', 'offer_products.offer_id', '=', 'offers.offer_id')
                    ->selectRaw('offer_products.request_product_id')
                    ->where('offers.request_id', $request_id)
                    ->where('offers.active', 1)
                    ->where('offer_products.active', 1)
                    ->distinct()
                    ->get();

                foreach ($companies as $company) {
                    $supply_price = 0;
                    foreach ($products as $product) {
                        $offer_product = Offer::query()
                            ->leftJoin('offer_products', 'offer_products.offer_id', '=', 'offers.offer_id')
                            ->selectRaw('offer_products.*')
                            ->where('offers.request_id', $request_id)
                            ->where('offers.supplier_id', $company->company_id)
                            ->where('offer_products.request_product_id', $product->request_product_id)
                            ->where('offers.active', 1)
                            ->where('offer_products.active', 1)
                            ->first();

                        if ($offer_product) {
                            $supply_price += $offer_product->converted_price;
                        }
                    }
                    $company['supply_price'] = $supply_price;
                }

                foreach ($products as $product){
                    $offer_request_product = OfferRequestProduct::query()->where('id', $product->request_product_id)->first();
                    $product['request_quantity'] = $offer_request_product['quantity'];
                    $product['sequence'] = $offer_request_product['sequence'];
                    $product['product_name'] = Product::query()->where('id', $offer_request_product->product_id)->first()->product_name;

                    $companies2 = Offer::query()
                        ->leftJoin('companies', 'companies.id', '=', 'offers.supplier_id')
                        ->selectRaw('companies.id as company_id')
                        ->where('offers.request_id', $request_id)
                        ->where('offers.active', 1)
                        ->distinct()
                        ->get();
                    foreach ($companies2 as $company){
                        $offer_product = Offer::query()
                            ->leftJoin('offer_products', 'offer_products.offer_id', '=', 'offers.offer_id')
                            ->selectRaw('offer_products.*')
                            ->where('offers.request_id', $request_id)
                            ->where('offers.supplier_id', $company->company_id)
                            ->where('offer_products.request_product_id', $product->request_product_id)
                            ->where('offers.active', 1)
                            ->where('offer_products.active', 1)
                            ->first();

                        if ($offer_product) {
                            if ($offer_product->lead_time != null) {
                                $fastest = OfferProduct::query()
                                    ->leftJoin('offers', 'offers.offer_id', '=', 'offer_products.offer_id')
                                    ->selectRaw('offer_products.*')
                                    ->where('offer_products.lead_time', '!=', null)
                                    ->where('offer_products.request_product_id', $offer_product->request_product_id)
                                    ->where('offer_products.lead_time', '<', $offer_product->lead_time)
                                    ->where('offers.request_id', $request_id)
                                    ->count();
                                if ($fastest > 0) {
                                    $offer_product['fastest'] = false;
                                } else {
                                    $offer_product['fastest'] = true;
                                }
                            } else {
                                $offer_product['fastest'] = false;
                            }

                            if ($offer_product->converted_price != null) {
                                $cheapest = OfferProduct::query()
                                    ->leftJoin('offers', 'offers.offer_id', '=', 'offer_products.offer_id')
                                    ->selectRaw('offer_products.*')
                                    ->where('offer_products.total_price', '!=', null)
                                    ->where('offer_products.request_product_id', $offer_product->request_product_id)
                                    ->where('offer_products.converted_price', '<', $offer_product->converted_price)
                                    ->where('offers.request_id', $request_id)
                                    ->count();
                                if ($cheapest > 0) {
                                    $offer_product['cheapest'] = false;
                                } else {
                                    $offer_product['cheapest'] = true;
                                }
                            } else {
                                $offer_product['cheapest'] = false;
                            }
                        }

                        $company['offer_product'] = $offer_product;
                    }

                    $product['companies'] = $companies2;

                }

                $object = [
                    'offer_status' => true,
                    'status_id' => $sale->status_id,
                    'products' => $products,
                    'companies' => $companies
                ];

            }else{
                $object = [
                    'offer_status' => false,
                    'status_id' => $sale->status_id,
                    'products' => [],
                    'companies' => []
                ];
            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => $object]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getOffersByRequestId($request_id)
    {
        try {
            $sale = Sale::query()->where('request_id', $request_id)->where('active', 1)->first();

                $offers = Offer::query()
                    ->leftJoin('companies', 'companies.id', '=', 'offers.supplier_id')
                    ->selectRaw('offers.*, companies.name as company_name')
                    ->where('offers.request_id', $request_id)
                    ->where('offers.active', 1)
                    ->get();

                foreach ($offers as $offer) {
                    $offer['product_count'] = OfferProduct::query()->where('offer_id', $offer->offer_id)->where('active', 1)->count();
                    $products = OfferProduct::query()->where('offer_id', $offer->offer_id)->where('active', 1)->get();
                    foreach ($products as $product) {
                        $offer_request_product = OfferRequestProduct::query()->where('id', $product->request_product_id)->first();
                        $product['request_quantity'] = $offer_request_product['quantity'];
                        $product['sequence'] = $offer_request_product['sequence'];
                        $product['measurement_name_en'] = Measurement::query()->where('id', $product->measurement_id)->first()->name_en;
                        $product['product_detail'] = Product::query()->where('id', $offer_request_product->product_id)->first();
                        $product['measurement_name_tr'] = Measurement::query()->where('id', $product->measurement_id)->first()->name_tr;

                        if ($product->lead_time != null){
                            $fastest = OfferProduct::query()
                                ->leftJoin('offers', 'offers.offer_id', '=', 'offer_products.offer_id')
                                ->selectRaw('offer_products.*')
                                ->where('offer_products.lead_time', '!=', null)
                                ->where('offer_products.request_product_id', $product->request_product_id)
                                ->where('offer_products.lead_time', '<', $product->lead_time)
                                ->where('offers.request_id', $request_id)
                                ->count();
                            if ($fastest > 0){
                                $product['fastest'] = false;
                            }else{
                                $product['fastest'] = true;
                            }
                        }else{
                            $product['fastest'] = false;
                        }

                        if ($product->converted_price != null){
                            $cheapest = OfferProduct::query()
                                ->leftJoin('offers', 'offers.offer_id', '=', 'offer_products.offer_id')
                                ->selectRaw('offer_products.*')
                                ->where('offer_products.total_price', '!=', null)
                                ->where('offer_products.request_product_id', $product->request_product_id)
                                ->where('offer_products.converted_price', '<', $product->converted_price)
                                ->where('offers.request_id', $request_id)
                                ->count();
                            if ($cheapest > 0){
                                $product['cheapest'] = false;
                            }else{
                                $product['cheapest'] = true;
                            }
                        }else{
                            $product['cheapest'] = false;
                        }


                    }

                    $offer['products'] = $products;
                }

                $offer_status = true;
                $status_id = $sale->status_id;



            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['offer_status' => $offer_status, 'status_id' => $status_id, 'offers' => $offers]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getOfferById($offer_id)
    {
        try {
            $offer = Offer::query()
                ->leftJoin('companies', 'companies.id', '=', 'offers.supplier_id')
                ->selectRaw('offers.*, companies.name as company_name')
                ->where('offers.offer_id',$offer_id)
                ->where('offers.active',1)
                ->first();

            $offer['global_id'] = Sale::query()->where('request_id', $offer->request_id)->first()->id;
            $offer['owner_id'] = Sale::query()->where('request_id', $offer->request_id)->first()->owner_id;
            $offer['product_count'] = OfferProduct::query()->where('offer_id', $offer_id)->where('active', 1)->count();
            $offer['company'] = Company::query()
                ->leftJoin('countries', 'countries.id', '=', 'companies.country_id')
                ->selectRaw('companies.*, countries.lang as country_lang')
                ->where('companies.id', $offer->supplier_id)
                ->first();

            $products = OfferProduct::query()->where('offer_id', $offer->offer_id)->where('active', 1)->get();
            $offer_sub_total = 0;
            $offer_vat = 0;
            $offer_grand_total = 0;
            foreach ($products as $product){
                $offer_request_product = OfferRequestProduct::query()->where('id', $product->request_product_id)->first();
                $product_detail = Product::query()->where('id', $offer_request_product->product_id)->first();
                $product['ref_code'] = $product_detail->ref_code;
                $product['product_name'] = $product_detail->product_name;
                $vat = $product->total_price / 100 * $product->vat_rate;
                $product['vat'] = number_format($vat, 2,".","");
                $product['grand_total'] = number_format($product->total_price + $vat, 2,".","");

                $offer_sub_total += $product->total_price;
                $offer_vat += $vat;
                $offer_grand_total += $product->total_price + $vat;
                $product['measurement_name_tr'] = Measurement::query()->where('id', $product->measurement_id)->first()->name_tr;
                $product['measurement_name_en'] = Measurement::query()->where('id', $product->measurement_id)->first()->name_en;

                $product->discount_rate = number_format($product->discount_rate, 2,",",".");
                $product->discounted_price = number_format($product->discounted_price, 2,",",".");
                $product->grand_total = number_format($product->grand_total, 2,",",".");
                $product->total_price = number_format($product->total_price, 2,",",".");
                $product->pcs_price = number_format($product->pcs_price, 2,",",".");
                $product->vat_rate = number_format($product->vat_rate, 2,",",".");
            }

            $offer['products'] = $products;
            $offer['sub_total'] = number_format($offer_sub_total, 2,".","");
            $offer['vat'] = number_format($offer_vat, 2,".","");
            $offer['grand_total'] = number_format($offer_grand_total, 2,".","");

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['offer' => $offer]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function addOffer(Request $request)
    {
        try {
            $request->validate([
                'request_id' => 'required',
                'supplier_id' => 'required',
            ]);
            $offer_id = Uuid::uuid();
            Offer::query()->insertGetId([
                'user_id' => $request->user_id,
                'request_id' => $request->request_id,
                'offer_id' => $offer_id,
                'supplier_id' => $request->supplier_id
            ]);

            foreach ($request->products as $product){
                $has_product = OfferProduct::query()->where('offer_id', $offer_id)->where('request_product_id', $product['request_product_id'])->where('active', 1)->first();
                if (!$has_product) {
                    $request_product = OfferRequestProduct::query()->where('id', $product['request_product_id'])->first();
                    OfferProduct::query()->insert([
                        'offer_id' => $offer_id,
                        'request_product_id' => $product['request_product_id'],
                        'quantity' => $request_product['quantity'],
                        'measurement_id' => $request_product['measurement_id']
                    ]);
                }
            }

            $sale = Sale::query()->where('request_id', $request->request_id)->first();
            if ($sale->status_id == 1){
                Sale::query()->where('request_id', $request->request_id)->update([
                    'status_id' => 2
                ]);
                StatusHistoryHelper::addStatusHistory($sale->sale_id, 2, $request->user_id);
            }

            return response(['message' => __('Teklif ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function getOfferProductById($offer_id, $product_id)
    {
        try {
            $product = OfferProduct::query()->where('id', $product_id)->where('offer_id', $offer_id)->first();
            $offer = Offer::query()->where('offer_id', $offer_id)->first();
            $offer_request_product = OfferRequestProduct::query()->where('id', $product->request_product_id)->first();
            $product['company_name'] = Company::query()->where('id', $offer->supplier_id)->first()->name;
            $product['request_quantity'] = $offer_request_product['quantity'];
            $product['product_detail'] = Product::query()->where('id', $offer_request_product->product_id)->first();
            $product['measurement_name_tr'] = Measurement::query()->where('id', $product->measurement_id)->first()->name_tr;
            $product['measurement_name_en'] = Measurement::query()->where('id', $product->measurement_id)->first()->name_en;

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['product' => $product]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function addOfferProduct(Request $request, $offer_id)
    {
        try {
            $request->validate([
                'ref_code' => 'required',
                'product_name' => 'required',
            ]);
            $request_id = Offer::query()->where('offer_id', $offer_id)->first()->request_id;

            $has_product = Product::query()->where('ref_code', $request->ref_code)->where('active', 1)->first();
            if ($has_product) {
                $product_id = $has_product->id;
            }else{
                $product_id = Product::query()->insertGetId([
                    'ref_code' => $request->ref_code,
                    'product_name' => $request->product_name,
                ]);
            }
            $request_product_id = OfferRequestProduct::query()->insertGetId([
                'request_id' => $request_id,
                'product_id' => $product_id,
                'quantity' => $request->quantity
            ]);

            OfferProduct::query()->insert([
                'offer_id' => $offer_id,
                'request_product_id' => $request_product_id,
                'quantity' => $request->quantity,
                'pcs_price' => $request->pcs_price,
                'total_price' => $request->total_price,
                'discount_rate' => $request->discount_rate,
                'discounted_price' => $request->discounted_price,
                'package_type' => $request->package_type,
                'date_code' => $request->date_code,
                'vat_rate' => $request->vat_rate,
                'currency' => $request->currency,
                'lead_time' => $request->lead_time
            ]);

            return response(['message' => __('Teklif ürün ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function updateOfferProduct(Request $request, $offer_id, $product_id)
    {
        try {

            OfferProduct::query()->where('id', $product_id)->where('offer_id', $offer_id)->update([
                'quantity' => $request->quantity,
                'pcs_price' => $request->pcs_price,
                'total_price' => $request->total_price,
                'discount_rate' => $request->discount_rate,
                'discounted_price' => $request->discounted_price,
                'package_type' => $request->package_type,
                'date_code' => $request->date_code,
                'vat_rate' => $request->vat_rate,
                'currency' => $request->currency,
                'lead_time' => $request->lead_time
            ]);

            $offer_product = OfferProduct::query()->where('id', $product_id)->where('offer_id', $offer_id)->first();
            $request_product = OfferRequestProduct::query()->where('id', $offer_product->request_product_id)->first();
            Product::query()->where('id', $request_product->product_id)->update([
                'product_name' => $request->product_name
            ]);


            if ($request->offer_rev == true){

                //update converted price
                $sale_id = SaleOffer::query()->where('offer_id', $offer_id)->where('offer_product_id', $product_id)->first()->sale_id;
                $sale = Sale::query()->where('sale_id', $sale_id)->first();
                $convertible_price = $offer_product->total_price;
                if ($offer_product->discount_rate > 0){
                    $convertible_price = $offer_product->discounted_price;
                }

                $supply_price = CurrencyHelper::ChangePrice($offer_product->currency, $offer_product->converted_currency, $convertible_price, $sale->eur_rate, $sale->usd_rate, $sale->gbp_rate);

                OfferProduct::query()->where('id', $product_id)->where('offer_id', $offer_id)->update([
                    'converted_price' => $supply_price
                ]);

                //update sale offer price

                SaleOffer::query()->where('offer_id', $offer_id)->where('offer_product_id', $product_id)->update([
                    'pcs_price' => $request->pcs_price,
                    'total_price' => $request->total_price,
                    'discount_rate' => $request->discount_rate,
                    'discounted_price' => $request->discounted_price,
                    'vat_rate' => $request->vat_rate,
                    'currency' => $request->currency,
                    'lead_time' => $request->lead_time,
                    'sale_price' => $supply_price
                ]);

            }


            return response(['message' => __('Teklif ürün güncelleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function deleteOffer($offer_id){
        try {

            Offer::query()->where('offer_id',$offer_id)->update([
                'active' => 0,
            ]);

            OfferProduct::query()->where('offer_id',$offer_id)->update([
                'active' => 0,
            ]);
            return response(['message' => __('Teklif silme işlemi başarılı.'),'status' => 'success']);
        } catch (ValidationException $validationException) {
            return  response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'),'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => __('Hatalı sorgu.'),'status' => 'query-001']);
        } catch (\Throwable $throwable) {
            return  response(['message' => __('Hatalı işlem.'),'status' => 'error-001','ar' => $throwable->getMessage()]);
        }
    }

    public function getMailableSuppliersByRequestId($request_id)
    {
        try {

            $offers = Offer::query()
                ->where('offers.request_id', $request_id)
                ->where('offers.active', 1)
                ->where('offers.rfq_url', '!=', null)
                ->get();

            $request = OfferRequest::query()
                ->where('request_id', $request_id)
                ->first();

            $staff = Admin::query()->where('id', $request->purchasing_staff_id)->first();

            $suppliers = array();

            foreach ($offers as $offer) {
                $supplier = Company::query()->where('id', $offer->supplier_id)->first();
                $employees = Employee::query()->where('company_id', $offer->supplier_id)->where('active', 1)->get();
                $supplier['employees'] = $employees;

                array_push($suppliers, $supplier);
            }

            $sale = Sale::query()->where('request_id', $request_id)->first();
            $owner = Contact::query()->where('id', $sale->owner_id)->first();
            $global_id = $owner->short_code.'-RFQ-'.$sale->id;


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['suppliers' => $suppliers, 'purchasing_staff_id' => $request->purchasing_staff_id, 'staff' => $staff, 'global_id' => $global_id, 'owner' => $owner]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
}
