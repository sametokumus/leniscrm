<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminStatusRole;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Employee;
use App\Models\Measurement;
use App\Models\OfferProduct;
use App\Models\OfferRequest;
use App\Models\OfferRequestProduct;
use App\Models\PackingList;
use App\Models\PaymentMethod;
use App\Models\PaymentTerm;
use App\Models\PaymentType;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Sale;
use App\Models\SaleNote;
use App\Models\SaleOffer;
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

//            $sales = Sale::query()
//                ->leftJoin('contacts', 'contacts.id', '=', 'sales.owner_id')
//                ->leftJoin('statuses', 'statuses.id', '=', 'sales.status_id')
//                ->selectRaw('sales.*, statuses.name as status_name, contacts.short_code as owner_short_code')
//                ->where('sales.active',1)
//                ->whereRaw("(statuses.period = 'completed' OR statuses.period = 'approved')")
//                ->whereRaw("(sales.sale_id NOT IN (SELECT sale_id FROM sale_transactions))")
//                ->get();

            $packing_lists = PackingList::query()
                ->whereRaw("(packing_lists.packing_list_id NOT IN (SELECT packing_list_id FROM sale_transactions))")
                ->where('packing_lists.active',1)
                ->selectRaw('DISTINCT packing_lists.sale_id')
                ->get();

            $sales = array();

            foreach ($packing_lists as $packing_list) {

                $sale = Sale::query()->where('sale_id', $packing_list->sale_id)->where('active', 1)->first();
                if ($sale) {

                    $sale->status_name = Status::query()->where('id', $sale->status_id)->first()->name;
                    $sale->owner_short_code = Contact::query()->where('id', $sale->owner_id)->first()->short_code;

                    $status_role = AdminStatusRole::query()->where('admin_role_id', $admin->admin_role_id)->where('status_id', $sale->status_id)->where('active', 1)->count();
                    if ($status_role > 0) {
                        $sale['authorization'] = 1;
                    } else {
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
                    if ($sale->updated_at != null) {
                        $updated_at = $sale->updated_at;
//                    $updated_at = Carbon::parse($sale->updated_at);
//                    $updated_at = $updated_at->subHours(3);
                    } else {
                        $updated_at = $sale->created_at;
                        $updated_at = Carbon::parse($sale->created_at);
                        $updated_at = $updated_at->subHours(3);
                    }

                    $difference = $updated_at->diffForHumans($current_time);
                    $sale['diff_last_day'] = $difference;

                    array_push($sales, $sale);
                }

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

//            $packing_lists = PackingList::query()
//                ->leftJoin('sale_transactions', 'sale_transactions.sale_id', '=', 'packing_lists.sale_id')
//                ->leftJoin('sale_transaction_payments', 'sale_transaction_payments.transaction_id', '=', 'sale_transactions.transaction_id')
//                ->whereRaw("(packing_lists.packing_list_id IN (SELECT packing_list_id FROM sale_transactions))")
//                ->where('packing_lists.active',1)
//                ->where('sale_transaction_payments.payment_status_id',1)
//                ->selectRaw('DISTINCT packing_lists.sale_id')
//                ->groupBy('packing_lists.sale_id')
//                ->get();
            $packing_lists = SaleTransactionPayment::query()
                ->select('packing_lists.sale_id')
                ->leftJoin('sale_transactions', 'sale_transactions.transaction_id', '=', 'sale_transaction_payments.transaction_id')
                ->leftJoin('packing_lists', 'packing_lists.packing_list_id', '=', 'sale_transactions.packing_list_id')
                ->where('sale_transaction_payments.payment_status_id', 1)
                ->where('packing_lists.active', 1)
                ->where('sale_transactions.active', 1)
                ->where('sale_transaction_payments.active', 1)
                ->groupBy('packing_lists.sale_id')
                ->get();

            $sales = array();

            foreach ($packing_lists as $packing_list) {

                $sale = Sale::query()->where('sale_id', $packing_list->sale_id)->where('active', 1)->first();
                if ($sale) {

                    $sale->status_name = Status::query()->where('id', $sale->status_id)->first()->name;
                    $sale->owner_short_code = Contact::query()->where('id', $sale->owner_id)->first()->short_code;

                    $status_role = AdminStatusRole::query()->where('admin_role_id', $admin->admin_role_id)->where('status_id', $sale->status_id)->where('active', 1)->count();
                    if ($status_role > 0) {
                        $sale['authorization'] = 1;
                    } else {
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
                    if ($sale->updated_at != null) {
                        $updated_at = $sale->updated_at;
//                    $updated_at = Carbon::parse($sale->updated_at);
//                    $updated_at = $updated_at->subHours(3);
                    } else {
                        $updated_at = $sale->created_at;
                        $updated_at = Carbon::parse($sale->created_at);
                        $updated_at = $updated_at->subHours(3);
                    }

                    $difference = $updated_at->diffForHumans($current_time);
                    $sale['diff_last_day'] = $difference;

                    array_push($sales, $sale);
                }

            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001', 'a' => $queryException->getMessage()]);
        }
    }
    public function getCompletedAccountingSales($user_id)
    {
        try {
            $admin = Admin::query()->where('id', $user_id)->first();

            $packing_lists = PackingList::query()
                ->leftJoin('sale_transactions', 'sale_transactions.sale_id', '=', 'packing_lists.sale_id')
                ->leftJoin('sale_transaction_payments', 'sale_transaction_payments.transaction_id', '=', 'sale_transactions.transaction_id')
                ->whereRaw("(packing_lists.packing_list_id IN (SELECT packing_list_id FROM sale_transactions))")
                ->where('packing_lists.active',1)
                ->where('sale_transaction_payments.payment_status_id',2)
                ->selectRaw('DISTINCT packing_lists.sale_id')
                ->groupBy('packing_lists.sale_id')
                ->get();

            $sales = array();

            foreach ($packing_lists as $packing_list) {

                $sale = Sale::query()->where('sale_id', $packing_list->sale_id)->where('active', 1)->first();
                if ($sale) {

                    $sale->status_name = Status::query()->where('id', $sale->status_id)->first()->name;
                    $sale->owner_short_code = Contact::query()->where('id', $sale->owner_id)->first()->short_code;

                    $status_role = AdminStatusRole::query()->where('admin_role_id', $admin->admin_role_id)->where('status_id', $sale->status_id)->where('active', 1)->count();
                    if ($status_role > 0) {
                        $sale['authorization'] = 1;
                    } else {
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
                    if ($sale->updated_at != null) {
                        $updated_at = $sale->updated_at;
//                    $updated_at = Carbon::parse($sale->updated_at);
//                    $updated_at = $updated_at->subHours(3);
                    } else {
                        $updated_at = $sale->created_at;
                        $updated_at = Carbon::parse($sale->created_at);
                        $updated_at = $updated_at->subHours(3);
                    }

                    $difference = $updated_at->diffForHumans($current_time);
                    $sale['diff_last_day'] = $difference;

                    array_push($sales, $sale);
                }

            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['sales' => $sales]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function getAccountingPayments($sale_id)
    {
        try {
            $packing_lists = PackingList::query()->where('sale_id', $sale_id)->where('active', 1)->get();

            foreach ($packing_lists as $packing_list){
                $transaction = SaleTransaction::query()
                    ->leftJoin('sale_transaction_payments', 'sale_transactions.transaction_id', '=', 'sale_transaction_payments.transaction_id')
                    ->leftJoin('payment_types', 'payment_types.id', '=', 'sale_transaction_payments.payment_type')
                    ->leftJoin('payment_methods', 'payment_methods.id', '=', 'sale_transaction_payments.payment_method')
                    ->selectRaw('sale_transaction_payments.*, payment_types.name as payment_type, payment_methods.name as payment_method, sale_transactions.*')
                    ->where('sale_transactions.sale_id', $sale_id)
                    ->where('sale_transactions.packing_list_id', $packing_list->packing_list_id)
                    ->where('sale_transactions.active', 1)
                    ->first();

                $sale_offers = SaleOffer::query()
                    ->leftJoin('packing_list_products', 'packing_list_products.sale_offer_id', '=', 'sale_offers.id')
                    ->leftJoin('sales', 'sales.sale_id', '=', 'sale_offers.sale_id')
                    ->selectRaw('sale_offers.*, packing_list_products.quantity as list_quantity')
                    ->where('sale_offers.sale_id', $sale_id)
                    ->where('sale_offers.active', 1)
//                    ->whereRaw("(sales.sale_id NOT IN (SELECT sale_id FROM sale_transactions))")
                    ->where('packing_list_products.packing_list_id', $packing_list->packing_list_id)
                    ->get();
                $list_grand_total = 0;
                $count = 0;
                foreach ($sale_offers as $sale_offer){
                    $offer_pcs_price = $sale_offer->offer_price / $sale_offer->offer_quantity;
                    $list_offer_price = $offer_pcs_price * $sale_offer->list_quantity;
                    $list_grand_total += $list_offer_price;
                    $count++;
                }

                $packing_list['transaction'] = $transaction;
                $packing_list['sale_offers'] = $sale_offers;
                $packing_list['count'] = $count;
                $packing_list['currency'] = Sale::query()->where('sale_id', $sale_id)->first()->currency;
                $packing_list['list_grand_total'] = number_format($list_grand_total, 2,",",".");
            }


            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['packing_lists' => $packing_lists]]);
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
            $transaction_id = Uuid::uuid();
            SaleTransaction::query()->insert([
                'sale_id' => $sale_id,
                'transaction_id' => $transaction_id,
                'packing_list_id' => $request->packing_list_id
            ]);

            $payment_id = Uuid::uuid();

            $tax_rate = $request->payment_tax_rate;
            if ($tax_rate == '' || $tax_rate == null){
                $tax_rate = 0;
            }


            $payment_total = $request->payment_price + $request->payment_tax;

            SaleTransactionPayment::query()->insert([
                'payment_id' => $payment_id,
                'transaction_id' => $transaction_id,
                'payment_term' => $request->payment_term,
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'payment_tax_rate' => $tax_rate,
                'payment_price' => $request->payment_price,
                'payment_tax' => $request->payment_tax,
                'payment_total' => $payment_total,
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

            $payment_total = $request->payment_price + $request->payment_tax;

            SaleTransactionPayment::query()->where('payment_id', $payment_id)->update([
                'payment_term' => $request->payment_term,
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'payment_tax_rate' => $request->payment_tax_rate,
                'payment_price' => $request->payment_price,
                'payment_tax' => $request->payment_tax,
                'payment_total' => $payment_total,
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
    public function updateAccountingPaymentStatus(Request $request)
    {
        try {
            $request->validate([
//                'sale_id' => 'required',
            ]);

            $payment_id = $request->payment_id;
            if ($request->status_id == 1){
                $payment_date = null;
            }elseif ($request->status_id == 2) {
                $payment_date = Carbon::now()->format('Y-m-d');
            }

            SaleTransactionPayment::query()->where('payment_id', $payment_id)->update([
                'payment_status_id' => $request->status_id,
                'payment_date' => $payment_date
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
    public function getAccountingPaymentType($sale_id)
    {
        try {
            $term = array();
            $quote = Quote::query()->where('sale_id', $sale_id)->where('active', 1)->first();
            if ($quote) {
                $payment_term = PaymentTerm::query()->where('id', $quote->payment_term)->where('active', 1)->first();
                if ($payment_term){
                    $term['payment_type_id'] = $payment_term['payment_type_id'];
                    $term['expiry'] = $payment_term['expiry'];
                }
            }

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['term' => $term]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }
    public function updateAccountingWaybill(Request $request)
    {
        try {
            $request->validate([
//                'sale_id' => 'required',
            ]);

            PackingList::query()->where('packing_list_id', $request->packing_list_id)->update([
                'waybill' => $request->status_id
            ]);

            return response(['message' => __('İrsaliye işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }
}
