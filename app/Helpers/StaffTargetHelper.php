<?php

namespace App\Helpers;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CurrencyLog;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\OfferProduct;
use App\Models\OfferRequest;
use App\Models\Sale;
use App\Models\SaleOffer;
use App\Models\StaffTarget;
use Carbon\Carbon;

class StaffTargetHelper
{
    public static function getTargetStatus($target_id)
    {
        $target = StaffTarget::query()->where('id', $target_id)->first();

        if ($target->type_id == 1){
            $status = StaffTargetHelper::getTargetStatusType1($target);
        }else if ($target->type_id == 2){
            $status = StaffTargetHelper::getTargetStatusType2($target);
        }else if ($target->type_id == 3){
            $status = StaffTargetHelper::getTargetStatusType3($target);
        }

        return $status;
    }

    private static function getTargetStatusType1($target)
    {
       $sales = Sale::query()
           ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
           ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 'sales.request_id')
           ->join('status_histories AS sh', function ($join) {
               $join->on('sales.sale_id', '=', 'sh.sale_id')
                   ->whereRaw('sh.created_at = (SELECT MAX(created_at) FROM status_histories as sh2 WHERE sh2.sale_id = sales.sale_id AND sh2.status_id = 7)');
           })
           ->selectRaw('sales.*')
           ->where('offer_requests.authorized_personnel_id',$target->admin_id)
           ->where('sales.active',1)
           ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
           ->whereYear('sh.created_at', '=', $target->year);

       if ($target->month != 0) {
           $sales = $sales
               ->whereMonth('sh.created_at', '=', $target->month);
       }

        $sales = $sales
           ->get();

        $target_total_price = 0;

        foreach ($sales as $sale){

            $sale_total_price = $sale->grand_total;
            if ($sale->grand_total_with_shipping != null){
                $sale_total_price = $sale->grand_total_with_shipping;
            }

            if ($target->currency == $sale->currency){
                $target_total_price += $sale_total_price;
            }else{
                if ($target->currency == 'TRY') {
                    $sc = strtolower($sale->currency);
                    $converted_price = $sale_total_price * $sale->{$sc.'_rate'};
                }else{
                    if ($sale->currency == 'TRY') {
                        $tc = strtolower($target->currency);
                        $converted_price = $sale_total_price / $sale->{$tc.'_rate'};
                    }else{
                        $tc = strtolower($target->currency);
                        $sc = strtolower($sale->currency);
                        if ($sale->{$sc.'_rate'} != 0) {
                            $converted_price = $sale_total_price * $sale->{$tc . '_rate'} / $sale->{$sc . '_rate'};
                        }else{
                            $converted_price = 0;
                        }
                    }
                }
                $target_total_price += $converted_price;
            }

        }

        $status = array();
        $status['price'] = number_format($target_total_price, 2, ".", "");
        if ($target_total_price != 0 && $target->target != 0) {
            $rate = 100 * $target_total_price / $target->target;
        }else{
            $rate = 0;
        }
        $status['rate'] = number_format($rate, 2, ",", "");

