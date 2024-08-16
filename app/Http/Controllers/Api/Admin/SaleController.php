<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\StatusChange;
use App\Helpers\CurrencyHelper;
use App\Helpers\StatusHistoryHelper;
use App\Helpers\StatusNotifyHelper;
use App\Http\Controllers\Controller;
use App\Mail\StatusChangeMail;
use App\Models\Address;
use App\Models\Admin;
use App\Models\AdminStatusRole;
use App\Models\CancelNote;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CurrencyLog;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Measurement;
use App\Models\MobileDocument;
use App\Models\Offer;
use App\Models\OfferProduct;
use App\Models\OfferRequest;
use App\Models\OfferRequestProduct;
use App\Models\OrderConfirmationDetail;
use App\Models\PackingList;
use App\Models\PackingListProduct;
use App\Models\PackingStatus;
use App\Models\PaymentTerm;
use App\Models\Product;
use App\Models\ProformaInvoiceDetails;
use App\Models\PurchasingOrderDetails;
use App\Models\Quote;
use App\Models\RfqDetails;
use App\Models\Sale;
use App\Models\SaleNote;
use App\Models\SaleOffer;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionPayment;
use App\Models\SaleType;
use App\Models\Status;
use App\Models\StatusHistory;
use App\Models\StatusNotify;
use App\Models\StatusNotifySetting;
use App\Models\User;
use App\Models\UserContactRule;
use App\Models\UserDocumentCheck;
use App\Models\UserProfile;
use DateTime;
use Faker\Provider\Uuid;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Nette\Schema\ValidationException;
use Carbon\Carbon;

class SaleController extends Controller
{

