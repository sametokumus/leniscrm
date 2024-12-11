<?php

namespace App\Helpers;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Expense;
use App\Models\OfferProduct;
use App\Models\OfferRequest;
use App\Models\PaymentTerm;
use App\Models\Quote;
use App\Models\Sale;
use App\Models\SaleOffer;
use App\Models\StaffPoint;
use App\Models\CurrencyLog;
use Illuminate\Support\Facades\DB;

class StaffHelper
{
    //calculate point and rate
    public static function get_point_rate($point, $rate)
    {
        if ($point != 0 && $rate != 0) {
            $point_rate = $point * $rate / 100;
        }else{
            $point_rate = 0;
        }
        return $point_rate;

    }
    //c1
    public static function get_request_and_sales_rate($request_count, $sale_count)
    {
        if ($sale_count != 0 && $request_count != 0) {
            $c2 = ($sale_count * 100 / $request_count) / 10;
        }else{
            $c2 = 0;
        }
        return (int)$c2;

    }
    //c2
    public static function get_sales_total_rate($usd_price)
    {
        if ($usd_price == 0){
            return 0;
        }else if ($usd_price > 0 && $usd_price < 10000){
            return 1;
        }else if ($usd_price >= 10000 && $usd_price < 20000){
            return 2;
        }else if ($usd_price >= 20000 && $usd_price < 30000){
            return 3;
        }else if ($usd_price >= 30000 && $usd_price < 40000){
            return 4;
        }else if ($usd_price >= 40000 && $usd_price < 50000){
            return 5;
        }else if ($usd_price >= 50000 && $usd_price < 60000){
            return 6;
        }else if ($usd_price >= 60000 && $usd_price < 70000){
            return 7;
        }else if ($usd_price >= 70000 && $usd_price < 80000){
            return 8;
        }else if ($usd_price >= 80000 && $usd_price < 90000){
            return 9;
//        }else if ($usd_price >= 90000){
        }else{
            return 10;
        }
    }
    //c3
    public static function get_sales_profit_rate($all_sales_price, $all_sales_expense)
    {
        if ($all_sales_expense == 0 && $all_sales_price > 0){
            return 10;
        }
        if ($all_sales_price == 0){
            return 0;
        }
        $rate = 100 * ($all_sales_price - $all_sales_expense) / $all_sales_expense;
        if ($rate < 10){
            return 0;
        }else if ($rate >= 10 && $rate < 20){
            return 1;
        }else if ($rate >= 20 && $rate < 30){
            return 2;
        }else if ($rate >= 30 && $rate < 40){
            return 3;
        }else if ($rate >= 40 && $rate < 50){
            return 4;
        }else if ($rate >= 50 && $rate < 60){
            return 5;
        }else if ($rate >= 60 && $rate < 70){
            return 6;
        }else if ($rate >= 70 && $rate < 80){
            return 7;
        }else if ($rate >= 80 && $rate < 90){
            return 8;
        }else if ($rate >= 90 && $rate < 100){
            return 9;
//        }else if ($rate >= 100){
        }else{
            return 10;
        }
    }
    //c4 single payment
    public static function get_sale_payment_point($advance, $expiry)
    {
        if ($advance == null && $expiry == null){
            return 0;
        }else {
            if ($expiry == 1){
                return 10;
            }else if ($advance >= 50){
                return 9;
            }else if ($advance >= 40){
                return 8;
            }else if ($advance >= 30){
                return 7;
            }else if ($advance >= 20){
                return 6;
            }else if ($expiry > 1 && $expiry <= 7){
                return 5;
            }else if ($expiry > 7 && $expiry <= 15){
                return 4;
            }else if ($expiry > 15 && $expiry <= 30){
                return 3;
            }else if ($expiry > 30 && $expiry <= 60){
                return 2;
            }else if ($expiry > 60 && $expiry <= 90){
                return 1;
            }else{
                return 0;
            }
        }
    }
    //c4 all payment average
    public static function get_sales_payment_point($total_payment_point, $total_payment_count)
    {
        if ($total_payment_point != 0 && $total_payment_count != 0) {
            $c4 = $total_payment_point / $total_payment_count;
        }else{
            $c4 = 0;
        }
        return (int)$c4;

    }
    //c5
    public static function get_manager_point($staff_id)
    {
        $staff_point = StaffPoint::query()->where('staff_id', $staff_id)->where('active', 1)->orderByDesc('id')->first();
        if ($staff_point) {
            $c5 = $staff_point->point;
        }else{
            // default değer 5 olarak tanımlıyoruz
            $c5 = 5;
        }
        return $c5;

    }
    //c6 single customer
    public static function get_sale_customer_point($count)
    {
        if ($count == 1){
            return 0;
        }else if ($count == 2){
            return 10;
        }else if ($count == 3){
            return 9;
        }else if ($count == 4){
            return 8;
        }else if ($count == 5){
            return 7;
        }else if ($count == 6){
            return 6;
        }else if ($count == 7){
            return 5;
        }else if ($count == 8){
            return 4;
        }else if ($count == 9){
            return 3;
        }else if ($count == 10){
            return 2;
        }else if ($count == 11){
            return 1;
        }else{
            return 0;
        }
    }
    //c6 all customer average
    public static function get_sales_customer_point($customer_total_point, $customer_total_count)
    {
        if ($customer_total_point != 0 && $customer_total_count != 0) {
            $c6 = $customer_total_point / $customer_total_count;
        }else{
            $c6 = 0;
        }
        return (int)$c6;

    }
    //c7 activity point
    public static function get_activity_point($count)
    {
        if ($count < 10) {
            return $count;
        }else{
            return 10;
        }
    }
    //c8 export point
    public static function get_export_sale_point($count)
    {
        if ($count < 10) {
            return $count;
        }else{
            return 10;
        }
    }
    //c9 first sale
    public static function get_first_sale_point($count)
    {
        if ($count < 10) {
            return $count;
        }else{
            return 10;
        }
    }
    //c10 customer point
    public static function get_customer_point($count)
    {
        if ($count == 0){
            return 0;
        }else if ($count <= 3){
            return 1;
        }else if ($count <= 5){
            return 2;
        }else if ($count <= 7){
            return 3;
        }else if ($count <= 9){
            return 4;
        }else if ($count <= 11){
            return 5;
        }else if ($count <= 13){
            return 6;
        }else if ($count <= 15){
            return 7;
        }else if ($count <= 17){
            return 8;
        }else if ($count <= 19){
            return 9;
        }else{
            return 10;
        }
    }


