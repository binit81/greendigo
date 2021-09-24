<?php

Route::get('Referral_Points', function()
{
   return 'Referral_Points';
});
Route::group(['namespace'=>'Retailcore\Referral_Points\Http\Controllers','middleware' => ['web','auth']],Function()
{
//FLAT POINTS
    Route::get('/view_referral_points', 'referral_points\ReferralPointController@index')->name('view_referral_points');
    Route::post('/add_referral_point', 'referral_points\ReferralPointController@add_referral_point')->name('add_referral_point');

});

