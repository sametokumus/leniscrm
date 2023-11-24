<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\OfferRequest;
use App\Models\Sale;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Nette\Schema\ValidationException;

class CompanyController extends Controller
{
    public function getCompanies()
    {
        try {
            $companies = Company::query()->where('active',1)->get();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['companies' => $companies]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getPotentialCustomers()
    {
        try {
            $companies = Company::query()->where('active',1)->where('is_potential_customer', 1)->get();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['companies' => $companies]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getCustomers()
    {
        try {
            $companies = Company::query()->where('active',1)->where('is_customer', 1)->get();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['companies' => $companies]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getSuppliers()
    {
        try {
            $companies = Company::query()->where('active',1)->where('is_supplier', 1)->get();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['companies' => $companies]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function getCompanyById($company_id)
    {
        try {
            $company = Company::query()->where('id', $company_id)->where('active',1)->first();

            return response(['message' => __('İşlem Başarılı.'), 'status' => 'success', 'object' => ['company' => $company]]);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001']);
        }
    }

    public function addCompany(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
            ]);
            $company_id = Company::query()->insertGetId([
                'name' => $request->name,
                'website' => $request->website,
                'email' => $request->email,
                'phone' => $request->phone,
                'fax' => $request->fax,
                'address' => $request->address,
                'country_id' => $request->country,
                'tax_office' => $request->tax_office,
                'tax_number' => $request->tax_number,
                'is_potential_customer' => $request->is_potential_customer,
                'is_customer' => $request->is_customer,
                'is_supplier' => $request->is_supplier,
                'linkedin' => $request->linkedin,
                'skype' => $request->skype,
                'online' => $request->online,
                'registration_number' => $request->registration_number,
                'payment_term' => $request->payment_term
            ]);
            if ($request->hasFile('logo')) {
                $rand = uniqid();
                $image = $request->file('logo');
                $image_name = $rand . "-" . $image->getClientOriginalName();
                $image->move(public_path('/img/company/'), $image_name);
                $image_path = "/img/company/" . $image_name;
                Company::query()->where('id',$company_id)->update([
                    'logo' => $image_path
                ]);
            }

            return response(['message' => __('Firma ekleme işlemi başarılı.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'), 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => __('Hatalı sorgu.'), 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => __('Hatalı işlem.'), 'status' => 'error-001','a' => $throwable->getMessage()]);
        }
    }

    public function updateCompany(Request $request,$company_id){
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
            ]);
            Company::query()->where('id', $company_id)->update([
                'name' => $request->name,
                'website' => $request->website,
                'email' => $request->email,
                'phone' => $request->phone,
                'fax' => $request->fax,
                'address' => $request->address,
                'country_id' => $request->country,
                'tax_office' => $request->tax_office,
                'tax_number' => $request->tax_number,
                'is_potential_customer' => $request->is_potential_customer,
                'is_customer' => $request->is_customer,
                'is_supplier' => $request->is_supplier,
                'linkedin' => $request->linkedin,
                'skype' => $request->skype,
                'online' => $request->online,
                'registration_number' => $request->registration_number,
                'payment_term' => $request->payment_term
            ]);
            if ($request->hasFile('logo')) {
                $rand = uniqid();
                $image = $request->file('logo');
                $image_name = $rand . "-" . $image->getClientOriginalName();
                $image->move(public_path('/img/company/'), $image_name);
                $image_path = "/img/company/" . $image_name;
                Company::query()->where('id',$company_id)->update([
                    'logo' => $image_path
                ]);
            }

            return response(['message' => __('Firma güncelleme işlemi başarılı.'),'status' => 'success']);
        } catch (ValidationException $validationException) {
            return  response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'),'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => __('Hatalı sorgu.'),'status' => 'query-001','ar' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return  response(['message' => __('Hatalı işlem.'),'status' => 'error-001','ar' => $throwable->getTraceAsString()]);
        }
    }

    public function deleteCompany($company_id){
        try {

            Company::query()->where('id',$company_id)->update([
                'active' => 0,
            ]);
            return response(['message' => __('Firma silme işlemi başarılı.'),'status' => 'success']);
        } catch (ValidationException $validationException) {
            return  response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'),'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => __('Hatalı sorgu.'),'status' => 'query-001']);
        } catch (\Throwable $throwable) {
            return  response(['message' => __('Hatalı işlem.'),'status' => 'error-001','ar' => $throwable->getMessage()]);
        }
    }

    public function getBestCustomer(){
        try {

            $all_companies = Company::query()->where('active', 1)->get();
            $companies = array();

            foreach ($all_companies as $company){
                $data = array();
                $data['company'] = $company;

                $request_count = OfferRequest::query()->where('company_id', $company->id)
                    ->whereBetween('created_at', [now()->subDays(90), now()])
                    ->count();

                $sale_count = Sale::query()->where('customer_id', $company->id)
                    ->whereBetween('created_at', [now()->subDays(90), now()])
                    ->count();

                $c1 = ($sale_count * 100 / $request_count) / 10;


                $data['request_count'] = $request_count;
                $data['sale_count'] = $sale_count;
                $data['c1'] = $c1;


                array_push($companies, $data);
            }




            return response(['message' => __('İşlem başarılı.'), 'status' => 'success', 'object' => ['companies' => $companies]]);
        } catch (ValidationException $validationException) {
            return  response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'),'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => __('Hatalı sorgu.'),'status' => 'query-001', 'message' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return  response(['message' => __('Hatalı işlem.'),'status' => 'error-001','ar' => $throwable->getMessage()]);
        }
    }
}
