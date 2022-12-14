<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () { return view('login'); });
Route::get('/login', function () { return view('login'); });
Route::get('/dashboard', function () { return view('dashboard'); });
Route::get('/admins', function () { return view('admins'); });
Route::get('/roles', function () { return view('roles'); });
Route::get('/potential-customers', function () { return view('potential-customers'); });
Route::get('/customers', function () { return view('customers'); });
Route::get('/suppliers', function () { return view('suppliers'); });
Route::get('/company-detail/{id}', function () { return view('company-detail'); });
Route::get('/offer-requests', function () { return view('offer-requests'); });
Route::get('/offer-request', function () { return view('add-offer-request'); });
Route::get('/offer-request/{id}', function () { return view('update-offer-request'); });




Route::get('/settings', function () { return view('settings'); });
