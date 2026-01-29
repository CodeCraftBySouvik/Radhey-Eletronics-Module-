<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::middleware(['guest'])->group(function() {
    Route::view('/', 'admin.auth.login')->name('home');
    Route::get('/login', 'Admin\Auth\AuthController@index');
});


Route::prefix('cron')->name('cron.')->group(function(){
    Route::get('/generateTask', 'CronController@generateTask')->name('generateTask');
    Route::get('/generate_commission', 'CronController@generate_commission')->name('generate_commission');
    Route::get('/test', 'CronController@test')->name('test');
    Route::get('/daily-ledger-send', 'CronController@daily_ledger_send')->name('daily_ledger_send');
    Route::get('/auto-invoice-generate/{id}', 'CronController@auto_invoice_generate')->name('auto_invoice_generate');
    Route::get('/daily-invoices-send', 'CronController@daily_invoices_send')->name('daily_invoices_send');
    Route::get('/delete/whatsapp-message-logs', 'CronController@weekly_whatsapp_message_logs_delete')->name('weekly_whatsapp_message_logs_delete');
});


Route::prefix('test')->name('test.')->group(function(){    
    Route::get('/invoicePayments', 'TestController@invoicePayments')->name('invoicePayments');
    Route::get('/staffCommission', 'TestController@staffCommission')->name('staffCommission');
    Route::get('/save', 'TestController@save')->name('save');
    Route::get('/saveStoreVisitLedger', 'TestController@saveStoreVisitLedger')->name('saveStoreVisitLedger');
    Route::any('/removeLedgerRecord', 'TestController@removeLedgerRecord')->name('removeLedgerRecord');
    Route::get('/checkDistance', 'TestController@checkDistance')->name('checkDistance');
    Route::get('/generateStaffSalary', 'TestController@generateStaffSalary')->name('generateStaffSalary');
    Route::get('/index', 'TestController@index')->name('index');
    Route::get('/downloadInvoicePDF/{invoice_no?}', 'TestController@downloadInvoicePDF');
    Route::get('/resetInvoicePayments', 'TestController@resetInvoicePayments');
    Route::get('/getCurrentStockBarcodes', 'TestController@getCurrentStockBarcodes');
    Route::get('/change_numbering', 'TestController@change_numbering');
});

Auth::routes();

