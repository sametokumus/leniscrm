<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminStatusRole;
use App\Models\Company;
use App\Models\Employee;
use App\Models\OfferRequest;
use App\Models\OfferRequestProduct;
use App\Models\PaymentMethod;
use App\Models\PaymentType;
use App\Models\Sale;
use App\Models\SaleNote;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionPayment;
use App\Models\Status;
use Carbon\Carbon;
use Faker\Provider\Uuid;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Nette\Schema\ValidationException;

class AccountingController extends Controller
{
    public function getPaymentTypes()
    {
        try {

            $types = PaymentType::query()->where('active', 1)->get();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['types' => $types]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getPaymentMethods()
    {
        try {

            $methods = PaymentMethod::query()->where('active', 1)->get();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['methods' => $methods]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getPendingAccountingSales($user_id)
    {
        try {
            $admin = Admin::query()->where('id', $user_id)->first();

            $sales = Sale::query()
                ->leftJoin('contacts', 'contacts.id', '=', 'sales.owner_id')
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name, contacts.short_code as owner_short_code')
                ->where('sales.active',1)
                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
                ->whereRaw("(sales.sale_id NOT IN (SELECT sale_id FROM sale_transactions))")
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
                $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->where('active', 1)->first();
                $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->where('active', 1)->first();
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
    public function getOngoingAccountingSales($user_id)
    {
        try {
            $admin = Admin::query()->where('id', $user_id)->first();

            $sales = Sale::query()
                ->leftJoin('sale_transactions', 'sale_transactions.sale_id', '=', 'sales.sale_id')
                ->leftJoin('contacts', 'contacts.id', '=', 'sales.owner_id')
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name, contacts.short_code as owner_short_code')
                ->where('sales.active',1)
                ->whereRaw("(sale_transactions.transaction_status_id = 1 OR sale_transactions.transaction_status_id = 2)")
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
                $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->where('active', 1)->first();
                $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->where('active', 1)->first();
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
    public function getCompletedAccountingSales($user_id)
    {
        try {
            $admin = Admin::query()->where('id', $user_id)->first();

            $sales = Sale::query()
                ->leftJoin('sale_transactions', 'sale_transactions.sale_id', '=', 'sales.sale_id')
                ->leftJoin('contacts', 'contacts.id', '=', 'sales.owner_id')
                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
                ->selectRaw('sales.*, statuses.name as status_name, contacts.short_code as owner_short_code')
                ->where('sale_transactions.transaction_status_id',3)
                ->where('sales.active',1)
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
                $offer_request['company'] = Company::query()->where('id', $offer_request->company_id)->where('active', 1)->first();
                $offer_request['company_employee'] = Employee::query()->where('id', $offer_request->company_employee_id)->where('active', 1)->first();
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
    public function getAccountingPayments($sale_id)
    {
        try {
            $transaction = SaleTransaction::query()->where('sale_id', $sale_id)->first();

            if ($transaction) {
                $payments = SaleTransactionPayment::query()
                    ->leftJoin('payment_types', 'payment_types.id', '=', 'sale_transaction_payments.payment_type')
                    ->leftJoin('payment_methods', 'payment_methods.id', '=', 'sale_transaction_payments.payment_method')
                    ->selectRaw('sale_transaction_payments.*, payment_types.name as payment_type, payment_methods.name as payment_method')
                    ->where('sale_transaction_payments.transaction_id', $transaction->transaction_id)
                    ->where('sale_transaction_payments.active', 1)
                    ->get();

                $transaction['payments'] = $payments;
            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['transaction' => $transaction]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getAccountingPaymentById($payment_id)
    {
        try {
                $payment = SaleTransactionPayment::query()
                    ->leftJoin('payment_types', 'payment_types.id', '=', 'sale_transaction_payments.payment_type')
                    ->leftJoin('payment_methods', 'payment_methods.id', '=', 'sale_transaction_payments.payment_method')
                    ->selectRaw('sale_transaction_payments.*, payment_types.name as payment_type_name, payment_methods.name as payment_method_name')
                    ->where('sale_transaction_payments.payment_id', $payment_id)
                    ->where('sale_transaction_payments.active', 1)
                    ->first();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['payment' => $payment]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function addAccountingPayment(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required',
            ]);

            $sale_id = $request->sale_id;
            $transaction = SaleTransaction::query()->where('sale_id', $sale_id)->first();

            if ($transaction) {
                $transaction_id = $transaction->transaction_id;
            }else{
                $transaction_id = Uuid::uuid();
                SaleTransaction::query()->insert([
                    'sale_id' => $sale_id,
                    'transaction_id' => $transaction_id,
                ]);
            }

            $payment_id = Uuid::uuid();

            SaleTransactionPayment::query()->insert([
                'payment_id' => $payment_id,
                'transaction_id' => $transaction_id,
                'payment_term' => $request->payment_term,
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'payment_price' => $request->payment_price,
                'currency' => $request->currency,
            ]);

            return response(['message' => __('Ödeme ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }
    public function updateAccountingPayment(Request $request)
    {
        try {
            $request->validate([
//                'sale_id' => 'required',
            ]);

            $payment_id = $request->payment_id;

            SaleTransactionPayment::query()->where('payment_id', $payment_id)->update([
                'payment_term' => $request->payment_term,
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'payment_price' => $request->payment_price,
                'currency' => $request->currency,
            ]);

            return response(['message' => __('Ödeme ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }
}