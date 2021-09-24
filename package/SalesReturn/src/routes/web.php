<?php

Route::get('SalesReturn', function()
{
    return 'SalesReturn';
});
Route::group(['namespace'=>'Retailcore\SalesReturn\Http\Controllers','middleware' => ['web','auth']],Function()
{


//sales return
Route::get('sales_return', 'ReturnBillController@index');
Route::post('returnbill_data',['as' => 'returnbill_data' , 'uses'=> 'ReturnBillController@returnbill_data']);
Route::post('returnbillsecond_data',['as' => 'returnbillsecond_data' , 'uses'=> 'ReturnBillController@returnbillsecond_data']);
Route::post('returnbilling_create', ['as' => 'returnbilling_create', 'uses' => 'ReturnBillController@returnbilling_create']);
Route::post('returnbillingprint_create', ['as' => 'returnbillingprint_create', 'uses' => 'ReturnBillController@returnbillingprint_create']);
Route::post('billno_search',['as' => 'billno_search', 'uses'=> 'ReturnBillController@billno_search']);
Route::get('returned_products', 'ReturnbillProductController@index');
Route::post('restock_products', ['as' => 'restock_products', 'uses' => 'ReturnbillProductController@restock_products']);
Route::get('viewreturn_data',['as' => 'viewreturn_data', 'uses'=> 'ReturnbillProductController@viewreturn_data']);
Route::get('returned_products', 'ReturnbillProductController@index');
Route::post('restock_products', ['as' => 'restock_products', 'uses' => 'ReturnbillProductController@restock_products']);
Route::get('viewreturn_data',['as' => 'viewreturn_data', 'uses'=> 'ReturnbillProductController@viewreturn_data']);
Route::get('datewise_returnproduct_detail', ['as' => 'datewise_returnproduct_detail', 'uses' => 'ReturnbillProductController@datewise_returnproduct_detail']);
Route::post('edit_returnbill','ReturnBillController@edit_returnbill')->name('edit_returnbill');
Route::post('manualproduct_search',['as' => 'manualproduct_search', 'uses'=> 'ReturnBillController@manualproduct_search']);
Route::post('manualproduct_detail',['as' => 'manualproduct_detail' , 'uses'=> 'ReturnBillController@manualproduct_detail']);
Route::post('returncustomer_detail',['as' => 'returncustomer_detail' , 'uses'=> 'ReturnBillController@returncustomer_detail']);
Route::post('returnproduct_search',['as' => 'returnproduct_search', 'uses'=> 'ReturnBillController@returnproduct_search']);

Route::post('consignbill_search',['as' => 'consignbill_search', 'uses'=> 'ReturnBillController@consignbill_search']);
Route::post('returnconsigncustomer_detail',['as' => 'returnconsigncustomer_detail' , 'uses'=> 'ReturnBillController@returnconsigncustomer_detail']);
Route::post('manualconsignproduct_search',['as' => 'manualconsignproduct_search', 'uses'=> 'ReturnBillController@manualconsignproduct_search']);
Route::post('manualconsignproduct_detail',['as' => 'manualconsignproduct_detail' , 'uses'=> 'ReturnBillController@manualconsignproduct_detail']);
Route::post('returnconsignbillsecond_data',['as' => 'returnconsignbillsecond_data' , 'uses'=> 'ReturnBillController@returnconsignbillsecond_data']);
Route::post('returnconsignbill_data',['as' => 'returnconsignbill_data' , 'uses'=> 'ReturnBillController@returnconsignbill_data']);


});

