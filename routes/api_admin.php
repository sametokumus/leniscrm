<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\AdminRoleController;
use App\Http\Controllers\Api\Admin\AdminPermissionController;
use App\Http\Controllers\Api\Admin\OfferRequestController;
use App\Http\Controllers\Api\Admin\ImportController;
use App\Http\Controllers\Api\Admin\CountryController;
use App\Http\Controllers\Api\Admin\StateController;
use App\Http\Controllers\Api\Admin\CityController;
use App\Http\Controllers\Api\Admin\CompanyController;
use App\Http\Controllers\Api\Admin\EmployeeController;
use App\Http\Controllers\Api\Admin\ActivityController;
use App\Http\Controllers\Api\Admin\NoteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//
Route::post('login', [AuthController::class, 'login'])->name('admin.login');


Route::middleware(['auth:sanctum', 'type.admin'])->group(function (){

    Route::get('logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::post('register', [AuthController::class, 'register'])->name('admin.register');

    Route::get('adminRole/getAdmins', [AdminRoleController::class, 'getAdmins']);
    Route::get('adminRole/getAdminById/{id}', [AdminRoleController::class, 'getAdminById']);
    Route::post('adminRole/addAdmin', [AdminRoleController::class, 'addAdmin']);
    Route::post('adminRole/updateAdmin/{id}', [AdminRoleController::class, 'updateAdmin']);
    Route::get('adminRole/deleteAdmin/{id}', [AdminRoleController::class, 'deleteAdmin']);

    Route::get('adminRole/getAdminRoles', [AdminRoleController::class, 'getAdminRoles']);
    Route::get('adminRole/getAdminRoleById/{id}', [AdminRoleController::class, 'getAdminRoleById']);
    Route::post('adminRole/addAdminRole', [AdminRoleController::class, 'addAdminRole']);
    Route::post('adminRole/updateAdminRole/{role_id}', [AdminRoleController::class, 'updateAdminRole']);
    Route::get('adminRole/deleteAdminRole/{role_id}', [AdminRoleController::class, 'deleteAdminRole']);

    Route::get('adminRole/getAdminRolePermissions/{role_id}', [AdminRoleController::class, 'getAdminRolePermissions']);
    Route::get('adminRole/addAdminRolePermission/{role_id}/{permission_id}', [AdminRoleController::class, 'addAdminRolePermission']);
    Route::get('adminRole/deleteAdminRolePermission/{role_id}/{permission_id}', [AdminRoleController::class, 'deleteAdminRolePermission']);

    Route::get('adminPermission/getAdminPermissions', [AdminPermissionController::class, 'getAdminPermissions']);
    Route::post('adminPermission/addAdminPermission', [AdminPermissionController::class, 'addAdminPermission']);
    Route::post('adminPermission/updateAdminPermission/{id}', [AdminPermissionController::class, 'updateAdminPermission']);
    Route::get('adminPermission/deleteAdminPermission/{id}', [AdminPermissionController::class, 'deleteAdminPermission']);




    Route::get('countries/getCountries', [CountryController::class, 'getCountries']);
    Route::get('states/getStatesByCountryId/{country_id}', [StateController::class, 'getStatesByCountryId']);
    Route::get('cities/getCitiesByStateId/{state_id}', [CityController::class, 'getCitiesByStateId']);


    Route::post('excel/productExcelImport', [ImportController::class, 'productExcelImport']);
    Route::post('excel/priceExcelImport', [ImportController::class, 'priceExcelImport']);
    Route::get('excel/addAllProduct', [ImportController::class, 'addAllProduct']);
    Route::get('excel/addProductPrice', [ImportController::class, 'addProductPrice']);
    Route::get('excel/productVariationUpdate', [ImportController::class, 'productVariationUpdate']);
    Route::get('excel/setProductCategory', [ImportController::class, 'setProductCategory']);
    Route::post('excel/newProduct', [ImportController::class, 'newProduct']);
    Route::post('excel/postNewProducts', [ImportController::class, 'postNewProducts']);
    Route::get('excel/updateProductNew', [ImportController::class, 'updateProductNew']);



    //Company
    Route::get('company/getCompanies', [CompanyController::class, 'getCompanies']);
    Route::get('company/getPotentialCustomers', [CompanyController::class, 'getPotentialCustomers']);
    Route::get('company/getCustomers', [CompanyController::class, 'getCustomers']);
    Route::get('company/getSuppliers', [CompanyController::class, 'getSuppliers']);
    Route::get('company/getCompanyById/{company_id}', [CompanyController::class, 'getCompanyById']);
    Route::post('company/addCompany', [CompanyController::class, 'addCompany']);
    Route::post('company/updateCompany/{company_id}', [CompanyController::class, 'updateCompany']);
    Route::get('company/deleteCompany/{company_id}', [CompanyController::class, 'deleteCompany']);

    //Employee
    Route::get('employee/getEmployees', [EmployeeController::class, 'getEmployees']);
    Route::get('employee/getEmployeesByCompanyId/{company_id}', [EmployeeController::class, 'getEmployeesByCompanyId']);
    Route::get('employee/getEmployeeById/{employee_id}', [EmployeeController::class, 'getEmployeeById']);
    Route::post('employee/addEmployee', [EmployeeController::class, 'addEmployee']);
    Route::post('employee/updateEmployee/{employee_id}', [EmployeeController::class, 'updateEmployee']);
    Route::get('employee/deleteEmployee/{employee_id}', [EmployeeController::class, 'deleteEmployee']);

    //Activity
    Route::get('activity/getActivities', [ActivityController::class, 'getActivities']);
    Route::get('activity/getActivitiesByCompanyId/{company_id}', [ActivityController::class, 'getActivitiesByCompanyId']);
    Route::get('activity/getActivityById/{activity_id}', [ActivityController::class, 'getActivityById']);
    Route::post('activity/addActivity', [ActivityController::class, 'addActivity']);
    Route::post('activity/updateActivity/{activity_id}', [ActivityController::class, 'updateActivity']);
    Route::get('activity/deleteActivity/{activity_id}', [ActivityController::class, 'deleteActivity']);
    //Activity Tasks
    Route::get('activity/getActivityTasksByCompanyId/{company_id}', [ActivityController::class, 'getActivityTasksByCompanyId']);
    Route::get('activity/getActivityTaskById/{task_id}', [ActivityController::class, 'getActivityTaskById']);
    Route::post('activity/addActivityTask', [ActivityController::class, 'addActivityTask']);
    Route::post('activity/updateActivityTask/{task_id}', [ActivityController::class, 'updateActivityTask']);
    Route::get('activity/deleteActivityTask/{task_id}', [ActivityController::class, 'deleteActivityTask']);
    Route::get('activity/completeActivityTask/{task_id}', [ActivityController::class, 'completeActivityTask']);
    Route::get('activity/unCompleteActivityTask/{task_id}', [ActivityController::class, 'unCompleteActivityTask']);
    //Activity Types
    Route::get('activity/getActivityTypes', [ActivityController::class, 'getActivityTypes']);
    Route::get('activity/getActivityTypeById/{type_id}', [ActivityController::class, 'getActivityTypeById']);
    Route::post('activity/addActivityType', [ActivityController::class, 'addActivityType']);
    Route::post('activity/updateActivityType/{type_id}', [ActivityController::class, 'updateActivityType']);
    Route::get('activity/deleteActivityType/{type_id}', [ActivityController::class, 'deleteActivityType']);

    //Note
    Route::get('note/getNotes', [NoteController::class, 'getNotes']);
    Route::get('note/getNotesByCompanyId/{company_id}', [NoteController::class, 'getNotesByCompanyId']);
    Route::get('note/getNoteById/{note_id}', [NoteController::class, 'getNoteById']);
    Route::post('note/addNote', [NoteController::class, 'addNote']);
    Route::post('note/updateNote/{note_id}', [NoteController::class, 'updateNote']);
    Route::get('note/deleteNote/{note_id}', [NoteController::class, 'deleteNote']);


    //OfferRequest
    Route::get('offerRequest/getOfferRequests', [OfferRequestController::class, 'getOfferRequests']);
    Route::get('offerRequest/getOfferRequestById/{offer_request_id}', [OfferRequestController::class, 'getOfferRequestById']);
    Route::post('offerRequest/addOfferRequest', [OfferRequestController::class, 'addOfferRequest']);
    Route::post('offerRequest/updateOfferRequest/{request_id}', [OfferRequestController::class, 'updateOfferRequest']);
    Route::post('offerRequest/addProductToOfferRequest/{request_id}', [OfferRequestController::class, 'addProductToOfferRequest']);
    Route::get('offerRequest/deleteProductToOfferRequest/{request_product_id}', [OfferRequestController::class, 'deleteProductToOfferRequest']);

});

