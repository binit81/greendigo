<?php

Route::get('DiscountMaster', function()
{
    return 'DiscountMaster';
});
Route::group(['namespace'=>'Retailcore\DiscountMaster\Http\Controllers','middleware' => ['web','auth']],Function()
{


Route::get('discount_master', 'DiscountMasterController@index');
Route::get('search_flatproduct_data', ['as' => 'search_flatproduct_data', 'uses' => 'DiscountMasterController@search_flatproduct_data']);
Route::post('flatdiscount_create', ['as' => 'flatdiscount_create', 'uses' => 'DiscountMasterController@flatdiscount_create']);
Route::get('view_flatproducts', 'DiscountMasterController@view_flatproducts')->name('view_flatproducts');
Route::post('flatdiscount_delete',['as' => 'flatdiscount_delete', 'uses'=> 'DiscountMasterController@flatdiscount_delete']);
Route::get('datewise_flatdiscount_detail', ['as' => 'datewise_flatdiscount_detail', 'uses' => 'DiscountMasterController@datewise_flatdiscount_detail']);


});

