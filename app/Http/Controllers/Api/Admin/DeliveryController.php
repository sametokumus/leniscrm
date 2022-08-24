<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\DeliveryPrice;
use App\Models\RegionalDeliveryPrice;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Nette\Schema\ValidationException;

class DeliveryController extends Controller
{
    public function getDeliveryPrices(){
        try {
            $delivery_prices = DeliveryPrice::query()->where('active',1)->get();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['delivery_prices' => $delivery_prices]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
    public function getDeliveryPriceById($id){
        try {
            $delivery_price = DeliveryPrice::query()->where('id',$id)->where('active',1)->first();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['delivery_price' => $delivery_price]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
    public function addDeliveryPrice(Request $request){
        try {
            $request->validate([
                'min_value' => 'required',
                'max_value' => 'required',
                'price' => 'required',
            ]);

            $delivery_price = DeliveryPrice::query()->insert([
                'min_value' => $request->min_value,
                'max_value' => $request->max_value,
                'price' => $request->price
            ]);

            return response(['message' => 'Default ücret ekleme işlemi başarılı.','status' => 'success','object' => ['delivery_price' => $delivery_price]]);
        } catch (ValidationException $validationException) {
            return  response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.','status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001']);
        } catch (\Throwable $throwable) {
            return  response(['message' => 'Hatalı işlem.','status' => 'error-001','e' => $throwable->getMessage()]);
        }
    }
    public function updateDeliveryPrice(Request $request,$id){
        try {
            $request->validate([
                'min_value' => 'required',
                'max_value' => 'required',
                'price' => 'required',
            ]);

            $delivery_price = DeliveryPrice::query()->where('id',$id)->update([
                'min_value' => $request->min_value,
                'max_value' => $request->max_value,
                'price' => $request->price
            ]);

            return response(['message' => 'Default ücret güncelleme işlemi başarılı.','status' => 'success','object' => ['delivery_price' => $delivery_price]]);
        } catch (ValidationException $validationException) {
            return  response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.','status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001']);
        } catch (\Throwable $throwable) {
            return  response(['message' => 'Hatalı işlem.','status' => 'error-001','e' => $throwable->getMessage()]);
        }
    }
    public function deleteDeliveryPrice($id){
        try {

            $delivery_price = DeliveryPrice::query()->where('id',$id)->update([
                'active' => 0,
            ]);
            return response(['message' => 'Default ücret silme işlemi başarılı.','status' => 'success','object' => ['delivery_price' => $delivery_price]]);
        } catch (ValidationException $validationException) {
            return  response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.','status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001']);
        } catch (\Throwable $throwable) {
            return  response(['message' => 'Hatalı işlem.','status' => 'error-001','ar' => $throwable->getMessage()]);
        }
    }

    public function syncCitiesToRegionalDelivery()
    {
        try {
            $cities = City::query()->get();
            foreach ($cities as $city){
                $delivery_prices = DeliveryPrice::query()->where('active', 1)->get();
                foreach ($delivery_prices as $delivery_price){
                    $check_regional_delivery = RegionalDeliveryPrice::query()->where('city_id', $city->id)->where('delivery_price_id', $delivery_price->id)->where('active', 1)->count();
                    if($check_regional_delivery == 0){
                        RegionalDeliveryPrice::query()->insert([
                            'city_id' => $city->id,
                            'delivery_price_id' => $delivery_price->id,
                            'price' => $delivery_price->price
                        ]);
                    }
                }
            }
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
    public function resetAllPricesToDefault()
    {
        try {
            $delivery_prices = DeliveryPrice::query()->where('active', 1)->get();
            foreach ($delivery_prices as $delivery_price){
                RegionalDeliveryPrice::query()->where('delivery_price_id', $delivery_price->id)->update([
                    'price' => $delivery_price->price
                ]);
            }
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
    public function resetPricesToDefaultByCityId($city_id)
    {
        try {
            $delivery_prices = DeliveryPrice::query()->where('active', 1)->get();
            foreach ($delivery_prices as $delivery_price){
                RegionalDeliveryPrice::query()->where('delivery_price_id', $delivery_price->id)->where('city_id', $city_id)->update([
                    'price' => $delivery_price->price
                ]);
            }
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
    public function resetPricesToDefaultByDeliveryPriceId($delivery_price_id)
    {
        try {
            $delivery_price = DeliveryPrice::query()->where('active', 1)->where('id', $delivery_price_id)->first();
            RegionalDeliveryPrice::query()->where('delivery_price_id', $delivery_price_id)->update([
                'price' => $delivery_price->price
            ]);
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
    public function getRegionalDeliveryPriceByCityId($id){
        try {
            $regional_delivery_prices = RegionalDeliveryPrice::query()->where('city_id',$id)->where('active',1)->get();
            foreach ($regional_delivery_prices as $regional_delivery_price){
                $regional_delivery_price['delivery_price'] = DeliveryPrice::query()->where('id', $regional_delivery_price->delivery_price_id)->first();
            }
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['regional_delivery_prices' => $regional_delivery_prices]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','e' => $queryException->getMessage()]);
        }
    }
    public function getRegionalDeliveryPrice($city_id, $delivery_price_id){
        try {
            $regional_delivery_price = RegionalDeliveryPrice::query()->where('city_id',$city_id)->where('delivery_price_id',$delivery_price_id)->where('active',1)->first();
            $regional_delivery_price['delivery_price'] = DeliveryPrice::query()->where('id', $regional_delivery_price->delivery_price_id)->first();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['regional_delivery_price' => $regional_delivery_price]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    public function updateRegionalDeliveryPrice(Request $request, $city_id, $delivery_price_id){
        try {
            $request->validate([
                'price' => 'required',
            ]);

            $delivery_price = RegionalDeliveryPrice::query()->where('city_id',$city_id)->where('delivery_price_id',$delivery_price_id)->update([
                'price' => $request->price
            ]);

            return response(['message' => 'Ücret güncelleme işlemi başarılı.','status' => 'success','object' => ['delivery_price' => $delivery_price]]);
        } catch (ValidationException $validationException) {
            return  response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.','status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001']);
        } catch (\Throwable $throwable) {
            return  response(['message' => 'Hatalı işlem.','status' => 'error-001','e' => $throwable->getMessage()]);
        }
    }
}
