<?php

Route::get('StoreReturn', function()
{
    return 'StoreReturn';
});
Route::group(['namespace'=>'Retailcore\StoreReturn\Http\Controllers','middleware' => ['web','auth']],Function()
{


Route::get('store_return', 'StorereturnProductController@index');
Route::post('storereturnproduct_search',['as' => 'storereturnproduct_search', 'uses'=> 'StorereturnProductController@storereturnproduct_search']);
Route::post('storereturnproduct_detail',['as' => 'storereturnproduct_detail' , 'uses'=> 'StorereturnProductController@storereturnproduct_detail']);
Route::post('return_issueno_search',['as' => 'return_issueno_search' , 'uses'=> 'StorereturnProductController@return_issueno_search']);
Route::post('storereturnissueno_detail',['as' => 'storereturnissueno_detail' , 'uses'=> 'StorereturnProductController@storereturnissueno_detail']);
Route::post('storereturn_create', ['as' => 'storereturn_create', 'uses' => 'StorereturnProductController@storereturn_create']);
Route::get('view_storereturn', 'StoreReturnController@index')->name('view_storereturn');
Route::post('edit_storerreturnbill','StorereturnProductController@edit_storerreturnbill')->name('edit_storerreturnbill');
Route::post('view_storereturn_popup', ['as' => 'view_storereturn_popup', 'uses' => 'StoreReturnController@view_storereturn_popup']);
Route::post('previous_storereturn', ['as' => 'previous_storereturn', 'uses' => 'StoreReturnController@previous_storereturn']);
Route::post('next_storereturn', ['as' => 'next_storereturn', 'uses' => 'StoreReturnController@next_storereturn']);
Route::get('datewise_storereturnproduct_detail',['as' => 'datewise_storereturnproduct_detail', 'uses'=> 'StoreReturnController@datewise_storereturnproduct_detail']);
Route::get('manage_storereturn', 'StoreReturnController@manage_storereturn');
Route::get('datewise_manage_storereturn',['as' => 'datewise_manage_storereturn', 'uses'=> 'StoreReturnController@datewise_manage_storereturn']);
Route::post('storerestock_products', ['as' => 'storerestock_products', 'uses' => 'StoreReturnController@storerestock_products']);

});

