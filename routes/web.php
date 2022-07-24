<?php

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

/*Route::get('/', function () {
    return view('welcome');
});*/

Auth::routes();

Route::get('/', 'HomeController@index')->name('home');
Route::get('/home', 'HomeController@index')->name('home');

// スタッフ関連
Route::get('StaffEdit/{staff_id}', 'StaffController@edit');
Route::get('StaffIndex/{page_num}', 'StaffController@index');
Route::get('StaffIndex', 'StaffController@index');
Route::post('StaffIndex/{page_num}', 'StaffController@index');
Route::post('StaffIndex', 'StaffController@index');
Route::get('StaffCreate', 'StaffController@create');
Route::post('StaffConfirm', 'StaffController@confirm');
Route::post('StaffEditComplete', 'StaffController@editComplete');
Route::post('StaffComplete', 'StaffController@complete');

// 製品関連
Route::get('ProductEdit/{product_id}', 'ProductController@edit');
Route::get('ProductIndex', 'ProductController@index');
Route::get('ProductIndex/{page_num}', 'ProductController@index');
Route::post('ProductIndex', 'ProductController@index');
Route::post('ProductIndex/{page_num}', 'ProductController@index');
Route::get('ProductCreate', 'ProductController@create');
Route::post('ProductConfirm', 'ProductController@confirm');
Route::post('ProductComplete', 'ProductController@complete');
Route::post('ProductEditComplete', 'ProductController@editComplete');
Route::post('ProductAjaxAddStandard', 'ProductController@AjaxAddStandard');

// 仕入先企業関連
Route::get('SupplyCompanyEdit/{supply_company_id}', 'SupplyCompanyController@edit');
Route::get('SupplyCompanyIndex', 'SupplyCompanyController@index');
Route::get('SupplyCompanyIndex/{page_num}', 'SupplyCompanyController@index');
Route::post('SupplyCompanyIndex', 'SupplyCompanyController@index');
Route::post('SupplyCompanyIndex/{page_num}', 'SupplyCompanyController@index');
Route::get('SupplyCompanyCreate', 'SupplyCompanyController@create');
Route::post('SupplyCompanyConfirm', 'SupplyCompanyController@confirm');
Route::post('SupplyCompanyComplete', 'SupplyCompanyController@complete');
Route::post('SupplyCompanyEditComplete', 'SupplyCompanyController@editComplete');
Route::post('SupplyCompanyAjaxAddStandard', 'SupplyCompanyController@AjaxAddStandard');

// 仕入先店舗関連
Route::get('SupplyShopEdit/{supply_company_id}', 'SupplyShopController@edit');
Route::get('SupplyShopIndex', 'SupplyShopController@index');
Route::get('SupplyShopIndex/{page_num}', 'SupplyShopController@index');
Route::post('SupplyShopIndex', 'SupplyShopController@index');
Route::post('SupplyShopIndex/{page_num}', 'SupplyShopController@index');
Route::get('SupplyShopCreate', 'SupplyShopController@create');
Route::post('SupplyShopConfirm', 'SupplyShopController@confirm');
Route::post('SupplyShopComplete', 'SupplyShopController@complete');
Route::post('SupplyShopEditComplete', 'SupplyShopController@editComplete');
Route::post('SupplyShopAjaxAddStandard', 'SupplyShopController@AjaxAddStandard');

// 売上先企業関連
Route::get('SaleCompanyEdit/{supply_company_id}', 'SaleCompanyController@edit');
Route::get('SaleCompanyIndex', 'SaleCompanyController@index');
Route::get('SaleCompanyIndex/{page_num}', 'SaleCompanyController@index');
Route::post('SaleCompanyIndex', 'SaleCompanyController@index');
Route::post('SaleCompanyIndex/{page_num}', 'SaleCompanyController@index');
Route::get('SaleCompanyCreate', 'SaleCompanyController@create');
Route::post('SaleCompanyConfirm', 'SaleCompanyController@confirm');
Route::post('SaleCompanyComplete', 'SaleCompanyController@complete');
Route::post('SaleCompanyEditComplete', 'SaleCompanyController@editComplete');
Route::post('SaleCompanyAjaxAddStandard', 'SaleCompanyController@AjaxAddStandard');

