<?php

Route::get('Products', function()
{
    return 'Products';
});
Route::group(['namespace'=>'Retailcore\Products\Http\Controllers','middleware' => ['web','auth']],Function()
{
//product and service
    Route::get('/product_show', 'product\ProductController@index')->name('product');
    Route::get('/product_data', 'product\ProductController@product_data')->name('product_data');
    Route::get('/service_data', 'product\ProductController@service_data')->name('service_data');
    Route::post('/product_create', ['as' => 'product_create', 'uses' => 'product\ProductController@product_create']);
    Route::post('/product_edit', ['as' => 'product_edit', 'uses' => 'product\ProductController@product_edit']);
    Route::post('/product_delete', ['as' => 'product_delete', 'uses' => 'product\ProductController@product_delete']);
    Route::get('/room_fetch_data', ['as' => 'room_fetch_data', 'uses' => 'product\ProductController@room_fetch_data']);
    Route::get('/product_fetch_data', ['as' => 'product_fetch_data', 'uses' => 'product\ProductController@product_fetch_data']);
    Route::post('/product_check', ['as' => 'product_check', 'uses' => 'product\ProductController@product_check']);
    Route::post('/products_update_check', ['as' => 'products_update_check', 'uses' => 'product\ProductController@products_update_check']);
    Route::post('/update_product_data', ['as' => 'update_product_data', 'uses' => 'product\ProductController@update_product_data']);
    Route::post('/inward_product_detail', ['as' => 'inward_product_detail', 'uses' => 'product\ProductController@inward_product_detail']);
    Route::post('/import_inward_product_detail', ['as' => 'import_inward_product_detail', 'uses' => 'product\ProductController@import_inward_product_detail']);
    Route::post('/product_name_search', ['as' => 'product_name_search', 'uses' => 'product\ProductController@product_name_search']);
    Route::post('/product_code_search', ['as' => 'product_code_search', 'uses' => 'product\ProductController@product_code_search']);
    Route::post('/sku_code_search', ['as' => 'sku_code_search', 'uses' => 'product\ProductController@sku_code_search']);
    Route::post('/product_barcode_search', ['as' => 'product_barcode_search', 'uses' => 'product\ProductController@product_barcode_search']);
    Route::get('/ProductremovePicture', ['as' => 'ProductremovePicture', 'uses' => 'product\ProductController@ProductremovePicture']);
    Route::get('/get_productImages', ['as' => 'get_productImages', 'uses' => 'product\ProductController@get_productImages']);



    //product export
    Route::get('/product_export', ['as' => 'product_export', 'uses' => 'product\ProductController@product_export']);
    //products which client want to update that excel file
    Route::get('/product_update_export', ['as' => 'product_update_export', 'uses' => 'product\ProductController@product_update_export']);

    //Product DOWNLOAD EXCEL TEMPLATE
    Route::get('product_template','product\ProductController@product_template')->name('product_template');
    Route::post('import_products_check','product\ProductController@import_products_check')->name('import_products_check');
    Route::post('inward_unique_product_check','product\ProductController@inward_unique_product_check')->name('inward_unique_product_check');

    //brand
    Route::get('/brand_show', 'product\BrandController@index')->name('brand');
    Route::post('/brand_create', ['as' => 'brand_create', 'uses' => 'product\BrandController@brand_create']);
    Route::get('/brand_edit', ['as' => 'brand_edit', 'uses' => 'product\BrandController@brand_edit']);
    Route::get('/brand_delete', ['as' => 'brand_delete', 'uses' => 'product\BrandController@brand_delete']);
    //for product module listing of brand
    Route::get('/get_brand', ['as' => 'get_brand', 'uses' => 'product\BrandController@get_brand']);

    //colour
    Route::get('/colour_show', 'product\ColourController@index')->name('colour');
    Route::post('/colour_create', ['as' => 'colour_create', 'uses' => 'product\ColourController@colour_create']);
    Route::get('/colour_edit', ['as' => 'colour_edit', 'uses' => 'product\ColourController@colour_edit']);
    Route::get('/colour_delete', ['as' => 'colour_delete', 'uses' => 'product\ColourController@colour_delete']);
    //get colour list for product module
    Route::get('/get_colour', ['as' => 'get_colour', 'uses' => 'product\ColourController@get_colour']);


    //size
    Route::get('/size_show', 'product\SizeController@index')->name('size');
    Route::post('/size_create', ['as' => 'size_create', 'uses' => 'product\SizeController@size_create']);
    Route::get('/size_edit', ['as' => 'size_edit', 'uses' => 'product\SizeController@size_edit']);
    Route::get('/size_delete', ['as' => 'size_delete', 'uses' => 'product\SizeController@size_delete']);
    //get size list for product module
    Route::get('/get_size', ['as' => 'get_size', 'uses' => 'product\SizeController@get_size']);

    //uqc
    Route::get('/uqc_show', 'product\UqcController@index')->name('uqc');
    Route::post('/uqc_create', ['as' => 'uqc_create', 'uses' => 'product\UqcController@uqc_create']);
    Route::get('/uqc_edit', ['as' => 'uqc_edit', 'uses' => 'product\UqcController@uqc_edit']);
    Route::get('/uqc_delete', ['as' => 'uqc_delete', 'uses' => 'product\UqcController@uqc_delete']);
    //get uqc list for product module
    Route::get('/get_uqc', ['as' => 'get_uqc', 'uses' => 'product\UqcController@get_uqc']);


    //category
    Route::get('/category_show', 'product\CategoryController@index')->name('category');
    Route::post('/category_create', ['as' => 'category_create', 'uses' => 'product\CategoryController@category_create']);
    Route::get('/category_edit', ['as' => 'category_edit', 'uses' => 'product\CategoryController@category_edit']);
    Route::get('/category_delete', ['as' => 'category_delete', 'uses' => 'product\CategoryController@category_delete']);
    //Category list for product module
    Route::get('/get_category', ['as' => 'get_category', 'uses' => 'product\CategoryController@get_category']);


    //Sub Category
    Route::get('/subcategory_show', 'product\SubcategoryController@index')->name('subcategory');
    Route::post('/subcategory_create', ['as' => 'subcategory_create', 'uses' => 'product\SubcategoryController@subcategory_create']);
    Route::get('/subcategory_edit', ['as' => 'subcategory_edit', 'uses' => 'product\SubcategoryController@subcategory_edit']);
    Route::get('/subcategory_delete', ['as' => 'subcategory_delete', 'uses' => 'product\SubcategoryController@subcategory_delete']);
    //for getting subcategory based on category for product module
    Route::post('/get_subcategory', ['as' => 'get_subcategory', 'uses' => 'product\SubcategoryController@get_subcategory']);


    //FOR GETTING DEPENDENCY RECORD OF PRODUCT
    Route::post('/product_dependency',['as' => 'product_dependency','uses' => 'product\ProductController@product_dependency']);
    //END OF GETTING DEPENDENCY RECORD OF PRODUCT

    //FOR GETING SIZE DATA
     Route::get('/view_size', ['as' => 'view_size', 'uses' => 'product\SizeController@index']);

     //FOR GETING COLOR DATA
     Route::get('/view_colour', ['as' => 'view_colour', 'uses' => 'product\ColourController@index']);


    Route::get('/product_features', ['as' => 'product_features', 'uses' => 'product\ProductFeaturesController@index']);

    //FOR ADD DYNAMIC PRODUCT FEATURES
    Route::post('/productfeatures_create', ['as' => 'product_features', 'uses' => 'product\ProductFeaturesController@productfeatures_create']);

     Route::post('/getfeatures', ['as' => 'getfeatures', 'uses' => 'product\ProductFeaturesController@getfeatures']);

    Route::get('/lowstock_report', 'product\StockreportController@lowstock_report')->name('lowstock_report');
    Route::get('/search_lowstock',['as' => 'search_lowstock','uses' => 'product\StockreportController@search_lowstock']);
    Route::get('/lowstock_data',['as' => 'lowstock_data','uses' => 'product\StockreportController@lowstock_data']);
    Route::get('/exportlowstock_details',['as' => 'exportlowstock_details', 'uses'=> 'product\StockreportController@exportlowstock_details']);

    //get parent of feature
    Route::post('/get_parent_of_feature', ['as' => 'get_parent_of_feature', 'uses' => 'product\ProductFeaturesController@get_parent_of_feature']);

    //get summery of product
   // Route::post('/product_summery', ['as' => 'product_summery', 'uses' => 'product\ProductController@product_summery']);
    Route::get('/product_summary',['as' =>'product_summary','uses'=>'product\ProductController@product_summary']);
    Route::get('/product_summary_search',['as' =>'product_summary_search','uses'=>'product\ProductController@product_summary_search']);


    //EXPORT PRODUCT SUMMARY
    Route::get('/product_summary_export', ['as' => 'product_summary_export', 'uses' => 'product\ProductController@product_summary_export']);

  
    Route::get('/product_summary_report', ['as' => 'product_summary_report', 'uses' => 'product\ProductController@product_summary']);
  //FOR ENTRY IN PRICE MASTER
    Route::get('/insert_product_pricemaster', ['as' => 'insert_product_pricemaster', 'uses' => 'product\ProductController@insert_product_pricemaster']);

});

