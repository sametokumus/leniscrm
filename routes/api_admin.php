<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\AdminRoleController;
use App\Http\Controllers\Api\Admin\AdminPermissionController;
use App\Http\Controllers\Api\Admin\OfferRequestController;
use App\Http\Controllers\Api\Admin\OfferController;
use App\Http\Controllers\Api\Admin\ImportController;
use App\Http\Controllers\Api\Admin\CountryController;
use App\Http\Controllers\Api\Admin\StateController;
use App\Http\Controllers\Api\Admin\CityController;
use App\Http\Controllers\Api\Admin\CompanyController;
use App\Http\Controllers\Api\Admin\EmployeeController;
use App\Http\Controllers\Api\Admin\ActivityController;
use App\Http\Controllers\Api\Admin\NoteController;
use App\Http\Controllers\Api\Admin\ContactController;
use App\Http\Controllers\Api\Admin\SaleController;
use App\Http\Controllers\Api\Admin\StatusController;
use App\Http\Controllers\Api\Admin\OwnerController;
use App\Http\Controllers\Api\Admin\NewsFeedController;
use App\Http\Controllers\Api\Admin\MeasurementController;
use App\Http\Controllers\Api\Admin\LanguageController;
use App\Http\Controllers\Api\Admin\BrandController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\AccountingController;
use App\Http\Controllers\Api\Admin\AccountingDashboardController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\MobileController;
use App\Http\Controllers\Api\Admin\PdfController;
use App\Http\Controllers\Api\Admin\StaffController;
use App\Http\Controllers\Api\Admin\ChatController;
use App\Http\Controllers\Api\Admin\SocketsController;
use App\Http\Controllers\Api\Admin\NotifyController;
use App\Http\Controllers\Api\Admin\MailController;
use App\Http\Controllers\Api\Admin\DemoController;

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
Route::get('language/changeLanguage/{language_key}',[LanguageController::class,'language']);
//Route::get('',[LanguageController::class,'emptyLanguage'])->name('front.emptyLanguage');

Route::post('login', [AuthController::class, 'login'])->name('admin.login');

Route::get('sale/getLiveCurrencyLog', [SaleController::class, 'getLiveCurrencyLog']);
Route::get('notify/getCheckSystemNotifies', [NotifyController::class, 'getCheckSystemNotifies']);



Route::get('mobile/getOrder/{sale_id}', [MobileController::class, 'getOrder']);
Route::post('mobile/getOrders', [MobileController::class, 'getOrders']);
Route::get('mobile/getDocuments/{sale_id}', [MobileController::class, 'getDocuments']);
Route::get('mobile/getDocumentTypes', [MobileController::class, 'getDocumentTypes']);
Route::get('mobile/getDocumentUrl/{sale_id}', [MobileController::class, 'getDocumentUrl']);
Route::post('mobile/addDocument/{sale_id}', [MobileController::class, 'addDocument']);
Route::get('mobile/deleteDocument/{document_id}', [MobileController::class, 'deleteDocument']);