    public function deleteSale($sale_id)
    {
        try {

            SaleOffer::query()->where('sale_id', $sale_id)->update([
                'active' => 0
            ]);
            Sale::query()->where('sale_id', $sale_id)->update([
                'active' => 0
            ]);

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getSalesByCompanyId($company_id)
    {
        try {

            $sales = Sale::query()
                ->leftJoin('contacts', 'contacts.id', '=', 'sales.owner_id')
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name, contacts.short_code as owner_short_code')
                ->where('sales.active',1)
                ->where('sales.customer_id',$company_id)
                ->whereRaw("(statuses.period = 'continue' OR statuses.period = 'approved' OR statuses.period = 'creating' OR statuses.period = 'completed')")
                ->get();

            foreach ($sales as $sale) {

                $sale['sale_notes'] = SaleNote::query()->where('sale_id', $sale->sale_id)->get();

                $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
                $offer_request['product_count'] = OfferRequestProduct::query()->where('request_id', $offer_request->request_id)->where('active', 1)->count();
                $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->where('active', 1)->first();
                $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
                $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
                $sale['request'] = $offer_request;
                $sale['status'] = Status::query()->where('id', $sale->status_id)->first();


                $current_time = Carbon::now();
                if ($sale->updated_at != null) {
                    $updated_at = $sale->updated_at;
                } else {
                    $updated_at = Carbon::parse($sale->created_at);
                }

                $difference = $updated_at->diffForHumans($current_time);
                $sale['diff_last_day'] = $difference;
                $sale->created_at = $sale->created_at->addHours(3);

            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getSales()
    {
        try {
            $sales = Sale::query()
                ->leftJoin('contacts', 'contacts.id', '=', 'sales.owner_id')
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name, contacts.short_code as owner_short_code')
                ->where('sales.active',1)
                ->get();

            foreach ($sales as $sale) {
                $sale['sale_notes'] = SaleNote::query()->where('sale_id', $sale->sale_id)->get();

                $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
                $offer_request['product_count'] = OfferRequestProduct::query()->where('request_id', $offer_request->request_id)->where('active', 1)->count();
                $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->first();
                $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
                $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
                $sale['request'] = $offer_request;
                $sale['status'] = Status::query()->where('id', $sale->status_id)->first();
//                $sale_offer = SaleOffer::query()->where('sale_id', $sale->sale_id)->first();
//                $sale['currency'] = '';
//                if ($sale_offer){
//                    if ($sale_offer->offer_currency != '' && $sale_offer->offer_currency != null){
//                        $sale['currency'] = $sale_offer->offer_currency;
//                    }
//                }

                $current_time = Carbon::now();
                if ($sale->updated_at != null){
                    $updated_at = $sale->updated_at;
//                    $updated_at = Carbon::parse($sale->updated_at);
//                    $updated_at = $updated_at->subHours(3);
                }else{
                    $updated_at = $sale->created_at;
//                    $updated_at = Carbon::parse($sale->created_at);
//                    $updated_at = $updated_at->subHours(3);
                }

                $difference = $updated_at->diffForHumans($current_time);
                $sale['diff_last_day'] = $difference;
                $sale->created_at = $sale->created_at->addHours(3);


            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getFilteredSales(Request $request, $user_id)
    {
        try {
            $admin = Admin::query()->where('id', $user_id)->first();

            $sales = Sale::query()
                ->leftJoin('contacts', 'contacts.id', '=', 'sales.owner_id')
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->leftJoin('offer_requests', 'offer_requests.request_id', '=', 'sales.request_id')
                ->where('sales.active',1);

            if ($request->owner != 0){
                $sales = $sales->where('sales.owner_id', $request->owner);
            }

            if ($request->status != 0){
                $sales = $sales->where('sales.status_id', $request->status);
            }

            if ($request->authorized_personnel != 0){
                $sales = $sales->where('offer_requests.authorized_personnel_id', $request->authorized_personnel);
            }

            if ($request->purchasing_staff != 0){
                $sales = $sales->where('offer_requests.purchasing_staff_id', $request->purchasing_staff);
            }

            if ($request->company != 0){
                $sales = $sales->where('offer_requests.company_id', $request->company);
            }

            if ($request->company_employee != '' && $request->company_employee != 0){
                $sales = $sales->where('offer_requests.company_employee_id', $request->company_employee);
            }

            $sales = $sales
                ->selectRaw('sales.*, statuses.name as status_name, contacts.short_code as owner_short_code')
                ->get();

            foreach ($sales as $sale) {

                $status_role = AdminStatusRole::query()->where('admin_role_id', $admin->admin_role_id)->where('status_id', $sale->status_id)->where('active', 1)->count();
                if ($status_role > 0){
                    $sale['authorization'] = 1;
                }else{
                    $sale['authorization'] = 0;
                }

                $sale['sale_notes'] = SaleNote::query()->where('sale_id', $sale->sale_id)->get();

                $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
                $offer_request['product_count'] = OfferRequestProduct::query()->where('request_id', $offer_request->request_id)->where('active', 1)->count();
                $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->where('active', 1)->first();
                $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
                $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
                $sale['request'] = $offer_request;
                $sale['status'] = Status::query()->where('id', $sale->status_id)->first();
//                $sale_offer = SaleOffer::query()->where('sale_id', $sale->sale_id)->first();
//                $sale['currency'] = '';
//                if ($sale_offer){
//                    if ($sale_offer->offer_currency != '' && $sale_offer->offer_currency != null){
//                        $sale['currency'] = $sale_offer->offer_currency;
//                    }
//                }

                $current_time = Carbon::now();
                if ($sale->updated_at != null){
                    $updated_at = $sale->updated_at;
//                    $updated_at = Carbon::parse($sale->updated_at);
//                    $updated_at = $updated_at->subHours(3);
//                    $sale->updated_at = $sale->updated_at->addHours(3);
                }else{
                    $updated_at = Carbon::parse($sale->created_at);
//                    $updated_at = $updated_at->subHours(3);
                }

                $difference = $updated_at->diffForHumans($current_time);
                $sale['diff_last_day'] = $difference;
                $sale->created_at = $sale->created_at->addHours(3);

            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getApprovedSales($user_id)
    {
        try {
            $admin = Admin::query()->where('id', $user_id)->first();

            $sales = Sale::query()
                ->leftJoin('contacts', 'contacts.id', '=', 'sales.owner_id')
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name, contacts.short_code as owner_short_code')
                ->where('sales.active',1)
//                ->where('statuses.period','approved')
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
                ->get();

            foreach ($sales as $sale) {

                $status_role = AdminStatusRole::query()->where('admin_role_id', $admin->admin_role_id)->where('status_id', $sale->status_id)->where('active', 1)->count();
                if ($status_role > 0){
                    $sale['authorization'] = 1;
                }else{
                    $sale['authorization'] = 0;
                }

                $sale['sale_notes'] = SaleNote::query()->where('sale_id', $sale->sale_id)->get();

                $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
                $offer_request['product_count'] = OfferRequestProduct::query()->where('request_id', $offer_request->request_id)->where('active', 1)->count();
                $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->where('active', 1)->first();
                $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
                $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
                $sale['request'] = $offer_request;
                $sale['status'] = Status::query()->where('id', $sale->status_id)->first();
//                $sale_offer = SaleOffer::query()->where('sale_id', $sale->sale_id)->first();
//                $sale['currency'] = '';
//                if ($sale_offer){
//                    if ($sale_offer->offer_currency != '' && $sale_offer->offer_currency != null){
//                        $sale['currency'] = $sale_offer->offer_currency;
//                    }
//                }

                $current_time = Carbon::now();
                if ($sale->updated_at != null){
                    $updated_at = $sale->updated_at;
//                    $updated_at = Carbon::parse($sale->updated_at);
//                    $updated_at = $updated_at->subHours(3);
                }else{
                    $updated_at = $sale->created_at;
                    $updated_at = Carbon::parse($sale->created_at);
                    $updated_at = $updated_at->subHours(3);
                }

                $difference = $updated_at->diffForHumans($current_time);
                $sale['diff_last_day'] = $difference;

            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getCancelledSales($user_id)
    {
        try {
            $admin = Admin::query()->where('id', $user_id)->first();

            $sales = Sale::query()
                ->leftJoin('contacts', 'contacts.id', '=', 'sales.owner_id')
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name, contacts.short_code as owner_short_code')
                ->where('sales.active',1)
                ->where('statuses.id',23)
                ->get();

            foreach ($sales as $sale) {

                $status_role = AdminStatusRole::query()->where('admin_role_id', $admin->admin_role_id)->where('status_id', $sale->status_id)->where('active', 1)->count();
                if ($status_role > 0){
                    $sale['authorization'] = 1;
                }else{
                    $sale['authorization'] = 0;
                }

                $sale['sale_notes'] = SaleNote::query()->where('sale_id', $sale->sale_id)->get();


                $cancel = CancelNote::query()->where('sale_id', $sale->sale_id)->first();
                $sale['cancel_note'] = '';
                if ($cancel){
                    $sale['cancel_note'] = $cancel->note;
                }

                $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
                $offer_request['product_count'] = OfferRequestProduct::query()->where('request_id', $offer_request->request_id)->where('active', 1)->count();
                $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->where('active', 1)->first();
                $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
                $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
                $sale['request'] = $offer_request;
                $sale['status'] = Status::query()->where('id', $sale->status_id)->first();
//                $sale_offer = SaleOffer::query()->where('sale_id', $sale->sale_id)->first();
//                $sale['currency'] = '';
//                if ($sale_offer){
//                    if ($sale_offer->offer_currency != '' && $sale_offer->offer_currency != null){
//                        $sale['currency'] = $sale_offer->offer_currency;
//                    }
//                }

                $current_time = Carbon::now();
                if ($sale->updated_at != null){
                    $updated_at = $sale->updated_at;
//                    $updated_at = Carbon::parse($sale->updated_at);
//                    $updated_at = $updated_at->subHours(3);
                }else{
                    $updated_at = $sale->created_at;
                    $updated_at = Carbon::parse($sale->created_at);
                    $updated_at = $updated_at->subHours(3);
                }

                $difference = $updated_at->diffForHumans($current_time);
                $sale['diff_last_day'] = $difference;
            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getActiveSales($user_id)
    {
        try {
            $admin = Admin::query()->where('id', $user_id)->first();

            $sales = Sale::query()
                ->leftJoin('contacts', 'contacts.id', '=', 'sales.owner_id')
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name, contacts.short_code as owner_short_code')
                ->where('sales.active',1)
//                ->where('statuses.period','continue')
                ->whereRaw("(statuses.period = 'continue' OR statuses.period = 'approved' OR statuses.period = 'creating')")
                ->get();

            foreach ($sales as $sale) {

                $status_role = AdminStatusRole::query()->where('admin_role_id', $admin->admin_role_id)->where('status_id', $sale->status_id)->where('active', 1)->count();
                if ($status_role > 0){
                    $sale['authorization'] = 1;
                }else{
                    $sale['authorization'] = 0;
                }

                $sale['sale_notes'] = SaleNote::query()->where('sale_id', $sale->sale_id)->get();

                $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
                $offer_request['product_count'] = OfferRequestProduct::query()->where('request_id', $offer_request->request_id)->where('active', 1)->count();
                $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->where('active', 1)->first();
                $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
                $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
                $sale['request'] = $offer_request;
                $sale['status'] = Status::query()->where('id', $sale->status_id)->first();
//                $sale_offer = SaleOffer::query()->where('sale_id', $sale->sale_id)->first();
//                $sale['currency'] = '';
//                if ($sale_offer){
//                    if ($sale_offer->offer_currency != '' && $sale_offer->offer_currency != null){
//                        $sale['currency'] = $sale_offer->offer_currency;
//                    }
//                }

                $current_time = Carbon::now();
                if ($sale->updated_at != null){
                    $updated_at = $sale->updated_at;
//                    $updated_at = Carbon::parse($sale->updated_at);
//                    $updated_at = $updated_at->subHours(3);
//                    $sale->updated_at = $sale->updated_at->addHours(3);
                }else{
                    $updated_at = Carbon::parse($sale->created_at);
//                    $updated_at = $updated_at->subHours(3);
                }

                $difference = $updated_at->diffForHumans($current_time);
                $sale['diff_last_day'] = $difference;
                $sale->created_at = $sale->created_at->addHours(3);

            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getSaleById($sale_id)
    {
        try {
            $sale = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name')
                ->where('sales.active',1)
                ->where('sales.sale_id',$sale_id)
                ->first();

            $sale['sale_notes'] = SaleNote::query()->where('sale_id', $sale_id)->get();

            $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
            $offer_request['product_count'] = OfferRequestProduct::query()->where('request_id', $offer_request->request_id)->where('active', 1)->count();
            $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->where('active', 1)->first();
            $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
            $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
            $sale['request'] = $offer_request;

            $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
            foreach ($sale_offers as $sale_offer){
                $sale_offer['supplier_name'] = Company::query()->where('id', $sale_offer->supplier_id)->first()->name;
                $sale_offer['product_name'] = Product::query()->where('id', $sale_offer->product_id)->first()->product_name;
                $sale_offer['product_ref_code'] = Product::query()->where('id', $sale_offer->product_id)->first()->ref_code;
                $offer_pcs_price = $sale_offer->offer_price / $sale_offer->offer_quantity;
                $sale_offer['offer_pcs_price'] = number_format($offer_pcs_price, 2,".","");
                $offer_price = $sale_offer->offer_price;
                $sale_offer->offer_price = number_format($sale_offer->offer_price, 2,",",".");
                $sale_offer->pcs_price = number_format($sale_offer->pcs_price, 2,",",".");
                $sale_offer->total_price = number_format($sale_offer->total_price, 2,",",".");
                $sale_offer->discounted_price = number_format($sale_offer->discounted_price, 2,",",".");
                $sale_offer['measurement_name_tr'] = Measurement::query()->where('id', $sale_offer->measurement_id)->first()->name_tr;
                $sale_offer['measurement_name_en'] = Measurement::query()->where('id', $sale_offer->measurement_id)->first()->name_en;

                $offer_product = OfferProduct::query()->where('id', $sale_offer->offer_product_id)->first();
                $request_product = OfferRequestProduct::query()->where('id', $offer_product->request_product_id)->first();
                $sale_offer['sequence'] = $request_product->sequence;

                if ($offer_price != 0 && $sale_offer->sale_price != 0) {
                    $profit_rate = 100 * (floatval($offer_price) - floatval($sale_offer->sale_price)) / floatval($sale_offer->sale_price);
                }else{
                    $profit_rate = 0;
                }
                $sale_offer['profit_rate'] = '%'.number_format($profit_rate, 2,",","");

            }
            $sale['sale_offers'] = $sale_offers;

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sale' => $sale]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getSaleConfirmationById($sale_id)
    {
        try {
            $sale = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name')
                ->where('sales.active',1)
                ->where('sales.sale_id',$sale_id)
                ->first();

            $sale['owner'] = Contact::query()->where('id', $sale->owner_id)->first();
            $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
            $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
            $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
            $sale['request'] = $offer_request;

            $supply_total = 0;
            $offer_total = 0;

            $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
            foreach ($sale_offers as $sale_offer){

                $supply_total += $sale_offer->sale_price;
                $offer_total += $sale_offer->offer_price;

                $sale_offer['supplier_name'] = Company::query()->where('id', $sale_offer->supplier_id)->first()->name;
                $sale_offer['product_name'] = Product::query()->where('id', $sale_offer->product_id)->first()->product_name;
                $sale_offer['product_ref_code'] = Product::query()->where('id', $sale_offer->product_id)->first()->ref_code;
                $offer_pcs_price = $sale_offer->offer_price / $sale_offer->offer_quantity;
                $sale_offer['offer_pcs_price'] = number_format($offer_pcs_price, 2,".","");
                $offer_price = $sale_offer->offer_price;
                $sale_offer->offer_price = number_format($sale_offer->offer_price, 2,",",".");
                $sale_offer->pcs_price = number_format($sale_offer->pcs_price, 2,",",".");
                $sale_offer->total_price = number_format($sale_offer->total_price, 2,",",".");
                $sale_offer->discounted_price = number_format($sale_offer->discounted_price, 2,",",".");
                $sale_offer['measurement_name_tr'] = Measurement::query()->where('id', $sale_offer->measurement_id)->first()->name_tr;
                $sale_offer['measurement_name_en'] = Measurement::query()->where('id', $sale_offer->measurement_id)->first()->name_en;

                $offer_product = OfferProduct::query()->where('id', $sale_offer->offer_product_id)->first();
                $request_product = OfferRequestProduct::query()->where('id', $offer_product->request_product_id)->first();
                $sale_offer['sequence'] = $request_product->sequence;

                if ($offer_price != 0 && $sale_offer->sale_price != 0) {
                    $profit_rate = 100 * (floatval($offer_price) - floatval($sale_offer->sale_price)) / floatval($sale_offer->sale_price);
                }else{
                    $profit_rate = 0;
                }
                $sale_offer['profit'] = number_format((floatval($offer_price) - floatval($sale_offer->sale_price)), 2,",",".");
                $sale_offer['profit_rate'] = '%'.number_format($profit_rate, 2,",","");

                $request_product_id = OfferProduct::query()->where('id', $sale_offer->offer_product_id)->first()->request_product_id;
                $offer_products = OfferProduct::query()->where('request_product_id', $request_product_id)->where('active', 1)->get();
                foreach ($offer_products as $offer_product){
                    $offer_product['is_sended_offer'] = 0;
                    if ($offer_product->id == $sale_offer->offer_product_id){
                        $offer_product['is_sended_offer'] = 1;
                    }
                    $offer = Offer::query()->where('offer_id', $offer_product->offer_id)->first();
                    $offer_product['supplier'] = Company::query()->where('id', $offer->supplier_id)->first();
                }
                $sale_offer['supplier_offers'] = $offer_products;

            }
            $sale['sale_offers'] = $sale_offers;

            $sale['supply_price'] = number_format($supply_total, 2,",",".");
            $sale['offer_price'] = number_format($offer_total, 2,",",".");
            $sale['profit'] = number_format($offer_total - $supply_total, 2,",",".");
            $profit_rate = 100 * ($offer_total - $supply_total) / $supply_total;
            $sale['profit_rate'] = number_format($profit_rate, 2,",","");

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sale' => $sale]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getPackingListSaleById($packing_list_id)
    {
        try {
            $packing_list = PackingList::query()->where('packing_list_id', $packing_list_id)->first();
            $sale_id = $packing_list->sale_id;

            $sale = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name')
                ->where('sales.active',1)
                ->where('sales.sale_id',$sale_id)
                ->first();

            $sale['sale_notes'] = SaleNote::query()->where('sale_id', $sale_id)->get();

            $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
            $offer_request['product_count'] = OfferRequestProduct::query()->where('request_id', $offer_request->request_id)->where('active', 1)->count();
            $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->where('active', 1)->first();
            $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
            $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
            $sale['request'] = $offer_request;

            $sale_offers = SaleOffer::query()
                ->leftJoin('packing_list_products', 'packing_list_products.sale_offer_id', '=', 'sale_offers.id')
                ->leftJoin('sales', 'sales.sale_id', '=', 'sale_offers.sale_id')
                ->selectRaw('sale_offers.*, packing_list_products.quantity as list_quantity')
                ->where('sale_offers.sale_id', $sale->sale_id)
                ->where('sale_offers.active', 1)
//                ->whereRaw("(sales.sale_id NOT IN (SELECT sale_id FROM sale_transactions))")
                ->where('packing_list_products.packing_list_id', $packing_list_id)
                ->get();
            $list_grand_total = 0;
            foreach ($sale_offers as $sale_offer){
                $sale_offer['supplier_name'] = Company::query()->where('id', $sale_offer->supplier_id)->first()->name;
                $sale_offer['product_name'] = Product::query()->where('id', $sale_offer->product_id)->first()->product_name;
                $sale_offer['product_ref_code'] = Product::query()->where('id', $sale_offer->product_id)->first()->ref_code;
                $offer_pcs_price = $sale_offer->offer_price / $sale_offer->offer_quantity;
                $sale_offer['offer_pcs_price'] = number_format($offer_pcs_price, 2,".","");
                $list_offer_price = $offer_pcs_price * $sale_offer->list_quantity;
                $list_grand_total += $list_offer_price;
                $sale_offer->offer_price = number_format($list_offer_price, 2,",",".");
                $sale_offer->pcs_price = number_format($sale_offer->pcs_price, 2,",",".");
                $sale_offer->total_price = number_format($sale_offer->total_price, 2,",",".");
                $sale_offer->discounted_price = number_format($sale_offer->discounted_price, 2,",",".");
                $sale_offer['measurement_name_tr'] = Measurement::query()->where('id', $sale_offer->measurement_id)->first()->name_tr;
                $sale_offer['measurement_name_en'] = Measurement::query()->where('id', $sale_offer->measurement_id)->first()->name_en;

                $offer_product = OfferProduct::query()->where('id', $sale_offer->offer_product_id)->first();
                $request_product = OfferRequestProduct::query()->where('id', $offer_product->request_product_id)->first();
                $sale_offer['sequence'] = $request_product->sequence;

            }
            $sale['sale_offers'] = $sale_offers;
            $sale['list_total'] = number_format($list_grand_total, 2,",",".");
            $transaction = SaleTransaction::query()->where('packing_list_id', $packing_list_id)->where('active', 1)->first();
            $transaction_payment = SaleTransactionPayment::query()->where('transaction_id', $transaction->transaction_id)->where('active', 1)->first();
            $sale['list_tax'] = number_format($transaction_payment->payment_tax, 2,",",".");
            $sale['list_grand_total'] = number_format($transaction_payment->payment_total, 2,",",".");

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sale' => $sale]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getApproveOfferBySaleId($sale_id, $user_id, $revize)
    {
        try {
            if ($revize == 0) {
                Sale::query()->where('sale_id', $sale_id)->update([
                    'status_id' => 26
                ]);

                StatusHistoryHelper::addStatusHistory($sale_id, 26, $user_id);
            }else if ($revize == 1){
                Sale::query()->where('sale_id', $sale_id)->update([
                    'status_id' => 32
                ]);

                StatusHistoryHelper::addStatusHistory($sale_id, 32, $user_id);
            }else{
                return response(['message' => __('Hatalı işlem.'), 'status' => 'query-001']);
            }



            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getRejectOfferBySaleId($sale_id, $user_id, $revize)
    {
        try {
            if ($revize == 0) {
                Sale::query()->where('sale_id', $sale_id)->update([
                    'status_id' => 27
                ]);

                StatusHistoryHelper::addStatusHistory($sale_id, 27, $user_id);
            }else if ($revize == 1){
                Sale::query()->where('sale_id', $sale_id)->update([
                    'status_id' => 33
                ]);

                StatusHistoryHelper::addStatusHistory($sale_id, 33, $user_id);
            }else{
                return response(['message' => __('Hatalı işlem.'), 'status' => 'query-001']);
            }



            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function addSale(Request $request)
    {
        try {
            $request->validate([
                'request_id' => 'required',
            ]);
            $sale_id = Sale::query()->where('request_id', $request->request_id)->first()->sale_id;
            Sale::query()->where('sale_id', $sale_id)->update([
                'status_id' => 4,
                'sub_total' => null,
                'freight' => null,
                'vat' => null,
                'grand_total' => null,
                'shipping_price' => null,
                'grand_total_with_shipping' => null
            ]);

            StatusHistoryHelper::addStatusHistory($sale_id, 4, $request->user_id);

            SaleOffer::query()->where('sale_id', $sale_id)->update([
                'active' => 0
            ]);

            foreach ($request->offers as $offer){
                $measurement = Measurement::query()->where('name_tr', $offer['measurement'])->where('active', 1)->first();
                SaleOffer::query()->insert([
                    'sale_id' => $sale_id,
                    'offer_id' => $offer['offer_id'],
                    'offer_product_id' => $offer['offer_product_id'],
                    'product_id' => $offer['product_id'],
                    'supplier_id' => $offer['supplier_id'],
                    'date_code' => "",
                    'package_type' => "",
                    'request_quantity' => $offer['request_quantity'],
                    'offer_quantity' => $offer['offer_quantity'],
                    'measurement_id' => $measurement->id,
                    'pcs_price' => $offer['pcs_price'],
                    'total_price' => $offer['total_price'],
                    'discount_rate' => $offer['discount_rate'],
                    'discounted_price' => $offer['discounted_price'],
                    'vat_rate' => $offer['vat_rate'],
                    'currency' => $offer['currency'],
                    'lead_time' => $offer['lead_time'],
                    'sale_price' => $offer['converted_price'],
                    'sale_currency' => $offer['converted_currency'],
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

    public function updateSaleStatus(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
                'status_id' => 'required',
                'user_id' => 'required',
            ]);
            $old_status_id = Sale::query()->where('sale_id', $request->sale_id)->first()->status_id;
            $old_status = Status::query()->where('id', $old_status_id)->first();
            $new_status = Status::query()->where('id', $request->status_id)->first();
            $last_forced = Status::query()
                ->where('sequence', '<', $new_status->sequence)
                ->where('forced', 1)
                ->where('active', 1)
                ->orderByDesc('sequence')
                ->first();


            if ($old_status_id == 21 && $old_status->sequence < $new_status->sequence){
                $shipment_price_check = Expense::query()->where('sale_id', $request->sale_id)->where('category_id', 1)->where('active', 1)->count();
                if ($shipment_price_check == 0){

                    $message = 'Nakliye tutarı zorunludur.';
                    return response(['message' => $message, 'status' => 'status-002']);

                }
            }



            if ($last_forced && $new_status->period != 'cancelled'){

                $history_check = StatusHistory::query()
                    ->where('status_id', $last_forced->id)
                    ->where('sale_id', $request->sale_id)
                    ->where('active', 1)
                    ->orderByDesc('id')
                    ->first();

                if ($history_check){

                    Sale::query()->where('sale_id', $request->sale_id)->update([
                        'status_id' => $request->status_id,
                    ]);

                    StatusHistoryHelper::addStatusHistory($request->sale_id, $request->status_id, $request->user_id);

                    $status = Status::query()->where('id', $request->status_id)->first();

                }else{
                    $message = '"'.$last_forced->name.'" sipariş durumu zorunludur.';
                    return response(['message' => $message, 'status' => 'status-001', 'a' => $history_check, 'b' => $last_forced, 'c' => $new_status]);

                }

            }else{

                Sale::query()->where('sale_id', $request->sale_id)->update([
                    'status_id' => $request->status_id,
                ]);

                StatusHistoryHelper::addStatusHistory($request->sale_id, $request->status_id, $request->user_id);

                $status = Status::query()->where('id', $request->status_id)->first();

            }


            return response(['message' => __('Durum güncelleme işlemi başarılı.'), 'status' => 'success', 'object' => ['period' => $status->period]]);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage(), 'b'=>$throwable->getLine()]);
        }
    }

    public function updatePackingListStatus(Request $request)
    {
        try {
            $request->validate([
                'packing_list_id' => 'required',
                'status_id' => 'required'
            ]);
            PackingList::query()->where('packing_list_id', $request->packing_list_id)->update([
                'packing_status_id' => $request->status_id,
            ]);



            return response(['message' => __('Durum güncelleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function addPackingDeliveryAddress(Request $request)
    {
        try {
            $request->validate([
                'packing_list_id' => 'required',
                'address_id' => 'required'
            ]);
            PackingList::query()->where('packing_list_id', $request->packing_list_id)->update([
                'address_id' => $request->address_id
            ]);



            return response(['message' => __('Durum güncelleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function updatePackingDeliveryAddress(Request $request)
    {
        try {
            $request->validate([
                'packing_list_id' => 'required',
                'address_id' => 'required'
            ]);
            PackingList::query()->where('packing_list_id', $request->packing_list_id)->update([
                'address_id' => $request->address_id
            ]);



            return response(['message' => __('Durum güncelleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function addCancelSaleNote(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
                'user_id' => 'required',
                'note' => 'required',
            ]);

            CancelNote::query()->insert([
                'sale_id' => $request->sale_id,
                'user_id' => $request->user_id,
                'note' => $request->note,
            ]);

            return response(['message' => __('Not ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function getSaleOfferById($offer_product_id)
    {
        try {
            $sale_offer = SaleOffer::query()->where('offer_product_id',$offer_product_id)->first();
            $sale_offer['company'] = Company::query()->where('id', $sale_offer->supplier_id)->where('active', 1)->first();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sale_offer' => $sale_offer]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getSaleOffersByOfferId($offer_id)
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


            $products = SaleOffer::query()
                ->leftJoin('offer_products', 'offer_products.id', '=', 'sale_offers.offer_product_id')
                ->selectRaw('offer_products.*')
                ->where('offer_products.offer_id', $offer->offer_id)
                ->where('offer_products.active', 1)
                ->where('sale_offers.active', 1)
                ->get();

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

                $product['sequence'] = $offer_request_product->sequence;
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

    public function addSaleOfferPrice(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'user_id' => 'required',
            ]);
            $sale_offer_detail = SaleOffer::query()->where('id', $request->id)->first();
            $sale_id = $sale_offer_detail->sale_id;
            $offer_id = $sale_offer_detail->offer_id;
            $offer_product_id = $sale_offer_detail->offer_product_id;

            $is_first_price_check = false;
            $so = SaleOffer::query()->where('id', $request->id)->first();
            if ($so->offer_price == null){
                $is_first_price_check = true;
            }

            SaleOffer::query()->where('id', $request->id)->update([
                'offer_price' => $request->offer_price,
                'offer_currency' => $request->offer_currency,
                'offer_lead_time' => $request->offer_lead_time
            ]);

            $offer_check = SaleOffer::query()
                ->where('sale_id', $sale_id)
                ->where('active', 1)
                ->where('offer_price', null)
                ->count();
            if ($offer_check == 0) {

                $sale_offers = SaleOffer::query()->where('sale_id', $sale_id)->where('active', 1)->get();
                $sub_total = 0;
                $vat = 0;
                $check_all_is_notify = true;
                foreach ($sale_offers as $sale_offer){
                    $sub_total += $sale_offer->offer_price;
                    $vat += $sale_offer->offer_price / 100 * $sale_offer->vat_rate;

                    if ($sale_offer->offer_price == null){
                        $check_all_is_notify = false;
                    }
                }

                $grand_total = $sub_total + $vat;
                Sale::query()->where('sale_id', $sale_id)->update([
                    'sub_total' => $sub_total,
                    'vat' => $vat,
                    'grand_total' => $grand_total
                ]);

                if ($check_all_is_notify){

                    Sale::query()->where('sale_id', $sale_id)->update([
                        'status_id' => 5
                    ]);

                    if ($is_first_price_check) {
                        StatusHistoryHelper::addStatusHistory($sale_id, 5, $request->user_id);
                    }

                }
            }

            return response(['message' => __('Satış fiyatı ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function updateSaleOfferPrice(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
                'user_id' => 'required',
                'offer_id' => 'required',
                'offer_product_id' => 'required',
                'price' => 'required',
                'currency' => 'required',
            ]);
            SaleOffer::query()->where('sale_id', $request->sale_id)->where('offer_id', $request->offer_id)->where('offer_product_id', $request->offer_product_id)->update([
                'offer_price' => $request->price,
                'offer_currency' => $request->currency
            ]);

            return response(['message' => __('Satış fiyatı güncelleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function getRfqDetailById($offer_id)
    {
        try {
            $rfq_detail = RfqDetails::query()->where('offer_id', $offer_id)->first();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['rfq_detail' => $rfq_detail]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function updateRfqDetail(Request $request)
    {
        try {
            $request->validate([
                'offer_id' => 'required',
            ]);
            $count = RfqDetails::query()->where('offer_id', $request->offer_id)->count();
            if ($count == 0){
                RfqDetails::query()->insert([
                    'offer_id' => $request->offer_id,
                    'note' => $request->note,
                ]);
            }else {
                RfqDetails::query()->where('offer_id', $request->offer_id)->update([
                    'note' => $request->note,
                ]);
            }

            return response(['message' => __('Detay güncelleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function getQuoteBySaleId($sale_id)
    {
        try {

            $quote_count = Quote::query()->where('sale_id', $sale_id)->count();
            if ($quote_count == 0){
                $quote_id = Uuid::uuid();
                $now = Carbon::now();
                $nowPlusSevenDays = $now->addDays(7);
                $nowPlusSevenDaysFormatted = $nowPlusSevenDays->format('Y-m-d');

                Quote::query()->insert([
                    'quote_id' => $quote_id,
                    'sale_id' => $sale_id,
                    'expiry_date' => $nowPlusSevenDaysFormatted
                ]);
            }
            $quote = Quote::query()->where('sale_id', $sale_id)->first();
            $sale = Sale::query()->where('sale_id', $sale_id)->first();
            $quote['freight'] = $sale->freight;

            $quote['quatotion_pdf'] = '';
            $quatotion_pdf = Document::query()->where('sale_id', $sale_id)->where('document_type_id', 1)->where('active', 1)->first();
            if ($quatotion_pdf){
                $quote['quatotion_pdf'] = $quatotion_pdf->file_url;
            }

            $quote['oc_pdf'] = '';
            $oc_pdf = Document::query()->where('sale_id', $sale_id)->where('document_type_id', 2)->where('active', 1)->first();
            if ($oc_pdf){
                $quote['oc_pdf'] = $oc_pdf->file_url;
            }

            $quote['pi_pdf'] = '';
            $pi_pdf = Document::query()->where('sale_id', $sale_id)->where('document_type_id', 4)->where('active', 1)->first();
            if ($pi_pdf){
                $quote['pi_pdf'] = $pi_pdf->file_url;
            }

            $quote['inv_pdf'] = '';
            $inv_pdf = Document::query()->where('sale_id', $sale_id)->where('document_type_id', 3)->where('active', 1)->first();
            if ($inv_pdf){
                $quote['inv_pdf'] = $inv_pdf->file_url;
            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['quote' => $quote]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function updateQuote(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
                'quote_id' => 'required',
            ]);
            Quote::query()->where('id', $request->quote_id)->update([
                'payment_term' => $request->payment_term,
                'advance_price' => $request->advance_price,
                'lead_time' => $request->lead_time,
                'delivery_term' => $request->delivery_term,
                'country_of_destination' => $request->country_of_destination,
                'expiry_date' => $request->expiry_date,
                'note' => $request->note
            ]);
            $sale = Sale::query()->where('sale_id', $request->sale_id)->first();
            $grand_total = null;
            if ($request->freight == ""){
                $freight = null;
                $grand_total = $sale->sub_total + $sale->vat;
            }else{
                $freight = $request->freight;
                $grand_total = $sale->sub_total + $sale->vat + $freight;
            }

            Sale::query()->where('sale_id', $request->sale_id)->update([
                'freight' => $freight,
                'grand_total' => $grand_total
            ]);

            return response(['message' => __('Bilgi güncelleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function updateQuoteTerms()
    {
        try {
            $quotes = Quote::query()->where('active', 1)->get();
            foreach ($quotes as $quote){
                $term = PaymentTerm::query()->where('name', $quote->payment_term)->first();
                if ($term){
                    Quote::query()->where('id', $quote->id)->update([
                        'payment_term' => $term->id
                    ]);
                }
            }

            $sales = Sale::query()
                ->leftJoin('quotes', 'quotes.sale_id', '=', 'sales.sale_id')
                ->leftJoin('payment_terms', 'payment_terms.id', '=', 'quotes.payment_term')
                ->where('sales.active', 1)
                ->whereRaw('sales.active = 1 AND (quotes.payment_term = 2 OR quotes.payment_term = 4 OR quotes.payment_term = 14 OR quotes.payment_term = 15 OR quotes.payment_term = 17 OR quotes.payment_term = 18 OR quotes.payment_term = 19)')
                ->selectRaw('sales.*, quotes.quote_id as quote_id, payment_terms.advance as advance')
                ->get();

            foreach ($sales as $sale){
                $total_price = $sale->grand_total;
                if ($sale->grand_total_with_shipping != null){
                    $total_price = $sale->grand_total_with_shipping;
                }
                $advance = $total_price / 100 * $sale->advance;

                Quote::query()->where('quote_id', $sale->quote_id)->update([
                    'advance_price' => $advance
                ]);
            }

            return response(['message' => __('Bilgi güncelleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function getPurchasingOrderDetailById($offer_id)
    {
        try {
            $purchasing_order_detail = PurchasingOrderDetails::query()->where('offer_id', $offer_id)->where('active', 1)->first();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['purchasing_order_detail' => $purchasing_order_detail]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function addPurchasingOrderDetail(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
                'offer_id' => 'required',
                'note' => 'required',
            ]);

            $has_detail = PurchasingOrderDetails::query()->where('sale_id', $request->sale_id)->where('offer_id', $request->offer_id)->where('active', 1)->first();
            if ($has_detail) {
                PurchasingOrderDetails::query()->where('sale_id', $request->sale_id)->where('offer_id', $request->offer_id)->update([
                    'note' => $request->note
                ]);
            }else{
                PurchasingOrderDetails::query()->insert([
                    'sale_id' => $request->sale_id,
                    'offer_id' => $request->offer_id,
                    'note' => $request->note
                ]);
            }

            return response(['message' => __('Not ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function updatePurchasingOrderDetail(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
                'offer_id' => 'required',
                'note' => 'required',
            ]);
            PurchasingOrderDetails::query()->where('sale_id', $request->sale_id)->where('offer_id', $request->offer_id)->update([
                'note' => $request->note
            ]);

            return response(['message' => __('Not ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function getOrderConfirmationDetailById($sale_id)
    {
        try {
            $order_confirmation_detail = OrderConfirmationDetail::query()->where('sale_id', $sale_id)->first();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['order_confirmation_detail' => $order_confirmation_detail]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function addOrderConfirmationDetail(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
                'note' => 'required',
            ]);
            $has_detail = OrderConfirmationDetail::query()->where('sale_id', $request->sale_id)->where('active', 1)->first();
            if ($has_detail) {
                OrderConfirmationDetail::query()->where('sale_id', $request->sale_id)->update([
                    'note' => $request->note
                ]);
            }else{
                OrderConfirmationDetail::query()->insert([
                    'sale_id' => $request->sale_id,
                    'note' => $request->note
                ]);
            }

            return response(['message' => __('Not ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function updateOrderConfirmationDetail(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
                'note' => 'required',
            ]);
            OrderConfirmationDetail::query()->where('sale_id', $request->sale_id)->update([
                'note' => $request->note
            ]);

            return response(['message' => __('Not ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function getProformaInvoiceDetailById($sale_id)
    {
        try {
            $proforma_invoice_detail = ProformaInvoiceDetails::query()->where('sale_id', $sale_id)->first();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['proforma_invoice_detail' => $proforma_invoice_detail]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function updateProformaInvoiceDetail(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
            ]);
            $count = ProformaInvoiceDetails::query()->where('sale_id', $request->sale_id)->count();
            if ($count == 0){
                ProformaInvoiceDetails::query()->insert([
                    'sale_id' => $request->sale_id,
                    'note' => $request->note
                ]);
            }else {
                ProformaInvoiceDetails::query()->where('sale_id', $request->sale_id)->update([
                    'note' => $request->note
                ]);
            }

            return response(['message' => __('Detay güncelleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function updateShippingPrice(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
            ]);
            $sale = Sale::query()->where('sale_id', $request->sale_id)->first();
            $grand_total_with_shipping = $sale->grand_total + $request->shipping_price;
            Sale::query()->where('sale_id', $request->sale_id)->update([
                'shipping_price' => $request->shipping_price,
                'grand_total_with_shipping' => $grand_total_with_shipping,
            ]);

            return response(['message' => __('Detay güncelleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }



    public function getSaleDetailInfo($sale_id)
    {
        try {
            $sale = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name')
                ->where('sales.active',1)
                ->where('sales.sale_id',$sale_id)
                ->first();

            $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
            $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->first();
            $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
            $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
            $sale['request'] = $offer_request;

            $sale['customer'] = Company::query()->where('id', $sale->customer_id)->first();
            $sale['owner'] = Contact::query()->where('id', $sale->owner_id)->first();
            $sale['product_count'] = SaleOffer::query()->where('sale_id', $sale_id)->where('active', 1)->count();
            $sale['total_product_count'] = SaleOffer::query()->where('sale_id', $sale_id)->where('active', 1)->sum('offer_quantity');

            $sale_offers = SaleOffer::query()->where('sale_id', $sale_id)->where('active', 1)->get();
            $total_offer_price = 0;
            foreach ($sale_offers as $sale_offer){
                $offer_product = OfferProduct::query()->where('id', $sale_offer->offer_product_id)->where('active', 1)->first();
                if ($offer_product) {
                    $total_offer_price += $offer_product->converted_price;
                }
            }


            $total_price = $sale->grand_total;
            if ($sale->grand_total_with_shipping != null){
                $total_price = $sale->grand_total_with_shipping;
            }

            $transactions = SaleTransaction::query()->where('sale_id', $sale_id)->where('active', 1)->get();
            $sale['remaining_message'] = false;
            $payed_price = 0;

            if ($transactions) {
                foreach ($transactions as $transaction) {
                    $payments = SaleTransactionPayment::query()
                        ->where('sale_transaction_payments.transaction_id', $transaction->transaction_id)
                        ->where('sale_transaction_payments.active', 1)
                        ->where('sale_transaction_payments.payment_status_id', 2)
                        ->get();

                    if ($payments) {
                        foreach ($payments as $payment) {
                            $payed_price += $payment->payment_price;
                        }
                        $sale['remaining_message'] = true;
                    }
                }
            }


            $advance_price = 0;
            $quote = Quote::query()->where('sale_id', $sale->sale_id)->where('active', 1)->first();
            if ($quote){
                $advance_price = $quote->advance_price;
            }

            if (($payed_price + $advance_price) == $total_price && $advance_price > 0) {
                $remaining_price = $total_price - $payed_price;
            }else if (($payed_price + $advance_price) > $total_price){
                $remaining_price = $total_price - $payed_price;
            }else{
                $remaining_price = $total_price - $payed_price - $advance_price;
            }

            $sale['total_price'] = $total_price;
            $sale['advance_price'] = $advance_price;
            $sale['payed_price'] = number_format($payed_price, 2, ".", "");
            $sale['remaining_price'] = number_format($remaining_price, 2, ".", "");
            //let profit_rate = 100 * (offer_total - supplier_total) / supplier_total;
            if ($total_offer_price != 0) {
                $profit_rate = 100 * ($total_price - $total_offer_price) / $total_offer_price;
            }else{
                $profit_rate = 0;
            }
            $sale['profit_rate'] = number_format($profit_rate, 2, ",", "");
            $sale['supplier_total'] = number_format($total_offer_price, 2, ".", "");


            $packing_lists = PackingList::query()->where('sale_id', $sale_id)->where('active', 1)->get();
            $packing_count = 0;
            foreach ($packing_lists as $packing_list){
                $totalQuantity = PackingListProduct::query()
                    ->where('packing_list_id', '=', $packing_list->packing_list_id)
                    ->where('active', 1)
                    ->sum('quantity');
                $packing_count += $totalQuantity;
            }
            $sale['packing_count'] = $packing_count;

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sale' => $sale]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getSaleStatusHistory($sale_id)
    {
        try {
            $actions = StatusHistory::query()
                ->selectRaw('sale_id, id')
                ->orderByDesc('id')
                ->where('sale_id', $sale_id)
                ->get();

            $previous_status = 0;
            foreach ($actions as $action){
                $last_status = StatusHistory::query()->where('id', $action->id)->first();
                $last_status['status_name'] = Status::query()->where('id', $last_status->status_id)->first()->name;
                $admin = Admin::query()->where('id', $last_status->user_id)->first();
                $last_status['user_name'] = $admin->name." ".$admin->surname;
                $sale = Sale::query()->where('sale_id', $action->sale_id)->first();
                $customer = Company::query()->where('id', $sale->customer_id)->first();
                $sale['customer_name'] = $customer->name;
                $previous_status = StatusHistory::query()->where('id', '<' ,$action->id)->where('sale_id', $action->sale_id)->orderByDesc('id')->first();
                if (!empty($previous_status)) {
                    $previous_status['status_name'] = Status::query()->where('id', $previous_status->status_id)->first()->name;
                    $action['previous_status'] = $previous_status;
                }else{
                    $previous_status['status_name'] = "-";
                    $action['previous_status'] = 0;
                }

                $action['last_status'] = $last_status;
                $action['sale'] = $sale;

            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['actions' => $actions]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getSaleSuppliers($sale_id)
    {
        try {
            $offers = SaleOffer::query()
                ->selectRaw('sale_offers.supplier_id, SUM(total_price) as total_price, COUNT(supplier_id) as product_count')
                ->groupBy('sale_offers.supplier_id')
                ->orderBy('sale_offers.supplier_id')
                ->where('sale_id', $sale_id)
                ->where('active', 1)
                ->get();

            $sale = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name')
                ->where('sales.active',1)
                ->where('sales.sale_id',$sale_id)
                ->first();

            foreach ($offers as $offer){
                $supplier = Company::query()->where('id', $offer->supplier_id)->first();
                $offer['supplier'] = $supplier;
                $offer_currency = SaleOffer::query()->where('sale_id', $sale_id)->where('supplier_id', $offer->supplier_id)->first()->currency;
                $offer['currency'] = $offer_currency;

                if ($offer_currency == $sale->currency){
                    $offer['converted_price'] = number_format($offer->total_price, 2, ".", "");
                }else{
                    if ($offer_currency == 'TRY') {
//                        $oc = strtolower($offer_currency);
                        $sc = strtolower($sale->currency);
                        $c_price = $offer->total_price / $sale->{$sc.'_rate'};
                    }else if ($sale->currency == 'TRY') {
                        $oc = strtolower($offer_currency);
                        $c_price = $offer->total_price * $sale->{$oc.'_rate'};
                    }else{
                        $oc = strtolower($offer_currency);
                        $sc = strtolower($sale->currency);
                        if ($sale->{$sc.'_rate'} != 0) {
                            $c_price = $offer->total_price * $sale->{$oc . '_rate'} / $sale->{$sc . '_rate'};
                        }else{
                            $c_price = 0;
                        }
                    }
                    $offer['converted_price'] = number_format($c_price, 2, ".", "");
                }

                $offerr = Offer::query()
                    ->where('request_id', $sale->request_id)
                    ->where('supplier_id', $offer->supplier_id)
                    ->where('active', 1)
                    ->first();
                $offer['po_url'] = $offerr->po_url;
            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['offers' => $offers]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getSaleSummary($sale_id)
    {
        try {
            $sale = Sale::query()
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name')
                ->where('sales.active',1)
                ->where('sales.sale_id',$sale_id)
                ->first();

            $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
            $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->where('active', 1)->first();
            $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
            $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
            $sale['request'] = $offer_request;

            $sale['customer'] = Company::query()->where('id', $sale->customer_id)->first();
            $sale['owner'] = Contact::query()->where('id', $sale->owner_id)->first();

            $sale_offers = SaleOffer::query()->where('sale_id', $sale_id)->where('active', 1)->get();
            $total_offer_price = 0;
            foreach ($sale_offers as $sale_offer){
                $offer_product = OfferProduct::query()->where('id', $sale_offer->offer_product_id)->where('active', 1)->first();
                $sale_offer['offer_product'] = $offer_product;
                $total_offer_price += $offer_product->converted_price;
            }
            $sale['sale_offers'] = $sale_offers;


            $total_price = $sale->grand_total;
            if ($sale->grand_total_with_shipping != null){
                $total_price = $sale->grand_total_with_shipping;
            }

            if ($total_offer_price != 0) {
                $total_expense = $total_offer_price;
            }else{
                $total_expense = 0;
            }

            $expenses = Expense::query()->where('sale_id', $sale_id)->where('active', 1)->get();
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
//                    $expense['converted_price'] = $expense_price;
                    $expense['converted_price'] = number_format($expense_price, 2, '.', '');
                }
            }
            $sale['expenses'] = $expenses;


            if ($total_expense != 0) {
                $profit_rate = 100 * ($total_price - $total_expense) / $total_expense;
            }else{
                $profit_rate = 0;
            }
            $sale['profit_rate'] = number_format($profit_rate, 2, ",", "");
            $sale['supplier_total'] = number_format($total_offer_price, 2, ".", "");
            $sale['total_expense'] = number_format($total_expense, 2, ".", "");

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sale' => $sale]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }


    public function removeCancelledSales()
    {
        try {
            $sales = Sale::query()->where('status_id', 25)->get();

            foreach ($sales as $sale){

                CancelNote::query()->where('sale_id', $sale->sale_id)->delete();
                OrderConfirmationDetail::query()->where('sale_id', $sale->sale_id)->delete();
                PurchasingOrderDetails::query()->where('sale_id', $sale->sale_id)->delete();

                $offer = Offer::query()->where('request_id', $sale->request_id)->first();
                OfferProduct::query()->where('offer_id', $offer->offer_id)->delete();
                RfqDetails::query()->where('offer_id', $offer->offer_id)->delete();
                Offer::query()->where('request_id', $sale->request_id)->delete();

                OfferRequestProduct::query()->where('request_id', $sale->request_id)->delete();
                OfferRequest::query()->where('request_id', $sale->request_id)->delete();

                SaleOffer::query()->where('sale_id', $sale->sale_id)->delete();
                Sale::query()->where('id', $sale->id)->delete();

            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }





    public function getSaleNotes($sale_id)
    {
        try {
            $sale_notes = SaleNote::query()->where('sale_id', $sale_id)->get();
            foreach ($sale_notes as $sale_note){
                $user = Admin::query()->where('id', $sale_note->user_id)->first();
                $sale_note['user_name'] = $user->name.' '.$user->surname;
            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sale_notes' => $sale_notes]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function addSaleNote(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
                'user_id' => 'required',
                'note' => 'required',
            ]);

            SaleNote::query()->insert([
                'sale_id' => $request->sale_id,
                'user_id' => $request->user_id,
                'note' => $request->note,
            ]);

            return response(['message' => __('Not ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }



    public function getLastCurrencyLog()
    {
        try {
            $currency_log = CurrencyLog::query()->orderByDesc('id')->first();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['currency_log' => $currency_log]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getLiveCurrencyLog()
    {
        try {

            $xml = null;
            $url = 'https://www.tcmb.gov.tr/kurlar/today.xml';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);

            if ($data !== false) {
                $xml = simplexml_load_string($data);
            }

            curl_close($ch);

            if(empty($xml)){
                throw new \Exception('currency-001');
            }

            $eur_rate = $xml->Currency[3]->ForexSelling;
            $usd_rate = $xml->Currency[0]->ForexSelling;
            $gbp_rate = $xml->Currency[4]->ForexSelling;

            CurrencyLog::query()->insert([
                'usd' => $usd_rate,
                'eur' => $eur_rate,
                'gbp' => $gbp_rate,
                'day' => Carbon::now()->format('Y-m-d'),
            ]);

            return response(['message' => 'İşlem başarılı.','status' => 'success']);
        } catch (QueryException $queryException) {
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001','error' => $queryException->getMessage()]);
        } catch (\Exception $exception){
            if ($exception->getMessage() == 'currency-001'){
                return  response(['message' => 'Anlık bir hata nedeniyle döviz kuru alınamadı.Lütfen tekrar deneyiniz.','status' => 'currency-001']);
            }
            return  response(['message' => 'Hatalı işlem.','status' => 'error-001', 'err' => $exception->getMessage()]);
        }
    }

    public function getCheckSaleCurrencyLog($request_id)
    {
        try {
            $has_currency = true;

            $sale = Sale::query()->where('request_id', $request_id)->first();
            if ($sale->usd_rate == null || $sale->eur_rate == null || $sale->gbp_rate == null || $sale->currency == null){
                $has_currency = false;
            }

            $check_offer_currency = OfferProduct::query()
                ->selectRaw('offer_products.*')
                ->leftJoin('offers', 'offers.offer_id', '=', 'offer_products.offer_id')
                ->where('offers.request_id', $request_id)
                ->where('offer_products.active', 1)
                ->where('offers.active', 1)
                ->whereNotNull('offer_products.total_price')
                ->where('offer_products.total_price', '!=', '0.00')
                ->where(function($query) {
                    $query->whereNull('offer_products.converted_price')
                        ->orWhere('offer_products.converted_price', '');
                })
                ->count();

            if ($check_offer_currency > 0){
                $has_currency = false;
            }


            if ($has_currency){
                return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['has_currency' => true, 'sale' => $sale]]);
            }else{
                return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['has_currency' => false]]);
            }

//            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['currency_log' => $currency_log]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function addSaleCurrencyLog(Request $request, $request_id)
    {
        try {
//            $request->validate([
//                'request_id' => 'required',
//            ]);

            Sale::query()->where('request_id', $request_id)->update([
                'usd_rate' => $request->usd_rate,
                'eur_rate' => $request->eur_rate,
                'gbp_rate' => $request->gbp_rate,
                'currency' => $request->currency,
            ]);


            $offers = Offer::query()
                ->leftJoin('companies', 'companies.id', '=', 'offers.supplier_id')
                ->selectRaw('offers.*, companies.name as company_name')
                ->where('offers.request_id', $request_id)
                ->where('offers.active', 1)
                ->get();

            foreach ($offers as $offer) {
                $products = OfferProduct::query()->where('offer_id', $offer->offer_id)->where('active', 1)->get();
                foreach ($products as $product) {

                    $convertible_price = $product->total_price;
                    if ($product->discount_rate > 0){
                        $convertible_price = $product->discounted_price;
                    }

                    $supply_price = CurrencyHelper::ChangePrice($product->currency, $request->currency, $convertible_price, $request->eur_rate, $request->usd_rate, $request->gbp_rate);

                    OfferProduct::query()->where('id', $product->id)->update([
                        'converted_price' => $supply_price,
                        'converted_currency' => $request->currency
                    ]);

                }






                $offer['products'] = $products;
            }


            return response(['message' => __('Kur ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }
    public function updateSaleCurrencyLogOnPI(Request $request, $request_id)
    {
        try {
//            $request->validate([
//                'request_id' => 'required',
//            ]);

            Sale::query()->where('request_id', $request_id)->update([
                'usd_rate' => $request->usd_rate,
                'eur_rate' => $request->eur_rate,
                'gbp_rate' => $request->gbp_rate,
                'currency' => $request->currency,
            ]);


            $offers = Offer::query()
                ->leftJoin('companies', 'companies.id', '=', 'offers.supplier_id')
                ->selectRaw('offers.*, companies.name as company_name')
                ->where('offers.request_id', $request_id)
                ->where('offers.active', 1)
                ->get();

            foreach ($offers as $offer) {
                $products = OfferProduct::query()->where('offer_id', $offer->offer_id)->where('active', 1)->get();
                foreach ($products as $product) {

                    $convertible_price = $product->total_price;
                    if ($product->discount_rate > 0){
                        $convertible_price = $product->discounted_price;
                    }

                    $supply_price = CurrencyHelper::ChangePrice($product->currency, $request->currency, $convertible_price, $request->eur_rate, $request->usd_rate, $request->gbp_rate);

                    OfferProduct::query()->where('id', $product->id)->update([
                        'converted_price' => $supply_price,
                        'converted_currency' => $request->currency
                    ]);

                }






                $offer['products'] = $products;
            }


            return response(['message' => __('Kur ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }


    public function getCurrencyLogs()
    {
        try {
            $currency_logs = CurrencyLog::query()->get();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['currency_logs' => $currency_logs]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function addCurrencyLog(Request $request)
    {
        try {
//            $request->validate([
//                'request_id' => 'required',
//            ]);

            CurrencyLog::query()->insert([
                'usd' => $request->usd,
                'eur' => $request->eur,
                'gbp' => $request->gbp,
                'day' => Carbon::now()->format('Y-m-d'),
            ]);


            return response(['message' => __('Kur ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }
    public function updateOldCurrencies()
    {
        try {
            $sales = Sale::select('status_histories.*')
                ->leftJoin('status_histories', 'sales.sale_id', '=', 'status_histories.sale_id')
                ->where('sales.active', 1)
                ->where('sales.usd_rate', '1.00')
                ->where('status_histories.status_id', 4)
                ->where('status_histories.id', function ($query) {
                    $query->select(StatusHistory::raw('MAX(id)'))
                        ->from('status_histories')
                        ->whereRaw('status_histories.sale_id = sales.sale_id')
                        ->where('status_histories.status_id', 4);
                })
                ->get();

//            $date1 = date('Ym', strtotime($sale->created_at));
//            $date2 = date('dmY', strtotime($sale->created_at));
//            $xml = null;
//            $url = 'https://www.tcmb.gov.tr/kurlar/'.$date1.'/'.$date2.'.xml';
//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL, $url);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            $data = curl_exec($ch);
//            $sale['eur_rate2'] = $url;
//            $sale['eur_rate'] = $data;
//            if ($data !== false) {
//                $xml = simplexml_load_string($data);
//            }
//
//            curl_close($ch);





            foreach ($sales as $sale){

// Assuming $sale->created_at is a valid date string in Y-m-d format (e.g., "2023-07-27")
                $createdDate = Carbon::parse($sale->created_at);

// Check if the created date falls on a weekend (Saturday or Sunday)
                $dayOfWeek = $createdDate->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

                if ($dayOfWeek === Carbon::SUNDAY || $dayOfWeek === Carbon::SATURDAY) {
                    // If it's Sunday (0) or Saturday (6), find the previous Friday
                    $previousFriday = $createdDate->copy()->previous(Carbon::FRIDAY);
                    $previousFridayDate = $previousFriday->format('Y-m-d');
                } else {
                    // If it's not a weekend, the created_at date remains unchanged
                    $previousFridayDate = $createdDate->format('Y-m-d');
                }

// Now you can use $previousFridayDate for further processing or displaying the correct date.

// Convert the modified date back to the desired format (dmY)
                $date1 = Carbon::createFromFormat('Y-m-d', $previousFridayDate)->format('Ym');
                $date2 = Carbon::createFromFormat('Y-m-d', $previousFridayDate)->format('dmY');

//                $date1 = date('Ym', strtotime($createdDate));
//                $date2 = date('dmY', strtotime($createdDate));

                $xml = null;
                $url = 'https://www.tcmb.gov.tr/kurlar/'.$date1.'/'.$date2.'.xml';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $data = curl_exec($ch);
                $sale['eur_rate2'] = $url;
                $sale['eur_rate'] = $data;

                if ($data !== false) {
                    $xml = simplexml_load_string($data);
                }

                curl_close($ch);


//                $sale['eurBuying'] = (float) $xml->Currency[3]->ForexBuying;
//
//                if(empty($xml)){
//                    throw new \Exception('currency-001');
//                }
//
                if (!empty($xml->Currency)) {
//                    $sale['eur_rate'] = (float)$xml->Currency[3]->ForexSelling;
//                    $sale['usd_rate'] = (float)$xml->Currency[0]->ForexSelling;
//                    $sale['gbp_rate'] = (float)$xml->Currency[4]->ForexSelling;
                    $sale['eur_rate'] = number_format((float) $xml->Currency[3]->ForexSelling, 2,".","");
                    $sale['usd_rate'] = number_format((float) $xml->Currency[0]->ForexSelling, 2,".","");
                    $sale['gbp_rate'] = number_format((float) $xml->Currency[4]->ForexSelling, 2,".","");

                    Sale::query()->where('sale_id', $sale->sale_id)->update([
                        'usd_rate' => $sale['usd_rate'],
                        'eur_rate' => $sale['eur_rate'],
                        'gbp_rate' => $sale['gbp_rate']
                    ]);
                }

//                $sale['eur_rate'] = number_format((float) $xml->Currency[3]->ForexSelling, 2,".","");
//                $sale['usd_rate'] = number_format((float) $xml->Currency[0]->ForexSelling, 2,".","");
//                $sale['gbp_rate'] = number_format((float) $xml->Currency[4]->ForexSelling, 2,".","");



            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function deleteCurrencyLog($log_id)
    {
        try {
            CurrencyLog::query()->where('id', $log_id)->delete();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getSaleByRequestId($request_id)
    {
        try {
            $sale = Sale::query()->where('request_id', $request_id)->first();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sale' => $sale]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getPackingableProductsBySaleId($sale_id)
    {
        try {
            $sale = Sale::query()
                ->selectRaw('sales.*')
                ->where('sales.active',1)
                ->where('sales.sale_id',$sale_id)
                ->first();


            $sale['status_name'] = Status::query()->where('id', $sale->status_id)->first()->name;
            $sale['sale_notes'] = SaleNote::query()->where('sale_id', $sale->sale_id)->get();

            $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
            $offer_request['product_count'] = OfferRequestProduct::query()->where('request_id', $offer_request->request_id)->where('active', 1)->count();
            $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->where('active', 1)->first();
            $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
            $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
            $sale['request'] = $offer_request;

//            $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
            $sale_offers = SaleOffer::query()
                ->where('sale_id', $sale->sale_id)
                ->where('active', 1)
//                ->whereNotIn('id', function ($query) {
//                    $query->select('sale_offer_id')
//                        ->from('packing_list_products');
//                })
                ->get();
            foreach ($sale_offers as $sale_offer){
                $sale_offer['supplier_name'] = Company::query()->where('id', $sale_offer->supplier_id)->first()->name;
                $sale_offer['product_name'] = Product::query()->where('id', $sale_offer->product_id)->first()->product_name;
                $sale_offer['product_ref_code'] = Product::query()->where('id', $sale_offer->product_id)->first()->ref_code;
                $offer_pcs_price = $sale_offer->offer_price / $sale_offer->offer_quantity;
                $sale_offer['offer_pcs_price'] = number_format($offer_pcs_price, 2,".","");
                $sale_offer->offer_price = number_format($sale_offer->offer_price, 2,",",".");
                $sale_offer->pcs_price = number_format($sale_offer->pcs_price, 2,",",".");
                $sale_offer->total_price = number_format($sale_offer->total_price, 2,",",".");
                $sale_offer->discounted_price = number_format($sale_offer->discounted_price, 2,",",".");
                $sale_offer['measurement_name_tr'] = Measurement::query()->where('id', $sale_offer->measurement_id)->first()->name_tr;
                $sale_offer['measurement_name_en'] = Measurement::query()->where('id', $sale_offer->measurement_id)->first()->name_en;

                $offer_product = OfferProduct::query()->where('id', $sale_offer->offer_product_id)->first();
                $request_product = OfferRequestProduct::query()->where('id', $offer_product->request_product_id)->first();
                $sale_offer['sequence'] = $request_product->sequence;

                $sale_offer['packing_count'] = PackingListProduct::query()->where('active', 1)->where('sale_offer_id', $sale_offer->id)->sum('quantity');

            }
            $sale['sale_offers'] = $sale_offers;

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sale' => $sale]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getPackingListsBySaleId($sale_id)
    {
        try {
            $packing_lists = PackingList::query()
                ->where('active',1)
                ->where('sale_id',$sale_id)
                ->get();

            foreach ($packing_lists as $packing_list) {
                $packing_list['count'] = PackingListProduct::query()->where('packing_list_id', $packing_list->packing_list_id)->count();
                $packing_list['status'] = PackingStatus::query()->where('id', $packing_list->packing_status_id)->first();
                $packing_list['company_id'] = Sale::query()->where('sale_id', $sale_id)->first()->customer_id;
                if($packing_list->address_id != null){
                    $packing_list['address'] = Address::query()->where('id', $packing_list->address_id)->first()->address;
                }
            }


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['packing_lists' => $packing_lists]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getPackingListProductsById($packing_list_id)
    {
        try {
            $packing_list = PackingList::query()->where('packing_list_id', $packing_list_id)->first();


            $sale = Sale::query()
                ->selectRaw('sales.*')
                ->where('sales.active',1)
                ->where('sales.sale_id',$packing_list->sale_id)
                ->first();


            $sale['status_name'] = Status::query()->where('id', $sale->status_id)->first()->name;
            $sale['sale_notes'] = SaleNote::query()->where('sale_id', $sale->sale_id)->get();
            $sale['packing_note'] = $packing_list->note;
            $sale['pl_url'] = $packing_list->pl_url;
            $sale['pli_url'] = $packing_list->pli_url;

            $offer_request = OfferRequest::query()->where('request_id', $sale->request_id)->where('active', 1)->first();
            $offer_request['product_count'] = OfferRequestProduct::query()->where('request_id', $offer_request->request_id)->where('active', 1)->count();
            $offer_request['authorized_personnel'] = Admin::query()->where('id', $offer_request->authorized_personnel_id)->where('active', 1)->first();
            $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->first();
            $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->first();
            $sale['request'] = $offer_request;

//            $sale_offers = SaleOffer::query()->where('sale_id', $sale->sale_id)->where('active', 1)->get();
            $sale_offers = SaleOffer::query()
                ->join('packing_list_products', 'packing_list_products.sale_offer_id', '=', 'sale_offers.id')
                ->selectRaw('sale_offers.*')
                ->where('sale_offers.sale_id', $sale->sale_id)
                ->where('packing_list_products.packing_list_id', $packing_list_id)
                ->where('sale_offers.active', 1)
                ->get();
            foreach ($sale_offers as $sale_offer){
                $sale_offer['supplier_name'] = Company::query()->where('id', $sale_offer->supplier_id)->first()->name;
                $sale_offer['product_name'] = Product::query()->where('id', $sale_offer->product_id)->first()->product_name;
                $sale_offer['product_ref_code'] = Product::query()->where('id', $sale_offer->product_id)->first()->ref_code;
                $offer_pcs_price = $sale_offer->offer_price / $sale_offer->offer_quantity;
                $sale_offer['offer_pcs_price'] = number_format($offer_pcs_price, 2,".","");
                $sale_offer->offer_price = number_format($sale_offer->offer_price, 2,",",".");
                $sale_offer->pcs_price = number_format($sale_offer->pcs_price, 2,",",".");
                $sale_offer->total_price = number_format($sale_offer->total_price, 2,",",".");
                $sale_offer->discounted_price = number_format($sale_offer->discounted_price, 2,",",".");
                $sale_offer['measurement_name_tr'] = Measurement::query()->where('id', $sale_offer->measurement_id)->first()->name_tr;
                $sale_offer['measurement_name_en'] = Measurement::query()->where('id', $sale_offer->measurement_id)->first()->name_en;

                $offer_product = OfferProduct::query()->where('id', $sale_offer->offer_product_id)->first();
                $request_product = OfferRequestProduct::query()->where('id', $offer_product->request_product_id)->first();
                $sale_offer['sequence'] = $request_product->sequence;

                $sale_offer['packing_count'] = PackingListProduct::query()
                    ->where('active', 1)
                    ->where('sale_offer_id', $sale_offer->id)
                    ->where('packing_list_id', $packing_list->packing_list_id)
                    ->first()
                    ->quantity;

            }
            $sale['sale_offers'] = $sale_offers;

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sale' => $sale]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function addPackingList(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
            ]);

            $packing_list_id = Uuid::uuid();

            PackingList::query()->insert([
                'packing_list_id' => $packing_list_id,
                'sale_id' => $request->sale_id,
            ]);

            foreach ($request->packing_list as $offer){
                PackingListProduct::query()->insert([
                    'packing_list_id' => $packing_list_id,
                    'sale_offer_id' => $offer['sale_offer_id'],
                    'quantity' => $offer['quantity'],
                ]);
            }

            return response(['message' => __('Gönderi listesi oluşturma işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function deletePackingList($packing_list_id){
        try {

            PackingList::query()->where('packing_list_id',$packing_list_id)->update([
                'active' => 0,
            ]);

            PackingListProduct::query()->where('packing_list_id',$packing_list_id)->update([
                'active' => 0,
            ]);
            return response(['message' => __('Gönderi listesi silme işlemi başarılı.'),'status' => 'success']);
        } catch (ValidationException $validationException) {
            return  response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'),'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => __('Hatalı sorgu.'),'status' => 'query-001']);
        } catch (\Throwable $throwable) {
            return  response(['message' => __('Hatalı işlem.'),'status' => 'error-001','ar' => $throwable->getMessage()]);
        }
    }

    public function updatePackingListNote(Request $request)
    {
        try {
            $request->validate([
                'packing_list_id' => 'required',
            ]);
            PackingList::query()->where('packing_list_id', $request->packing_list_id)->update([
                'note' => $request->note
            ]);

            return response(['message' => __('Detay güncelleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function getSellingProcess($sale_id)
    {
        try {
            $sale = Sale::query()->where('sale_id', $sale_id)->first();
            $request_date = $sale->created_at;

            $history1 = StatusHistory::query()->where('sale_id', $sale_id)->where('status_id', 6)->orderByDesc('id')->first();
            if ($history1){
                $offer_date = $history1->created_at;
                $offer_day = $offer_date->diffInDays($request_date);
                $offer_hour = $offer_date->diffInHours($request_date);
            }else{
                $offer_date = null;
                $offer_day = 0;
                $offer_hour = 0;
            }

            $confirmed_counter = 0;
            $history2 = StatusHistory::query()->where('sale_id', $sale_id)->where('status_id', 7)->orderByDesc('id')->first();
            if ($history2){
                $confirmed_date = $history2->created_at;
                $confirmed_day = $confirmed_date->diffInDays($offer_date);
                $confirmed_hour = $confirmed_date->diffInHours($offer_date);
            }else{
//                $confirmed_date = null;
//                $confirmed_day = 0;
//                $confirmed_hour = 0;


                $confirmed_counter = 1;
                $confirmed_date = Carbon::now();
                $confirmed_day = $confirmed_date->diffInDays($offer_date);
                $confirmed_hour = $confirmed_date->diffInHours($offer_date);
            }

            $is_completed = 0;
            $history3 = StatusHistory::query()->where('sale_id', $sale_id)->where('status_id', 24)->orderByDesc('id')->first();
            if ($history3){
                $is_completed = 1;
                $completed_date = $history3->created_at;
                $completed_day = $completed_date->diffInDays($confirmed_date);
                $completed_hour = $completed_date->diffInHours($confirmed_date);
            }else{
                if ($confirmed_counter == 1) {
                    $completed_date = null;
                    $completed_day = 0;
                    $completed_hour = 0;
                }else{
                    $completed_date = Carbon::now();
                    $completed_day = $completed_date->diffInDays($confirmed_date);
                    $completed_hour = $completed_date->diffInHours($confirmed_date);
                }
            }

            $lead_time = SaleOffer::query()
                ->where('active', 1)
                ->where('sale_id', $sale_id)
                ->orderBy('offer_lead_time')
                ->first()->offer_lead_time;

            $process = array();
            $process['request_date'] = $request_date;
            $process['offer_date'] = $offer_date;
            $process['confirmed_date'] = $confirmed_date;
            $process['completed_date'] = $completed_date;
            $process['offer_day'] = $offer_day;
            $process['confirmed_day'] = $confirmed_day;
            $process['completed_day'] = $completed_day;
            $process['offer_hour'] = $offer_hour;
            $process['confirmed_hour'] = $confirmed_hour;
            $process['completed_hour'] = $completed_hour;
            $process['is_completed'] = $is_completed;
            $process['confirmed_counter'] = $confirmed_counter;
            if ($confirmed_counter == 1) {
                $process['lead_time'] = 0;
            }else {
                $process['lead_time'] = $lead_time;
            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['process' => $process]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getDocuments($sale_id)
    {
        try {
            $documents = DocumentType::query()->where('active', 1)->get();

            foreach ($documents as $document){
                $file = Document::query()->where('sale_id', $sale_id)->where('document_type_id', $document->id)->first();
                if ($file){
                    $document['file_url'] = $file->file_url;
                }else{
                    $document['file_url'] = null;
                }
            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['documents' => $documents]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getExpenseCategories()
    {
        try {
            $categories = ExpenseCategory::query()->where('active', 1)->orderBy('sequence')->get();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['categories' => $categories]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getSaleExpenseById($sale_id)
    {
        try {
            $expenses = Expense::query()
                ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.category_id')
                ->where('expenses.sale_id', $sale_id)
                ->where('expenses.active', 1)
                ->selectRaw('expenses.*, expense_categories.name as category_name')
                ->orderBy('expense_categories.sequence')
                ->get();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['expenses' => $expenses]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getSaleExpenseByCategoryId($sale_id, $category_id)
    {
        try {
            $expenses = Expense::query()
                ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.category_id')
                ->where('expenses.sale_id', $sale_id)
                ->where('expenses.category_id', $category_id)
                ->where('expenses.active', 1)
                ->selectRaw('expenses.*, expense_categories.name as category_name')
                ->orderBy('expense_categories.sequence')
                ->first();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['expenses' => $expenses]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function addSaleExpense(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required'
            ]);
            $has_price = Expense::query()
                ->where('sale_id', $request->sale_id)
                ->where('category_id', $request->category_id)
                ->first();
            if ($has_price) {
                Expense::query()->where('sale_id', $request->sale_id)->where('category_id', $request->category_id)->update([
                    'price' => $request->price,
                    'currency' => $request->currency,
                    'active' => 1
                ]);
            }else{
                Expense::query()->insert([
                    'sale_id' => $request->sale_id,
                    'category_id' => $request->category_id,
                    'price' => $request->price,
                    'currency' => $request->currency
                ]);
            }

            return response(['message' => __('İşlem başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function deleteSaleExpense($expense_id)
    {
        try {
            Expense::query()->where('id', $expense_id)->update([
                'active' => 0
            ]);

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function addCustomerPONumber(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required'
            ]);
            Sale::query()->where('sale_id', $request->sale_id)->update([
                'customer_po' => $request->customer_po
            ]);

            return response(['message' => __('İşlem başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function addSalePin($sale_id)
    {
        try {
            Sale::query()->where('sale_id', $sale_id)->update([
                'pinned' => 1
            ]);

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function deleteSalePin($sale_id)
    {
        try {
            Sale::query()->where('sale_id', $sale_id)->update([
                'pinned' => 0
            ]);

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getSaleTypes()
    {
        try {
            $types = SaleType::query()->where('active', 1)->get();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['types' => $types]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
}
