<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Expense;
use App\Models\SaleOffer;
use App\Models\Sale;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getMonthlySales()
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $sales = array();
            foreach ($last_months as $last_month){
                $try_sale = Sale::query()
                    ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month, currency, SUM(grand_total) AS monthly_total')
                    ->where('sales.active',1)
                    ->groupByRaw('YEAR(created_at), MONTH(created_at), currency')
                    ->whereYear('created_at', $last_month->year)
                    ->whereMonth('created_at', $last_month->month)
                    ->where('currency', 'TRY')
                    ->first();
                $usd_sale = Sale::query()
                    ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month, currency, SUM(grand_total) AS monthly_total')
                    ->where('sales.active',1)
                    ->groupByRaw('YEAR(created_at), MONTH(created_at), currency')
                    ->whereYear('created_at', $last_month->year)
                    ->whereMonth('created_at', $last_month->month)
                    ->where('currency', 'USD')
                    ->first();
                $eur_sale = Sale::query()
                    ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month, currency, SUM(grand_total) AS monthly_total')
                    ->where('sales.active',1)
                    ->groupByRaw('YEAR(created_at), MONTH(created_at), currency')
                    ->whereYear('created_at', $last_month->year)
                    ->whereMonth('created_at', $last_month->month)
                    ->where('currency', 'EUR')
                    ->first();
                $gbp_sale = Sale::query()
                    ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month, currency, SUM(grand_total) AS monthly_total')
                    ->where('sales.active',1)
                    ->groupByRaw('YEAR(created_at), MONTH(created_at), currency')
                    ->whereYear('created_at', $last_month->year)
                    ->whereMonth('created_at', $last_month->month)
                    ->where('currency', 'GBP')
                    ->first();


                $sale = array();
                $sale['year'] = $last_month->year;
                $sale['month'] = $last_month->month;
                $sale['try_sale'] = '0.00';
                $sale['usd_sale'] = '0.00';
                $sale['eur_sale'] = '0.00';
                $sale['gbp_sale'] = '0.00';
                if ($try_sale) {
                    $sale['try_sale'] = $try_sale->monthly_total;
                }
                if ($usd_sale) {
                    $sale['usd_sale'] = $usd_sale->monthly_total;
                }
                if ($eur_sale) {
                    $sale['eur_sale'] = $eur_sale->monthly_total;
                }
                if ($gbp_sale) {
                    $sale['gbp_sale'] = $gbp_sale->monthly_total;
                }
                array_push($sales, $sale);
            }



