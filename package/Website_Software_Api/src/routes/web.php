<?php

Route::get('Website_Software_Api', function()
{
    return 'Website_Software_Api';
});
Route::post('/product_listing', ['as' => 'product_listing', 'uses' => 'website_software\WebsiteSoftwareController@product_listing']);
Route::get('/payment_method', ['as' => 'payment_method', 'uses' => 'website_software\WebsiteSoftwareController@payment_method']);
Route::post('/billing_requestdata', ['as' => 'billing_requestdata', 'uses' => 'website_software\WebsiteSoftwareController@billing_requestdata']);