// 売上先店舗関連
Route::get('SaleShopEdit/{supply_company_id}', 'SaleShopController@edit');
Route::get('SaleShopIndex', 'SaleShopController@index');
Route::get('SaleShopIndex/{page_num}', 'SaleShopController@index');
Route::post('SaleShopIndex', 'SaleShopController@index');
Route::post('SaleShopIndex/{page_num}', 'SaleShopController@index');
Route::get('SaleShopCreate', 'SaleShopController@create');
Route::post('SaleShopConfirm', 'SaleShopController@confirm');
Route::post('SaleShopComplete', 'SaleShopController@complete');
Route::post('SaleShopEditComplete', 'SaleShopController@editComplete');
Route::post('SaleShopAjaxAddStandard', 'SaleShopController@AjaxAddStandard');

// 仕入登録
Route::get('SupplySlipIndex', 'SupplySlipController@index');
Route::get('SupplySlipIndex/{page_num}', 'SupplySlipController@index');
Route::post('SupplySlipIndex', 'SupplySlipController@index');
Route::post('SupplySlipIndex/{page_num}', 'SupplySlipController@index');
Route::get('SupplySlipCreate', 'SupplySlipController@create');
Route::post('SupplySlipAjaxAddSlip', 'SupplySlipController@AjaxAddSlip');
Route::post('SaleSlipAjaxChangeProductId', 'SupplySlipController@AjaxChangeProductId');
Route::post('AjaxAutoCompleteSupplyCompany', 'SupplySlipController@AjaxAutoCompleteSupplyCompany');
Route::post('AjaxSetSupplyCompany', 'SupplySlipController@AjaxSetSupplyCompany');
Route::post('AjaxAutoCompleteSupplyShop', 'SupplySlipController@AjaxAutoCompleteSupplyShop');
Route::post('AjaxSetSupplyShop', 'SupplySlipController@AjaxSetSupplyShop');
Route::post('AjaxAutoCompleteProduct', 'SupplySlipController@AjaxAutoCompleteProduct');
Route::post('AjaxSetProduct', 'SupplySlipController@AjaxSetProduct');
Route::post('AjaxAutoCompleteStandard', 'SupplySlipController@AjaxAutoCompleteStandard');
Route::post('AjaxSetStandard', 'SupplySlipController@AjaxSetStandard');
Route::post('AjaxAutoCompleteQuality', 'SupplySlipController@AjaxAutoCompleteQuality');
Route::post('AjaxSetQuality', 'SupplySlipController@AjaxSetQuality');
Route::post('AjaxAutoCompleteOriginArea', 'SupplySlipController@AjaxAutoCompleteOriginArea');
Route::post('AjaxSetOriginArea', 'SupplySlipController@AjaxSetOriginArea');
Route::post('AjaxAutoCompleteStaff', 'SupplySlipController@AjaxAutoCompleteStaff');
Route::post('AjaxSetStaff', 'SupplySlipController@AjaxSetStaff');
Route::post('AjaxAutoCompleteDelivery', 'SupplySlipController@AjaxAutoCompleteDelivery');
Route::post('AjaxSetDelivery', 'SupplySlipController@AjaxSetDelivery');
Route::post('registerSupplySlips', 'SupplySlipController@registerSupplySlips');
Route::get('SupplySlipEdit/{supply_slip_id}', 'SupplySlipController@edit');
Route::post('editRegisterSupplySlips', 'SupplySlipController@editRegister');
Route::post('getOrderSupplyUnitPrice', 'SupplySlipController@getOrderSupplyUnitPrice');