    //calculated staff data
    public static function get_staff_data($staff, $month, $year)
    {
        $data = array();
        $data['staff'] = $staff;


        $startOfMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();

        //Sipariş/Teklif Oranı
        $request_count = OfferRequest::query()->where('authorized_personnel_id', $staff->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

//        $sale_count = Sale::query()
//            ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 'sales.request_id')
//            ->where('offer_requests.authorized_personnel_id', $staff->id)
//            ->whereBetween('sales.created_at', [now()->startOfMonth(), now()->endOfMonth()])
//            ->count();
        $sale_count = DB::table('sales AS s')
            ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
            ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
            ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
            ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
            ->join('status_histories AS sh', function ($join) {
                $join->on('s.sale_id', '=', 'sh.sale_id')
                    ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
            })
            ->where('offer_requests.authorized_personnel_id', $staff->id)
            ->where('s.active', '=', 1)
            ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
            ->whereBetween('sh.created_at', [$startOfMonth, $endOfMonth])
            ->count();




        //Toplam İş Hacmi ve karlılık
        $sale_items = DB::table('sales AS s')
            ->select('s.*', 'sh.status_id AS last_status', 'sh.created_at AS last_status_created_at')
            ->addSelect(DB::raw('YEAR(sh.created_at) AS year, MONTH(sh.created_at) AS month'))
            ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
            ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
            ->join('status_histories AS sh', function ($join) {
                $join->on('s.sale_id', '=', 'sh.sale_id')
                    ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
            })
            ->where('offer_requests.authorized_personnel_id', $staff->id)
            ->where('s.active', '=', 1)
            ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
            ->whereBetween('sh.created_at', [$startOfMonth, $endOfMonth])
            ->get();

        $sale = array();
        $usd_price = 0;
        $total_profit_rate = 0;
        $total_item_count = 0;
        $total_payment_point = 0;
        $total_payment_count = 0;
        $all_sales_price = 0;
        $all_sales_expense = 0;

        foreach ($sale_items as $item){

            //satış
            $total_price = $item->grand_total;
            if ($item->grand_total_with_shipping != null){
                $total_price = $item->grand_total_with_shipping;
            }

            if ($item->currency == 'TRY'){
                $usd_price += $total_price / $item->usd_rate;
            }else if ($item->currency == 'USD'){
                $usd_price += $total_price;
            }else if ($item->currency == 'EUR'){
                $usd_price += $total_price / $item->usd_rate * $item->eur_rate;
            }else if ($item->currency == 'GBP'){
                $usd_price += $total_price / $item->usd_rate * $item->gbp_rate;
            }

            //satın alma
            $sale_offers = SaleOffer::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
            $total_offer_price = 0;
            //tedarik gideri
            foreach ($sale_offers as $sale_offer){
                $offer_product = OfferProduct::query()->where('id', $sale_offer->offer_product_id)->where('active', 1)->first();
                if ($offer_product) {
                    $total_offer_price += $offer_product->converted_price;
                }
            }
            if ($total_offer_price != 0) {
                $total_expense = $total_offer_price;
            }else{
                $total_expense = 0;
            }
            //ek giderler
            $expenses = Expense::query()->where('sale_id', $item->sale_id)->where('active', 1)->get();
            foreach ($expenses as $expense){
                if ($expense->currency == $item->currency){
                    $total_expense += $expense->price;
                    $expense['converted_price'] = $expense->price;
                }else{
                    if ($expense->currency == 'TRY') {
                        $sc = strtolower($item->currency);
                        $expense_price = $expense->price / $item->{$sc.'_rate'};
                    }else{
                        if ($item->currency == 'TRY') {
                            $ec = strtolower($expense->currency);
                            $expense_price = $expense->price * $item->{$ec.'_rate'};
                        }else{
                            $ec = strtolower($expense->currency);
                            $sc = strtolower($item->currency);
                            if ($item->{$sc.'_rate'} != 0) {
                                $expense_price = $expense->price * $item->{$ec . '_rate'} / $item->{$sc . '_rate'};
                            }else{
                                $expense_price = 0;
                            }
                        }
                    }
                    $total_expense += $expense_price;
                }
            }

            //kar oranı
            if ($total_expense != 0) {
                $profit_rate = 100 * ($total_price - $total_expense) / $total_expense;
            }else{
                $profit_rate = 0;
            }
//            $item['profit_rate'] = $profit_rate;
//            $total_profit_rate += $profit_rate;
            $total_item_count++;
            $all_sales_price += $total_price;
            $all_sales_expense += $total_expense;


            //ödeme yöntemi
            $quote = Quote::query()->where('sale_id', $item->sale_id)->where('active', 1)->first();
            if ($quote){
                $pt = PaymentTerm::query()->where('id', $quote->payment_term)->first();
                if ($pt){
                    $total_payment_point += StaffHelper::get_sale_payment_point($pt->advance, $pt->expiry);
                    $total_payment_count++;
                }
            }

        }




        //c6
        $c6_sales = DB::table('sales AS s')
            ->selectRaw('companies.*, COUNT(*) as sale_count')
            ->leftJoin('companies', 'companies.id', '=', 's.customer_id')
            ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
            ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
            ->join('status_histories AS sh', function ($join) {
                $join->on('s.sale_id', '=', 'sh.sale_id')
                    ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
            })
            ->where('offer_requests.authorized_personnel_id', $staff->id)
            ->where('s.active', '=', 1)
            ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
            ->whereBetween('sh.created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('s.customer_id')
            ->get();


        $customer_total_count = 0;
        $customer_total_point = 0;
        foreach ($c6_sales as $item){
            $customer_total_point += StaffHelper::get_sale_customer_point($item->sale_count);
            $customer_total_count++;
        }




        //c7
        $activity_count = Activity::query()
            ->leftJoin('activity_types', 'activity_types.id', '=', 'activities.type_id')
            ->where('activity_types.face_to_face', 1)
            ->where('activities.user_id', $staff->id)
            ->where('activities.active', 1)
            ->whereBetween('activities.start', [$startOfMonth, $endOfMonth])
            ->count();




        //c8
        $export_sale_count = Sale::query()
            ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 'sales.request_id')
            ->where('sales.type_id', 2)
            ->where('offer_requests.authorized_personnel_id', $staff->id)
            ->whereBetween('sales.created_at', [$startOfMonth, $endOfMonth])
            ->count();



        //c9
        $c9_sales = DB::table('sales AS s')
            ->select('s.*')
            ->leftJoin('companies', 'companies.id', '=', 's.customer_id')
            ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 's.request_id')
            ->leftJoin('statuses', 'statuses.id', '=', 's.status_id')
            ->join('status_histories AS sh', function ($join) {
                $join->on('s.sale_id', '=', 'sh.sale_id')
                    ->where('sh.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM status_histories WHERE sale_id = s.sale_id AND status_id = 7)'));
            })
            ->where('offer_requests.authorized_personnel_id', $staff->id)
            ->where('s.active', 1)
            ->where(function ($query) {
                $query->where('statuses.period', 'completed')
                    ->orWhere('statuses.period', 'approved');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('sales AS s2')
                    ->whereRaw('s2.customer_id = s.customer_id AND s2.created_at < s.created_at');
            })
            ->get();