//            foreach ($sales as $sale) {
//                $year = $sale->year;
//                $month = $sale->month;
//                $currency = $sale->currency;
//                $monthlyTotal = $sale->monthly_total;
//
//            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getApprovedSales($owner_id)
    {
        try {
            $currentYearArray = array();
            $previousYearArray = array();

            $currentYear = date('Y');
            $previousYear = $currentYear - 1;

            for ($month = 1; $month <= 12; $month++) {
                $month_array = array();
                $month_array['year'] = $currentYear;
                $month_array['month'] = $month;
                array_push($currentYearArray, $month_array);

                $month_array2 = array();
                $month_array2['year'] = $previousYear;
                $month_array2['month'] = $month;
                array_push($previousYearArray, $month_array2);
            }

            $sales = array();
            $previous_sales = array();

            foreach ($currentYearArray as $currentMonth){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'approved')
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();

                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }


                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $try_price += $expense->price;
                            $usd_price += $expense->price / $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $usd_price += $expense->price;
                            $try_price += $expense->price * $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $eur_price += $expense->price;
                            $try_price += $expense->price * $item->eur_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $gbp_price += $expense->price;
                            $try_price += $expense->price * $item->gbp_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }

                    }

                }





                $currentMonth['try_sale'] = number_format($try_price, 2,".","");
                $currentMonth['usd_sale'] = number_format($usd_price, 2,".","");
                $currentMonth['eur_sale'] = number_format($eur_price, 2,".","");
                $currentMonth['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $currentMonth);
            }

            foreach ($previousYearArray as $currentMonth){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'approved')
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();

                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }


                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $try_price += $expense->price;
                            $usd_price += $expense->price / $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $usd_price += $expense->price;
                            $try_price += $expense->price * $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $eur_price += $expense->price;
                            $try_price += $expense->price * $item->eur_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $gbp_price += $expense->price;
                            $try_price += $expense->price * $item->gbp_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }

                    }

                }

                $currentMonth['try_sale'] = number_format($try_price, 2,".","");
                $currentMonth['usd_sale'] = number_format($usd_price, 2,".","");
                $currentMonth['eur_sale'] = number_format($eur_price, 2,".","");
                $currentMonth['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($previous_sales, $currentMonth);
            }


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => [
                'sales' => $sales,
                'previous_sales' => $previous_sales
            ]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getPotentialSales($owner_id)
    {
        try {
            $currentYearArray = array();
            $previousYearArray = array();

            $currentYear = date('Y');
            $previousYear = strval($currentYear - 1);

            for ($month = 1; $month <= 12; $month++) {
                $month_array = array();
                $month_array['year'] = $currentYear;
                $month_array['month'] = $month;
                array_push($currentYearArray, $month_array);

                $month_array2 = array();
                $month_array2['year'] = $previousYear;
                $month_array2['month'] = $month;
                array_push($previousYearArray, $month_array2);
            }

            $sales = array();
            $previous_sales = array();

            foreach ($currentYearArray as $currentMonth){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 5)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'continue')
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();


                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }


                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $try_price += $expense->price;
                            $usd_price += $expense->price / $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $usd_price += $expense->price;
                            $try_price += $expense->price * $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $eur_price += $expense->price;
                            $try_price += $expense->price * $item->eur_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $gbp_price += $expense->price;
                            $try_price += $expense->price * $item->gbp_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }

                    }

                }

                $currentMonth['try_sale'] = number_format($try_price, 2,".","");
                $currentMonth['usd_sale'] = number_format($usd_price, 2,".","");
                $currentMonth['eur_sale'] = number_format($eur_price, 2,".","");
                $currentMonth['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $currentMonth);
            }

            foreach ($previousYearArray as $currentMonth){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 5)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'continue')
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();


                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }


                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $try_price += $expense->price;
                            $usd_price += $expense->price / $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $usd_price += $expense->price;
                            $try_price += $expense->price * $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $eur_price += $expense->price;
                            $try_price += $expense->price * $item->eur_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $gbp_price += $expense->price;
                            $try_price += $expense->price * $item->gbp_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }

                    }

                }

                $currentMonth['try_sale'] = number_format($try_price, 2,".","");
                $currentMonth['usd_sale'] = number_format($usd_price, 2,".","");
                $currentMonth['eur_sale'] = number_format($eur_price, 2,".","");
                $currentMonth['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($previous_sales, $currentMonth);
            }


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => [
                'sales' => $sales,
                'previous_sales' => $previous_sales
            ]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getCancelledSales($owner_id)
    {
        try {
            $currentYearArray = array();
            $previousYearArray = array();

            $currentYear = date('Y');
            $previousYear = $currentYear - 1;

            for ($month = 1; $month <= 12; $month++) {
                $month_array = array();
                $month_array['year'] = $currentYear;
                $month_array['month'] = $month;
                array_push($currentYearArray, $month_array);

                $month_array2 = array();
                $month_array2['year'] = $previousYear;
                $month_array2['month'] = $month;
                array_push($previousYearArray, $month_array2);
            }

            $sales = array();
            $previous_sales = array();

            foreach ($currentYearArray as $currentMonth){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND (status_id=23 or status_id=25 or status_id=28))'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'cancelled')
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();

                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }


                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $try_price += $expense->price;
                            $usd_price += $expense->price / $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $usd_price += $expense->price;
                            $try_price += $expense->price * $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $eur_price += $expense->price;
                            $try_price += $expense->price * $item->eur_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $gbp_price += $expense->price;
                            $try_price += $expense->price * $item->gbp_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }

                    }

                }

                $currentMonth['try_sale'] = number_format($try_price, 2,".","");
                $currentMonth['usd_sale'] = number_format($usd_price, 2,".","");
                $currentMonth['eur_sale'] = number_format($eur_price, 2,".","");
                $currentMonth['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $currentMonth);
            }

            foreach ($previousYearArray as $currentMonth){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND (status_id=23 or status_id=25 or status_id=28))'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'cancelled')
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();

                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }


                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $try_price += $expense->price;
                            $usd_price += $expense->price / $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $usd_price += $expense->price;
                            $try_price += $expense->price * $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $eur_price += $expense->price;
                            $try_price += $expense->price * $item->eur_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $gbp_price += $expense->price;
                            $try_price += $expense->price * $item->gbp_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }

                    }

                }

                $currentMonth['try_sale'] = number_format($try_price, 2,".","");
                $currentMonth['usd_sale'] = number_format($usd_price, 2,".","");
                $currentMonth['eur_sale'] = number_format($eur_price, 2,".","");
                $currentMonth['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($previous_sales, $currentMonth);
            }


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => [
                'sales' => $sales,
                'previous_sales' => $previous_sales
            ]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getCompletedSales($owner_id)
    {
        try {
            $currentYearArray = array();
            $previousYearArray = array();

            $currentYear = date('Y');
            $previousYear = $currentYear - 1;

            for ($month = 1; $month <= 12; $month++) {
                $month_array = array();
                $month_array['year'] = $currentYear;
                $month_array['month'] = $month;
                array_push($currentYearArray, $month_array);

                $month_array2 = array();
                $month_array2['year'] = $previousYear;
                $month_array2['month'] = $month;
                array_push($previousYearArray, $month_array2);
            }

            $sales = array();
            $previous_sales = array();


            foreach ($currentYearArray as $currentMonth){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 24)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'completed')
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();

                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }


                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $try_price += $expense->price;
                            $usd_price += $expense->price / $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $usd_price += $expense->price;
                            $try_price += $expense->price * $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $eur_price += $expense->price;
                            $try_price += $expense->price * $item->eur_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $gbp_price += $expense->price;
                            $try_price += $expense->price * $item->gbp_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }

                    }

                }

                $currentMonth['try_sale'] = number_format($try_price, 2,".","");
                $currentMonth['usd_sale'] = number_format($usd_price, 2,".","");
                $currentMonth['eur_sale'] = number_format($eur_price, 2,".","");
                $currentMonth['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $currentMonth);
            }

            foreach ($previousYearArray as $currentMonth){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 24)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'completed')
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();

                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }


                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $try_price += $expense->price;
                            $usd_price += $expense->price / $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $usd_price += $expense->price;
                            $try_price += $expense->price * $item->usd_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $eur_price += $expense->price;
                            $try_price += $expense->price * $item->eur_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $gbp_price += $expense->price;
                            $try_price += $expense->price * $item->gbp_rate;
                            $usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }

                    }

                }

                $currentMonth['try_sale'] = number_format($try_price, 2,".","");
                $currentMonth['usd_sale'] = number_format($usd_price, 2,".","");
                $currentMonth['eur_sale'] = number_format($eur_price, 2,".","");
                $currentMonth['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($previous_sales, $currentMonth);
            }


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => [
                'sales' => $sales,
                'previous_sales' => $previous_sales
            ]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }


    public function getMonthlySalesLastTwelveMonths()
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $sales = array();
            $total_sales = array();
            $try_total = 0;
            $usd_total = 0;
            $eur_total = 0;
            $gbp_total = 0;
            foreach ($last_months as $last_month){
                $sale_items = Sale::query()
                    ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                    ->selectRaw('YEAR(sales.created_at) AS year, MONTH(sales.created_at) AS month, sales.*')
                    ->where('sales.active',1)
                    ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue')")
                    ->whereYear('sales.created_at', $last_month->year)
                    ->whereMonth('sales.created_at', $last_month->month)
                    ->get();


                $sale = array();
                $sale['year'] = $last_month->year;
                $sale['month'] = $last_month->month;
                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'EUR'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }
                }

                $try_total += $try_price;
                $usd_total += $usd_price;
                $eur_total += $eur_price;
                $gbp_total += $gbp_price;


                $sale = array();
                $sale['year'] = $last_month->year;
                $sale['month'] = $last_month->month;
                $sale['try_sale'] = number_format($try_price, 2,".","");
                $sale['usd_sale'] = number_format($usd_price, 2,".","");
                $sale['eur_sale'] = number_format($eur_price, 2,".","");
                $sale['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $sale);
            }

            $total_sales['try_total'] = number_format($try_total, 2,".","");
            $total_sales['usd_total'] = number_format($usd_total, 2,".","");
            $total_sales['eur_total'] = number_format($eur_total, 2,".","");
            $total_sales['gbp_total'] = number_format($gbp_total, 2,".","");


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales, 'total_sales' => $total_sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getMonthlyApprovedSalesLastTwelveMonths($owner_id)
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $sales = array();
            $total_sales = array();
            $try_total = 0;
            $usd_total = 0;
            $eur_total = 0;
            $gbp_total = 0;
            foreach ($last_months as $last_month){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'approved')
                    ->whereYear('sh.created_at', $last_month->year)
                    ->whereMonth('sh.created_at', $last_month->month);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();

                $sale = array();
                $sale['year'] = $last_month->year;
                $sale['month'] = $last_month->month;
                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }
                }

                $try_total += $try_price;
                $usd_total += $usd_price;
                $eur_total += $eur_price;
                $gbp_total += $gbp_price;


                $sale['try_sale'] = number_format($try_price, 2,".","");
                $sale['usd_sale'] = number_format($usd_price, 2,".","");
                $sale['eur_sale'] = number_format($eur_price, 2,".","");
                $sale['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $sale);
            }

            $total_sales['try_total'] = number_format($try_total, 2,".","");
            $total_sales['usd_total'] = number_format($usd_total, 2,".","");
            $total_sales['eur_total'] = number_format($eur_total, 2,".","");
            $total_sales['gbp_total'] = number_format($gbp_total, 2,".","");


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales, 'total_sales' => $total_sales, 'l'=>$last_months]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getMonthlyApprovedSalesLastTwelveMonthsByAdmin($admin_id)
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $sales = array();
            $total_sales = array();
            $try_total = 0;
            $usd_total = 0;
            $eur_total = 0;
            $gbp_total = 0;
            foreach ($last_months as $last_month){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'approved')
                    ->where('offer_requests.authorized_personnel_id', '=', $admin_id)
                    ->whereYear('sh.created_at', $last_month->year)
                    ->whereMonth('sh.created_at', $last_month->month)
                    ->get();

                $sale = array();
                $sale['year'] = $last_month->year;
                $sale['month'] = $last_month->month;
                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }
                }

                $try_total += $try_price;
                $usd_total += $usd_price;
                $eur_total += $eur_price;
                $gbp_total += $gbp_price;


                $sale['try_sale'] = number_format($try_price, 2,".","");
                $sale['usd_sale'] = number_format($usd_price, 2,".","");
                $sale['eur_sale'] = number_format($eur_price, 2,".","");
                $sale['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $sale);
            }

            $total_sales['try_total'] = number_format($try_total, 2,".","");
            $total_sales['usd_total'] = number_format($usd_total, 2,".","");
            $total_sales['eur_total'] = number_format($eur_total, 2,".","");
            $total_sales['gbp_total'] = number_format($gbp_total, 2,".","");


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales, 'total_sales' => $total_sales, 'l'=>$last_months]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getMonthlyCompletedSalesLastTwelveMonths($owner_id)
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $sales = array();
            $total_sales = array();
            $try_total = 0;
            $usd_total = 0;
            $eur_total = 0;
            $gbp_total = 0;
            foreach ($last_months as $last_month){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 24)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'completed')
                    ->whereYear('sh.created_at', $last_month->year)
                    ->whereMonth('sh.created_at', $last_month->month);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();


                $sale = array();
                $sale['year'] = $last_month->year;
                $sale['month'] = $last_month->month;
                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }
                }

                $try_total += $try_price;
                $usd_total += $usd_price;
                $eur_total += $eur_price;
                $gbp_total += $gbp_price;

                $sale['try_sale'] = number_format($try_price, 2,".","");
                $sale['usd_sale'] = number_format($usd_price, 2,".","");
                $sale['eur_sale'] = number_format($eur_price, 2,".","");
                $sale['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $sale);
            }

            $total_sales['try_total'] = number_format($try_total, 2,".","");
            $total_sales['usd_total'] = number_format($usd_total, 2,".","");
            $total_sales['eur_total'] = number_format($eur_total, 2,".","");
            $total_sales['gbp_total'] = number_format($gbp_total, 2,".","");


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales, 'total_sales' => $total_sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getMonthlyCompletedSalesLastTwelveMonthsByAdmin($admin_id)
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $sales = array();
            $total_sales = array();
            $try_total = 0;
            $usd_total = 0;
            $eur_total = 0;
            $gbp_total = 0;
            foreach ($last_months as $last_month){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 24)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'completed')
                    ->where('offer_requests.authorized_personnel_id', '=', $admin_id)
                    ->whereYear('sh.created_at', $last_month->year)
                    ->whereMonth('sh.created_at', $last_month->month)
                    ->get();


                $sale = array();
                $sale['year'] = $last_month->year;
                $sale['month'] = $last_month->month;
                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }
                }

                $try_total += $try_price;
                $usd_total += $usd_price;
                $eur_total += $eur_price;
                $gbp_total += $gbp_price;

                $sale['try_sale'] = number_format($try_price, 2,".","");
                $sale['usd_sale'] = number_format($usd_price, 2,".","");
                $sale['eur_sale'] = number_format($eur_price, 2,".","");
                $sale['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $sale);
            }

            $total_sales['try_total'] = number_format($try_total, 2,".","");
            $total_sales['usd_total'] = number_format($usd_total, 2,".","");
            $total_sales['eur_total'] = number_format($eur_total, 2,".","");
            $total_sales['gbp_total'] = number_format($gbp_total, 2,".","");


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales, 'total_sales' => $total_sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getMonthlyPotentialSalesLastTwelveMonths($owner_id)
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $sales = array();
            $total_sales = array();
            $try_total = 0;
            $usd_total = 0;
            $eur_total = 0;
            $gbp_total = 0;
            foreach ($last_months as $last_month){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 5)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'continue')
                    ->whereYear('sh.created_at', $last_month->year)
                    ->whereMonth('sh.created_at', $last_month->month);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();


                $sale = array();
                $sale['year'] = $last_month->year;
                $sale['month'] = $last_month->month;
                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }
                }

                $try_total += $try_price;
                $usd_total += $usd_price;
                $eur_total += $eur_price;
                $gbp_total += $gbp_price;

                $sale['try_sale'] = number_format($try_price, 2,".","");
                $sale['usd_sale'] = number_format($usd_price, 2,".","");
                $sale['eur_sale'] = number_format($eur_price, 2,".","");
                $sale['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $sale);
            }

            $total_sales['try_total'] = number_format($try_total, 2,".","");
            $total_sales['usd_total'] = number_format($usd_total, 2,".","");
            $total_sales['eur_total'] = number_format($eur_total, 2,".","");
            $total_sales['gbp_total'] = number_format($gbp_total, 2,".","");


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales, 'total_sales' => $total_sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getMonthlyPotentialSalesLastTwelveMonthsByAdmin($admin_id)
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $sales = array();
            $total_sales = array();
            $try_total = 0;
            $usd_total = 0;
            $eur_total = 0;
            $gbp_total = 0;
            foreach ($last_months as $last_month){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 5)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'continue')
                    ->where('offer_requests.authorized_personnel_id', '=', $admin_id)
                    ->whereYear('sh.created_at', $last_month->year)
                    ->whereMonth('sh.created_at', $last_month->month)
                    ->get();


                $sale = array();
                $sale['year'] = $last_month->year;
                $sale['month'] = $last_month->month;
                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }
                }

                $try_total += $try_price;
                $usd_total += $usd_price;
                $eur_total += $eur_price;
                $gbp_total += $gbp_price;

                $sale['try_sale'] = number_format($try_price, 2,".","");
                $sale['usd_sale'] = number_format($usd_price, 2,".","");
                $sale['eur_sale'] = number_format($eur_price, 2,".","");
                $sale['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $sale);
            }

            $total_sales['try_total'] = number_format($try_total, 2,".","");
            $total_sales['usd_total'] = number_format($usd_total, 2,".","");
            $total_sales['eur_total'] = number_format($eur_total, 2,".","");
            $total_sales['gbp_total'] = number_format($gbp_total, 2,".","");


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales, 'total_sales' => $total_sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getMonthlyCancelledSalesLastTwelveMonths($owner_id)
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $sales = array();
            $total_sales = array();
            $try_total = 0;
            $usd_total = 0;
            $eur_total = 0;
            $gbp_total = 0;
            foreach ($last_months as $last_month){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND (status_id=23 or status_id=25 or status_id=28))'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'cancelled')
                    ->whereYear('sh.created_at', $last_month->year)
                    ->whereMonth('sh.created_at', $last_month->month);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();


                $sale = array();
                $sale['year'] = $last_month->year;
                $sale['month'] = $last_month->month;
                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }
                }

                $try_total += $try_price;
                $usd_total += $usd_price;
                $eur_total += $eur_price;
                $gbp_total += $gbp_price;

                $sale['try_sale'] = number_format($try_price, 2,".","");
                $sale['usd_sale'] = number_format($usd_price, 2,".","");
                $sale['eur_sale'] = number_format($eur_price, 2,".","");
                $sale['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $sale);
            }

            $total_sales['try_total'] = number_format($try_total, 2,".","");
            $total_sales['usd_total'] = number_format($usd_total, 2,".","");
            $total_sales['eur_total'] = number_format($eur_total, 2,".","");
            $total_sales['gbp_total'] = number_format($gbp_total, 2,".","");


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales, 'total_sales' => $total_sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getMonthlyCancelledSalesLastTwelveMonthsByAdmin($admin_id)
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $sales = array();
            $total_sales = array();
            $try_total = 0;
            $usd_total = 0;
            $eur_total = 0;
            $gbp_total = 0;
            foreach ($last_months as $last_month){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND (status_id=23 or status_id=25 or status_id=28))'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'cancelled')
                    ->where('offer_requests.authorized_personnel_id', '=', $admin_id)
                    ->whereYear('sh.created_at', $last_month->year)
                    ->whereMonth('sh.created_at', $last_month->month)
                    ->get();


                $sale = array();
                $sale['year'] = $last_month->year;
                $sale['month'] = $last_month->month;
                $try_price = 0;
                $usd_price = 0;
                $eur_price = 0;
                $gbp_price = 0;

                foreach ($sale_items as $item){

                    if ($item->currency == 'TRY'){
                        $try_price += $item->grand_total;
                        $usd_price += $item->grand_total / $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $usd_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->usd_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $eur_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->eur_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $gbp_price += $item->grand_total;
                        $try_price += $item->grand_total * $item->gbp_rate;
                        $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }
                }

                $try_total += $try_price;
                $usd_total += $usd_price;
                $eur_total += $eur_price;
                $gbp_total += $gbp_price;

                $sale['try_sale'] = number_format($try_price, 2,".","");
                $sale['usd_sale'] = number_format($usd_price, 2,".","");
                $sale['eur_sale'] = number_format($eur_price, 2,".","");
                $sale['gbp_sale'] = number_format($gbp_price, 2,".","");
                array_push($sales, $sale);
            }

            $total_sales['try_total'] = number_format($try_total, 2,".","");
            $total_sales['usd_total'] = number_format($usd_total, 2,".","");
            $total_sales['eur_total'] = number_format($eur_total, 2,".","");
            $total_sales['gbp_total'] = number_format($gbp_total, 2,".","");


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales, 'total_sales' => $total_sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getTotalSales($owner_id)
    {
        try {

            $sales = array();

            $sale_items = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.period as period')
                ->where('sales.active',1)
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue' OR statuses.period = 'cancelled')");

            if ($owner_id != 0){
                $sale_items = $sale_items
                    ->where('sales.owner_id', $owner_id);
            }

            $sale_items = $sale_items
                ->get();


            $continue_try_price = 0;
            $continue_usd_price = 0;
            $continue_eur_price = 0;
            $continue_gbp_price = 0;
            $approved_try_price = 0;
            $approved_usd_price = 0;
            $approved_eur_price = 0;
            $approved_gbp_price = 0;
            $completed_try_price = 0;
            $completed_usd_price = 0;
            $completed_eur_price = 0;
            $completed_gbp_price = 0;
            $cancelled_try_price = 0;
            $cancelled_usd_price = 0;
            $cancelled_eur_price = 0;
            $cancelled_gbp_price = 0;

            foreach ($sale_items as $item){

                //ek giderler
                $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();

                if($item->period == 'continue'){

                    if ($item->currency == 'TRY'){
                        $continue_try_price += $item->grand_total;
                        $continue_usd_price += $item->grand_total / $item->usd_rate;
                        $continue_eur_price += $item->grand_total / $item->eur_rate;
                        $continue_gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $continue_usd_price += $item->grand_total;
                        $continue_try_price += $item->grand_total * $item->usd_rate;
                        $continue_eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $continue_gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $continue_eur_price += $item->grand_total;
                        $continue_try_price += $item->grand_total * $item->eur_rate;
                        $continue_usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $continue_gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $continue_gbp_price += $item->grand_total;
                        $continue_try_price += $item->grand_total * $item->gbp_rate;
                        $continue_usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $continue_eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }

                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $continue_try_price += $expense->price;
                            $continue_usd_price += $expense->price / $item->usd_rate;
                            $continue_eur_price += $expense->price / $item->eur_rate;
                            $continue_gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $continue_usd_price += $expense->price;
                            $continue_try_price += $expense->price * $item->usd_rate;
                            $continue_eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $continue_gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $continue_eur_price += $expense->price;
                            $continue_try_price += $expense->price * $item->eur_rate;
                            $continue_usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $continue_gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $continue_gbp_price += $expense->price;
                            $continue_try_price += $expense->price * $item->gbp_rate;
                            $continue_usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $continue_eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }
                    }

                }else if($item->period == 'approved'){

                    if ($item->currency == 'TRY'){
                        $approved_try_price += $item->grand_total;
                        $approved_usd_price += $item->grand_total / $item->usd_rate;
                        $approved_eur_price += $item->grand_total / $item->eur_rate;
                        $approved_gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $approved_usd_price += $item->grand_total;
                        $approved_try_price += $item->grand_total * $item->usd_rate;
                        $approved_eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $approved_gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $approved_eur_price += $item->grand_total;
                        $approved_try_price += $item->grand_total * $item->eur_rate;
                        $approved_usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $approved_gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $approved_gbp_price += $item->grand_total;
                        $approved_try_price += $item->grand_total * $item->gbp_rate;
                        $approved_usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $approved_eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }

                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $approved_try_price += $expense->price;
                            $approved_usd_price += $expense->price / $item->usd_rate;
                            $approved_eur_price += $expense->price / $item->eur_rate;
                            $approved_gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $approved_usd_price += $expense->price;
                            $approved_try_price += $expense->price * $item->usd_rate;
                            $approved_eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $approved_gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $approved_eur_price += $expense->price;
                            $approved_try_price += $expense->price * $item->eur_rate;
                            $approved_usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $approved_gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $approved_gbp_price += $expense->price;
                            $approved_try_price += $expense->price * $item->gbp_rate;
                            $approved_usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $approved_eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }
                    }

                }else if($item->period == 'completed'){

                    if ($item->currency == 'TRY'){
                        $completed_try_price += $item->grand_total;
                        $completed_usd_price += $item->grand_total / $item->usd_rate;
                        $completed_eur_price += $item->grand_total / $item->eur_rate;
                        $completed_gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $completed_usd_price += $item->grand_total;
                        $completed_try_price += $item->grand_total * $item->usd_rate;
                        $completed_eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $completed_gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $completed_eur_price += $item->grand_total;
                        $completed_try_price += $item->grand_total * $item->eur_rate;
                        $completed_usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $completed_gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $completed_gbp_price += $item->grand_total;
                        $completed_try_price += $item->grand_total * $item->gbp_rate;
                        $completed_usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $completed_eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }

                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $completed_try_price += $expense->price;
                            $completed_usd_price += $expense->price / $item->usd_rate;
                            $completed_eur_price += $expense->price / $item->eur_rate;
                            $completed_gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $completed_usd_price += $expense->price;
                            $completed_try_price += $expense->price * $item->usd_rate;
                            $completed_eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $completed_gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $completed_eur_price += $expense->price;
                            $completed_try_price += $expense->price * $item->eur_rate;
                            $completed_usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $completed_gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $completed_gbp_price += $expense->price;
                            $completed_try_price += $expense->price * $item->gbp_rate;
                            $completed_usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $completed_eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }
                    }

                }else if($item->period == 'cancelled'){

                    if ($item->currency == 'TRY'){
                        $cancelled_try_price += $item->grand_total;
                        $cancelled_usd_price += $item->grand_total / $item->usd_rate;
                        $cancelled_eur_price += $item->grand_total / $item->eur_rate;
                        $cancelled_gbp_price += $item->grand_total / $item->gbp_rate;
                    }else if ($item->currency == 'USD'){
                        $cancelled_usd_price += $item->grand_total;
                        $cancelled_try_price += $item->grand_total * $item->usd_rate;
                        $cancelled_eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                        $cancelled_gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                    }else if ($item->currency == 'EUR'){
                        $cancelled_eur_price += $item->grand_total;
                        $cancelled_try_price += $item->grand_total * $item->eur_rate;
                        $cancelled_usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                        $cancelled_gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                    }else if ($item->currency == 'GBP'){
                        $cancelled_gbp_price += $item->grand_total;
                        $cancelled_try_price += $item->grand_total * $item->gbp_rate;
                        $cancelled_usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                        $cancelled_eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                    }

                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $cancelled_try_price += $expense->price;
                            $cancelled_usd_price += $expense->price / $item->usd_rate;
                            $cancelled_eur_price += $expense->price / $item->eur_rate;
                            $cancelled_gbp_price += $expense->price / $item->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $cancelled_usd_price += $expense->price;
                            $cancelled_try_price += $expense->price * $item->usd_rate;
                            $cancelled_eur_price += $expense->price / $item->eur_rate * $item->usd_rate;
                            $cancelled_gbp_price += $expense->price / $item->gbp_rate * $item->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $cancelled_eur_price += $expense->price;
                            $cancelled_try_price += $expense->price * $item->eur_rate;
                            $cancelled_usd_price += $expense->price / $item->usd_rate * $item->eur_rate;
                            $cancelled_gbp_price += $expense->price / $item->gbp_rate * $item->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $cancelled_gbp_price += $expense->price;
                            $cancelled_try_price += $expense->price * $item->gbp_rate;
                            $cancelled_usd_price += $expense->price / $item->usd_rate * $item->gbp_rate;
                            $cancelled_eur_price += $expense->price / $item->eur_rate * $item->gbp_rate;
                        }
                    }

                }

            }

            $continue = array();
            $continue['try_sale'] = number_format($continue_try_price, 2,".","");
            $continue['usd_sale'] = number_format($continue_usd_price, 2,".","");
            $continue['eur_sale'] = number_format($continue_eur_price, 2,".","");
            $continue['gbp_sale'] = number_format($continue_gbp_price, 2,".","");

            $approved = array();
            $approved['try_sale'] = number_format($approved_try_price, 2,".","");
            $approved['usd_sale'] = number_format($approved_usd_price, 2,".","");
            $approved['eur_sale'] = number_format($approved_eur_price, 2,".","");
            $approved['gbp_sale'] = number_format($approved_gbp_price, 2,".","");

            $completed = array();
            $completed['try_sale'] = number_format($completed_try_price, 2,".","");
            $completed['usd_sale'] = number_format($completed_usd_price, 2,".","");
            $completed['eur_sale'] = number_format($completed_eur_price, 2,".","");
            $completed['gbp_sale'] = number_format($completed_gbp_price, 2,".","");

            $cancelled = array();
            $cancelled['try_sale'] = number_format($cancelled_try_price, 2,".","");
            $cancelled['usd_sale'] = number_format($cancelled_usd_price, 2,".","");
            $cancelled['eur_sale'] = number_format($cancelled_eur_price, 2,".","");
            $cancelled['gbp_sale'] = number_format($cancelled_gbp_price, 2,".","");




            $sales['continue'] = $continue;
            $sales['approved'] = $approved;
            $sales['completed'] = $completed;
            $sales['cancelled'] = $cancelled;


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getLastMonthSales($owner_id)
    {
        try {

            $sales = array();

            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $continue_try_price = 0;
            $continue_usd_price = 0;
            $continue_eur_price = 0;
            $continue_gbp_price = 0;
            $approved_try_price = 0;
            $approved_usd_price = 0;
            $approved_eur_price = 0;
            $approved_gbp_price = 0;
            $completed_try_price = 0;
            $completed_usd_price = 0;
            $completed_eur_price = 0;
            $completed_gbp_price = 0;
            $cancelled_try_price = 0;
            $cancelled_usd_price = 0;
            $cancelled_eur_price = 0;
            $cancelled_gbp_price = 0;


            $continue = array();
            $approved = array();
            $completed = array();
            $cancelled = array();


            $day_count = 0;


            $firstDayOfMonth = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
            $lastDayOfMonth = Carbon::create($currentYear, $currentMonth, 1)->lastOfMonth()->endOfDay();


            $continue_serie = array();
            $continue_serie_try = array();

            for ($date = $firstDayOfMonth; $date <= $lastDayOfMonth; $date->addDay()) {
                $day_count++;

                $daily_total_continue_sales = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 5)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'continue')
                    ->whereDate('sh.created_at', $date->toDateString());

                if ($owner_id != 0){
                    $daily_total_continue_sales = $daily_total_continue_sales
                        ->where('s.owner_id', $owner_id);
                }

                $daily_total_continue_sales = $daily_total_continue_sales
                    ->get();

                $daily_continue_try_price = 0;
                $daily_continue_usd_price = 0;
                $daily_continue_eur_price = 0;
                $daily_continue_gbp_price = 0;

                foreach ($daily_total_continue_sales as $sl){

                    if ($sl->currency == 'TRY'){
                        $daily_continue_try_price += $sl->grand_total;
                        $daily_continue_usd_price += $sl->grand_total / $sl->usd_rate;
                        $daily_continue_eur_price += $sl->grand_total / $sl->eur_rate;
                        $daily_continue_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $daily_continue_usd_price += $sl->grand_total;
                        $daily_continue_try_price += $sl->grand_total * $sl->usd_rate;
                        $daily_continue_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $daily_continue_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $daily_continue_eur_price += $sl->grand_total;
                        $daily_continue_try_price += $sl->grand_total * $sl->eur_rate;
                        $daily_continue_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $daily_continue_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $daily_continue_gbp_price += $sl->grand_total;
                        $daily_continue_try_price += $sl->grand_total * $sl->gbp_rate;
                        $daily_continue_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $daily_continue_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    if ($sl->currency == 'TRY'){
                        $continue_try_price += $sl->grand_total;
                        $continue_usd_price += $sl->grand_total / $sl->usd_rate;
                        $continue_eur_price += $sl->grand_total / $sl->eur_rate;
                        $continue_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $continue_usd_price += $sl->grand_total;
                        $continue_try_price += $sl->grand_total * $sl->usd_rate;
                        $continue_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $continue_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $continue_eur_price += $sl->grand_total;
                        $continue_try_price += $sl->grand_total * $sl->eur_rate;
                        $continue_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $continue_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $continue_gbp_price += $sl->grand_total;
                        $continue_try_price += $sl->grand_total * $sl->gbp_rate;
                        $continue_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $continue_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $sl->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $daily_continue_try_price += $expense->price;
                            $daily_continue_usd_price += $expense->price / $sl->usd_rate;
                            $daily_continue_eur_price += $expense->price / $sl->eur_rate;
                            $daily_continue_gbp_price += $expense->price / $sl->gbp_rate;

                            $continue_try_price += $expense->price;
                            $continue_usd_price += $expense->price / $sl->usd_rate;
                            $continue_eur_price += $expense->price / $sl->eur_rate;
                            $continue_gbp_price += $expense->price / $sl->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $daily_continue_usd_price += $expense->price;
                            $daily_continue_try_price += $expense->price * $sl->usd_rate;
                            $daily_continue_eur_price += $expense->price / $sl->eur_rate * $sl->usd_rate;
                            $daily_continue_gbp_price += $expense->price / $sl->gbp_rate * $sl->usd_rate;

                            $continue_usd_price += $expense->price;
                            $continue_try_price += $expense->price * $sl->usd_rate;
                            $continue_eur_price += $expense->price / $sl->eur_rate * $sl->usd_rate;
                            $continue_gbp_price += $expense->price / $sl->gbp_rate * $sl->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $daily_continue_eur_price += $expense->price;
                            $daily_continue_try_price += $expense->price * $sl->eur_rate;
                            $daily_continue_usd_price += $expense->price / $sl->usd_rate * $sl->eur_rate;
                            $daily_continue_gbp_price += $expense->price / $sl->gbp_rate * $sl->eur_rate;

                            $continue_eur_price += $expense->price;
                            $continue_try_price += $expense->price * $sl->eur_rate;
                            $continue_usd_price += $expense->price / $sl->usd_rate * $sl->eur_rate;
                            $continue_gbp_price += $expense->price / $sl->gbp_rate * $sl->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $daily_continue_gbp_price += $expense->price;
                            $daily_continue_try_price += $expense->price * $sl->gbp_rate;
                            $daily_continue_usd_price += $expense->price / $sl->usd_rate * $sl->gbp_rate;
                            $daily_continue_eur_price += $expense->price / $sl->eur_rate * $sl->gbp_rate;

                            $continue_gbp_price += $expense->price;
                            $continue_try_price += $expense->price * $sl->gbp_rate;
                            $continue_usd_price += $expense->price / $sl->usd_rate * $sl->gbp_rate;
                            $continue_eur_price += $expense->price / $sl->eur_rate * $sl->gbp_rate;
                        }

                    }



                }

                $continue_serie_this_day = array();
                $continue_serie_this_day['date'] = $date->toDateString();
                $continue_serie_this_day['try'] = number_format($daily_continue_try_price, 2,".","");
                $continue_serie_this_day['usd'] = number_format($daily_continue_usd_price, 2,".","");
                $continue_serie_this_day['eur'] = number_format($daily_continue_eur_price, 2,".","");
                $continue_serie_this_day['gbp'] = number_format($daily_continue_gbp_price, 2,".","");

                array_push($continue_serie, $continue_serie_this_day);
                array_push($continue_serie_try, number_format($daily_continue_try_price, 2,".",""));
            }
            $continue['continue_serie'] = $continue_serie;
            $continue['continue_serie_try'] = $continue_serie_try;



            $firstDayOfMonth2 = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
            $lastDayOfMonth2 = Carbon::create($currentYear, $currentMonth, 1)->lastOfMonth()->endOfDay();

            $approved_serie = array();
            $approved_serie_try = array();

            for ($date = $firstDayOfMonth2; $date <= $lastDayOfMonth2; $date->addDay()) {

                $daily_total_approved_sales = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'approved')
                    ->whereDate('sh.created_at', $date->toDateString());

                if ($owner_id != 0){
                    $daily_total_approved_sales = $daily_total_approved_sales
                        ->where('s.owner_id', $owner_id);
                }

                $daily_total_approved_sales = $daily_total_approved_sales
                    ->get();

                $daily_approved_try_price = 0;
                $daily_approved_usd_price = 0;
                $daily_approved_eur_price = 0;
                $daily_approved_gbp_price = 0;

                foreach ($daily_total_approved_sales as $sl){

                    if ($sl->currency == 'TRY'){
                        $daily_approved_try_price += $sl->grand_total;
                        $daily_approved_usd_price += $sl->grand_total / $sl->usd_rate;
                        $daily_approved_eur_price += $sl->grand_total / $sl->eur_rate;
                        $daily_approved_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $daily_approved_usd_price += $sl->grand_total;
                        $daily_approved_try_price += $sl->grand_total * $sl->usd_rate;
                        $daily_approved_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $daily_approved_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $daily_approved_eur_price += $sl->grand_total;
                        $daily_approved_try_price += $sl->grand_total * $sl->eur_rate;
                        $daily_approved_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $daily_approved_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $daily_approved_gbp_price += $sl->grand_total;
                        $daily_approved_try_price += $sl->grand_total * $sl->gbp_rate;
                        $daily_approved_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $daily_approved_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    if ($sl->currency == 'TRY'){
                        $approved_try_price += $sl->grand_total;
                        $approved_usd_price += $sl->grand_total / $sl->usd_rate;
                        $approved_eur_price += $sl->grand_total / $sl->eur_rate;
                        $approved_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $approved_usd_price += $sl->grand_total;
                        $approved_try_price += $sl->grand_total * $sl->usd_rate;
                        $approved_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $approved_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $approved_eur_price += $sl->grand_total;
                        $approved_try_price += $sl->grand_total * $sl->eur_rate;
                        $approved_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $approved_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $approved_gbp_price += $sl->grand_total;
                        $approved_try_price += $sl->grand_total * $sl->gbp_rate;
                        $approved_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $approved_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $sl->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $daily_approved_try_price += $expense->price;
                            $daily_approved_usd_price += $expense->price / $sl->usd_rate;
                            $daily_approved_eur_price += $expense->price / $sl->eur_rate;
                            $daily_approved_gbp_price += $expense->price / $sl->gbp_rate;

                            $approved_try_price += $expense->price;
                            $approved_usd_price += $expense->price / $sl->usd_rate;
                            $approved_eur_price += $expense->price / $sl->eur_rate;
                            $approved_gbp_price += $expense->price / $sl->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $daily_approved_usd_price += $expense->price;
                            $daily_approved_try_price += $expense->price * $sl->usd_rate;
                            $daily_approved_eur_price += $expense->price / $sl->eur_rate * $sl->usd_rate;
                            $daily_approved_gbp_price += $expense->price / $sl->gbp_rate * $sl->usd_rate;

                            $approved_usd_price += $expense->price;
                            $approved_try_price += $expense->price * $sl->usd_rate;
                            $approved_eur_price += $expense->price / $sl->eur_rate * $sl->usd_rate;
                            $approved_gbp_price += $expense->price / $sl->gbp_rate * $sl->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $daily_approved_eur_price += $expense->price;
                            $daily_approved_try_price += $expense->price * $sl->eur_rate;
                            $daily_approved_usd_price += $expense->price / $sl->usd_rate * $sl->eur_rate;
                            $daily_approved_gbp_price += $expense->price / $sl->gbp_rate * $sl->eur_rate;

                            $approved_eur_price += $expense->price;
                            $approved_try_price += $expense->price * $sl->eur_rate;
                            $approved_usd_price += $expense->price / $sl->usd_rate * $sl->eur_rate;
                            $approved_gbp_price += $expense->price / $sl->gbp_rate * $sl->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $daily_approved_gbp_price += $expense->price;
                            $daily_approved_try_price += $expense->price * $sl->gbp_rate;
                            $daily_approved_usd_price += $expense->price / $sl->usd_rate * $sl->gbp_rate;
                            $daily_approved_eur_price += $expense->price / $sl->eur_rate * $sl->gbp_rate;

                            $approved_gbp_price += $expense->price;
                            $approved_try_price += $expense->price * $sl->gbp_rate;
                            $approved_usd_price += $expense->price / $sl->usd_rate * $sl->gbp_rate;
                            $approved_eur_price += $expense->price / $sl->eur_rate * $sl->gbp_rate;
                        }

                    }

                }

                $approved_serie_this_day = array();
                $approved_serie_this_day['date'] = $date->toDateString();
                $approved_serie_this_day['try'] = number_format($daily_approved_try_price, 2,".","");
                $approved_serie_this_day['usd'] = number_format($daily_approved_usd_price, 2,".","");
                $approved_serie_this_day['eur'] = number_format($daily_approved_eur_price, 2,".","");
                $approved_serie_this_day['gbp'] = number_format($daily_approved_gbp_price, 2,".","");

                array_push($approved_serie, $approved_serie_this_day);
                array_push($approved_serie_try, number_format($daily_approved_try_price, 2,".",""));
            }
            $approved['approved_serie'] = $approved_serie;
            $approved['approved_serie_try'] = $approved_serie_try;



            $firstDayOfMonth3 = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
            $lastDayOfMonth3 = Carbon::create($currentYear, $currentMonth, 1)->lastOfMonth()->endOfDay();

            $completed_serie = array();
            $completed_serie_try = array();

            for ($date = $firstDayOfMonth3; $date <= $lastDayOfMonth3; $date->addDay()) {

                $daily_total_completed_sales = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 24)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'completed')
                    ->whereDate('sh.created_at', $date->toDateString());

                if ($owner_id != 0){
                    $daily_total_completed_sales = $daily_total_completed_sales
                        ->where('s.owner_id', $owner_id);
                }

                $daily_total_completed_sales = $daily_total_completed_sales
                    ->get();

                $daily_completed_try_price = 0;
                $daily_completed_usd_price = 0;
                $daily_completed_eur_price = 0;
                $daily_completed_gbp_price = 0;

                foreach ($daily_total_completed_sales as $sl){

                    if ($sl->currency == 'TRY'){
                        $daily_completed_try_price += $sl->grand_total;
                        $daily_completed_usd_price += $sl->grand_total / $sl->usd_rate;
                        $daily_completed_eur_price += $sl->grand_total / $sl->eur_rate;
                        $daily_completed_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $daily_completed_usd_price += $sl->grand_total;
                        $daily_completed_try_price += $sl->grand_total * $sl->usd_rate;
                        $daily_completed_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $daily_completed_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $daily_completed_eur_price += $sl->grand_total;
                        $daily_completed_try_price += $sl->grand_total * $sl->eur_rate;
                        $daily_completed_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $daily_completed_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $daily_completed_gbp_price += $sl->grand_total;
                        $daily_completed_try_price += $sl->grand_total * $sl->gbp_rate;
                        $daily_completed_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $daily_completed_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    if ($sl->currency == 'TRY'){
                        $completed_try_price += $sl->grand_total;
                        $completed_usd_price += $sl->grand_total / $sl->usd_rate;
                        $completed_eur_price += $sl->grand_total / $sl->eur_rate;
                        $completed_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $completed_usd_price += $sl->grand_total;
                        $completed_try_price += $sl->grand_total * $sl->usd_rate;
                        $completed_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $completed_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $completed_eur_price += $sl->grand_total;
                        $completed_try_price += $sl->grand_total * $sl->eur_rate;
                        $completed_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $completed_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $completed_gbp_price += $sl->grand_total;
                        $completed_try_price += $sl->grand_total * $sl->gbp_rate;
                        $completed_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $completed_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $sl->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $daily_completed_try_price += $expense->price;
                            $daily_completed_usd_price += $expense->price / $sl->usd_rate;
                            $daily_completed_eur_price += $expense->price / $sl->eur_rate;
                            $daily_completed_gbp_price += $expense->price / $sl->gbp_rate;

                            $completed_try_price += $expense->price;
                            $completed_usd_price += $expense->price / $sl->usd_rate;
                            $completed_eur_price += $expense->price / $sl->eur_rate;
                            $completed_gbp_price += $expense->price / $sl->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $daily_completed_usd_price += $expense->price;
                            $daily_completed_try_price += $expense->price * $sl->usd_rate;
                            $daily_completed_eur_price += $expense->price / $sl->eur_rate * $sl->usd_rate;
                            $daily_completed_gbp_price += $expense->price / $sl->gbp_rate * $sl->usd_rate;

                            $completed_usd_price += $expense->price;
                            $completed_try_price += $expense->price * $sl->usd_rate;
                            $completed_eur_price += $expense->price / $sl->eur_rate * $sl->usd_rate;
                            $completed_gbp_price += $expense->price / $sl->gbp_rate * $sl->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $daily_completed_eur_price += $expense->price;
                            $daily_completed_try_price += $expense->price * $sl->eur_rate;
                            $daily_completed_usd_price += $expense->price / $sl->usd_rate * $sl->eur_rate;
                            $daily_completed_gbp_price += $expense->price / $sl->gbp_rate * $sl->eur_rate;

                            $completed_eur_price += $expense->price;
                            $completed_try_price += $expense->price * $sl->eur_rate;
                            $completed_usd_price += $expense->price / $sl->usd_rate * $sl->eur_rate;
                            $completed_gbp_price += $expense->price / $sl->gbp_rate * $sl->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $daily_completed_gbp_price += $expense->price;
                            $daily_completed_try_price += $expense->price * $sl->gbp_rate;
                            $daily_completed_usd_price += $expense->price / $sl->usd_rate * $sl->gbp_rate;
                            $daily_completed_eur_price += $expense->price / $sl->eur_rate * $sl->gbp_rate;

                            $completed_gbp_price += $expense->price;
                            $completed_try_price += $expense->price * $sl->gbp_rate;
                            $completed_usd_price += $expense->price / $sl->usd_rate * $sl->gbp_rate;
                            $completed_eur_price += $expense->price / $sl->eur_rate * $sl->gbp_rate;
                        }

                    }

                }

                $completed_serie_this_day = array();
                $completed_serie_this_day['date'] = $date->toDateString();
                $completed_serie_this_day['try'] = number_format($daily_completed_try_price, 2,".","");
                $completed_serie_this_day['usd'] = number_format($daily_completed_usd_price, 2,".","");
                $completed_serie_this_day['eur'] = number_format($daily_completed_eur_price, 2,".","");
                $completed_serie_this_day['gbp'] = number_format($daily_completed_gbp_price, 2,".","");

                array_push($completed_serie, $completed_serie_this_day);
                array_push($completed_serie_try, number_format($daily_completed_try_price, 2,".",""));
            }
            $completed['completed_serie'] = $completed_serie;
            $completed['completed_serie_try'] = $completed_serie_try;




            $firstDayOfMonth4 = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
            $lastDayOfMonth4 = Carbon::create($currentYear, $currentMonth, 1)->lastOfMonth()->endOfDay();

            $cancelled_serie = array();
            $cancelled_serie_try = array();

            for ($date = $firstDayOfMonth4; $date <= $lastDayOfMonth4; $date->addDay()) {

                $daily_total_cancelled_sales = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND (status_id=23 OR status_id=25 OR status_id=28))'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'cancelled')
                    ->whereDate('sh.created_at', $date->toDateString());

                if ($owner_id != 0){
                    $daily_total_cancelled_sales = $daily_total_cancelled_sales
                        ->where('s.owner_id', $owner_id);
                }

                $daily_total_cancelled_sales = $daily_total_cancelled_sales
                    ->get();

                $daily_cancelled_try_price = 0;
                $daily_cancelled_usd_price = 0;
                $daily_cancelled_eur_price = 0;
                $daily_cancelled_gbp_price = 0;

                foreach ($daily_total_cancelled_sales as $sl){

                    if ($sl->currency == 'TRY'){
                        $daily_cancelled_try_price += $sl->grand_total;
                        $daily_cancelled_usd_price += $sl->grand_total / $sl->usd_rate;
                        $daily_cancelled_eur_price += $sl->grand_total / $sl->eur_rate;
                        $daily_cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $daily_cancelled_usd_price += $sl->grand_total;
                        $daily_cancelled_try_price += $sl->grand_total * $sl->usd_rate;
                        $daily_cancelled_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $daily_cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $daily_cancelled_eur_price += $sl->grand_total;
                        $daily_cancelled_try_price += $sl->grand_total * $sl->eur_rate;
                        $daily_cancelled_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $daily_cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $daily_cancelled_gbp_price += $sl->grand_total;
                        $daily_cancelled_try_price += $sl->grand_total * $sl->gbp_rate;
                        $daily_cancelled_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $daily_cancelled_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    if ($sl->currency == 'TRY'){
                        $cancelled_try_price += $sl->grand_total;
                        $cancelled_usd_price += $sl->grand_total / $sl->usd_rate;
                        $cancelled_eur_price += $sl->grand_total / $sl->eur_rate;
                        $cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $cancelled_usd_price += $sl->grand_total;
                        $cancelled_try_price += $sl->grand_total * $sl->usd_rate;
                        $cancelled_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $cancelled_eur_price += $sl->grand_total;
                        $cancelled_try_price += $sl->grand_total * $sl->eur_rate;
                        $cancelled_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $cancelled_gbp_price += $sl->grand_total;
                        $cancelled_try_price += $sl->grand_total * $sl->gbp_rate;
                        $cancelled_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $cancelled_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $sl->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == 'TRY'){
                            $daily_cancelled_try_price += $expense->price;
                            $daily_cancelled_usd_price += $expense->price / $sl->usd_rate;
                            $daily_cancelled_eur_price += $expense->price / $sl->eur_rate;
                            $daily_cancelled_gbp_price += $expense->price / $sl->gbp_rate;

                            $cancelled_try_price += $expense->price;
                            $cancelled_usd_price += $expense->price / $sl->usd_rate;
                            $cancelled_eur_price += $expense->price / $sl->eur_rate;
                            $cancelled_gbp_price += $expense->price / $sl->gbp_rate;
                        }else if ($expense->currency == 'USD'){
                            $daily_cancelled_usd_price += $expense->price;
                            $daily_cancelled_try_price += $expense->price * $sl->usd_rate;
                            $daily_cancelled_eur_price += $expense->price / $sl->eur_rate * $sl->usd_rate;
                            $daily_cancelled_gbp_price += $expense->price / $sl->gbp_rate * $sl->usd_rate;

                            $cancelled_usd_price += $expense->price;
                            $cancelled_try_price += $expense->price * $sl->usd_rate;
                            $cancelled_eur_price += $expense->price / $sl->eur_rate * $sl->usd_rate;
                            $cancelled_gbp_price += $expense->price / $sl->gbp_rate * $sl->usd_rate;
                        }else if ($expense->currency == 'EUR'){
                            $daily_cancelled_eur_price += $expense->price;
                            $daily_cancelled_try_price += $expense->price * $sl->eur_rate;
                            $daily_cancelled_usd_price += $expense->price / $sl->usd_rate * $sl->eur_rate;
                            $daily_cancelled_gbp_price += $expense->price / $sl->gbp_rate * $sl->eur_rate;

                            $cancelled_eur_price += $expense->price;
                            $cancelled_try_price += $expense->price * $sl->eur_rate;
                            $cancelled_usd_price += $expense->price / $sl->usd_rate * $sl->eur_rate;
                            $cancelled_gbp_price += $expense->price / $sl->gbp_rate * $sl->eur_rate;
                        }else if ($expense->currency == 'GBP'){
                            $daily_cancelled_gbp_price += $expense->price;
                            $daily_cancelled_try_price += $expense->price * $sl->gbp_rate;
                            $daily_cancelled_usd_price += $expense->price / $sl->usd_rate * $sl->gbp_rate;
                            $daily_cancelled_eur_price += $expense->price / $sl->eur_rate * $sl->gbp_rate;

                            $cancelled_gbp_price += $expense->price;
                            $cancelled_try_price += $expense->price * $sl->gbp_rate;
                            $cancelled_usd_price += $expense->price / $sl->usd_rate * $sl->gbp_rate;
                            $cancelled_eur_price += $expense->price / $sl->eur_rate * $sl->gbp_rate;
                        }

                    }

                }

                $cancelled_serie_this_day = array();
                $cancelled_serie_this_day['date'] = $date->toDateString();
                $cancelled_serie_this_day['try'] = number_format($daily_cancelled_try_price, 2,".","");
                $cancelled_serie_this_day['usd'] = number_format($daily_cancelled_usd_price, 2,".","");
                $cancelled_serie_this_day['eur'] = number_format($daily_cancelled_eur_price, 2,".","");
                $cancelled_serie_this_day['gbp'] = number_format($daily_cancelled_gbp_price, 2,".","");

                array_push($cancelled_serie, $cancelled_serie_this_day);
                array_push($cancelled_serie_try, number_format($daily_cancelled_try_price, 2,".",""));
            }
            $cancelled['cancelled_serie'] = $cancelled_serie;
            $cancelled['cancelled_serie_try'] = $cancelled_serie_try;





            $continue['try_sale'] = number_format($continue_try_price, 2,".","");
            $continue['usd_sale'] = number_format($continue_usd_price, 2,".","");
            $continue['eur_sale'] = number_format($continue_eur_price, 2,".","");
            $continue['gbp_sale'] = number_format($continue_gbp_price, 2,".","");

            $approved['try_sale'] = number_format($approved_try_price, 2,".","");
            $approved['usd_sale'] = number_format($approved_usd_price, 2,".","");
            $approved['eur_sale'] = number_format($approved_eur_price, 2,".","");
            $approved['gbp_sale'] = number_format($approved_gbp_price, 2,".","");

            $completed['try_sale'] = number_format($completed_try_price, 2,".","");
            $completed['usd_sale'] = number_format($completed_usd_price, 2,".","");
            $completed['eur_sale'] = number_format($completed_eur_price, 2,".","");
            $completed['gbp_sale'] = number_format($completed_gbp_price, 2,".","");

            $cancelled['try_sale'] = number_format($cancelled_try_price, 2,".","");
            $cancelled['usd_sale'] = number_format($cancelled_usd_price, 2,".","");
            $cancelled['eur_sale'] = number_format($cancelled_eur_price, 2,".","");
            $cancelled['gbp_sale'] = number_format($cancelled_gbp_price, 2,".","");


            $sales['continue'] = $continue;
            $sales['approved'] = $approved;
            $sales['completed'] = $completed;
            $sales['cancelled'] = $cancelled;
            $sales['day_count'] = $day_count;


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getLastMonthSalesByAdmin($admin_id)
    {
        try {

            $sales = array();

            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $continue_try_price = 0;
            $continue_usd_price = 0;
            $continue_eur_price = 0;
            $continue_gbp_price = 0;
            $approved_try_price = 0;
            $approved_usd_price = 0;
            $approved_eur_price = 0;
            $approved_gbp_price = 0;
            $completed_try_price = 0;
            $completed_usd_price = 0;
            $completed_eur_price = 0;
            $completed_gbp_price = 0;
            $cancelled_try_price = 0;
            $cancelled_usd_price = 0;
            $cancelled_eur_price = 0;
            $cancelled_gbp_price = 0;


            $continue = array();
            $approved = array();
            $completed = array();
            $cancelled = array();


            $day_count = 0;


            $firstDayOfMonth = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
            $lastDayOfMonth = Carbon::create($currentYear, $currentMonth, 1)->lastOfMonth()->endOfDay();


            $continue_serie = array();
            $continue_serie_try = array();

            for ($date = $firstDayOfMonth; $date <= $lastDayOfMonth; $date->addDay()) {
                $day_count++;

                $daily_total_continue_sales = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 5)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'continue')
                    ->where('offer_requests.authorized_personnel_id', '=', $admin_id)
                    ->whereDate('sh.created_at', $date->toDateString())
                    ->get();

                $daily_continue_try_price = 0;
                $daily_continue_usd_price = 0;
                $daily_continue_eur_price = 0;
                $daily_continue_gbp_price = 0;

                foreach ($daily_total_continue_sales as $sl){

                    if ($sl->currency == 'TRY'){
                        $daily_continue_try_price += $sl->grand_total;
                        $daily_continue_usd_price += $sl->grand_total / $sl->usd_rate;
                        $daily_continue_eur_price += $sl->grand_total / $sl->eur_rate;
                        $daily_continue_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $daily_continue_usd_price += $sl->grand_total;
                        $daily_continue_try_price += $sl->grand_total * $sl->usd_rate;
                        $daily_continue_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $daily_continue_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $daily_continue_eur_price += $sl->grand_total;
                        $daily_continue_try_price += $sl->grand_total * $sl->eur_rate;
                        $daily_continue_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $daily_continue_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $daily_continue_gbp_price += $sl->grand_total;
                        $daily_continue_try_price += $sl->grand_total * $sl->gbp_rate;
                        $daily_continue_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $daily_continue_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    if ($sl->currency == 'TRY'){
                        $continue_try_price += $sl->grand_total;
                        $continue_usd_price += $sl->grand_total / $sl->usd_rate;
                        $continue_eur_price += $sl->grand_total / $sl->eur_rate;
                        $continue_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $continue_usd_price += $sl->grand_total;
                        $continue_try_price += $sl->grand_total * $sl->usd_rate;
                        $continue_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $continue_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $continue_eur_price += $sl->grand_total;
                        $continue_try_price += $sl->grand_total * $sl->eur_rate;
                        $continue_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $continue_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $continue_gbp_price += $sl->grand_total;
                        $continue_try_price += $sl->grand_total * $sl->gbp_rate;
                        $continue_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $continue_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                }

                $continue_serie_this_day = array();
                $continue_serie_this_day['date'] = $date->toDateString();
                $continue_serie_this_day['try'] = number_format($daily_continue_try_price, 2,".","");
                $continue_serie_this_day['usd'] = number_format($daily_continue_usd_price, 2,".","");
                $continue_serie_this_day['eur'] = number_format($daily_continue_eur_price, 2,".","");
                $continue_serie_this_day['gbp'] = number_format($daily_continue_gbp_price, 2,".","");

                array_push($continue_serie, $continue_serie_this_day);
                array_push($continue_serie_try, number_format($daily_continue_try_price, 2,".",""));
            }
            $continue['continue_serie'] = $continue_serie;
            $continue['continue_serie_try'] = $continue_serie_try;



            $firstDayOfMonth2 = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
            $lastDayOfMonth2 = Carbon::create($currentYear, $currentMonth, 1)->lastOfMonth()->endOfDay();

            $approved_serie = array();
            $approved_serie_try = array();

            for ($date = $firstDayOfMonth2; $date <= $lastDayOfMonth2; $date->addDay()) {

                $daily_total_approved_sales = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'approved')
                    ->where('offer_requests.authorized_personnel_id', '=', $admin_id)
                    ->whereDate('sh.created_at', $date->toDateString())
                    ->get();

                $daily_approved_try_price = 0;
                $daily_approved_usd_price = 0;
                $daily_approved_eur_price = 0;
                $daily_approved_gbp_price = 0;

                foreach ($daily_total_approved_sales as $sl){

                    if ($sl->currency == 'TRY'){
                        $daily_approved_try_price += $sl->grand_total;
                        $daily_approved_usd_price += $sl->grand_total / $sl->usd_rate;
                        $daily_approved_eur_price += $sl->grand_total / $sl->eur_rate;
                        $daily_approved_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $daily_approved_usd_price += $sl->grand_total;
                        $daily_approved_try_price += $sl->grand_total * $sl->usd_rate;
                        $daily_approved_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $daily_approved_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $daily_approved_eur_price += $sl->grand_total;
                        $daily_approved_try_price += $sl->grand_total * $sl->eur_rate;
                        $daily_approved_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $daily_approved_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $daily_approved_gbp_price += $sl->grand_total;
                        $daily_approved_try_price += $sl->grand_total * $sl->gbp_rate;
                        $daily_approved_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $daily_approved_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    if ($sl->currency == 'TRY'){
                        $approved_try_price += $sl->grand_total;
                        $approved_usd_price += $sl->grand_total / $sl->usd_rate;
                        $approved_eur_price += $sl->grand_total / $sl->eur_rate;
                        $approved_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $approved_usd_price += $sl->grand_total;
                        $approved_try_price += $sl->grand_total * $sl->usd_rate;
                        $approved_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $approved_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $approved_eur_price += $sl->grand_total;
                        $approved_try_price += $sl->grand_total * $sl->eur_rate;
                        $approved_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $approved_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $approved_gbp_price += $sl->grand_total;
                        $approved_try_price += $sl->grand_total * $sl->gbp_rate;
                        $approved_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $approved_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                }

                $approved_serie_this_day = array();
                $approved_serie_this_day['date'] = $date->toDateString();
                $approved_serie_this_day['try'] = number_format($daily_approved_try_price, 2,".","");
                $approved_serie_this_day['usd'] = number_format($daily_approved_usd_price, 2,".","");
                $approved_serie_this_day['eur'] = number_format($daily_approved_eur_price, 2,".","");
                $approved_serie_this_day['gbp'] = number_format($daily_approved_gbp_price, 2,".","");

                array_push($approved_serie, $approved_serie_this_day);
                array_push($approved_serie_try, number_format($daily_approved_try_price, 2,".",""));
            }
            $approved['approved_serie'] = $approved_serie;
            $approved['approved_serie_try'] = $approved_serie_try;



            $firstDayOfMonth3 = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
            $lastDayOfMonth3 = Carbon::create($currentYear, $currentMonth, 1)->lastOfMonth()->endOfDay();

            $completed_serie = array();
            $completed_serie_try = array();

            for ($date = $firstDayOfMonth3; $date <= $lastDayOfMonth3; $date->addDay()) {

                $daily_total_completed_sales = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 24)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'completed')
                    ->where('offer_requests.authorized_personnel_id', '=', $admin_id)
                    ->whereDate('sh.created_at', $date->toDateString())
                    ->get();

                $daily_completed_try_price = 0;
                $daily_completed_usd_price = 0;
                $daily_completed_eur_price = 0;
                $daily_completed_gbp_price = 0;

                foreach ($daily_total_completed_sales as $sl){

                    if ($sl->currency == 'TRY'){
                        $daily_completed_try_price += $sl->grand_total;
                        $daily_completed_usd_price += $sl->grand_total / $sl->usd_rate;
                        $daily_completed_eur_price += $sl->grand_total / $sl->eur_rate;
                        $daily_completed_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $daily_completed_usd_price += $sl->grand_total;
                        $daily_completed_try_price += $sl->grand_total * $sl->usd_rate;
                        $daily_completed_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $daily_completed_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $daily_completed_eur_price += $sl->grand_total;
                        $daily_completed_try_price += $sl->grand_total * $sl->eur_rate;
                        $daily_completed_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $daily_completed_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $daily_completed_gbp_price += $sl->grand_total;
                        $daily_completed_try_price += $sl->grand_total * $sl->gbp_rate;
                        $daily_completed_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $daily_completed_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    if ($sl->currency == 'TRY'){
                        $completed_try_price += $sl->grand_total;
                        $completed_usd_price += $sl->grand_total / $sl->usd_rate;
                        $completed_eur_price += $sl->grand_total / $sl->eur_rate;
                        $completed_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $completed_usd_price += $sl->grand_total;
                        $completed_try_price += $sl->grand_total * $sl->usd_rate;
                        $completed_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $completed_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $completed_eur_price += $sl->grand_total;
                        $completed_try_price += $sl->grand_total * $sl->eur_rate;
                        $completed_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $completed_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $completed_gbp_price += $sl->grand_total;
                        $completed_try_price += $sl->grand_total * $sl->gbp_rate;
                        $completed_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $completed_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                }

                $completed_serie_this_day = array();
                $completed_serie_this_day['date'] = $date->toDateString();
                $completed_serie_this_day['try'] = number_format($daily_completed_try_price, 2,".","");
                $completed_serie_this_day['usd'] = number_format($daily_completed_usd_price, 2,".","");
                $completed_serie_this_day['eur'] = number_format($daily_completed_eur_price, 2,".","");
                $completed_serie_this_day['gbp'] = number_format($daily_completed_gbp_price, 2,".","");

                array_push($completed_serie, $completed_serie_this_day);
                array_push($completed_serie_try, number_format($daily_completed_try_price, 2,".",""));
            }
            $completed['completed_serie'] = $completed_serie;
            $completed['completed_serie_try'] = $completed_serie_try;




            $firstDayOfMonth4 = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
            $lastDayOfMonth4 = Carbon::create($currentYear, $currentMonth, 1)->lastOfMonth()->endOfDay();

            $cancelled_serie = array();
            $cancelled_serie_try = array();

            for ($date = $firstDayOfMonth4; $date <= $lastDayOfMonth4; $date->addDay()) {

                $daily_total_cancelled_sales = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND (status_id=23 OR status_id=25 OR status_id=28))'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'cancelled')
                    ->where('offer_requests.authorized_personnel_id', '=', $admin_id)
                    ->whereDate('sh.created_at', $date->toDateString())
                    ->get();

                $daily_cancelled_try_price = 0;
                $daily_cancelled_usd_price = 0;
                $daily_cancelled_eur_price = 0;
                $daily_cancelled_gbp_price = 0;

                foreach ($daily_total_cancelled_sales as $sl){

                    if ($sl->currency == 'TRY'){
                        $daily_cancelled_try_price += $sl->grand_total;
                        $daily_cancelled_usd_price += $sl->grand_total / $sl->usd_rate;
                        $daily_cancelled_eur_price += $sl->grand_total / $sl->eur_rate;
                        $daily_cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $daily_cancelled_usd_price += $sl->grand_total;
                        $daily_cancelled_try_price += $sl->grand_total * $sl->usd_rate;
                        $daily_cancelled_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $daily_cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $daily_cancelled_eur_price += $sl->grand_total;
                        $daily_cancelled_try_price += $sl->grand_total * $sl->eur_rate;
                        $daily_cancelled_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $daily_cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $daily_cancelled_gbp_price += $sl->grand_total;
                        $daily_cancelled_try_price += $sl->grand_total * $sl->gbp_rate;
                        $daily_cancelled_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $daily_cancelled_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                    if ($sl->currency == 'TRY'){
                        $cancelled_try_price += $sl->grand_total;
                        $cancelled_usd_price += $sl->grand_total / $sl->usd_rate;
                        $cancelled_eur_price += $sl->grand_total / $sl->eur_rate;
                        $cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate;
                    }else if ($sl->currency == 'USD'){
                        $cancelled_usd_price += $sl->grand_total;
                        $cancelled_try_price += $sl->grand_total * $sl->usd_rate;
                        $cancelled_eur_price += $sl->grand_total / $sl->eur_rate * $sl->usd_rate;
                        $cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->usd_rate;
                    }else if ($sl->currency == 'EUR'){
                        $cancelled_eur_price += $sl->grand_total;
                        $cancelled_try_price += $sl->grand_total * $sl->eur_rate;
                        $cancelled_usd_price += $sl->grand_total / $sl->usd_rate * $sl->eur_rate;
                        $cancelled_gbp_price += $sl->grand_total / $sl->gbp_rate * $sl->eur_rate;
                    }else if ($sl->currency == 'GBP'){
                        $cancelled_gbp_price += $sl->grand_total;
                        $cancelled_try_price += $sl->grand_total * $sl->gbp_rate;
                        $cancelled_usd_price += $sl->grand_total / $sl->usd_rate * $sl->gbp_rate;
                        $cancelled_eur_price += $sl->grand_total / $sl->eur_rate * $sl->gbp_rate;
                    }

                }

                $cancelled_serie_this_day = array();
                $cancelled_serie_this_day['date'] = $date->toDateString();
                $cancelled_serie_this_day['try'] = number_format($daily_cancelled_try_price, 2,".","");
                $cancelled_serie_this_day['usd'] = number_format($daily_cancelled_usd_price, 2,".","");
                $cancelled_serie_this_day['eur'] = number_format($daily_cancelled_eur_price, 2,".","");
                $cancelled_serie_this_day['gbp'] = number_format($daily_cancelled_gbp_price, 2,".","");

                array_push($cancelled_serie, $cancelled_serie_this_day);
                array_push($cancelled_serie_try, number_format($daily_cancelled_try_price, 2,".",""));
            }
            $cancelled['cancelled_serie'] = $cancelled_serie;
            $cancelled['cancelled_serie_try'] = $cancelled_serie_try;





            $continue['try_sale'] = number_format($continue_try_price, 2,".","");
            $continue['usd_sale'] = number_format($continue_usd_price, 2,".","");
            $continue['eur_sale'] = number_format($continue_eur_price, 2,".","");
            $continue['gbp_sale'] = number_format($continue_gbp_price, 2,".","");

            $approved['try_sale'] = number_format($approved_try_price, 2,".","");
            $approved['usd_sale'] = number_format($approved_usd_price, 2,".","");
            $approved['eur_sale'] = number_format($approved_eur_price, 2,".","");
            $approved['gbp_sale'] = number_format($approved_gbp_price, 2,".","");

            $completed['try_sale'] = number_format($completed_try_price, 2,".","");
            $completed['usd_sale'] = number_format($completed_usd_price, 2,".","");
            $completed['eur_sale'] = number_format($completed_eur_price, 2,".","");
            $completed['gbp_sale'] = number_format($completed_gbp_price, 2,".","");

            $cancelled['try_sale'] = number_format($cancelled_try_price, 2,".","");
            $cancelled['usd_sale'] = number_format($cancelled_usd_price, 2,".","");
            $cancelled['eur_sale'] = number_format($cancelled_eur_price, 2,".","");
            $cancelled['gbp_sale'] = number_format($cancelled_gbp_price, 2,".","");


            $sales['continue'] = $continue;
            $sales['approved'] = $approved;
            $sales['completed'] = $completed;
            $sales['cancelled'] = $cancelled;
            $sales['day_count'] = $day_count;


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }


    public function getMonthlyApprovedSalesThisYearByAdmins($owner_id)
    {
        try {

            $currentYear = Carbon::now()->year;
            $last_months = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('YEAR(sales.created_at) AS year, MONTH(sales.created_at) AS month')
                ->where('sales.active',1)
                ->whereIn('statuses.period', ['completed', 'approved'])
                ->whereRaw('YEAR(sales.created_at) = ?', [$currentYear])
                ->groupByRaw('YEAR(sales.created_at), MONTH(sales.created_at)')
                ->orderByRaw('YEAR(sales.created_at) DESC, MONTH(sales.created_at) DESC')
                ->get();



            $admins = Admin::query()->where('active', 1)->get();

            foreach ($admins as $admin) {

                $sales = array();
                $total_sales = array();
                $try_total = 0;
                $usd_total = 0;
                $eur_total = 0;
                $gbp_total = 0;
                $sale_count = 0;

                foreach ($last_months as $last_month) {
                    $sale_items = Sale::query()
                        ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                        ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 'sales.request_id')
                        ->selectRaw('YEAR(sales.created_at) AS year, MONTH(sales.created_at) AS month, sales.*')
                        ->where('offer_requests.authorized_personnel_id', $admin->id)
                        ->where('sales.active', 1)
                        ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
                        ->whereYear('sales.created_at', $last_month->year)
                        ->whereMonth('sales.created_at', $last_month->month);

                    if ($owner_id != 0){
                        $sale_items = $sale_items
                            ->where('sales.owner_id', $owner_id);
                    }

                    $sale_items = $sale_items
                        ->get();


                    $try_price = 0;
                    $usd_price = 0;
                    $eur_price = 0;
                    $gbp_price = 0;

                    foreach ($sale_items as $item) {
                        $sale_count++;

                        if ($item->currency == 'TRY') {
                            $try_price += $item->grand_total;
                            $usd_price += $item->grand_total / $item->usd_rate;
                            $eur_price += $item->grand_total / $item->eur_rate;
                            $gbp_price += $item->grand_total / $item->gbp_rate;
                        } else if ($item->currency == 'USD') {
                            $usd_price += $item->grand_total;
                            $try_price += $item->grand_total * $item->usd_rate;
                            $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                            $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                        } else if ($item->currency == 'EUR') {
                            $eur_price += $item->grand_total;
                            $try_price += $item->grand_total * $item->eur_rate;
                            $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                            $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                        } else if ($item->currency == 'GBP') {
                            $gbp_price += $item->grand_total;
                            $try_price += $item->grand_total * $item->gbp_rate;
                            $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                            $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                        }
                    }

                    $try_total += $try_price;
                    $usd_total += $usd_price;
                    $eur_total += $eur_price;
                    $gbp_total += $gbp_price;


                    $sale = array();
                    $sale['year'] = $last_month->year;
                    $sale['month'] = $last_month->month;
                    $sale['try_sale'] = number_format($try_price, 2, ".", "");
                    $sale['usd_sale'] = number_format($usd_price, 2, ".", "");
                    $sale['eur_sale'] = number_format($eur_price, 2, ".", "");
                    $sale['gbp_sale'] = number_format($gbp_price, 2, ".", "");
                    array_push($sales, $sale);
                }

                $total_sales['try_total'] = number_format($try_total, 2, ".", "");
                $total_sales['usd_total'] = number_format($usd_total, 2, ".", "");
                $total_sales['eur_total'] = number_format($eur_total, 2, ".", "");
                $total_sales['gbp_total'] = number_format($gbp_total, 2, ".", "");
                $total_sales['sale_count'] = $sale_count;


                $admin['total_sales'] = $total_sales;

            }


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['admins' => $admins]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        }
    }

    public function getMonthlyApprovedSalesLastTwelveMonthsByAdmins($owner_id)
    {
        try {
            $last_months = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('YEAR(sales.created_at) AS year, MONTH(sales.created_at) AS month')
                ->where('sales.active',1)
                ->whereIn('statuses.period', ['completed', 'approved'])
                ->groupByRaw('YEAR(sales.created_at), MONTH(sales.created_at)')
                ->orderByRaw('YEAR(sales.created_at) DESC, MONTH(sales.created_at) DESC')
                ->limit(12)
                ->get();

            $admins = Admin::query()->where('active', 1)->get();

            foreach ($admins as $admin) {

                $sales = array();
                $total_sales = array();
                $try_total = 0;
                $usd_total = 0;
                $eur_total = 0;
                $gbp_total = 0;
                $sale_count = 0;

                foreach ($last_months as $last_month) {
                    $sale_items = Sale::query()
                        ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                        ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 'sales.request_id')
                        ->selectRaw('YEAR(sales.created_at) AS year, MONTH(sales.created_at) AS month, sales.*')
                        ->where('offer_requests.authorized_personnel_id', $admin->id)
                        ->where('sales.active', 1)
                        ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
                        ->whereYear('sales.created_at', $last_month->year)
                        ->whereMonth('sales.created_at', $last_month->month);

                    if ($owner_id != 0){
                        $sale_items = $sale_items
                            ->where('sales.owner_id', $owner_id);
                    }

                    $sale_items = $sale_items
                        ->get();


                    $try_price = 0;
                    $usd_price = 0;
                    $eur_price = 0;
                    $gbp_price = 0;

                    foreach ($sale_items as $item) {
                        $sale_count++;

                        if ($item->currency == 'TRY') {
                            $try_price += $item->grand_total;
                            $usd_price += $item->grand_total / $item->usd_rate;
                            $eur_price += $item->grand_total / $item->eur_rate;
                            $gbp_price += $item->grand_total / $item->gbp_rate;
                        } else if ($item->currency == 'USD') {
                            $usd_price += $item->grand_total;
                            $try_price += $item->grand_total * $item->usd_rate;
                            $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                            $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                        } else if ($item->currency == 'EUR') {
                            $eur_price += $item->grand_total;
                            $try_price += $item->grand_total * $item->eur_rate;
                            $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                            $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                        } else if ($item->currency == 'GBP') {
                            $gbp_price += $item->grand_total;
                            $try_price += $item->grand_total * $item->gbp_rate;
                            $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                            $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                        }
                    }

                    $try_total += $try_price;
                    $usd_total += $usd_price;
                    $eur_total += $eur_price;
                    $gbp_total += $gbp_price;


                    $sale = array();
                    $sale['year'] = $last_month->year;
                    $sale['month'] = $last_month->month;
                    $sale['try_sale'] = number_format($try_price, 2, ".", "");
                    $sale['usd_sale'] = number_format($usd_price, 2, ".", "");
                    $sale['eur_sale'] = number_format($eur_price, 2, ".", "");
                    $sale['gbp_sale'] = number_format($gbp_price, 2, ".", "");
                    array_push($sales, $sale);
                }

                $total_sales['try_total'] = number_format($try_total, 2, ".", "");
                $total_sales['usd_total'] = number_format($usd_total, 2, ".", "");
                $total_sales['eur_total'] = number_format($eur_total, 2, ".", "");
                $total_sales['gbp_total'] = number_format($gbp_total, 2, ".", "");
                $total_sales['sale_count'] = $sale_count;


                $admin['total_sales'] = $total_sales;

            }


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['admins' => $admins]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        }
    }
    public function getMonthlyApprovedSalesLastTwelveMonthsByAdminId($admin_id)
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $admin = Admin::query()->where('id', $admin_id)->first();



                $sales = array();
                $total_sales = array();
                $try_total = 0;
                $usd_total = 0;
                $eur_total = 0;
                $gbp_total = 0;

                foreach ($last_months as $last_month) {
                    $sale_items = Sale::query()
                        ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                        ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 'sales.request_id')
                        ->selectRaw('YEAR(sales.created_at) AS year, MONTH(sales.created_at) AS month, sales.*')
                        ->where('offer_requests.request_id.authorized_personnel_id', $admin->id)
                        ->where('sales.active', 1)
                        ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
                        ->whereYear('sales.created_at', $last_month->year)
                        ->whereMonth('sales.created_at', $last_month->month)
                        ->get();


                    $sale = array();
                    $sale['year'] = $last_month->year;
                    $sale['month'] = $last_month->month;
                    $try_price = 0;
                    $usd_price = 0;
                    $eur_price = 0;
                    $gbp_price = 0;

                    foreach ($sale_items as $item) {

                        if ($item->currency == 'TRY') {
                            $try_price += $item->grand_total;
                            $usd_price += $item->grand_total / $item->usd_rate;
                            $eur_price += $item->grand_total / $item->eur_rate;
                            $gbp_price += $item->grand_total / $item->gbp_rate;
                        } else if ($item->currency == 'USD') {
                            $usd_price += $item->grand_total;
                            $try_price += $item->grand_total * $item->usd_rate;
                            $eur_price += $item->grand_total / $item->eur_rate * $item->usd_rate;
                            $gbp_price += $item->grand_total / $item->gbp_rate * $item->usd_rate;
                        } else if ($item->currency == 'EUR') {
                            $eur_price += $item->grand_total;
                            $try_price += $item->grand_total * $item->eur_rate;
                            $usd_price += $item->grand_total / $item->usd_rate * $item->eur_rate;
                            $gbp_price += $item->grand_total / $item->gbp_rate * $item->eur_rate;
                        } else if ($item->currency == 'GBP') {
                            $gbp_price += $item->grand_total;
                            $try_price += $item->grand_total * $item->gbp_rate;
                            $usd_price += $item->grand_total / $item->usd_rate * $item->gbp_rate;
                            $eur_price += $item->grand_total / $item->eur_rate * $item->gbp_rate;
                        }
                    }

                    $try_total += $try_price;
                    $usd_total += $usd_price;
                    $eur_total += $eur_price;
                    $gbp_total += $gbp_price;


                    $sale = array();
                    $sale['year'] = $last_month->year;
                    $sale['month'] = $last_month->month;
                    $sale['try_sale'] = number_format($try_price, 2, ".", "");
                    $sale['usd_sale'] = number_format($usd_price, 2, ".", "");
                    $sale['eur_sale'] = number_format($eur_price, 2, ".", "");
                    $sale['gbp_sale'] = number_format($gbp_price, 2, ".", "");
                    array_push($sales, $sale);
                }

                $total_sales['try_total'] = number_format($try_total, 2, ".", "");
                $total_sales['usd_total'] = number_format($usd_total, 2, ".", "");
                $total_sales['eur_total'] = number_format($eur_total, 2, ".", "");
                $total_sales['gbp_total'] = number_format($gbp_total, 2, ".", "");


                $admin['total_sales'] = $total_sales;




            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['admin' => $admin]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }


    public function getMostValuableCustomers()
    {
        try {

            $companies = Company::all();

            $company_sales = array();
            foreach ($companies as $company) {


                $sale_items = Sale::query()
                    ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                    ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 'sales.request_id')
                    ->selectRaw('YEAR(sales.created_at) AS year, MONTH(sales.created_at) AS month, sales.*')
                    ->where('offer_requests.company_id', $company->id)
                    ->where('sales.active', 1)
                    ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
                    ->get();


                $data = array();
                $data['id'] = $company->id;
                $sale_total = 0;

                foreach ($sale_items as $item) {
                    $try_price = 0;

                    if ($item->currency == 'TRY') {
                        $try_price += $item->grand_total;
                    } else if ($item->currency == 'USD') {
                        $try_price += $item->grand_total * $item->usd_rate;
                    } else if ($item->currency == 'EUR') {
                        $try_price += $item->grand_total * $item->eur_rate;
                    } else if ($item->currency == 'GBP') {
                        $try_price += $item->grand_total * $item->gbp_rate;
                    }

                    $sale_total += $try_price;
                }

                $data['sale_price'] = number_format($sale_total, 2, ".", "");
                $data['detail'] = Company::query()->where('id', $company->id)->first();
                array_push($company_sales, $data);


            }

            $sortedCompanies = collect($company_sales)->sortByDesc('sale_price')->take(10)->values()->all();



            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['companies' => $sortedCompanies]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        }
    }
    public function getCustomerByNotSaleLongTimes()
    {
        try {

            $companies = Company::query()
                ->leftJoin('sales', function ($join) {
                    $join->on('sales.id', '=', DB::raw("(SELECT MAX(s.id)
                        FROM sales s
                        LEFT JOIN statuses ON statuses.id = s.status_id
                        WHERE statuses.period IN ('completed', 'approved')
                          AND s.customer_id = companies.id
                          AND s.active = 1)")
                    );
                })
                ->where('companies.active', 1)
                ->where('companies.is_customer', 1)
                ->whereNotNull('sales.created_at')
                ->select('companies.*', 'sales.created_at as last_sale_date')
                ->orderBy('last_sale_date')
                ->get();


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['companies' => $companies]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        }
    }
    public function getCustomerByNotSale()
    {
        try {

            $companies = Company::query()->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('sales')
                    ->join('statuses', 'sales.status_id', '=', 'statuses.id')
                    ->whereRaw('companies.id = sales.customer_id
                                    AND companies.active = 1')
                    ->whereNotIn('statuses.period', ['approved', 'completed']);
                })
                ->where('companies.is_customer', 1)
                ->get();


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['companies' => $companies]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        }
    }


    public function getTotalProfitRate($owner_id)
    {
        try {

            //Karlılık
            $sale_items = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.period as period')
                ->where('sales.active',1)
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')");

            if ($owner_id != 0){
                $sale_items = $sale_items
                    ->where('sales.owner_id', $owner_id);
            }

            $sale_items = $sale_items
                ->get();

            $sale_total = 0;
            $offer_total = 0;
            foreach ($sale_items as $sale){
                $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                foreach ($sale_offers as $sale_offer){
                    if ($sale->currency == 'TRY'){
                        $sale_total += $sale_offer->sale_price;
                        $offer_total += $sale_offer->offer_price;
                    }else if ($sale->currency == 'USD'){
                        $sale_total += $sale_offer->sale_price * $sale->usd_rate;
                        $offer_total += $sale_offer->offer_price * $sale->usd_rate;
                    }else if ($sale->currency == 'EUR'){
                        $sale_total += $sale_offer->sale_price * $sale->eur_rate;
                        $offer_total += $sale_offer->offer_price * $sale->eur_rate;
                    }else if ($sale->currency == 'GBP'){
                        $sale_total += $sale_offer->sale_price * $sale->gbp_rate;
                        $offer_total += $sale_offer->offer_price * $sale->gbp_rate;
                    }
                }

                //ek giderler
                $expenses = Expense::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                foreach ($expenses as $expense){
                    if ($expense->currency == $sale->currency){
                        $sale_total += $expense->price;
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
                        $sale_total += $expense_price;
                    }
                }


            }
            if ($offer_total == 0 || $sale_total == 0){
                $profit_rate = 0;
            }else {
                $profit_rate = 100 * ($offer_total - $sale_total) / $sale_total;
            }
            $profit_rate = number_format($profit_rate, 2,",","");


            $currentYear = date('Y');
            $previousYear = $currentYear;
            $currentMonth = date('n');
            $previousMonth = date('n', strtotime('last month'));
            if ($previousMonth == 12){
                $previousYear = $currentYear - 1;
            }



            //Bu ay Karlılık
            $this_month_sale_items = DB::table('sales AS s')
                ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                ->join('status_histories AS sh', function ($join) {
                    $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                })
                ->where('s.active', '=', 1)
                ->where('statuses.period', '=', 'approved')
                ->whereYear('sh.created_at', $currentYear)
                ->whereMonth('sh.created_at', $currentMonth);

            if ($owner_id != 0){
                $this_month_sale_items = $this_month_sale_items
                    ->where('s.owner_id', $owner_id);
            }

            $this_month_sale_items = $this_month_sale_items
                ->get();

            $this_month_sale_total = 0;
            $this_month_offer_total = 0;
            foreach ($this_month_sale_items as $sale){

                $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                foreach ($sale_offers as $sale_offer){
                    if ($sale->currency == 'TRY'){
                        $this_month_sale_total += $sale_offer->sale_price;
                        $this_month_offer_total += $sale_offer->offer_price;
                    }else if ($sale->currency == 'USD'){
                        $this_month_sale_total += $sale_offer->sale_price * $sale->usd_rate;
                        $this_month_offer_total += $sale_offer->offer_price * $sale->usd_rate;
                    }else if ($sale->currency == 'EUR'){
                        $this_month_sale_total += $sale_offer->sale_price * $sale->eur_rate;
                        $this_month_offer_total += $sale_offer->offer_price * $sale->eur_rate;
                    }else if ($sale->currency == 'GBP'){
                        $this_month_sale_total += $sale_offer->sale_price * $sale->gbp_rate;
                        $this_month_offer_total += $sale_offer->offer_price * $sale->gbp_rate;
                    }
                }

                //ek giderler
                $expenses = Expense::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                foreach ($expenses as $expense){
                    if ($expense->currency == $sale->currency){
                        $this_month_sale_total += $expense->price;
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
                        $this_month_sale_total += $expense_price;
                    }
                }


            }

            if ($this_month_offer_total == 0 || $this_month_sale_total == 0) {
                $this_month_profit_rate = 0;
            }else {
                $this_month_profit_rate = 100 * ($this_month_offer_total - $this_month_sale_total) / $this_month_sale_total;
            }



            //Önceki ay Karlılık
            $previous_month_sale_items = DB::table('sales AS s')
                ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                ->join('status_histories AS sh', function ($join) {
                    $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                })
                ->where('s.active', '=', 1)
                ->where('statuses.period', '=', 'approved')
                ->whereYear('sh.created_at', $previousYear)
                ->whereMonth('sh.created_at', $previousMonth);

            if ($owner_id != 0){
                $previous_month_sale_items = $previous_month_sale_items
                    ->where('s.owner_id', $owner_id);
            }

            $previous_month_sale_items = $previous_month_sale_items
                ->get();

            $previous_month_sale_total = 0;
            $previous_month_offer_total = 0;
            foreach ($previous_month_sale_items as $sale){

                $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                foreach ($sale_offers as $sale_offer){
                    if ($sale->currency == 'TRY'){
                        $previous_month_sale_total += $sale_offer->sale_price;
                        $previous_month_offer_total += $sale_offer->offer_price;
                    }else if ($sale->currency == 'USD'){
                        $previous_month_sale_total += $sale_offer->sale_price * $sale->usd_rate;
                        $previous_month_offer_total += $sale_offer->offer_price * $sale->usd_rate;
                    }else if ($sale->currency == 'EUR'){
                        $previous_month_sale_total += $sale_offer->sale_price * $sale->eur_rate;
                        $previous_month_offer_total += $sale_offer->offer_price * $sale->eur_rate;
                    }else if ($sale->currency == 'GBP'){
                        $previous_month_sale_total += $sale_offer->sale_price * $sale->gbp_rate;
                        $previous_month_offer_total += $sale_offer->offer_price * $sale->gbp_rate;
                    }
                }

                //ek giderler
                $expenses = Expense::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                foreach ($expenses as $expense){
                    if ($expense->currency == $sale->currency){
                        $previous_month_sale_total += $expense->price;
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
                        $previous_month_sale_total += $expense_price;
                    }
                }


            }

            if ($previous_month_offer_total == 0 || $previous_month_sale_total == 0) {
                $previous_month_profit_rate = 0;
            }else {
                $previous_month_profit_rate = 100 * ($previous_month_offer_total - $previous_month_sale_total) / $previous_month_sale_total;
            }

            $profit_rate_icon = '';
            if ($this_month_profit_rate == $previous_month_profit_rate){
                $profit_rate_icon = '';
            }else if ($this_month_profit_rate > $previous_month_profit_rate){
                $profit_rate_icon = 'up';
            }else if ($this_month_profit_rate < $previous_month_profit_rate){
                $profit_rate_icon = 'down';
            }


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => [
                'profit_rate' => $profit_rate,
                'profit_rate_icon' => $profit_rate_icon,
                'this_month_profit_rate' => $this_month_profit_rate,
                'previous_month_profit_rate' => $previous_month_profit_rate
            ]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        }
    }
    public function getMonthlyProfitRatesLastTwelveMonths($owner_id)
    {
        try {
            $last_months = Sale::query()
                ->selectRaw('YEAR(created_at) AS year, MONTH(created_at) AS month')
                ->where('sales.active',1)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
                ->limit(12)
                ->get();

            $profit_rates = array();
            foreach ($last_months as $last_month){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'approved')
                    ->whereYear('sh.created_at', $last_month->year)
                    ->whereMonth('sh.created_at', $last_month->month);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();

                $month = array();
                $month['year'] = $last_month->year;
                $month['month'] = $last_month->month;

                $sale_total = 0;
                $offer_total = 0;
                foreach ($sale_items as $sale){

                    $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                    foreach ($sale_offers as $sale_offer){
                        if ($sale->currency == 'TRY'){
                            $sale_total += $sale_offer->sale_price;
                            $offer_total += $sale_offer->offer_price;
                        }else if ($sale->currency == 'USD'){
                            $sale_total += $sale_offer->sale_price * $sale->usd_rate;
                            $offer_total += $sale_offer->offer_price * $sale->usd_rate;
                        }else if ($sale->currency == 'EUR'){
                            $sale_total += $sale_offer->sale_price * $sale->eur_rate;
                            $offer_total += $sale_offer->offer_price * $sale->eur_rate;
                        }else if ($sale->currency == 'GBP'){
                            $sale_total += $sale_offer->sale_price * $sale->gbp_rate;
                            $offer_total += $sale_offer->offer_price * $sale->gbp_rate;
                        }
                    }

                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == $sale->currency){
                            $sale_total += $expense->price;
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
                            $sale_total += $expense_price;
                        }
                    }


                }

                if ($offer_total == 0 || $sale_total == 0) {
                    $profit_rate = 0;
                }else {
                    $profit_rate = 100 * ($offer_total - $sale_total) / $sale_total;
                }

                $month['offer_total'] = $offer_total;
                $month['sale_total'] = $sale_total;
                $month['profit_rate'] = number_format($profit_rate, 2,".","");
                array_push($profit_rates, $month);
            }



            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['profit_rates' => $profit_rates]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getMonthlyProfitRates($owner_id)
    {
        try {
            $currentYearArray = array();
            $previousYearArray = array();

            $currentYear = date('Y');
            $previousYear = $currentYear - 1;

            for ($month = 1; $month <= 12; $month++) {
                $month_array = array();
                $month_array['year'] = $currentYear;
                $month_array['month'] = $month;
                array_push($currentYearArray, $month_array);

                $month_array2 = array();
                $month_array2['year'] = $previousYear;
                $month_array2['month'] = $month;
                array_push($previousYearArray, $month_array2);
            }

            $profit_rates = array();
            $previous_profit_rates = array();

            foreach ($currentYearArray as $currentMonth){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'approved')
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();

                $month = array();
                $month['year'] = $currentMonth['year'];
                $month['month'] = $currentMonth['month'];

                $sale_total = 0;
                $offer_total = 0;
                foreach ($sale_items as $sale){

                    $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                    foreach ($sale_offers as $sale_offer){
                        if ($sale->currency == 'TRY'){
                            $sale_total += $sale_offer->sale_price;
                            $offer_total += $sale_offer->offer_price;
                        }else if ($sale->currency == 'USD'){
                            $sale_total += $sale_offer->sale_price * $sale->usd_rate;
                            $offer_total += $sale_offer->offer_price * $sale->usd_rate;
                        }else if ($sale->currency == 'EUR'){
                            $sale_total += $sale_offer->sale_price * $sale->eur_rate;
                            $offer_total += $sale_offer->offer_price * $sale->eur_rate;
                        }else if ($sale->currency == 'GBP'){
                            $sale_total += $sale_offer->sale_price * $sale->gbp_rate;
                            $offer_total += $sale_offer->offer_price * $sale->gbp_rate;
                        }
                    }

                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == $sale->currency){
                            $sale_total += $expense->price;
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
                            $sale_total += $expense_price;
                        }
                    }


                }

                if ($offer_total == 0 || $sale_total == 0) {
                    $profit_rate = 0;
                }else {
                    $profit_rate = 100 * ($offer_total - $sale_total) / $sale_total;
                }

                $month['offer_total'] = $offer_total;
                $month['sale_total'] = $sale_total;
                $month['profit_rate'] = number_format($profit_rate, 2,".","");
                array_push($profit_rates, $month);
            }

            foreach ($previousYearArray as $currentMonth){

                $sale_items = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                    })
                    ->where('s.active', '=', 1)
                    ->where('statuses.period', '=', 'approved')
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);

                if ($owner_id != 0){
                    $sale_items = $sale_items
                        ->where('s.owner_id', $owner_id);
                }

                $sale_items = $sale_items
                    ->get();

                $month = array();
                $month['year'] = $currentMonth['year'];
                $month['month'] = $currentMonth['month'];

                $sale_total = 0;
                $offer_total = 0;
                foreach ($sale_items as $sale){

                    $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                    foreach ($sale_offers as $sale_offer){
                        if ($sale->currency == 'TRY'){
                            $sale_total += $sale_offer->sale_price;
                            $offer_total += $sale_offer->offer_price;
                        }else if ($sale->currency == 'USD'){
                            $sale_total += $sale_offer->sale_price * $sale->usd_rate;
                            $offer_total += $sale_offer->offer_price * $sale->usd_rate;
                        }else if ($sale->currency == 'EUR'){
                            $sale_total += $sale_offer->sale_price * $sale->eur_rate;
                            $offer_total += $sale_offer->offer_price * $sale->eur_rate;
                        }else if ($sale->currency == 'GBP'){
                            $sale_total += $sale_offer->sale_price * $sale->gbp_rate;
                            $offer_total += $sale_offer->offer_price * $sale->gbp_rate;
                        }
                    }

                    //ek giderler
                    $expenses = Expense::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                    foreach ($expenses as $expense){
                        if ($expense->currency == $sale->currency){
                            $sale_total += $expense->price;
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
                            $sale_total += $expense_price;
                        }
                    }


                }

                if ($offer_total == 0 || $sale_total == 0) {
                    $profit_rate = 0;
                }else {
                    $profit_rate = 100 * ($offer_total - $sale_total) / $sale_total;
                }

                $month['offer_total'] = $offer_total;
                $month['sale_total'] = $sale_total;
                $month['profit_rate'] = number_format($profit_rate, 2,".","");
                array_push($previous_profit_rates, $month);
            }



            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => [
                'profit_rates' => $profit_rates,
                'previous_profit_rates' => $previous_profit_rates
            ]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getMonthlyTurningRates($owner_id)
    {
        try {
            $currentYearArray = array();
            $previousYearArray = array();

            $currentYear = date('Y');
            $previousYear = $currentYear - 1;

            for ($month = 1; $month <= 12; $month++) {
                $month_array = array();
                $month_array['year'] = $currentYear;
                $month_array['month'] = $month;
                array_push($currentYearArray, $month_array);

                $month_array2 = array();
                $month_array2['year'] = $previousYear;
                $month_array2['month'] = $month;
                array_push($previousYearArray, $month_array2);
            }

            $turning_rates = array();
            $previous_turning_rates = array();

            foreach ($currentYearArray as $currentMonth){

                // talep sayısı
                $total_request = Sale::query()
                    ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                    ->where('sales.active',1)
                    ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue' OR statuses.period = 'cancelled')")
                    ->whereYear('sales.created_at', $currentMonth['year'])
                    ->whereMonth('sales.created_at', $currentMonth['month']);
                if ($owner_id != 0){
                    $total_request = $total_request
                        ->where('sales.owner_id', $owner_id);
                }
                $total_request = $total_request
                    ->get()->count();


                // sipariş sayısı
                $total_sale = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                    })
                    ->where('s.active', '=', 1)
                    ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue')")
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);
                if ($owner_id != 0){
                    $total_sale = $total_sale
                        ->where('s.owner_id', $owner_id);
                }
                $total_sale = $total_sale
                    ->get()->count();

                $currentMonth['total_request'] = $total_request;
                $currentMonth['total_sale'] = $total_sale;
                if ($total_sale != 0 && $total_request != 0) {
                    $currentMonth['turning_rate'] = number_format($total_sale * 100 / $total_request, 2, ".", "");
                }else{
                    $currentMonth['turning_rate'] = '0.00';
                }
                array_push($turning_rates, $currentMonth);
            }

            foreach ($previousYearArray as $currentMonth){

                // talep sayısı
                $total_request = Sale::query()
                    ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                    ->where('sales.active',1)
                    ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue' OR statuses.period = 'cancelled')")
                    ->whereYear('sales.created_at', $currentMonth['year'])
                    ->whereMonth('sales.created_at', $currentMonth['month']);
                if ($owner_id != 0){
                    $total_request = $total_request
                        ->where('sales.owner_id', $owner_id);
                }
                $total_request = $total_request
                    ->get()->count();


                // sipariş sayısı
                $total_sale = DB::table('sales AS s')
                    ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                    ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                    ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                    ->join('status_histories AS sh', function ($join) {
                        $join->on('s.sale_id', '=', 'sh.sale_id')
                            ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                    })
                    ->where('s.active', '=', 1)
                    ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue')")
                    ->whereYear('sh.created_at', $currentMonth['year'])
                    ->whereMonth('sh.created_at', $currentMonth['month']);
                if ($owner_id != 0){
                    $total_sale = $total_sale
                        ->where('s.owner_id', $owner_id);
                }
                $total_sale = $total_sale
                    ->get()->count();

                $currentMonth['total_request'] = $total_request;
                $currentMonth['total_sale'] = $total_sale;
                if ($total_sale != 0 && $total_request != 0) {
                    $currentMonth['turning_rate'] = number_format($total_sale * 100 / $total_request, 2, ".", "");
                }else{
                    $currentMonth['turning_rate'] = '0.00';
                }
                array_push($previous_turning_rates, $currentMonth);
            }



            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => [
                'turning_rates' => $turning_rates,
                'previous_turning_rates' => $previous_turning_rates
            ]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001', 'a' => $queryException->getMessage()]);
        }
    }


    public function getBestSalesLastNinetyDays($owner_id)
    {
        try {

            $sale_items = DB::table('sales AS s')
                ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                ->join('status_histories AS sh', function ($join) {
                    $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                })
                ->where('s.active', '=', 1)
                ->whereIn('statuses.period', ['completed', 'approved'])
                ->whereBetween('sh.created_at', [now()->subDays(90), now()]);

            if ($owner_id != 0){
                $sale_items = $sale_items
                    ->where('s.owner_id', $owner_id);
            }

            $sale_items = $sale_items
                ->get();

            $sales = array();
            foreach ($sale_items as $sale){
                $item = array();

                $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                $sale_total = 0;
                $offer_total = 0;

                foreach ($sale_offers as $sale_offer){
                    if ($sale->currency == 'TRY'){
                        $sale_total += $sale_offer->sale_price;
                        $offer_total += $sale_offer->offer_price;
                    }else if ($sale->currency == 'USD'){
                        $sale_total += $sale_offer->sale_price * $sale->usd_rate;
                        $offer_total += $sale_offer->offer_price * $sale->usd_rate;
                    }else if ($sale->currency == 'EUR'){
                        $sale_total += $sale_offer->sale_price * $sale->eur_rate;
                        $offer_total += $sale_offer->offer_price * $sale->eur_rate;
                    }else if ($sale->currency == 'GBP'){
                        $sale_total += $sale_offer->sale_price * $sale->gbp_rate;
                        $offer_total += $sale_offer->offer_price * $sale->gbp_rate;
                    }
                }

                //ek giderler
                $expenses = Expense::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
                foreach ($expenses as $expense){
                    if ($expense->currency == $sale->currency){
                        $sale_total += $expense->price;
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
                        $sale_total += $expense_price;
                    }
                }

                $item['id'] = $sale->id;
                $item['short_code'] = Contact::query()->where('id', $sale->owner_id)->first()->short_code;
                $item['currency'] = $sale->currency;
                $item['sale_total'] = number_format($sale_total, 2, '.', '');
                $item['offer_total'] = number_format($offer_total, 2, '.', '');
                $profit = $offer_total - $sale_total;
                if ($profit <= 0 || $sale_total <= 0){
                    $profit_rate = 0;
                }else {
                    $profit_rate = 100 * $profit / $sale_total;
                }
                $item['profit'] = number_format($profit, 2, '.', '');
                $item['profit_rate'] = number_format($profit_rate, 2, '.', '');

                array_push($sales, $item);

            }


            $by_sale_price = collect($sales)->sortByDesc('offer_total')->take(10)->values()->all();
            $by_profit_rate = collect($sales)->sortByDesc('profit_rate')->take(10)->values()->all();


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['by_sale_price' => $by_sale_price, 'by_profit_rate' => $by_profit_rate]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        }
    }


    public function getDashboardStats($owner_id)
    {
        try {

            // Tekliflerin siparişe dönme oranı = Toplam onaylanan / Toplam Potansiyel
            $total_request = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->where('sales.active',1)
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue' OR statuses.period = 'cancelled')");
            if ($owner_id != 0){
                $total_request = $total_request
                    ->where('sales.owner_id', $owner_id);
            }
            $total_request = $total_request
                ->get()->count();

            $total_sale = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->where('sales.active',1)
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue')");
            if ($owner_id != 0){
                $total_sale = $total_sale
                    ->where('sales.owner_id', $owner_id);
            }
            $total_sale = $total_sale
                ->get()->count();

            if ($total_sale == 0 || $total_request == 0){
                $offer_turning_rate = 0;
            }else {
                $offer_turning_rate = $total_sale * 100 / $total_request;
            }

            //ciro oranı
            $currentYear = date('Y');
            $previousYear = $currentYear;
            $currentMonth = date('n');
            $previousMonth = date('n', strtotime('last month'));
            if ($previousMonth == 12){
                $previousYear = $currentYear - 1;
            }

            //bu ay
            $this_month_sales = DB::table('sales AS s')
                ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                ->join('status_histories AS sh', function ($join) {
                    $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 24)'));
                })
                ->where('s.active', '=', 1)
                ->where('statuses.period', '=', 'completed')
//                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
                ->whereYear('sh.created_at', $currentYear)
                ->whereMonth('sh.created_at', $currentMonth);

            if ($owner_id != 0){
                $this_month_sales = $this_month_sales
                    ->where('s.owner_id', $owner_id);
            }

            $this_month_sales = $this_month_sales
                ->get();

            $this_month_price = 0;

            foreach ($this_month_sales as $item){

                if ($item->currency == 'TRY'){
                    $this_month_price += $item->grand_total;
                }else if ($item->currency == 'USD'){
                    $this_month_price += $item->grand_total * $item->usd_rate;
                }else if ($item->currency == 'EUR'){
                    $this_month_price += $item->grand_total * $item->eur_rate;
                }else if ($item->currency == 'GBP'){
                    $this_month_price += $item->grand_total * $item->gbp_rate;
                }

                //ek giderler
                $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
                foreach ($expenses as $expense){
                    if ($expense->currency == 'TRY'){
                        $this_month_price += $expense->price;
                    }else if ($expense->currency == 'USD'){
                        $this_month_price += $expense->price * $item->usd_rate;
                    }else if ($expense->currency == 'EUR'){
                        $this_month_price += $expense->price * $item->eur_rate;
                    }else if ($expense->currency == 'GBP'){
                        $this_month_price += $expense->price * $item->gbp_rate;
                    }

                }

            }

            //önceki ay
            $previous_month_sales = DB::table('sales AS s')
                ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                ->join('status_histories AS sh', function ($join) {
                    $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 24)'));
                })
                ->where('s.active', '=', 1)
                ->where('statuses.period', '=', 'completed')
