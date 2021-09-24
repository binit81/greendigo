<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::prefix('v1')->namespace('API')->group(function ()
{
    // Login
    Route::post('/login','AuthController@postLogin');
    // Register
    Route::post('/register','AuthController@postRegister');
    // Protected with APIToken Middleware
    Route::middleware('APIToken')->group(function ()
    {
        // Logout
        Route::post('/logout','AuthController@postLogout');
    });
});*/

//Route::get('admin/profile', function () { })->middleware('auth');
/*Route::post('api/ecommerce','CustomerController@index’)->middleware('');*/

// routes for which you need to be an authenticated user (at any level)

Route::post('listing', 'Auth\RegisterController@register')->name('registerPost');

Route::post('/fetch_Main_Navigation', ['as' => 'fetch_Main_Navigation', 'uses' => 'ecommerce\ecommerceController@fetch_Main_Navigation']);
Route::get('/searchSchool', ['as' => 'searchSchool', 'uses' => 'ecommerce\ecommerceController@searchSchool']);
Route::post('/fetch_schools', ['as' => 'fetch_schools', 'uses' => 'ecommerce\ecommerceController@fetch_schools']);
Route::post('/getPageType', ['as' => 'getPageType', 'uses' => 'ecommerce\ecommerceController@getPageType']);
Route::post('/getSchoolHeader', ['as' => 'getSchoolHeader', 'uses' => 'ecommerce\ecommerceController@getSchoolHeader']);
Route::post('/addtocart', ['as' => 'addtocart', 'uses' => 'ecommerce\ecommerceController@addtocart']);
Route::post('/getCartItems', ['as' => 'getCartItems', 'uses' => 'ecommerce\ecommerceController@getCartItems']);
Route::post('/getTotalCartQty', ['as' => 'getTotalCartQty', 'uses' => 'ecommerce\ecommerceController@getTotalCartQty']);

Route::post('/ajaxProducts', ['as' => 'ajaxProducts', 'uses' => 'ecommerce\ecommerceController@ajaxProducts']);
Route::post('/addtoBasket', ['as' => 'addtoBasket', 'uses' => 'ecommerce\ecommerceController@addtoBasket']);
Route::post('/myBasket_items', ['as' => 'myBasket_items', 'uses' => 'ecommerce\ecommerceController@myBasket_items']);
Route::post('/removeBasket', ['as' => 'removeBasket', 'uses' => 'ecommerce\ecommerceController@removeBasket']);
Route::post('/customer_register_form', ['as' => 'customer_register_form', 'uses' => 'ecommerce\ecommerceController@customer_register_form']);
Route::post('/customer_login_form', ['as' => 'customer_login_form', 'uses' => 'ecommerce\ecommerceController@customer_login_form']);
Route::post('/getBasketItems', ['as' => 'getBasketItems', 'uses' => 'ecommerce\ecommerceController@getBasketItems']);



Route::post('/getPageContent', ['as' => 'getPageContent', 'uses' => 'ecommerce\ecommerceController@getPageContent']);
Route::post('/verifyAccount', ['as' => 'verifyAccount', 'uses' => 'ecommerce\ecommerceController@verifyAccount']);

Route::get('/searchSchool_header', ['as' => 'searchSchool_header', 'uses' => 'ecommerce\ecommerceController@searchSchool_header']);


// User routes
/*Route::prefix('/{user}')->group(function ($userRoutes)
{
    $userRoutes->get('/', 'UserController@dashboard');
    $userRoutes->get('/dashboard', 'UserController@dashboard')->name('userDashboard');
    $userRoutes->get('/profile', 'UserController@profile')->name('userProfile');
    $userRoutes->get('/settings', 'UserController@settings')->name('userSettings');
    $userRoutes->get('/wishlist', 'UserController@wishlist')->name('userWishlist');

    $userRoutes->post('/profile/identity', 'UserController@updateIdentity')->name('userIdentityPost');
    $userRoutes->post('/profile/personal', 'UserController@updatePersonal')->name('userPersonalPost');
});*/