        $first_sale_count = 0;
        foreach ($c9_sales as $item) {
            if (now()->format('Y-m') == \Carbon\Carbon::parse($item->created_at)->format('Y-m')) {
                $first_sale_count++;
            }
        }



        //c10
        $customer_count = Company::query()
            ->where('active', 1)
            ->where('user_id', $staff->id)
            ->where('is_customer', 1)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();



        $data['request_count'] = $request_count;
        $data['sale_count'] = $sale_count;
        $c1 = StaffHelper::get_request_and_sales_rate($request_count, $sale_count);
        $data['c1'] = $c1;

        $data['usd_price'] = $usd_price;
        $c2 = StaffHelper::get_sales_total_rate($usd_price);
        $data['c2'] = $c2;

        $data['total_profit_rate'] = $total_profit_rate;
        $data['total_item_count'] = $total_item_count;
        $c3 = StaffHelper::get_sales_profit_rate($all_sales_price, $all_sales_expense);
        $data['c3'] = $c3;
        $data['c3_sales'] = $sale_items;
        $data['all_sales_price'] = $all_sales_price;
        $data['all_sales_expense'] = $all_sales_expense;

        $data['total_payment_point'] = $total_payment_point;
        $data['total_payment_count'] = $total_payment_count;
        $c4 = StaffHelper::get_sales_payment_point($total_payment_point, $total_payment_count);
        $data['c4'] = $c4;

