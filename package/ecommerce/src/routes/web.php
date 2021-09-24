<?php

Route::get('ecommerce', function()
{
    return 'ecommerce';
});
Route::group(['namespace'=>'Retailcore\ecommerce\Http\Controllers','middleware' => ['web','auth']],Function()
{


Route::get('/ecommerce-index', 'ecommerce\ecommerceController@index')->name('ecommerce-index');

Route::get('/product_features', 'ecommerce\ecommerceController@product_features')->name('product_features');
Route::get('/page_manager', 'ecommerce\ecommerceController@page_manager')->name('page_manager');

Route::post('/EditPagePopup',['as' => 'EditPagePopup', 'uses'=> 'ecommerce\ecommerceController@EditPagePopup']);
Route::post('/addEditPage',['as' => 'addEditPage', 'uses'=> 'ecommerce\ecommerceController@addEditPage']);
Route::post('/addEditFeature',['as' => 'addEditFeature', 'uses'=> 'ecommerce\ecommerceController@addEditFeature']);
Route::post('/EditFeaturePopup',['as' => 'EditFeaturePopup', 'uses'=> 'ecommerce\ecommerceController@EditFeaturePopup']);
Route::post('/EditSubFeaturePopup',['as' => 'EditSubFeaturePopup', 'uses'=> 'ecommerce\ecommerceController@EditSubFeaturePopup']);

Route::post('/deleteFeature',['as' => 'deleteFeature', 'uses'=> 'ecommerce\ecommerceController@deleteFeature']);
Route::post('/deleteSubFeature',['as' => 'deleteSubFeature', 'uses'=> 'ecommerce\ecommerceController@deleteSubFeature']);


Route::post('/FeatureActive',['as' => 'FeatureActive', 'uses'=> 'ecommerce\ecommerceController@FeatureActive']);
Route::post('/FeatureSubActive',['as' => 'FeatureSubActive', 'uses'=> 'ecommerce\ecommerceController@FeatureSubActive']);

Route::post('/UpdateOrdering',['as' => 'UpdateOrdering', 'uses'=> 'ecommerce\ecommerceController@UpdateOrdering']);
Route::post('/UpdateOrderingSub',['as' => 'UpdateOrderingSub', 'uses'=> 'ecommerce\ecommerceController@UpdateOrderingSub']);



});

