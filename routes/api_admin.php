<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\AdminRoleController;
use App\Http\Controllers\Api\Admin\AdminPermissionController;
use App\Http\Controllers\Api\Admin\UserController;
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


//    //Customer
//    Route::get('customer/getCustomers', [CustomerController::class, 'getCustomers']);
//    Route::get('customer/getCustomerById/{customer_id}', [CustomerController::class, 'getCustomerById']);
//    Route::post('customer/addCustomer', [CustomerController::class, 'addCustomer']);
//    Route::post('customer/updateCustomer/{customer_id}', [CustomerController::class, 'updateCustomer']);
//    Route::get('customer/deleteCustomer/{customer_id}', [CustomerController::class, 'deleteCustomer']);
//
//    Route::get('customer/getCustomerAddresses/{customer_id}', [CustomerController::class, 'getCustomerAddresses']);
//    Route::get('customer/getCustomerAddressById/{address_id}', [CustomerController::class, 'getCustomerAddressById']);
//    Route::post('customer/addCustomerAddress', [CustomerController::class, 'addCustomerAddress']);
//    Route::post('customer/updateCustomerAddress/{address_id}', [CustomerController::class, 'updateCustomerAddress']);
//    Route::get('customer/deleteCustomerAddress/{address_id}', [CustomerController::class, 'deleteCustomerAddress']);
//
//    Route::get('customer/getAppointments/{customer_id}', [CustomerController::class, 'getAppointments']);
//    Route::get('customer/getAppointmentById/{appointment_id}', [CustomerController::class, 'getAppointmentById']);
//    Route::post('customer/addAppointment', [CustomerController::class, 'addAppointment']);
//    Route::post('customer/updateAppointment/{appointment_id}', [CustomerController::class, 'updateAppointment']);
//    Route::get('customer/deleteAppointment/{appointment_id}', [CustomerController::class, 'deleteAppointment']);
//
//    Route::get('customer/getCustomerContacts/{customer_id}', [CustomerController::class, 'getCustomerContacts']);
//    Route::get('customer/getCustomerContactById/{contact_id}', [CustomerController::class, 'getCustomerContactById']);
//    Route::post('customer/addCustomerContact', [CustomerController::class, 'addCustomerContact']);
//    Route::post('customer/updateCustomerContact/{contact_id}', [CustomerController::class, 'updateCustomerContact']);
//    Route::get('customer/deleteCustomerContact/{contact_id}', [CustomerController::class, 'deleteCustomerContact']);
//
//
//    //Supplier
//    Route::get('supplier/getSuppliers', [SupplierController::class, 'getSuppliers']);
//    Route::get('supplier/getSupplierById/{supplier_id}', [SupplierController::class, 'getSupplierById']);
//    Route::post('supplier/addSupplier', [SupplierController::class, 'addSupplier']);
//    Route::post('supplier/updateSupplier/{supplier_id}', [SupplierController::class, 'updateSupplier']);
//    Route::get('supplier/deleteSupplier/{supplier_id}', [SupplierController::class, 'deleteSupplier']);
//
//    Route::get('supplier/getSupplierAddresses/{supplier_id}', [SupplierController::class, 'getSupplierAddresses']);
//    Route::get('supplier/getSupplierAddressById/{address_id}', [SupplierController::class, 'getSupplierAddressById']);
//    Route::post('supplier/addSupplierAddress', [SupplierController::class, 'addSupplierAddress']);
//    Route::post('supplier/updateSupplierAddress/{address_id}', [SupplierController::class, 'updateSupplierAddress']);
//    Route::get('supplier/deleteSupplierAddress/{address_id}', [SupplierController::class, 'deleteSupplierAddress']);
//
//    Route::get('supplier/getSupplierContacts/{supplier_id}', [SupplierController::class, 'getSupplierContacts']);
//    Route::get('supplier/getSupplierContactById/{contact_id}', [SupplierController::class, 'getSupplierContactById']);
//    Route::post('supplier/addSupplierContact', [SupplierController::class, 'addSupplierContact']);
//    Route::post('supplier/updateSupplierContact/{contact_id}', [SupplierController::class, 'updateSupplierContact']);
//    Route::get('supplier/deleteSupplierContact/{contact_id}', [SupplierController::class, 'deleteSupplierContact']);


    //Company
    Route::get('customer/getCompanies', [CompanyController::class, 'getCompanies']);
    Route::get('customer/getPotentialCustomers', [CompanyController::class, 'getPotentialCustomers']);
    Route::get('customer/getCustomers', [CompanyController::class, 'getCustomers']);
    Route::get('customer/getSuppliers', [CompanyController::class, 'getSuppliers']);
    Route::get('customer/getCompanyById/{company_id}', [CompanyController::class, 'getCompanyById']);
    Route::post('customer/addCompany', [CompanyController::class, 'addCompany']);
    Route::post('customer/updateCompany/{company_id}', [CompanyController::class, 'updateCompany']);
    Route::get('customer/deleteCompany/{company_id}', [CompanyController::class, 'deleteCompany']);

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
    Route::get('activity/getNotes', [NoteController::class, 'getNotes']);
    Route::get('activity/getNotesByCompanyId/{company_id}', [NoteController::class, 'getNotesByCompanyId']);
    Route::get('activity/getNoteById/{note_id}', [NoteController::class, 'getNoteById']);
    Route::post('activity/addNote', [NoteController::class, 'addNote']);
    Route::post('activity/updateNote/{note_id}', [NoteController::class, 'updateNote']);
    Route::get('activity/deleteNote/{note_id}', [NoteController::class, 'deleteNote']);
});

