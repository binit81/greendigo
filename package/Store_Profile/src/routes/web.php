<?php

Route::get('Store_Profile', function()
{
    return 'Store_Profile';
});
Route::group(['namespace'=>'Retailcore\Store_Profile\Http\Controllers','middleware' => ['web','auth']],Function()
{
	//STORE PROFILE
    Route::get('/store_profile', 'store_profile\CompanyRelationshipTreeController@index')->name('store_profile');
    Route::get('/view_store', 'store_profile\CompanyRelationshipTreeController@view_store_data')->name('view_store_data');
   	Route::post('/edit_store', ['as' => 'edit_store', 'uses' => 'store_profile\CompanyRelationshipTreeController@edit_store']);

   	Route::post('/view_store_popup', ['as' => 'view_store_popup', 'uses' => 'store_profile\CompanyRelationshipTreeController@view_store_popup']);
    Route::get('/view_store_fetch_data', ['as' => 'view_store_fetch_data', 'uses' => 'store_profile\CompanyRelationshipTreeController@view_store_fetch_data']);
    Route::post('/get_store_list', ['as' => 'get_store_list', 'uses' => 'store_profile\CompanyRelationshipTreeController@get_store_list']);


});