// 売上登録
Route::get('SaleSlipIndex', 'SaleSlipController@index');
Route::get('SaleSlipIndex/{page_num}', 'SaleSlipController@index');
Route::post('SaleSlipIndex', 'SaleSlipController@index');
Route::post('SaleSlipIndex/{page_num}', 'SaleSlipController@index');
Route::get('SaleSlipCreate', 'SaleSlipController@create');
Route::post('SaleSlipAjaxAddSlip', 'SaleSlipController@AjaxAddSlip');
Route::post('SaleSlipAjaxChangeProductId', 'SaleSlipController@AjaxChangeProductId');
Route::post('AjaxAutoCompleteSaleCompany', 'SaleSlipController@AjaxAutoCompleteSaleCompany');
Route::post('AjaxSetSaleCompany', 'SaleSlipController@AjaxSetSaleCompany');
Route::post('AjaxAutoCompleteSaleShop', 'SaleSlipController@AjaxAutoCompleteSaleShop');
Route::post('AjaxSetSaleShop', 'SaleSlipController@AjaxSetSaleShop');
Route::post('AjaxAutoCompleteProduct', 'SaleSlipController@AjaxAutoCompleteProduct');
Route::post('AjaxSetProduct', 'SaleSlipController@AjaxSetProduct');
Route::post('AjaxAutoCompleteStandard', 'SaleSlipController@AjaxAutoCompleteStandard');
Route::post('AjaxSetStandard', 'SaleSlipController@AjaxSetStandard');
Route::post('AjaxAutoCompleteQuality', 'SaleSlipController@AjaxAutoCompleteQuality');
Route::post('AjaxSetQuality', 'SaleSlipController@AjaxSetQuality');
Route::post('AjaxAutoCompleteOriginArea', 'SaleSlipController@AjaxAutoCompleteOriginArea');
Route::post('AjaxSetOriginArea', 'SaleSlipController@AjaxSetOriginArea');
Route::post('AjaxAutoCompleteStaff', 'SaleSlipController@AjaxAutoCompleteStaff');
Route::post('AjaxSetStaff', 'SaleSlipController@AjaxSetStaff');
Route::post('AjaxAutoCompleteDelivery', 'SaleSlipController@AjaxAutoCompleteDelivery');
Route::post('AjaxSetDelivery', 'SaleSlipController@AjaxSetDelivery');
Route::post('registerSaleSlips', 'SaleSlipController@registerSaleSlips');
Route::get('SaleSlipEdit/{supply_slip_id}', 'SaleSlipController@edit');
Route::post('editRegisterSaleSlips', 'SaleSlipController@editRegister');
Route::post('AjaxShowSupplySlip', 'SaleSlipController@AjaxShowSupplySlip');
Route::post('getOrderSaleUnitPrice', 'SaleSlipController@getOrderSaleUnitPrice');

// 入出金管理関連
Route::get('WithdrawalIndex', 'WithdrawalController@index');
Route::post('WithdrawalIndex', 'WithdrawalController@index');
Route::get('WithdrawalCreate', 'WithdrawalController@create');
Route::post('WithdrawalCreate', 'WithdrawalController@create');
Route::get('WithdrawalEdit/{withdrawal_id}', 'WithdrawalController@edit');
Route::post('editRegisterWithdrawal', 'WithdrawalController@editRegisterWithdrawal');
Route::post('AjaxSearchSupplySlips', 'WithdrawalController@AjaxSearchSupplySlips');
Route::post('registerWithdrawal', 'WithdrawalController@registerWithdrawal');
Route::get('DepositIndex', 'DepositController@index');
Route::post('DepositIndex', 'DepositController@index');
Route::get('DepositCreate', 'DepositController@create');
Route::post('DepositCreate', 'DepositController@create');
Route::post('AjaxSearchSaleSlips', 'DepositController@AjaxSearchSaleSlips');
Route::post('registerDeposit', 'DepositController@registerDeposit');
Route::get('DepositEdit/{deposit_id}', 'DepositController@edit');
Route::post('editRegisterDeposit', 'DepositController@editRegisterDeposit');
Route::post('invoiceOutput', 'DepositController@invoiceOutput');
Route::get('invoiceOutput/{deposit_id}', 'DepositController@invoiceOutput');

// 在庫管理
Route::get('InventoryAdjustmentIndex', 'InventoryAdjustmentController@index');
Route::post('InventoryAdjustmentIndex', 'InventoryAdjustmentController@index');
Route::get('InventoryAdjustmentDetail/{link_id}', 'InventoryAdjustmentController@detail');
Route::post('InventoryAdjustmentEdit', 'InventoryAdjustmentController@edit');
Route::post('InventoryAdjustmentConfirm', 'InventoryAdjustmentController@confirm');
Route::post('editInventoryAdjustment', 'InventoryAdjustmentController@editInventoryAdjustment');

// 請求書出力
Route::get('InvoiceOutputIndex', 'InvoiceOutputController@index');
Route::post('InvoiceOutputIndex', 'InvoiceOutputController@index');
Route::get('InvoiceOutputOutput', 'InvoiceOutputController@output');
Route::post('InvoiceOutputOutput', 'InvoiceOutputController@output');