        $c5 = StaffHelper::get_manager_point($staff->id);
        $data['c5'] = $c5;

        $data['customer_total_point'] = $customer_total_point;
        $data['customer_total_count'] = $customer_total_count;
        $c6 = StaffHelper::get_sales_customer_point($customer_total_point, $customer_total_count);
        $data['c6'] = $c6;

        $c7 = StaffHelper::get_activity_point($activity_count);
        $data['c7'] = $c7;

        $c8 = StaffHelper::get_export_sale_point($export_sale_count);
        $data['c8'] = $c8;

        $c9 = StaffHelper::get_first_sale_point($first_sale_count);
        $data['c9'] = $c9;

        $c10 = StaffHelper::get_customer_point($customer_count);
        $data['c10'] = $c10;

        $c1_rate = StaffHelper::get_point_rate($c1, 17);
        $c2_rate = StaffHelper::get_point_rate($c2, 15);
        $c3_rate = StaffHelper::get_point_rate($c3, 14);
        $c4_rate = StaffHelper::get_point_rate($c4, 13);
        $c5_rate = StaffHelper::get_point_rate($c5, 11);
        $c6_rate = StaffHelper::get_point_rate($c6, 9);
        $c7_rate = StaffHelper::get_point_rate($c7, 7);
        $c8_rate = StaffHelper::get_point_rate($c8, 6);
        $c9_rate = StaffHelper::get_point_rate($c9, 5);
        $c10_rate = StaffHelper::get_point_rate($c10, 4);

        $data['c1_rate'] = $c1_rate;
        $data['c2_rate'] = $c2_rate;
        $data['c3_rate'] = $c3_rate;
        $data['c4_rate'] = $c4_rate;
        $data['c5_rate'] = $c5_rate;
        $data['c6_rate'] = $c6_rate;
        $data['c7_rate'] = $c7_rate;
        $data['c8_rate'] = $c8_rate;
        $data['c9_rate'] = $c9_rate;
        $data['c10_rate'] = $c10_rate;


        $staff_rate = $c1_rate + $c2_rate + $c3_rate + $c4_rate + $c5_rate + $c6_rate + $c7_rate + $c8_rate + $c9_rate + $c10_rate;
        $data['staff_rate'] = number_format($staff_rate, 2, '.', '');

        return $data;
    }




}
