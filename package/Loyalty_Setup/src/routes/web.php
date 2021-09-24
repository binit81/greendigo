<?php

Route::get('Loyalty_Setup', function()
{
    return 'Loyalty_Setup';
});
Route::group(['namespace'=>'Retailcore\Loyalty_Setup\Http\Controllers','middleware' => ['web','auth']],Function()
{
//GST SLABS
    Route::get('/loyalty_setup', 'Loyalty_Setup\LoyaltySetupController@index')->name('loyalty_setup');
    Route::get('/gst_slabs_data', 'Loyalty_Setup\GstSlabsMasterController@gst_slabs_data')->name('gst_slabs_data');
    Route::post('/add_loyalty_setup', ['as' => 'add_loyalty_setup', 'uses' => 'Loyalty_Setup\LoyaltySetupController@add_loyalty_setup']);



});