Route::middleware(['auth:sanctum', 'type.admin'])->group(function (){

    Route::get('logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::post('register', [AuthController::class, 'register'])->name('admin.register');

    Route::get('adminRole/getAdmins', [AdminRoleController::class, 'getAdmins']);
    Route::get('adminRole/getAdminById/{id}', [AdminRoleController::class, 'getAdminById']);
    Route::post('adminRole/addAdmin', [AdminRoleController::class, 'addAdmin']);
    Route::post('adminRole/updateAdmin/{id}', [AdminRoleController::class, 'updateAdmin']);
    Route::post('adminRole/updateUser/{id}', [AdminRoleController::class, 'updateUser']);
    Route::get('adminRole/deleteAdmin/{id}', [AdminRoleController::class, 'deleteAdmin']);
    Route::get('adminRole/getMailSignaturesByAdminId/{id}', [AdminRoleController::class, 'getMailSignaturesByAdminId']);
    Route::post('adminRole/updateMailSignature', [AdminRoleController::class, 'updateMailSignature']);

    Route::get('adminRole/getAdminRoles', [AdminRoleController::class, 'getAdminRoles']);
    Route::get('adminRole/getAdminRoleById/{id}', [AdminRoleController::class, 'getAdminRoleById']);
    Route::post('adminRole/addAdminRole', [AdminRoleController::class, 'addAdminRole']);
    Route::post('adminRole/updateAdminRole/{role_id}', [AdminRoleController::class, 'updateAdminRole']);
    Route::get('adminRole/deleteAdminRole/{role_id}', [AdminRoleController::class, 'deleteAdminRole']);

    Route::get('adminRole/getAdminRolePermissions/{role_id}', [AdminRoleController::class, 'getAdminRolePermissions']);
    Route::get('adminRole/addAdminRolePermission/{role_id}/{permission_id}', [AdminRoleController::class, 'addAdminRolePermission']);
    Route::get('adminRole/deleteAdminRolePermission/{role_id}/{permission_id}', [AdminRoleController::class, 'deleteAdminRolePermission']);
    Route::get('adminRole/getCheckAdminRolePermission/{admin_id}/{permission_id}', [AdminRoleController::class, 'getCheckAdminRolePermission']);

    Route::get('adminRole/getAdminRoleStatuses/{role_id}', [AdminRoleController::class, 'getAdminRoleStatuses']);
    Route::get('adminRole/addAdminRoleStatus/{role_id}/{status_id}', [AdminRoleController::class, 'addAdminRoleStatus']);
    Route::get('adminRole/deleteAdminRoleStatus/{role_id}/{status_id}', [AdminRoleController::class, 'deleteAdminRoleStatus']);

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
    Route::get('company/getBestCustomer', [CompanyController::class, 'getBestCustomer']);
    Route::get('company/getCompaniesByStaffId/{staff_id}', [CompanyController::class, 'getCompaniesByStaffId']);
    Route::get('company/getSaledProductsByCompanyId/{company_id}', [CompanyController::class, 'getSaledProductsByCompanyId']);

    //CompanyAddress
    Route::get('company/getAddressesByCompanyId/{company_id}', [CompanyController::class, 'getAddressesByCompanyId']);
    Route::get('company/getAddressById/{address_id}', [CompanyController::class, 'getAddressById']);
    Route::post('company/addCompanyAddress', [CompanyController::class, 'addCompanyAddress']);
    Route::post('company/updateCompanyAddress', [CompanyController::class, 'updateCompanyAddress']);
    Route::get('company/deleteCompanyAddress/{address_id}', [CompanyController::class, 'deleteCompanyAddress']);

    Route::get('company/getCompanyPointsByCompanyId/{company_id}', [CompanyController::class, 'getCompanyPointsByCompanyId']);
    Route::post('company/addCompanyPoint', [CompanyController::class, 'addCompanyPoint']);

    //Employee
    Route::get('employee/getEmployees', [EmployeeController::class, 'getEmployees']);
    Route::get('employee/getEmployeesByCompanyId/{company_id}', [EmployeeController::class, 'getEmployeesByCompanyId']);
    Route::get('employee/getEmployeeById/{employee_id}', [EmployeeController::class, 'getEmployeeById']);
    Route::post('employee/addEmployee', [EmployeeController::class, 'addEmployee']);
    Route::post('employee/updateEmployee/{employee_id}', [EmployeeController::class, 'updateEmployee']);
    Route::get('employee/deleteEmployee/{employee_id}', [EmployeeController::class, 'deleteEmployee']);

    //Activity
    Route::get('activity/getActivities', [ActivityController::class, 'getActivities']);
    Route::get('activity/getPastActivities', [ActivityController::class, 'getPastActivities']);
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
    Route::get('offerRequest/getOfferRequestProductsById/{offer_request_id}', [OfferRequestController::class, 'getOfferRequestProductsById']);
    Route::post('offerRequest/offerRequestProducts/{request_id}', [OfferRequestController::class, 'offerRequestProducts']);
    Route::post('offerRequest/createOfferRequest', [OfferRequestController::class, 'createOfferRequest']);
    Route::post('offerRequest/addOfferRequest', [OfferRequestController::class, 'addOfferRequest']);
    Route::post('offerRequest/updateOfferRequest/{request_id}', [OfferRequestController::class, 'updateOfferRequest']);
    Route::post('offerRequest/addProductToOfferRequest/{request_id}', [OfferRequestController::class, 'addProductToOfferRequest']);
    Route::get('offerRequest/deleteProductToOfferRequest/{request_product_id}', [OfferRequestController::class, 'deleteProductToOfferRequest']);
    Route::get('offerRequest/getOfferRequestsByCompanyId/{company_id}', [OfferRequestController::class, 'getOfferRequestsByCompanyId']);


    //Offer
    Route::get('offer/getOffersByRequestId/{request_id}', [OfferController::class, 'getOffersByRequestId']);
    Route::get('offer/getNewOffersByRequestId/{request_id}', [OfferController::class, 'getNewOffersByRequestId']);
    Route::get('offer/getOfferById/{offer_id}', [OfferController::class, 'getOfferById']);
    Route::post('offer/addOffer', [OfferController::class, 'addOffer']);
    Route::get('offer/getOfferProductById/{offer_id}/{product_id}', [OfferController::class, 'getOfferProductById']);
    Route::post('offer/addOfferProduct/{offer_id}', [OfferController::class, 'addOfferProduct']);
    Route::post('offer/updateOfferProduct/{offer_id}/{product_id}', [OfferController::class, 'updateOfferProduct']);
    Route::get('offer/deleteOffer/{offer_id}', [OfferController::class, 'deleteOffer']);


    //Sale
    Route::get('sale/getSales', [SaleController::class, 'getSales']);
    Route::get('sale/getSalesByCompanyId/{company_id}', [SaleController::class, 'getSalesByCompanyId']);
    Route::get('sale/getActiveSales/{user_id}', [SaleController::class, 'getActiveSales']);
    Route::get('sale/getApprovedSales/{user_id}', [SaleController::class, 'getApprovedSales']);
    Route::get('sale/getCancelledSales/{user_id}', [SaleController::class, 'getCancelledSales']);
    Route::post('sale/getFilteredSales/{user_id}', [SaleController::class, 'getFilteredSales']);
    Route::get('sale/getSaleById/{sale_id}', [SaleController::class, 'getSaleById']);
    Route::get('sale/getSaleConfirmationById/{sale_id}', [SaleController::class, 'getSaleConfirmationById']);
    Route::get('sale/getPackingListSaleById/{sale_id}', [SaleController::class, 'getPackingListSaleById']);
    Route::get('sale/getApproveOfferBySaleId/{sale_id}/{user_id}/{revize}', [SaleController::class, 'getApproveOfferBySaleId']);
    Route::get('sale/getRejectOfferBySaleId/{sale_id}/{user_id}/{revize}', [SaleController::class, 'getRejectOfferBySaleId']);
    Route::post('sale/addSale', [SaleController::class, 'addSale']);
    Route::get('sale/deleteSale/{sale_id}', [SaleController::class, 'deleteSale']);
    Route::post('sale/updateSaleStatus', [SaleController::class, 'updateSaleStatus']);
    Route::post('sale/updatePackingListStatus', [SaleController::class, 'updatePackingListStatus']);
    Route::post('sale/addPackingDeliveryAddress', [SaleController::class, 'addPackingDeliveryAddress']);
    Route::post('sale/updatePackingDeliveryAddress', [SaleController::class, 'updatePackingDeliveryAddress']);
    Route::get('sale/getSaleOfferById/{offer_product_id}', [SaleController::class, 'getSaleOfferById']);
    Route::get('sale/getSaleOffersByOfferId/{offer_id}', [SaleController::class, 'getSaleOffersByOfferId']);
    Route::post('sale/addSaleOfferPrice', [SaleController::class, 'addSaleOfferPrice']);
    Route::post('sale/updateSaleOfferPrice', [SaleController::class, 'updateSaleOfferPrice']);
    Route::get('sale/getQuoteBySaleId/{sale_id}', [SaleController::class, 'getQuoteBySaleId']);
    Route::post('sale/updateQuote', [SaleController::class, 'updateQuote']);
    Route::get('sale/updateQuoteTerms', [SaleController::class, 'updateQuoteTerms']);
    Route::post('sale/updateShippingPrice', [SaleController::class, 'updateShippingPrice']);
    Route::post('sale/addCancelSaleNote', [SaleController::class, 'addCancelSaleNote']);
    Route::get('sale/getRfqDetailById/{offer_id}', [SaleController::class, 'getRfqDetailById']);
    Route::post('sale/updateRfqDetail', [SaleController::class, 'updateRfqDetail']);

    Route::get('sale/getSaleTypes', [SaleController::class, 'getSaleTypes']);

    Route::get('sale/removeCancelledSales', [SaleController::class, 'removeCancelledSales']);

    Route::get('sale/getPackingableProductsBySaleId/{sale_id}', [SaleController::class, 'getPackingableProductsBySaleId']);
    Route::get('sale/getPackingListsBySaleId/{sale_id}', [SaleController::class, 'getPackingListsBySaleId']);
    Route::get('sale/getPackingListProductsById/{packing_list_id}', [SaleController::class, 'getPackingListProductsById']);
    Route::post('sale/addPackingList', [SaleController::class, 'addPackingList']);
    Route::get('sale/deletePackingList/{packing_list_id}', [SaleController::class, 'deletePackingList']);
    Route::post('sale/updatePackingListNote', [SaleController::class, 'updatePackingListNote']);


    Route::get('sale/getSaleNotes/{sale_id}', [SaleController::class, 'getSaleNotes']);
    Route::post('sale/addSaleNote', [SaleController::class, 'addSaleNote']);

    Route::get('sale/getLastCurrencyLog', [SaleController::class, 'getLastCurrencyLog']);
    Route::get('sale/getCheckSaleCurrencyLog/{request_id}', [SaleController::class, 'getCheckSaleCurrencyLog']);
    Route::post('sale/addSaleCurrencyLog/{request_id}', [SaleController::class, 'addSaleCurrencyLog']);
    Route::post('sale/updateSaleCurrencyLogOnPI/{request_id}', [SaleController::class, 'updateSaleCurrencyLogOnPI']);
    Route::get('sale/getSaleByRequestId/{request_id}', [SaleController::class, 'getSaleByRequestId']);
    Route::get('sale/getCurrencyLogs', [SaleController::class, 'getCurrencyLogs']);
    Route::get('sale/updateOldCurrencies', [SaleController::class, 'updateOldCurrencies']);
    Route::post('sale/addCurrencyLog', [SaleController::class, 'addCurrencyLog']);
    Route::get('sale/deleteCurrencyLog/{log_id}', [SaleController::class, 'deleteCurrencyLog']);

    Route::get('sale/getSellingProcess/{sale_id}', [SaleController::class, 'getSellingProcess']);

    //Owner
    Route::get('owner/getBankInfos', [OwnerController::class, 'getBankInfos']);
    Route::get('owner/getBankInfoById/{info_id}', [OwnerController::class, 'getBankInfoById']);
    Route::post('owner/addBankInfo', [OwnerController::class, 'addBankInfo']);
    Route::post('owner/updateBankInfo', [OwnerController::class, 'updateBankInfo']);
    Route::get('owner/deleteBankInfo/{info_id}', [OwnerController::class, 'deleteBankInfo']);

    //Purchasing Order Detail
    Route::get('sale/getPurchasingOrderDetailById/{offer_id}', [SaleController::class, 'getPurchasingOrderDetailById']);
    Route::post('sale/addPurchasingOrderDetail', [SaleController::class, 'addPurchasingOrderDetail']);
    Route::post('sale/updatePurchasingOrderDetail', [SaleController::class, 'updatePurchasingOrderDetail']);

    //Order Confirmation Detail
    Route::get('sale/getOrderConfirmationDetailById/{sale_id}', [SaleController::class, 'getOrderConfirmationDetailById']);
    Route::post('sale/addOrderConfirmationDetail', [SaleController::class, 'addOrderConfirmationDetail']);
    Route::post('sale/updateOrderConfirmationDetail', [SaleController::class, 'updateOrderConfirmationDetail']);

    //Proforma Invoice Detail
    Route::get('sale/getProformaInvoiceDetailById/{sale_id}', [SaleController::class, 'getProformaInvoiceDetailById']);
    Route::post('sale/updateProformaInvoiceDetail', [SaleController::class, 'updateProformaInvoiceDetail']);

    //Sale Detail
    Route::get('sale/getSaleDetailInfo/{sale_id}', [SaleController::class, 'getSaleDetailInfo']);
    Route::get('sale/getSaleStatusHistory/{sale_id}', [SaleController::class, 'getSaleStatusHistory']);
    Route::get('sale/getSaleSuppliers/{sale_id}', [SaleController::class, 'getSaleSuppliers']);
    Route::get('sale/getSaleSummary/{sale_id}', [SaleController::class, 'getSaleSummary']);

    //Documents
    Route::get('sale/getDocuments/{sale_id}', [SaleController::class, 'getDocuments']);

    //Expense
    Route::get('sale/getExpenseCategories', [SaleController::class, 'getExpenseCategories']);
    Route::get('sale/getSaleExpenseById/{sale_id}', [SaleController::class, 'getSaleExpenseById']);
    Route::get('sale/getSaleExpenseByCategoryId/{sale_id}/{category_id}', [SaleController::class, 'getSaleExpenseByCategoryId']);
    Route::post('sale/addSaleExpense', [SaleController::class, 'addSaleExpense']);
    Route::get('sale/deleteSaleExpense/{expense_id}', [SaleController::class, 'deleteSaleExpense']);

    //PO Number
    Route::post('sale/addCustomerPONumber', [SaleController::class, 'addCustomerPONumber']);

    //Pinned
    Route::get('sale/addSalePin/{sale_id}', [SaleController::class, 'addSalePin']);
    Route::get('sale/deleteSalePin/{sale_id}', [SaleController::class, 'deleteSalePin']);

    //Contact
    Route::get('contact/getContacts', [ContactController::class, 'getContacts']);
    Route::get('contact/getContactById/{contact_id}', [ContactController::class, 'getContactById']);
    Route::post('contact/updateContact', [ContactController::class, 'updateContact']);
    Route::get('contact/deleteContact/{contact_id}', [ContactController::class, 'deleteContact']);


    //Status
    Route::get('status/getStatuses', [StatusController::class, 'getStatuses']);
    Route::get('status/getChangeableStatuses', [StatusController::class, 'getChangeableStatuses']);
    Route::get('status/getPackingStatuses', [StatusController::class, 'getPackingStatuses']);
    Route::get('status/getAuthorizeStatuses/{user_id}', [StatusController::class, 'getAuthorizeStatuses']);

    //Measurement
    Route::get('measurement/getMeasurements', [MeasurementController::class, 'getMeasurements']);
    Route::get('measurement/getMeasurementById/{id}', [MeasurementController::class, 'getMeasurementById']);


    //News Feed
    Route::get('newsFeed/getSaleHistoryActions', [NewsFeedController::class, 'getSaleHistoryActions']);
    Route::get('newsFeed/getTopRequestedProducts', [NewsFeedController::class, 'getTopRequestedProducts']);
    Route::get('newsFeed/getTopSaledProducts/{owner_id}', [NewsFeedController::class, 'getTopSaledProducts']);
    Route::get('newsFeed/getSaleStats', [NewsFeedController::class, 'getSaleStats']);

    //Product
    Route::get('product/getProducts', [ProductController::class, 'getProducts']);
    Route::get('product/getProductById/{id}', [ProductController::class, 'getProductById']);
    Route::post('product/addImportedProducts', [ProductController::class, 'addImportedProducts']);
    Route::post('product/addProduct', [ProductController::class, 'addProduct']);
    Route::post('product/updateProduct/{id}', [ProductController::class, 'updateProduct']);
    Route::get('product/deleteProduct/{id}', [ProductController::class, 'deleteProduct']);
    Route::post('product/updateProductName/{id}', [ProductController::class, 'updateProductName']);

    //Brand
    Route::get('brand/getBrands', [BrandController::class, 'getBrands']);
    Route::get('brand/getBrandById/{id}', [BrandController::class, 'getBrandById']);
    Route::post('brand/addBrand', [BrandController::class, 'addBrand']);
    Route::post('brand/updateBrand/{id}', [BrandController::class, 'updateBrand']);
    Route::get('brand/deleteBrand/{id}', [BrandController::class, 'deleteBrand']);

    //Category
    Route::get('category/getCategory', [CategoryController::class, 'getCategory']);
    Route::get('category/getCategoryById/{id}', [CategoryController::class, 'getCategoryById']);
    Route::get('category/getCategoryByParentId/{parent_id}', [CategoryController::class, 'getCategoryByParentId']);
    Route::post('category/addCategory', [CategoryController::class, 'addCategory']);
    Route::post('category/updateCategory/{id}', [CategoryController::class, 'updateCategory']);
    Route::get('category/deleteCategory/{id}', [CategoryController::class, 'deleteCategory']);


    //Payment Terms
    Route::get('setting/getPaymentTerms', [SettingController::class, 'getPaymentTerms']);
    Route::get('setting/getPaymentTermById/{type_id}', [SettingController::class, 'getPaymentTermById']);
    Route::post('setting/addPaymentTerm', [SettingController::class, 'addPaymentTerm']);
    Route::post('setting/updatePaymentTerm/{type_id}', [SettingController::class, 'updatePaymentTerm']);
    Route::get('setting/deletePaymentTerm/{type_id}', [SettingController::class, 'deletePaymentTerm']);


    //Delivery Terms
    Route::get('setting/getDeliveryTerms', [SettingController::class, 'getDeliveryTerms']);
    Route::get('setting/getDeliveryTermById/{type_id}', [SettingController::class, 'getDeliveryTermById']);
    Route::post('setting/addDeliveryTerm', [SettingController::class, 'addDeliveryTerm']);
    Route::post('setting/updateDeliveryTerm/{type_id}', [SettingController::class, 'updateDeliveryTerm']);
    Route::get('setting/deleteDeliveryTerm/{type_id}', [SettingController::class, 'deleteDeliveryTerm']);

    //Accounting
    Route::get('accounting/getPaymentTypes', [AccountingController::class, 'getPaymentTypes']);
    Route::get('accounting/getPaymentMethods', [AccountingController::class, 'getPaymentMethods']);
    Route::get('accounting/getPendingAccountingSales/{user_id}', [AccountingController::class, 'getPendingAccountingSales']);
    Route::get('accounting/getOngoingAccountingSales/{user_id}', [AccountingController::class, 'getOngoingAccountingSales']);
    Route::get('accounting/getCompletedAccountingSales/{user_id}', [AccountingController::class, 'getCompletedAccountingSales']);
    Route::get('accounting/getAccountingPayments/{sale_id}', [AccountingController::class, 'getAccountingPayments']);
    Route::get('accounting/getAccountingPaymentById/{payment_id}', [AccountingController::class, 'getAccountingPaymentById']);
    Route::post('accounting/addAccountingPayment', [AccountingController::class, 'addAccountingPayment']);
    Route::post('accounting/updateAccountingPayment', [AccountingController::class, 'updateAccountingPayment']);
    Route::post('accounting/updateAccountingPaymentStatus', [AccountingController::class, 'updateAccountingPaymentStatus']);
    Route::get('accounting/getAccountingPaymentType/{sale_id}', [AccountingController::class, 'getAccountingPaymentType']);
    Route::post('accounting/updateAccountingWaybill', [AccountingController::class, 'updateAccountingWaybill']);

    //Accounting Dashboard
    Route::get('accounting-dashboard/getAccountingStats', [AccountingDashboardController::class, 'getAccountingStats']);
    Route::get('accounting-dashboard/getCashFlows', [AccountingDashboardController::class, 'getCashFlows']);
    Route::get('accounting-dashboard/getCashFlowPayments', [AccountingDashboardController::class, 'getCashFlowPayments']);

    //Dashboard
    Route::get('dashboard/getTotalSales/{owner_id}', [DashboardController::class, 'getTotalSales']);
    Route::get('dashboard/getLastMonthSales/{owner_id}', [DashboardController::class, 'getLastMonthSales']);
    Route::get('dashboard/getLastMonthSalesByAdmin/{admin_id}', [DashboardController::class, 'getLastMonthSalesByAdmin']);

    Route::get('dashboard/getMonthlySales', [DashboardController::class, 'getMonthlySales']);
    Route::get('dashboard/getApprovedSales/{owner_id}', [DashboardController::class, 'getApprovedSales']);
    Route::get('dashboard/getPotentialSales/{owner_id}', [DashboardController::class, 'getPotentialSales']);
    Route::get('dashboard/getCancelledSales/{owner_id}', [DashboardController::class, 'getCancelledSales']);
    Route::get('dashboard/getCompletedSales/{owner_id}', [DashboardController::class, 'getCompletedSales']);

    Route::get('dashboard/getMonthlySalesLastTwelveMonths', [DashboardController::class, 'getMonthlySalesLastTwelveMonths']);
    Route::get('dashboard/getMonthlyApprovedSalesLastTwelveMonths/{owner_id}', [DashboardController::class, 'getMonthlyApprovedSalesLastTwelveMonths']);
    Route::get('dashboard/getMonthlyApprovedSalesLastTwelveMonthsByAdmin/{admin_id}', [DashboardController::class, 'getMonthlyApprovedSalesLastTwelveMonthsByAdmin']);
    Route::get('dashboard/getMonthlyCompletedSalesLastTwelveMonths/{owner_id}', [DashboardController::class, 'getMonthlyCompletedSalesLastTwelveMonths']);
    Route::get('dashboard/getMonthlyCompletedSalesLastTwelveMonthsByAdmin/{admin_id}', [DashboardController::class, 'getMonthlyCompletedSalesLastTwelveMonthsByAdmin']);
    Route::get('dashboard/getMonthlyPotentialSalesLastTwelveMonths/{owner_id}', [DashboardController::class, 'getMonthlyPotentialSalesLastTwelveMonths']);
    Route::get('dashboard/getMonthlyPotentialSalesLastTwelveMonthsByAdmin/{admin_id}', [DashboardController::class, 'getMonthlyPotentialSalesLastTwelveMonthsByAdmin']);
    Route::get('dashboard/getMonthlyCancelledSalesLastTwelveMonths/{owner_id}', [DashboardController::class, 'getMonthlyCancelledSalesLastTwelveMonths']);
    Route::get('dashboard/getMonthlyCancelledSalesLastTwelveMonthsByAdmin/{admin_id}', [DashboardController::class, 'getMonthlyCancelledSalesLastTwelveMonthsByAdmin']);

    Route::get('dashboard/getMonthlyApprovedSalesLastTwelveMonthsByAdmins/{owner_id}', [DashboardController::class, 'getMonthlyApprovedSalesLastTwelveMonthsByAdmins']);
    Route::get('dashboard/getMonthlyApprovedSalesLastTwelveMonthsByAdminId/{admin_id}', [DashboardController::class, 'getMonthlyApprovedSalesLastTwelveMonthsByAdminId']);


    Route::get('dashboard/getDashboardStats/{owner_id}', [DashboardController::class, 'getDashboardStats']);
    Route::get('dashboard/getMostValuableCustomers', [DashboardController::class, 'getMostValuableCustomers']);
    Route::get('dashboard/getCustomerByNotSaleLongTimes', [DashboardController::class, 'getCustomerByNotSaleLongTimes']);
    Route::get('dashboard/getCustomerByNotSale', [DashboardController::class, 'getCustomerByNotSale']);
    Route::get('dashboard/getTotalProfitRate/{owner_id}', [DashboardController::class, 'getTotalProfitRate']);
    Route::get('dashboard/getMonthlyProfitRates/{owner_id}', [DashboardController::class, 'getMonthlyProfitRates']);
    Route::get('dashboard/getMonthlyTurningRates/{owner_id}', [DashboardController::class, 'getMonthlyTurningRates']);
    Route::get('dashboard/getMonthlyProfitRatesLastTwelveMonths/{owner_id}', [DashboardController::class, 'getMonthlyProfitRatesLastTwelveMonths']);
    Route::get('dashboard/getBestSalesLastNinetyDays/{owner_id}', [DashboardController::class, 'getBestSalesLastNinetyDays']);


    Route::get('pdf/getGeneratePDF/{owner_id}/{sale_id}', [PdfController::class, 'getGeneratePDF']);
    Route::get('pdf/getGenerateQuatotionPDF/{lang}/{owner_id}/{sale_id}', [PdfController::class, 'getGenerateQuatotionPDF']);
    Route::get('pdf/getGenerateOrderConfirmationPDF/{lang}/{owner_id}/{sale_id}/{bank_id}', [PdfController::class, 'getGenerateOrderConfirmationPDF']);
    Route::get('pdf/getGenerateProformaInvoicePDF/{lang}/{owner_id}/{sale_id}/{bank_id}/{target}', [PdfController::class, 'getGenerateProformaInvoicePDF']);
    Route::get('pdf/getGenerateInvoicePDF/{lang}/{owner_id}/{sale_id}/{bank_id}', [PdfController::class, 'getGenerateInvoicePDF']);
    Route::get('pdf/getGeneratePackingListInvoicePDF/{lang}/{owner_id}/{packing_list_id}/{bank_id}', [PdfController::class, 'getGeneratePackingListInvoicePDF']);
    Route::get('pdf/getGeneratePurchasingOrderPDF/{lang}/{owner_id}/{sale_id}/{offer_id}', [PdfController::class, 'getGeneratePurchasingOrderPDF']);
    Route::get('pdf/getGenerateRfqPDF/{lang}/{owner_id}/{offer_id}', [PdfController::class, 'getGenerateRfqPDF']);
    Route::get('pdf/getGeneratePackingListPDF/{lang}/{owner_id}/{packing_list_id}', [PdfController::class, 'getGeneratePackingListPDF']);


    Route::get('pdf/getGenerateSaleSummaryPDF/{sale_id}', [PdfController::class, 'getGenerateSaleSummaryPDF']);




    //StaffTarget
    Route::get('staff/getStaffTargets', [StaffController::class, 'getStaffTargets']);
    Route::get('staff/getStaffTargetsByStaffId/{staff_id}', [StaffController::class, 'getStaffTargetsByStaffId']);
    Route::get('staff/getStaffTargetById/{target_id}', [StaffController::class, 'getStaffTargetById']);
    Route::post('staff/addStaffTarget', [StaffController::class, 'addStaffTarget']);
    Route::post('staff/updateStaffTarget', [StaffController::class, 'updateStaffTarget']);
    Route::get('staff/deleteStaffTarget/{target_id}', [StaffController::class, 'deleteStaffTarget']);

    Route::get('staff/getStaffTargetTypes', [StaffController::class, 'getStaffTargetTypes']);

    Route::get('staff/getBestStaff', [StaffController::class, 'getBestStaff']);

    Route::get('staff/getStaffStatistics/{staff_id}', [StaffController::class, 'getStaffStatistics']);
    Route::get('staff/getStaffStatisticsWMY/{staff_id}/{month}/{year}', [StaffController::class, 'getStaffStatisticsWMY']);
    Route::get('staff/getStaffSituation/{staff_id}', [StaffController::class, 'getStaffSituation']);
    Route::get('staff/getAllStaffStatistics', [StaffController::class, 'getAllStaffStatistics']);
    Route::get('staff/getAllStaffStatisticsMonthly', [StaffController::class, 'getAllStaffStatisticsMonthly']);

    Route::get('staff/getStaffPointsByStaffId/{staff_id}', [StaffController::class, 'getStaffPointsByStaffId']);
    Route::post('staff/addStaffPoint', [StaffController::class, 'addStaffPoint']);


    //Notify
    Route::post('notify/addNotifySetting', [NotifyController::class, 'addNotifySetting']);
    Route::post('notify/updateNotifySetting', [NotifyController::class, 'updateNotifySetting']);
    Route::get('notify/deleteNotifySetting/{setting_id}', [NotifyController::class, 'deleteNotifySetting']);
    Route::get('notify/getNotifySettings', [NotifyController::class, 'getNotifySettings']);
    Route::get('notify/getNotifySettingById/{setting_id}', [NotifyController::class, 'getNotifySettingById']);
    Route::get('notify/getReadNotifyById/{notify_id}', [NotifyController::class, 'getReadNotifyById']);
    Route::get('notify/getReadAllNotifyByUserId/{user_id}', [NotifyController::class, 'getReadAllNotifyByUserId']);
    Route::get('notify/getNotReadNotifyCountByUserId/{user_id}', [NotifyController::class, 'getNotReadNotifyCountByUserId']);
    Route::get('notify/getNotifiesByUserId/{user_id}', [NotifyController::class, 'getNotifiesByUserId']);


    //Chat
    Route::post("/sockets/connect", [SocketsController::class, "connect"]);

    Route::post('/companyChat/sendMessage', [ChatController::class, 'sendPublicChatMessage']);
    Route::get('/companyChat/getPublicChatMessages/{page}', [ChatController::class, 'getPublicChatMessages']);


    //Email
    Route::post('mail/addLayout', [MailController::class, 'addLayout']);
    Route::post('mail/updateLayout/{layout_id}', [MailController::class, 'updateLayout']);
    Route::get('mail/deleteLayout/{layout_id}', [MailController::class, 'deleteLayout']);
    Route::get('mail/getLayouts', [MailController::class, 'getLayouts']);
    Route::get('mail/getLayoutById/{layout_id}', [MailController::class, 'getLayoutById']);
    Route::get('mail/getMailableSuppliersByRequestId/{request_id}', [OfferController::class, 'getMailableSuppliersByRequestId']);
    Route::post('mail/sendMailOfferToSupplier', [MailController::class, 'sendMailOfferToSupplier']);


    Route::get('demo/deleteOwnerData/{owner_id}', [DemoController::class, 'deleteOwnerData']);

});