// admin guard
Route::prefix('admin')->name('admin.')->group(function() {
    Route::middleware(['guest'])->group(function() {
        Route::view('/login', 'admin.auth.login')->name('login');
        Route::post('/check', 'Admin\Auth\AuthController@login')->name('login.check');
    });

    Route::middleware(['auth:web'])->group(function() {
        // dashboard
        Route::get('/home', 'Admin\AdminController@home')->name('home');
        Route::get('/profile', 'Admin\ProfileController@index')->name('admin.profile');
		Route::post('/profile', 'Admin\ProfileController@update')->name('admin.profile.update');
		Route::post('/changepassword', 'Admin\ProfileController@changePassword')->name('admin.profile.changepassword');

        // category
        Route::prefix('category')->name('category.')->group(function () {
            Route::get('/', 'Admin\CategoryController@index')->name('index');            
            Route::post('/store', 'Admin\CategoryController@store')->name('store');
            Route::get('/{id}/view', 'Admin\CategoryController@show')->name('view');
            Route::post('/{id}/update', 'Admin\CategoryController@update')->name('update');
            Route::get('/{id}/status', 'Admin\CategoryController@status')->name('status');
            Route::post('/bulkSuspend', 'Admin\CategoryController@bulkSuspend')->name('bulkSuspend');
        });

        // sub-category
        Route::prefix('subcategory')->name('subcategory.')->group(function () {
            Route::get('/', 'Admin\SubCategoryController@index')->name('index');
            Route::post('/store', 'Admin\SubCategoryController@store')->name('store');
            Route::get('/{id}/view', 'Admin\SubCategoryController@show')->name('view');
            Route::post('/{id}/update', 'Admin\SubCategoryController@update')->name('update');
            Route::get('/{id}/status', 'Admin\SubCategoryController@status')->name('status');
            Route::post('/bulkSuspend', 'Admin\SubCategoryController@bulkSuspend')->name('bulkSuspend');
        });
        
        // Expense Type
        Route::prefix('expense')->name('expense.')->group(function () {
            Route::get('/{parent_id}', 'Admin\ExpenseController@index')->name('index');
            Route::post('/store', 'Admin\ExpenseController@store')->name('store');
            Route::get('/{id}/view', 'Admin\ExpenseController@show')->name('view');
            Route::get('/{id}/status', 'Admin\ExpenseController@status')->name('status');
            Route::post('/{id}/update', 'Admin\ExpenseController@update')->name('update');
            Route::post('/bulkSuspend', 'Admin\ExpenseController@bulkSuspend')->name('bulkSuspend');
        });

        // purchaseorder
        Route::prefix('purchaseorder')->name('purchaseorder.')->group(function () {
            Route::get('/', 'Admin\PurchaseOrderController@index')->name('index');
            Route::get('/create/{supplier_id?}', 'Admin\PurchaseOrderController@create')->name('create');
            Route::post('/store', 'Admin\PurchaseOrderController@store')->name('store');
            Route::get('/{id}/view', 'Admin\PurchaseOrderController@show')->name('view');
            Route::get('/{id}/edit', 'Admin\PurchaseOrderController@edit')->name('edit');
            Route::post('/update', 'Admin\PurchaseOrderController@update')->name('update');
            
            Route::get('/showboxes/{id}', 'Admin\PurchaseOrderController@showboxes')->name('showboxes');            
            Route::post('/saveinventory', 'Admin\PurchaseOrderController@saveinventory')->name('saveinventory');
            Route::get('/{id}/grn', 'Admin\PurchaseOrderController@grn')->name('grn');
            
            Route::get('/archiveBox/{id}/{product_id}/{barcode_no}/{getQueryString?}', 'Admin\PurchaseOrderController@archiveBox')->name('archiveBox');
            Route::get('/{id}/archived', 'Admin\PurchaseOrderController@archived')->name('archived');
            Route::Post('/getProductsSupplier', 'Admin\PurchaseOrderController@getProductsSupplier')->name('getProductsSupplier');
            Route::post('/checkScannedboxes', 'Admin\PurchaseOrderController@checkScannedboxes')->name('checkScannedboxes');
            Route::post('/pobulkscan', 'Admin\PurchaseOrderController@pobulkscan')->name('pobulkscan');
            Route::get('/{id}/pdf','Admin\PurchaseOrderController@pdf')->name('pdf');
        });

        // purchasereturn
        Route::prefix('purchasereturn')->name('purchasereturn.')->group(function () {
            Route::get('/list', 'Admin\PurchaseReturnController@list')->name('list');
            Route::get('/add', 'Admin\PurchaseReturnController@add')->name('add');
            Route::post('/save', 'Admin\PurchaseReturnController@save')->name('save');
            Route::get('/edit/{id}', 'Admin\PurchaseReturnController@edit')->name('edit');
            Route::post('/update', 'Admin\PurchaseReturnController@update')->name('update');
            Route::get('/details/{id}', 'Admin\PurchaseReturnController@details')->name('details');
            Route::get('/pdf/{id}', 'Admin\PurchaseReturnController@pdf')->name('pdf');
            Route::get('/cancel/{id}', 'Admin\PurchaseReturnController@cancel')->name('cancel');
        });

        // grn
        Route::prefix('grn')->name('grn.')->group(function() {
            Route::get('/list', 'Admin\StockController@listgrn')->name('list');
            Route::get('/view/{id}', 'Admin\StockController@viewgrn')->name('view');
            Route::get('/barcodes/{id}', 'Admin\StockController@barcodes')->name('barcodes');
            Route::get('/searchbarcodes', 'Admin\StockController@searchbarcodes')->name('searchbarcodes');
            Route::get('/edit-amount/{id}', 'Admin\StockController@edit_amount')->name('edit-amount');
            Route::post('/update-amount/{id}', 'Admin\StockController@update_amount')->name('update-amount');
        });

        // order
        Route::prefix('order')->name('order.')->group(function () {
            Route::get('/', 'Admin\OrderController@index')->name('index');
            Route::post('/store', 'Admin\OrderController@store')->name('store');
            Route::get('/{id}/view', 'Admin\OrderController@show')->name('view');
            Route::get('/{id}/edit', 'Admin\OrderController@edit')->name('edit');
            Route::post('/{id}/update', 'Admin\OrderController@update')->name('update');
            Route::get('/{id}/status/{status}', 'Admin\OrderController@status')->name('status');
        });

        // packingslip
        Route::prefix('packingslip')->name('packingslip.')->group(function(){
            Route::get('/', 'Admin\PackingslipController@index')->name('index');
			Route::get('/order_in_progress', 'Admin\PackingslipController@order_in_progress')->name('order_in_progress');
            Route::get('/{id}/add', 'Admin\PackingslipController@add')->name('add');
            Route::post('/save', 'Admin\PackingslipController@save')->name('save');
            Route::get('/edit/{id}', 'Admin\PackingslipController@edit')->name('edit');
            Route::post('/update/{id}', 'Admin\PackingslipController@update')->name('update');
            Route::get('/{slip_no}/get_pdf', 'Admin\PackingslipController@get_pdf')->name('get_pdf');
            Route::get('/{invoice_no}/view_invoice', 'Admin\PackingslipController@view_invoice')->name('view_invoice');
            Route::get('/{id}/raise_invoice_form', 'Admin\PackingslipController@raise_invoice_form')->name('raise_invoice_form');
            Route::post('/save_invoice', 'Admin\PackingslipController@save_invoice')->name('save_invoice');
            Route::post('/upload_trn', 'Admin\PackingslipController@upload_trn')->name('upload_trn');
            Route::get('/{id}/view_goods_stock', 'Admin\PackingslipController@view_goods_stock')->name('view_goods_stock');
            Route::post('/checkScannedboxes', 'Admin\PackingslipController@checkScannedboxes')->name('checkScannedboxes');
            Route::post('/save_goods_out', 'Admin\PackingslipController@save_goods_out')->name('save_goods_out');
            Route::get('/{order_id}/{product_id}/{ctns}/{pcs}/pieces', 'Admin\PackingslipController@pieces')->name('pieces');                        
            Route::get('/revoke/{id}', 'Admin\PackingslipController@revoke')->name('revoke');
        });

        // invoice
        Route::prefix('invoice')->name('invoice.')->group(function(){
            Route::get('/', 'Admin\InvoiceController@index')->name('index');
            Route::get('/payments/{id}/{user_id?}/{user_type?}', 'Admin\InvoiceController@payments')->name('payments');
            Route::get('/edit/{id}', 'Admin\InvoiceController@edit')->name('edit');
            Route::post('/update', 'Admin\InvoiceController@update')->name('update');
            Route::get('/revoke/{id}', 'Admin\InvoiceController@revoke')->name('revoke');
            Route::get('/barcode/{id}', 'Admin\InvoiceController@barcode')->name('barcode');
        });

        
        Route::get('/barcodes/{id}', 'Admin\PurchaseOrderController@barcodes')->name('barcodes');

        // store
        Route::prefix('store')->name('store.')->group(function() {
            Route::get('/', 'Admin\StoreController@index')->name('index');
            Route::get('/create', 'Admin\StoreController@create')->name('create');
            Route::post('/store', 'Admin\StoreController@store')->name('store');
            Route::get('/{id}/edit', 'Admin\StoreController@edit')->name('edit');
            Route::get('/{id}/view', 'Admin\StoreController@show')->name('view');
            Route::post('/{id}/update', 'Admin\StoreController@update')->name('update');
            Route::get('/{id}/status', 'Admin\StoreController@status')->name('status');
            Route::get('/{id}/approve', 'Admin\StoreController@approve')->name('approve');
            Route::post('/bulkSuspend', 'Admin\StoreController@bulkSuspend')->name('bulkSuspend');
            
        });
       
        // user
        Route::prefix('user')->name('user.')->group(function() {
            Route::get('/list/{userType}', 'Admin\UserController@index')->name('index');
            Route::get('/create/{user_type}', 'Admin\UserController@create')->name('create');
            Route::post('/store', 'Admin\UserController@store')->name('store');
            Route::get('/{id}/view', 'Admin\UserController@show')->name('view');
            Route::get('/{userType}/{id}/edit', 'Admin\UserController@edit')->name('edit');
            Route::post('/{id}/update', 'Admin\UserController@update')->name('update');
            Route::get('/{id}/{userType}/status', 'Admin\UserController@status')->name('status');
            Route::get('/{id}/verification', 'Admin\UserController@verification')->name('verification');
            Route::get('/{id}/{userType}/delete', 'Admin\UserController@destroy')->name('delete');
            Route::post('/supplierBulkSuspend', 'Admin\UserController@supplierBulkSuspend')->name('supplierBulkSuspend');
            
        });

        // staff
        Route::prefix('staff')->name('staff.')->group(function() {
            Route::get('/', 'Admin\UserController@staffList')->name('index');
            // Route::view('/create', 'admin.staff.create')->name('create');
            Route::get('/create', 'Admin\UserController@createStaff')->name('create');
            Route::post('/store', 'Admin\UserController@storeStaff')->name('store');
            Route::get('/{id}/view', 'Admin\UserController@staffshow')->name('view');
            Route::get('/{id}/edit', 'Admin\UserController@editStaff')->name('edit');
           
            Route::get('/{id}/listtask', 'Admin\UserController@listtask')->name('listtask');
            Route::get('/{id}/createtask', 'Admin\UserController@createtask')->name('createtask');
            Route::post('/savetask','Admin\UserController@savetask')->name('savetask');
            Route::get('/{user_id}/{id}/edittask', 'Admin\UserController@edittask')->name('edittask');
            Route::post('/assignTask', 'Admin\UserController@assignTask')->name('assignTask');
            Route::post('/updatetask','Admin\UserController@updatetask')->name('updatetask');

            Route::post('/{id}/update', 'Admin\UserController@staffupdate')->name('update');
            Route::get('/{id}/delete', 'Admin\UserController@staffdestroy')->name('delete');
            Route::get('/{id}/status', 'Admin\UserController@staffstatus')->name('status');
            Route::post('/bulkSuspend', 'Admin\UserController@bulkSuspend')->name('bulkSuspend');
            Route::get('/{id}/show-areas', 'Admin\UserController@show_areas')->name('show-areas');
            Route::post('/save_areas', 'Admin\UserController@save_areas')->name('save_areas');
            Route::get('/{id}/logout_device', 'Admin\UserController@logout_device')->name('logout_device');
        });

        // designation
        Route::prefix('designation')->name('designation.')->group(function() {
            Route::get('/', 'Admin\DesignationController@index')->name('index');
            Route::get('/{id}/view', 'Admin\DesignationController@show')->name('view');
            Route::get('/{id}/status', 'Admin\DesignationController@status')->name('status');
            Route::post('/{id}/update', 'Admin\DesignationController@update')->name('update');
            Route::post('/store', 'Admin\DesignationController@store')->name('store');
            Route::post('/bulkSuspend', 'Admin\DesignationController@bulkSuspend')->name('bulkSuspend');            
        });

        // staff's store visit
        Route::prefix('visit')->name('visit.')->group(function() {
            Route::get('/{user_id}', 'Admin\VisitController@index')->name('index');
            Route::get('/details/{visit_id}', 'Admin\VisitController@details')->name('details');
            Route::get('/mapview/{visit_id}', 'Admin\VisitController@mapview')->name('mapview');
            Route::get('/mapdirection/{visit_id}', 'Admin\VisitController@mapdirection')->name('mapdirection');
        });

        //user activity
        Route::prefix('useractivity')->name('useractivity.')->group(function() {
            Route::get('/', 'Admin\ActivityController@index')->name('index');
            Route::get('/{id}/view', 'Admin\ActivityController@show')->name('view');
        });

        // product
        Route::prefix('product')->name('product.')->group(function () {
            Route::get('/list', 'Admin\ProductController@index')->name('index');            
            Route::post('/subcategoriesByCategory','Admin\ProductController@subcategoriesByCategory')->name('subcategoriesByCategory');
            Route::get('/create', 'Admin\ProductController@create')->name('create');
            Route::post('/store', 'Admin\ProductController@store')->name('store');
            Route::get('/{id}/view', 'Admin\ProductController@show')->name('view');            
            Route::get('/{id}/edit', 'Admin\ProductController@edit')->name('edit');
            Route::post('/update', 'Admin\ProductController@update')->name('update');
            Route::get('/{id}/status', 'Admin\ProductController@status')->name('status');
            Route::get('/{id}/delete', 'Admin\ProductController@destroy')->name('delete');
            Route::get('/{id}/image/delete', 'Admin\ProductController@destroySingleImage')->name('image.delete');
            Route::post('/bulkSuspend', 'Admin\ProductController@bulkSuspend')->name('bulkSuspend');
            
            Route::post('viewDetail','Admin\ProductController@viewDetail')->name('viewDetail');
            Route::post('viewDetailPcsPerCtn','Admin\ProductController@viewDetailPcsPerCtn')->name('viewDetailPcsPerCtn');
            Route::post('viewAllDetail','Admin\ProductController@viewAllDetail')->name('viewAllDetail');
            Route::post('/searchByName', 'Admin\ProductController@searchByName')->name('searchByName');
            Route::post('/searchByNameForStock', 'Admin\ProductController@searchByNameForStock')->name('searchByNameForStock');
        });
        
        // order
        Route::prefix('order')->name('order.')->group(function() {
            Route::get('/', 'Admin\OrderController@index')->name('index');
            Route::get('/add', 'Admin\OrderController@add')->name('add');
            Route::post('/store', 'Admin\OrderController@store')->name('store');
            Route::get('/{id}/view', 'Admin\OrderController@show')->name('view');
            Route::post('/{id}/update', 'Admin\OrderController@update')->name('update');
            Route::get('/{id}/status/{status}', 'Admin\OrderController@status')->name('status');

            Route::post('/store-threshold', 'Admin\OrderController@store_threshold')->name('store-threshold');
            Route::post('/update-threshold/{id}', 'Admin\OrderController@update_threshold')->name('update-threshold');
        });

        // threshold
        Route::prefix('threshold')->name('threshold.')->group(function() {
            

            Route::get('/list', 'Admin\ThresholdRequestController@index')->name('list');            
            Route::get('/{id}/view', 'Admin\ThresholdRequestController@view')->name('view');
            Route::post('/set-value', 'Admin\ThresholdRequestController@set_value')->name('set-value');
            Route::get('/{id}/view-requested-price-received-order', 'Admin\ThresholdRequestController@view_requested_price_received_order')->name('view-requested-price-received-order');
            Route::post('/save-requested-price-received-order', 'Admin\ThresholdRequestController@save_requested_price_received_order')->name('save-requested-price-received-order');


        });

        // paymentcollection
        Route::prefix('paymentcollection')->name('paymentcollection.')->group(function() {
            Route::get('/', 'Admin\PaymentCollectionController@index')->name('index');
            Route::get('/{id}/approve', 'Admin\PaymentCollectionController@approve')->name('approve');
            Route::get('/{id}/remove', 'Admin\PaymentCollectionController@remove')->name('remove');
            Route::get('/revoke/{id}', 'Admin\PaymentCollectionController@revoke')->name('revoke');
        });

        Route::prefix('ledger')->name('ledger.')->group(function(){            
            Route::any('/getUsersByType', 'Admin\LedgerController@getUsersByType')->name('getUsersByType');
            Route::post('/getRequiredExpenses', 'Admin\LedgerController@getRequiredExpenses')->name('getRequiredExpenses');
            Route::any('/getBankList', 'Admin\LedgerController@getBankList')->name('getBankList');
            Route::any('/storeSearch', 'Admin\LedgerController@storeSearch')->name('storeSearch');   Route::post('/searchCities', 'Admin\LedgerController@searchCities')->name('searchCities');
            Route::post('/getStoreLedgerAmount', 'Admin\LedgerController@getStoreLedgerAmount')->name('getStoreLedgerAmount');
        });

        Route::prefix('accounting')->name('accounting.')->group(function(){
            Route::get('/add_opening_balance/{type?}', 'Admin\AccountingController@add_opening_balance')->name('add-opening-balance');
            Route::post('/save_opening_balance', 'Admin\AccountingController@save_opening_balance')->name('save_opening_balance');
            Route::post('/save_opening_balance_partner', 'Admin\AccountingController@save_opening_balance_partner')->name('save_opening_balance_partner');
            Route::get('/add_expenses', 'Admin\AccountingController@add_expenses')->name('add_expenses');
            Route::get('/add_expense_partner_withdrawls/{id}', 'Admin\AccountingController@add_expense_partner_withdrawls')->name('add_expense_partner_withdrawls');
            Route::post('/save_expenses', 'Admin\AccountingController@save_expenses')->name('save_expenses');
            Route::get('/edit_partner_expense/{id}', 'Admin\AccountingController@edit_partner_expense')->name('edit_partner_expense');
            Route::post('/update_partner_expense/{id}', 'Admin\AccountingController@update_partner_expense')->name('update_partner_expense');
            Route::get('/add_payment_receipt/{paymentCollectionId?}', 'Admin\AccountingController@add_payment_receipt')->name('add_payment_receipt');
            Route::post('/save_payment_receipt', 'Admin\AccountingController@save_payment_receipt')->name('save_payment_receipt');
            Route::get('listopeningbalance', 'Admin\AccountingController@listopeningbalance')->name('listopeningbalance');
            Route::get('/{id}/deleteopeningbalance','Admin\AccountingController@deleteopeningbalance')->name('deleteopeningbalance');
            Route::post('/editopeningbalance','Admin\AccountingController@editopeningbalance')->name('editopeningbalance');
            Route::get('/add_partner_expense', 'Admin\AccountingController@add_partner_expense')->name('add_partner_expense');
            Route::post('/save_partner_expense', 'Admin\AccountingController@save_partner_expense')->name('save_partner_expense');
            Route::get('/edit_payment_receipt/{voucher_no}/{ledger_url?}', 'Admin\AccountingController@edit_payment_receipt')->name('edit_payment_receipt');
            Route::post('/update_payment_receipt', 'Admin\AccountingController@update_payment_receipt')->name('update_payment_receipt');
            
            Route::get('/list_expenses', 'Admin\AccountingController@list_expenses')->name('list_expenses');
            Route::get('/csv_export_expenses', 'Admin\AccountingController@csv_export_expenses')->name('csv_export_expenses');
            Route::get('/edit_expense/{id}', 'Admin\AccountingController@edit_expense')->name('edit_expense');
            Route::post('/update_expense', 'Admin\AccountingController@update_expense')->name('update_expense');

            Route::get('/add_staff_expense', 'Admin\AccountingController@add_staff_expense')->name('add_staff_expense');
            Route::post('/save_staff_expense', 'Admin\AccountingController@save_staff_expense')->name('save_staff_expense');

            Route::get('/list_bad_debt', 'Admin\AccountingController@list_bad_debt')->name('list_bad_debt');
            Route::get('/add_bad_debt', 'Admin\AccountingController@add_bad_debt')->name('add_bad_debt');
            Route::post('/save_bad_debt', 'Admin\AccountingController@save_bad_debt')->name('save_bad_debt');
			
			//Cashbook Module
			Route::get('/cashbook-module', 'Admin\AccountingController@cash_book_module')->name('cash_book_module');
			Route::get('/get-staff', 'Admin\AccountingController@get_staff')->name('get_staff');
			Route::get('/daily-cash-entry', 'Admin\AccountingController@day_cash_entry')->name('day_cash_entry');

        });

        // app settings

        Route::prefix('appsettings')->name('appsettings.')->group(function(){
            Route::get('/view', 'Admin\AppSettingsController@view')->name('view');
            Route::post('/save', 'Admin\AppSettingsController@save')->name('save');
        });

        // revenue
        Route::prefix('revenue')->name('revenue.')->group(function(){
            Route::get('/','Admin\RevenueController@details')->name('index');
            Route::get('/withdraw_form', 'Admin\RevenueController@withdraw_form')->name('withdraw_form');
            Route::post('/withdraw_partner_amount','Admin\RevenueController@withdraw_partner_amount')->name('withdraw_partner_amount');
            Route::get('/withdrawls', 'Admin\RevenueController@withdrawls')->name('withdrawls');
            Route::get('/delete_request/{id}', 'Admin\RevenueController@delete_request')->name('delete_request');
            Route::any('/downloadJournalCSV', 'Admin\RevenueController@downloadJournalCSV')->name('downloadJournalCSV');
        });

        // service slip
        Route::prefix('service-slip')->name('service-slip.')->group(function(){
            Route::get('/list', 'Admin\ServiceSlipController@index')->name('index');
            Route::get('/add', 'Admin\ServiceSlipController@add')->name('add');
            Route::post('/save', 'Admin\ServiceSlipController@save')->name('save');
            Route::post('/update', 'Admin\ServiceSlipController@update')->name('update');
            Route::get('/{slip_id?}/pdf', 'Admin\ServiceSlipController@pdf')->name('pdf');
        });

        // discount
        Route::prefix('discount')->name('discount.')->group(function(){
            Route::get('/list', 'Admin\DiscountController@list')->name('list');
            Route::get('/add', 'Admin\DiscountController@add')->name('add');
            Route::post('/save', 'Admin\DiscountController@save')->name('save');
            // Route::get('/{slip_id?}/pdf', 'Admin\DiscountController@pdf')->name('pdf');
            Route::get('/edit/{id}', 'Admin\DiscountController@edit')->name('edit');
            Route::post('/update/{id}', 'Admin\DiscountController@update')->name('update');
        });

        // attendance
        Route::prefix('attendance')->name('attendance.')->group(function(){
            Route::get('/view', 'Admin\AttendanceController@index')->name('view');
        });

        // returns
        Route::prefix('returns')->name('returns.')->group(function(){
            Route::get('/list', 'Admin\ReturnItemController@index')->name('list');
            Route::get('/view/{id}', 'Admin\ReturnItemController@details')->name('view');
            Route::get('/add', 'Admin\ReturnItemController@add')->name('add');
            Route::post('/save', 'Admin\ReturnItemController@store')->name('save');
            Route::get('/edit/{id}', 'Admin\ReturnItemController@edit')->name('edit');
            Route::post('/update/{id}', 'Admin\ReturnItemController@update')->name('update');
            Route::get('/edit-amount/{id}', 'Admin\ReturnItemController@edit_amount')->name('edit-amount');
            Route::post('/update-amount/{id}', 'Admin\ReturnItemController@update_amount')->name('update-amount');
            Route::get('/barcode/{id}', 'Admin\ReturnItemController@barcode')->name('barcode');
            Route::get('/goods-in/{id}', 'Admin\ReturnItemController@goods_in')->name('goods-in');
            Route::post('/returnbulkscan', 'Admin\ReturnItemController@returnbulkscan')->name('returnbulkscan');
            Route::post('/save_goods_in', 'Admin\ReturnItemController@save_goods_in')->name('save_goods_in');
            Route::post('/checkScannedboxes', 'Admin\ReturnItemController@checkScannedboxes')->name('checkScannedboxes');
            Route::get('/download-cash-slip/{order_no}', 'Admin\ReturnItemController@download_cash_slip')->name('download-cash-slip');
            Route::get('/cancel/{id}', 'Admin\ReturnItemController@cancel')->name('cancel');
        });

        // returns
        Route::prefix('stockaudit')->name('stockaudit.')->group(function(){
            Route::get('/list', 'Admin\StockAuditController@index')->name('list');
            Route::post('/upload_csv', 'Admin\StockAuditController@upload_csv')->name('upload_csv');
            Route::get('/get_uploaded_log_csv', 'Admin\StockAuditController@get_uploaded_log_csv')->name('get_uploaded_log_csv');
            Route::get('/view-final-stock/{entry_date}', 'Admin\StockAuditController@view_final_stock')->name('view-final-stock');
        });

        
        // report
        Route::prefix('report')->name('report.')->group(function(){
            Route::get('/cp-sp-report', 'Admin\ReportController@cp_sp_report')->name('cp-sp-report');
            Route::get('/cp-sp-csv', 'Admin\ReportController@cp_sp_csv')->name('cp-sp-csv');
            Route::get('/store-due-payment', 'Admin\ReportController@store_due_payment')->name('store-due-payment');
            Route::get('/store-due-csv', 'Admin\ReportController@store_due_csv')->name('store-due-csv');  
            Route::get('/sales-report', 'Admin\ReportController@sales_report')->name('sales-report');
            Route::get('/sales-report-pdf', 'Admin\ReportController@sales_report_pdf')->name('sales-report-pdf');
            Route::get('/sales-report-csv', 'Admin\ReportController@sales_report_csv')->name('sales-report-csv');
            Route::get('/sales-analysis', 'Admin\ReportController@sales_analysis')->name('sales-analysis');
            Route::post('/all-product-sales-analysis', 'Admin\ReportController@all_product_sales_analysis')->name('all-product-sales-analysis');
            Route::get('/sales-analysis-csv', 'Admin\ReportController@sales_analysis_csv')->name('sales-analysis-csv');
            Route::get('/staff-commission', 'Admin\ReportController@staff_commission')->name('staff-commission');
            Route::post('/save_commission_ledger', 'Admin\ReportController@save_commission_ledger')->name('save_commission_ledger');
            Route::get('/stock-report', 'Admin\ReportController@stock_report')->name('stock-report');
            Route::get('/stock-report-csv', 'Admin\ReportController@stock_report_csv')->name('stock-report-csv');
            Route::get('/stock-ledger', 'Admin\ReportController@stock_ledger')->name('stock-ledger');
            Route::get('/stock-ledger-csv', 'Admin\ReportController@stock_ledger_csv')->name('stock-ledger-csv');
            Route::get('/payment-receipt-report', 'Admin\ReportController@payment_receipt_report')->name('payment-receipt-report');
            Route::get('/payment-receipt-report-csv', 'Admin\ReportController@payment_receipt_report_csv')->name('payment-receipt-report-csv');
            Route::get('/save-current-stock', 'Admin\ReportController@save_current_stock')->name('save-current-stock');
            Route::get('/stock-audit-csv', 'Admin\ReportController@stock_audit_csv')->name('stock-audit-csv');
            Route::get('/store-notes', 'Admin\ReportController@store_notes')->name('store-notes');
            Route::get('/travel-report', 'Admin\ReportController@travel_report')->name('travel-report');

            Route::get('/monthly-commissionable-collections/{user_id}/{month}/{year}', 'Admin\ReportController@monthly_commissionable_collections')->name('monthly-commissionable-collections');

            Route::get('/choose-ledger-user', 'Admin\ReportController@user_ledger')->name('choose-ledger-user');
            Route::any('/user-ledger-pdf', 'Admin\ReportController@user_ledger_pdf')->name('user-ledger-pdf');
            Route::any('/user-ledger-csv', 'Admin\ReportController@user_ledger_csv')->name('user-ledger-csv');

            Route::get('/barcode-history', 'Admin\ReportController@barcode_history')->name('barcode-history');
            Route::get('/stock-log', 'Admin\ReportController@stock_log')->name('stock-log');
            Route::get('/stock-log-csv', 'Admin\ReportController@stock_log_csv')->name('stock-log-csv');
            
        });
        // report
        Route::prefix('whats-app')->group(function(){
            Route::get('/invoice/list', 'Admin\WhatsAppController@invoice_list')->name('whats-app.invoice_list');
            Route::post('/invoice/upload/tally-bill', 'Admin\WhatsAppController@upload_tally_bill')->name('whats-app.upload_tally_bill');
            Route::get('/invoice/upload/tally_bill_not_required', 'Admin\WhatsAppController@tally_bill_not_required')->name('whats-app.tally_bill_not_required');
            Route::post('/invoice/upload/lr-bill', 'Admin\WhatsAppController@upload_lr_bill')->name('whats-app.upload_lr_bill');
            Route::get('/invoice/upload/lr_bill_not_required', 'Admin\WhatsAppController@lr_bill_not_required')->name('whats-app.lr_bill_not_required');
            Route::get('/invoice/cancel/{id}', 'Admin\WhatsAppController@invoice_cancel')->name('whats-app.invoice_cancel');
            Route::get('/invoice/active/{id}', 'Admin\WhatsAppController@invoice_active')->name('whats-app.invoice_active');
            Route::get('/ledger/cancel/{id}', 'Admin\WhatsAppController@ledger_cancel')->name('whats-app.ledger_cancel');
            Route::get('/ledger/active/{id}', 'Admin\WhatsAppController@ledger_active')->name('whats-app.ledger_active');
            Route::get('/invoice/send-message/{invoice}/{id}', 'Admin\WhatsAppController@send_text_whatsapp_message')->name('whats-app.send_text_whatsapp_message');
            Route::get('/ledger/send-message', 'Admin\WhatsAppController@send_ledger_text_whatsapp_message')->name('whats-app.send_ledger_text_whatsapp_message');
            Route::get('/ledger-user', 'Admin\WhatsAppController@ledger_list')->name('whatsapp_ledger_user');
            Route::get('/choose-ledger-user', 'Admin\WhatsAppController@user_ledger')->name('whatsapp_choose_ledger_user');
            Route::get('/ledger-left-whatsapp-counter/{id}', 'Admin\WhatsAppController@getLedgerLeftWhatsappCounter')->name('getLedgerLeftWhatsappCounter');
            Route::get('/left-whatsapp-counter/{id}', 'Admin\WhatsAppController@getLeftWhatsappCounter')->name('getLeftWhatsappCounter');
            Route::post('/update-ledger-start-date', 'Admin\WhatsAppController@update_ledger_start_date')->name('update_ledger_start_date');
            Route::any('/user-ledger-pdf', 'Admin\WhatsAppController@whatsapp_user_ledger_pdf')->name('whatsapp-user-ledger-pdf');
        });
    });
});

Route::get('/home', 'HomeController@index')->name('home');

Route::post('/logout',function(Request $request){
    $redirect_url = isset($request->redirect_url)?$request->redirect_url:'';
    // dd($redirect_url);
    Auth::logout();
    if(!empty($redirect_url)){
        Session::flash('logout-message', 'Session timed out due to no activity !!! ');
        Session::flash('redirect_url', $redirect_url);
    }
    
    return redirect()->route('login');
})->name('logout');
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
