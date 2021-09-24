<?php

Route::get('ProductAge_Range', function()
{
    return 'ProductAge_Range';
});
Route::group(['namespace'=>'Retailcore\ProductAge_Range\Http\Controllers','middleware' => ['web','auth']],Function()
{
//GST SLABS
    Route::get('productage_range', 'ProductageRangeController@index')->name('productage_range');
    Route::get('age_range_data', 'ProductageRangeController@age_range_data')->name('age_range_data');
    Route::post('agerange_create', ['as' => 'agerange_create', 'uses' => 'ProductageRangeController@agerange_create']);
    Route::post('agerange_edit', ['as' => 'agerange_edit', 'uses' => 'ProductageRangeController@agerange_edit']);
    Route::post('agerange_delete', ['as' => 'agerange_delete', 'uses' => 'ProductageRangeController@agerange_delete']);
    Route::get('/gst_slabs_fetch_data', ['as' => 'gst_slabs_fetch_data', 'uses' => 'GST_Slabs\GstSlabsMasterController@gst_slabs_fetch_data']);



});

