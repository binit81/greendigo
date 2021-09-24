<?php

Route::get('Consignment', function()
{
    return 'Consignment';
});
Route::group(['namespace'=>'Retailcore\Consignment\Http\Controllers','middleware' => ['web','auth']],Function()
{

//Consignment
Route::get('consign_challan', 'ConsignBillController@index')->name('Consignment');
Route::post('consignbill_create', ['as' => 'consignbill_create', 'uses' => 'ConsignBillController@consignbill_create']);
Route::post('consignbillprint_create', ['as' => 'consignbillprint_create', 'uses' => 'ConsignBillController@consignbillprint_create']);
Route::get('view_consignchallan', 'ConsignBillController@view_consignment')->name('view_consignment');
Route::get('view_datewise_consigndata', ['as' => 'view_datewise_consigndata', 'uses' => 'ConsignBillController@view_datewise_consigndata']);
Route::post('view_consignment_popup', ['as' => 'view_consignment_popup', 'uses' => 'ConsignBillController@view_consignment_popup']);
Route::post('previous_consignment', ['as' => 'previous_consignment', 'uses' => 'ConsignBillController@previous_consignment']);
Route::post('next_consignment', ['as' => 'next_consignment', 'uses' => 'ConsignBillController@next_consignment']);
Route::get('consignno_search',['as' => 'consignno_search', 'uses'=> 'ConsignBillController@consignno_search']);
Route::post('edit_consignbill','ConsignBillController@edit_consignbill')->name('edit_consignbill');
Route::post('makeconsignment_bill', 'ConsignBillController@makeconsignment_bill')->name('makeconsignment_bill');

Route::post('view_returnconsignment_popup', ['as' => 'view_returnconsignment_popup', 'uses' => 'ConsignBillController@view_returnconsignment_popup']);
Route::post('rprevious_consignment', ['as' => 'rprevious_consignment', 'uses' => 'ConsignBillController@rprevious_consignment']);
Route::post('rnext_consignment', ['as' => 'rnext_consignment', 'uses' => 'ConsignBillController@rnext_consignment']);




});