        return $status;
    }

    private static function getTargetStatusType2($target)
    {
       $sales = Sale::query()
           ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
           ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 'sales.request_id')
           ->join('status_histories AS sh', function ($join) {
               $join->on('sales.sale_id', '=', 'sh.sale_id')
                   ->whereRaw('sh.created_at = (SELECT MAX(created_at) FROM status_histories as sh2 WHERE sh2.sale_id = sales.sale_id AND sh2.status_id = 7)');
           })
           ->selectRaw('sales.*')
           ->where('offer_requests.authorized_personnel_id',$target->admin_id)
           ->where('sales.active',1)
           ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
           ->whereYear('sh.created_at', '=', $target->year);

       if ($target->month != 0) {
           $sales = $sales
               ->whereMonth('sh.created_at', '=', $target->month);
       }

        $sales = $sales
           ->get();

        $total_sale_price = 0;
        $total_offer_price = 0;

        foreach ($sales as $sale){

            //satın alma
            $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
            foreach ($sale_offers as $sale_offer){
                $offer_product = OfferProduct::query()->where('id', $sale_offer->offer_product_id)->where('active', 1)->first();
                $sale_offer['offer_product'] = $offer_product;
                $offer_price = $offer_product->converted_price;


                if ($target->currency == $sale->currency){
                    $total_offer_price += $offer_price;
                }else{
                    if ($target->currency == 'TRY') {
                        $sc = strtolower($sale->currency);
                        $converted_price = $offer_price * $sale->{$sc.'_rate'};
                    }else{
                        if ($sale->currency == 'TRY') {
                            $tc = strtolower($target->currency);
                            if($sale->{$tc.'_rate'} == 0){
                                $converted_price = 0;
                            }else {
                                $converted_price = $offer_price / $sale->{$tc . '_rate'};
                            }
                        }else{
                            $tc = strtolower($target->currency);
                            $sc = strtolower($sale->currency);
                            if ($sale->{$sc.'_rate'} != 0) {
                                $converted_price = $offer_price * $sale->{$tc . '_rate'} / $sale->{$sc . '_rate'};
                            }else{
                                $converted_price = 0;
                            }
                        }
                    }
                    $total_offer_price += $converted_price;
                }

            }


            //satış
            $sale_price = $sale->grand_total;
            if ($sale->grand_total_with_shipping != null){
                $sale_price = $sale->grand_total_with_shipping;
            }

            if ($target->currency == $sale->currency){
                $total_sale_price += $sale_price;
            }else{
                if ($target->currency == 'TRY') {
                    $sc = strtolower($sale->currency);
                    $converted_price = $sale_price * $sale->{$sc.'_rate'};
                }else{
                    if ($sale->currency == 'TRY') {
                        $tc = strtolower($target->currency);
                        if($sale->{$tc.'_rate'} == 0){
                            $converted_price = 0;
                        }else {
                            $converted_price = $sale_price / $sale->{$tc . '_rate'};
                        }
                    }else{
                        $tc = strtolower($target->currency);
                        $sc = strtolower($sale->currency);
                        if ($sale->{$sc.'_rate'} != 0) {
                            $converted_price = $sale_price * $sale->{$tc . '_rate'} / $sale->{$sc . '_rate'};
                        }else{
                            $converted_price = 0;
                        }
                    }
                }
                $total_sale_price += $converted_price;
            }


        }

        $status = array();
        $status['price'] = number_format(($total_sale_price - $total_offer_price), 2, ".", "");
        if ($total_sale_price != 0 && $total_offer_price != 0 && $target->target) {
            $rate = 100 * ($total_sale_price - $total_offer_price) / $target->target;
        }else{
            $rate = 0;
        }
        $status['rate'] = number_format($rate, 2, ",", "");

        return $status;
    }

    private static function getTargetStatusType3($target)
    {
       $sales = Sale::query()
           ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
           ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 'sales.request_id')
           ->join('status_histories AS sh', function ($join) {
               $join->on('sales.sale_id', '=', 'sh.sale_id')
                   ->whereRaw('sh.created_at = (SELECT MAX(created_at) FROM status_histories as sh2 WHERE sh2.sale_id = sales.sale_id AND sh2.status_id = 7)');
           })
           ->selectRaw('sales.*')
           ->where('offer_requests.authorized_personnel_id',$target->admin_id)
           ->where('sales.active',1)
           ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
           ->whereYear('sh.created_at', '=', $target->year);

       if ($target->month != 0) {
           $sales = $sales
               ->whereMonth('sh.created_at', '=', $target->month);
       }

        $sales = $sales
           ->get();

        $target_total_price = 0;
        $target_offer_price = 0;

        foreach ($sales as $sale){

            $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
            $total_offer_price = 0;
            foreach ($sale_offers as $sale_offer){
                $offer_product = OfferProduct::query()->where('id', $sale_offer->offer_product_id)->where('active', 1)->first();
                $sale_offer['offer_product'] = $offer_product;
                $total_offer_price += $offer_product->converted_price;
            }

            if ($total_offer_price != 0) {
                $total_expense = $total_offer_price;
            }else{
                $total_expense = 0;
            }

            $expenses = Expense::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
            foreach ($expenses as $expense){
                $expense['category_name'] = ExpenseCategory::query()->where('id', $expense->category_id)->first()->name;
                if ($expense->currency == $sale->currency){
                    $total_expense += $expense->price;
                    $expense['converted_price'] = $expense->price;
                }else{
                    if ($expense->currency == 'TRY') {
                        $sc = strtolower($sale->currency);
                        $expense_price = $expense->price / $sale->{$sc.'_rate'};
                    }else{
                        if ($sale->currency == 'TRY') {
                            $ec = strtolower($expense->currency);
                            $expense_price = $expense->price * $sale->{$ec.'_rate'};
                        }else{
                            $ec = strtolower($expense->currency);
                            $sc = strtolower($sale->currency);
                            if ($sale->{$sc.'_rate'} != 0) {
                                $expense_price = $expense->price * $sale->{$ec . '_rate'} / $sale->{$sc . '_rate'};
                            }else{
                                $expense_price = 0;
                            }
                        }
                    }
                    $total_expense += $expense_price;
                }
            }

            $target_offer_price = $total_expense;


            $sale_total_price = $sale->grand_total;
            if ($sale->grand_total_with_shipping != null){
                $sale_total_price = $sale->grand_total_with_shipping;
            }

            $calculate_currency = 'TRY';
            if ($calculate_currency == $sale->currency){
                $target_total_price += $sale_total_price;
            }else{
                if ($calculate_currency == 'TRY') {
                    $sc = strtolower($sale->currency);
                    $converted_price = $sale_total_price * $sale->{$sc.'_rate'};
                }else{
                    if ($sale->currency == 'TRY') {
                        $tc = strtolower($calculate_currency);
                        $converted_price = $sale_total_price / $sale->{$tc.'_rate'};
                    }else{
                        $tc = strtolower($calculate_currency);
                        $sc = strtolower($sale->currency);
                        if ($sale->{$sc.'_rate'} != 0) {
                            $converted_price = $sale_total_price * $sale->{$tc . '_rate'} / $sale->{$sc . '_rate'};
                        }else{
                            $converted_price = 0;
                        }
                    }
                }
                $target_total_price += $converted_price;
            }


        }

        $status = array();
        $status['price'] = number_format(($target_total_price - $target_offer_price), 2, ".", "");
        if ($target_total_price != 0 && $target_offer_price != 0) {
            $sale_rate = 100 * ($target_total_price - $target_offer_price) / $target_total_price;
        }else{
            $sale_rate = 0;
        }
        if ($sale_rate != 0 && $target->target != 0) {
            $rate = 100 * $sale_rate / $target->target;
        }else{
            $rate = 0;
        }
        $status['rate'] = number_format($rate, 2, ",", "");

        return $status;
    }

}