//                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
                ->whereYear('sh.created_at', $previousYear)
                ->whereMonth('sh.created_at', $previousMonth);

            if ($owner_id != 0){
                $previous_month_sales = $previous_month_sales
                    ->where('s.owner_id', $owner_id);
            }

            $previous_month_sales = $previous_month_sales
                ->get();

            $previous_month_price = 0;

            foreach ($previous_month_sales as $item){

                if ($item->currency == 'TRY'){
                    $previous_month_price += $item->grand_total;
                }else if ($item->currency == 'USD'){
                    $previous_month_price += $item->grand_total * $item->usd_rate;
                }else if ($item->currency == 'EUR'){
                    $previous_month_price += $item->grand_total * $item->eur_rate;
                }else if ($item->currency == 'GBP'){
                    $previous_month_price += $item->grand_total * $item->gbp_rate;
                }

                //ek giderler
                $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
                foreach ($expenses as $expense){
                    if ($expense->currency == 'TRY'){
                        $previous_month_price += $expense->price;
                    }else if ($expense->currency == 'USD'){
                        $previous_month_price += $expense->price * $item->usd_rate;
                    }else if ($expense->currency == 'EUR'){
                        $previous_month_price += $expense->price * $item->eur_rate;
                    }else if ($expense->currency == 'GBP'){
                        $previous_month_price += $expense->price * $item->gbp_rate;
                    }

                }

            }

            //ciro sonuç
            $turnover_rate_icon = '';
            if ($this_month_price == $previous_month_price){
                $turnover_rate = '0,00';
                $turnover_rate_icon = '-';
            }else if ($previous_month_price == 0){
                $turnover_rate = '100,00';
                $turnover_rate_icon = 'up';
            }else if ($this_month_price < $previous_month_price){
                $turnover_rate = '-'.number_format(100 - ($this_month_price * 100 / $previous_month_price), 2,",","");
                $turnover_rate_icon = 'down';
            }else if ($this_month_price > $previous_month_price){
                $turnover_rate = '+'.number_format(($this_month_price * 100 / $previous_month_price) - 100, 2,",","");
                $turnover_rate_icon = 'up';
            }



            // bu ay talep sayısı
            $total_request = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->where('sales.active',1)
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue' OR statuses.period = 'cancelled')")
                ->whereYear('sales.created_at', $currentYear)
                ->whereMonth('sales.created_at', $currentMonth);
            if ($owner_id != 0){
                $total_request = $total_request
                    ->where('sales.owner_id', $owner_id);
            }
            $total_request = $total_request
                ->get()->count();


            // önceki ay talep sayısı
            $previous_total_request = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->where('sales.active',1)
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue' OR statuses.period = 'cancelled')")
                ->whereYear('sales.created_at', $previousYear)
                ->whereMonth('sales.created_at', $previousMonth);
            if ($owner_id != 0){
                $previous_total_request = $previous_total_request
                    ->where('sales.owner_id', $owner_id);
            }
            $previous_total_request = $previous_total_request
                ->get()->count();

            //talep ikon
            if ($total_request == $previous_total_request){
                $total_request_icon = '-';
            }else if ($total_request > $previous_total_request){
                $total_request_icon = 'up';
            }else{
                $total_request_icon = 'down';
            }

            // bu yıl talep sayısı
            $year_total_request = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->where('sales.active',1)
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue' OR statuses.period = 'cancelled')")
                ->whereYear('sales.created_at', $currentYear);
            if ($owner_id != 0){
                $year_total_request = $year_total_request
                    ->where('sales.owner_id', $owner_id);
            }
            $year_total_request = $year_total_request
                ->get()->count();


            // bu ay sipariş sayısı
            $total_sale = DB::table('sales AS s')
                ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                ->join('status_histories AS sh', function ($join) {
                    $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                })
                ->where('s.active', '=', 1)
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue')")
                ->whereYear('sh.created_at', $currentYear)
                ->whereMonth('sh.created_at', $currentMonth);
            if ($owner_id != 0){
                $total_sale = $total_sale
                    ->where('s.owner_id', $owner_id);
            }
            $total_sale = $total_sale
                ->get()->count();


            // önceki ay sipariş sayısı
            $previous_total_sale = DB::table('sales AS s')
                ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                ->join('status_histories AS sh', function ($join) {
                    $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                })
                ->where('s.active', '=', 1)
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue')")
                ->whereYear('sh.created_at', $previousYear)
                ->whereMonth('sh.created_at', $previousMonth);
            if ($owner_id != 0){
                $previous_total_sale = $previous_total_sale
                    ->where('s.owner_id', $owner_id);
            }
            $previous_total_sale = $previous_total_sale
                ->get()->count();

            //sipariş ikon
            if ($total_sale == $previous_total_sale){
                $total_sale_icon = '-';
            }else if ($total_sale > $previous_total_sale){
                $total_sale_icon = 'up';
            }else{
                $total_sale_icon = 'down';
            }


            // bu yıl sipariş sayısı
            $year_total_sale = DB::table('sales AS s')
                ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
                ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
                ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
                ->join('status_histories AS sh', function ($join) {
                    $join->on('s.sale_id', '=', 'sh.sale_id')
                        ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
                })
                ->where('s.active', '=', 1)
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved' OR statuses.period = 'continue')")
                ->whereYear('sh.created_at', $currentYear);
            if ($owner_id != 0){
                $year_total_sale = $year_total_sale
                    ->where('s.owner_id', $owner_id);
            }
            $year_total_sale = $year_total_sale
                ->get()->count();


            // bu ay aktivite sayısı
            $total_activity = Activity::query()
                ->where('active',1)
                ->whereYear('start', $currentYear)
                ->whereMonth('start', $currentMonth)
                ->get()->count();

            // önceki ay aktivite sayısı
            $previous_total_activity = Activity::query()
                ->where('active',1)
                ->whereYear('start', $previousYear)
                ->whereMonth('start', $previousMonth)
                ->get()->count();

            //aktivite ikon
            if ($total_activity == $previous_total_activity){
                $total_activity_icon = '-';
            }else if ($total_activity > $previous_total_activity){
                $total_activity_icon = 'up';
            }else{
                $total_activity_icon = 'down';
            }


            // bu yıl aktivite sayısı
            $year_total_activity = Activity::query()
                ->where('active',1)
                ->whereYear('start', $currentYear)
                ->get()->count();







            if ($previous_total_sale == 0 || $previous_total_request == 0){
                $previous_offer_turning_rate = 0;
            }else {
                $previous_offer_turning_rate = $previous_total_sale * 100 / $previous_total_request;
            }

            //teklif onaylanma ikon
            if ($offer_turning_rate == $previous_offer_turning_rate){
                $offer_turning_rate_icon = '-';
            }else if ($offer_turning_rate > $previous_offer_turning_rate){
                $offer_turning_rate_icon = 'up';
            }else{
                $offer_turning_rate_icon = 'down';
            }

            $offer_turning_rate = number_format($offer_turning_rate, 2,",","");
            $previous_offer_turning_rate = number_format($previous_offer_turning_rate, 2,",","");



            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => [
                'offer_turning_rate' => $offer_turning_rate,
                'previous_offer_turning_rate' => $previous_offer_turning_rate,
                'offer_turning_rate_icon' => $offer_turning_rate_icon,
                'turnover_rate' => $turnover_rate,
                'turnover_rate_icon' => $turnover_rate_icon,
                'total_request' => $total_request,
                'previous_total_request' => $previous_total_request,
                'year_total_request' => $year_total_request,
                'total_request_icon' => $total_request_icon,
                'total_sale' => $total_sale,
                'previous_total_sale' => $previous_total_sale,
                'year_total_sale' => $year_total_sale,
                'total_sale_icon' => $total_sale_icon,
                'total_activity' => $total_activity,
                'previous_total_activity' => $previous_total_activity,
                'year_total_activity' => $year_total_activity,
                'total_activity_icon' => $total_activity_icon
            ]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        }
    }


}