// ユーザー登録
Route::get('UserIndex', 'UserController@index');
Route::post('UserIndex', 'UserController@index');
Route::get('UserCreate', 'UserController@create');
Route::post('UserCreate', 'UserController@create');
Route::get('UserEdit/{user_id}', 'UserController@edit');
Route::get('UserRegister', 'UserController@register');
Route::post('UserRegister', 'UserController@register');
Route::post('UserEditRegister', 'UserController@editRegister');

// 発注単価登録
Route::get('OrderSupplyUnitPriceIndex', 'OrderSupplyUnitPriceController@index');
Route::post('OrderSupplyUnitPriceIndex', 'OrderSupplyUnitPriceController@index');
Route::get('OrderSupplyUnitPriceCreate', 'OrderSupplyUnitPriceController@create');
Route::post('OrderSupplyUnitPriceCreate', 'OrderSupplyUnitPriceController@create');
Route::get('OrderSupplyUnitPriceEdit/{order_supply_unit_price_id}', 'OrderSupplyUnitPriceController@edit');
Route::post('AjaxAddProduct', 'OrderSupplyUnitPriceController@AjaxAddProduct');
Route::get('OrderSupplyUnitPriceRegister', 'OrderSupplyUnitPriceController@register');
Route::post('OrderSupplyUnitPriceRegister', 'OrderSupplyUnitPriceController@register');
Route::post('OrderSupplyUnitPriceEditRegister', 'OrderSupplyUnitPriceController@editRegister');
Route::get('OrderSaleUnitPriceIndex', 'OrderSaleUnitPriceController@index');
Route::post('OrderSaleUnitPriceIndex', 'OrderSaleUnitPriceController@index');
Route::get('OrderSaleUnitPriceCreate', 'OrderSaleUnitPriceController@create');
Route::post('OrderSaleUnitPriceCreate', 'OrderSaleUnitPriceController@create');
Route::get('OrderSaleUnitPriceEdit/{order_sale_unit_price_id}', 'OrderSaleUnitPriceController@edit');
Route::post('AjaxAddSaleProduct', 'OrderSaleUnitPriceController@AjaxAddSaleProduct');
Route::get('OrderSaleUnitPriceRegister', 'OrderSaleUnitPriceController@register');
Route::post('OrderSaleUnitPriceRegister', 'OrderSaleUnitPriceController@register');
Route::post('OrderSaleUnitPriceEditRegister', 'OrderSaleUnitPriceController@editRegister');

// 日別一覧
Route::get('DailyPerformanceIndex', 'DailyPerformanceController@index');
Route::post('DailyPerformanceIndex', 'DailyPerformanceController@index');

// 期間実績一覧
Route::get('PeriodPerformanceIndex', 'PeriodPerformanceController@index');
Route::post('PeriodPerformanceIndex', 'PeriodPerformanceController@index');

// 経費関連
Route::get('ExpenseBillingIndex', 'ExpenseBillingController@index');
Route::get('ExpenseBillingIndex/{page_num}', 'ExpenseBillingController@index');
Route::post('ExpenseBillingIndex', 'ExpenseBillingController@index');
Route::post('ExpenseBillingIndex/{page_num}', 'ExpenseBillingController@index');
Route::get('ExpenseBillingCreate', 'ExpenseBillingController@create');
Route::get('ExpenseBillingEdit/{user_id}', 'ExpenseBillingController@edit');
Route::get('ExpenseBillingregister', 'ExpenseBillingController@register');

// 営業情報関連
Route::get('todayDeliverySlips', 'SaleSlipNonAuthController@todayDeliverySlips');

// CSV upload
Route::get('csvUpload', 'CsvUploadController@create');
Route::post('AjaxUploadCsv', 'CsvUploadController@AjaxUploadCsv');

// 全ユーザ
Route::group(['middleware' => ['auth', 'can:user-higher']], function () {

});

// 管理者以上
Route::group(['middleware' => ['auth', 'can:admin-higher']], function () {
    // ユーザ登録
    //Route::get('/register', 'RegisterController@register')->name('register');
    //Route::post('/register', 'RegisterController@register')->name('register');
});

// システム管理者のみ
Route::group(['middleware' => ['auth', 'can:system-only']], function () {
});

// PDFテスト用
Route::get('pdfTest', 'PdfController@index');
