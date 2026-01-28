<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubCategoryController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\UserController;
use App\Models\Store;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//product section
Route::any('products', 'Api\ProductController@list');
//store section
Route::get('store', 'Api\StoreController@list');
Route::post('store/create', 'Api\StoreController@store');
Route::post('store/verify-otp', 'Api\StoreController@verifyOtp');
Route::post('store/invoices', 'Api\StoreController@invoices');
Route::get('task-store-list/{userId}', 'Api\StoreController@taskStoreList');
Route::post('store/createnote', 'Api\StoreController@createnote');
Route::post('store/listnote', 'Api\StoreController@listnote');
Route::get('download-invoice/{invoice_no}', 'Api\StoreController@downloadinvoice');
Route::get('store/unpaid-payments', 'Api\StoreController@unpaid_payments');
//store visit
Route::post('store-visit/start', 'Api\ActivityController@storeVisitStore');
Route::post('store-visit/locationupdate', 'Api\ActivityController@updateVisitLocation');
Route::post('store-visit/end', 'Api\ActivityController@storeVisitEnd');
//staff auth
Route::post('get-otp', 'Api\UserController@logincheck');
Route::post('login-via-otp', 'Api\UserController@otpcheck');

// Location Update
Route::post('location-update', 'Api\UserController@location_update');
// Check User Existng Mac Id
Route::post('check-user-macId', 'Api\UserController@checkUserMacId');

Route::post('logout', 'Api\UserController@logout');
//no order reason
Route::post('no-order-reason/add', 'Api\StoreController@noorder');
// cart
Route::post('simpleBulkAddcart', 'Api\CartController@simpleBulkAddcart');
Route::get('cart/delete/{id}', 'Api\CartController@delete');
Route::get('cart/show/{userId}', 'Api\CartController@show');
Route::get('cart/clear/{userId}', 'Api\CartController@clear');
Route::post('cart/add-price-request', 'Api\CartController@addPriceRequest')->name('addPriceRequest');
Route::post('cart/requested-products', 'Api\CartController@requestedProducts')->name('requestedProducts');
// order
Route::post('place-order', 'Api\OrderController@placeorder');
Route::get('order/view/{userid}/{storeid}', 'Api\OrderController@view');
Route::post('order/list', 'Api\OrderController@list');
// payment collection
Route::post('payment-collection/add','Api\PaymentCollectionController@store');
Route::post('payment-collection/list-by-store', 'Api\PaymentCollectionController@listByStore');
// scan
Route::post('scan/box', 'Api\ScanController@box');
Route::post('scan/stockout', 'Api\ScanController@stockout');
Route::get('scan/pslist', 'Api\ScanController@ps_list');
Route::post('scan/ret_pro_in', 'Api\ScanController@ret_pro_in');
// purchase return
Route::get('scan/purchase_return_list', 'Api\ScanController@purchase_return_list');
Route::post('scan/purchase_return_stockout', 'Api\ScanController@purchase_return_stockout');
/* * ====================Partner API================== * */
//
// expense
Route::get('expense/types', 'Api\ExpenseController@types');
Route::get('expense/list/{id?}', 'Api\ExpenseController@list');
Route::post('expense/add', 'Api\ExpenseController@add');
Route::post('depotexpense/add', 'Api\ExpenseController@add_depot_expense');

Route::post('search-user', 'Api\LedgerController@search_user');
Route::post('ledger-user', 'Api\LedgerController@list');
Route::get('ledger-csv', 'Api\LedgerController@csv');
Route::get('ledger-pdf', 'Api\LedgerController@pdf');
