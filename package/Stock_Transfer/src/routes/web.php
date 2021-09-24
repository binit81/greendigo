<?php

Route::get('Stock_Transfer', function()
{
    return 'Stock_Transfer';
});
Route::group(['namespace'=>'Retailcore\Stock_Transfer\Http\Controllers','middleware' => ['web','auth']],Function()
{
	//STOCK TRANSFER
    Route::get('/stock_transfer', 'stock_transfer\StockTransferController@index')->name('stock_transfer');

   	Route::get('/stock_transfer_view', 'stock_transfer\StockTransferController@stock_transfer_view')->name('stock_transfer_view');
   	Route::get('/stock_transfer_fetch_data', 'stock_transfer\StockTransferController@stock_transfer_fetch_data')->name('stock_transfer_fetch_data');

    Route::get('/stock_transfer_detail_view', 'stock_transfer\StockTransferController@stock_transfer_detail_view')->name('stock_transfer_detail_view');
    Route::get('/stock_transfer_detail_fetch_data', 'stock_transfer\StockTransferController@stock_transfer_detail_fetch_data')->name('stock_transfer_detail_fetch_data');


   	Route::post('/search_batchdetail', 'stock_transfer\StockTransferController@search_batchdetail')->name('search_batchdetail');

    Route::post('/edit_stock_transfer', 'stock_transfer\StockTransferController@edit_stock_transfer')->name('edit_stock_transfer');

    // Route::get('/get_store', 'stock_transfer\StockTransferController@get_store')->name('get_store');

   	Route::post('/searchproduct', ['as' => 'searchproduct', 'uses' => 'stock_transfer\StockTransferController@searchproduct']);

   	Route::post('/stock_transfer_product_detail', ['as' => 'product_detail', 'uses' => 'stock_transfer\StockTransferController@product_detail']);
   	Route::post('/search_pricedetail', ['as' => 'search_pricedetail', 'uses' => 'Stock_Transfer\StockTransferController@search_pricedetail']);
   	Route::post('/stock_create', ['as' => 'stock_create', 'uses' => 'stock_transfer\StockTransferController@stock_create']);


    //for stock transfer inward
    Route::get('/stock_transfer_inward', 'stock_transfer\StockTransferInwardController@index')->name('stock_transfer_inward');
    Route::get('/stock_transfer_inward_fetch_data', 'stock_transfer\StockTransferInwardController@stock_transfer_inward_fetch_data')->name('stock_transfer_inward_fetch_data');
    Route::post('/view_stock_inward_detail', 'stock_transfer\StockTransferInwardController@view_stock_inward_detail')->name('view_stock_inward_detail');

    //for taking stock inward
    Route::post('/take_stock_inward', 'stock_transfer\StockTransferInwardController@take_stock_inward')->name('take_stock_inward');



 Route::post('/stock_transfer_number_search', ['as' => 'stock_transfer_number_search', 'uses' => 'stock_transfer\StockTransferInwardController@stock_transfer_number_search']);
 Route::post('/stock_transfer_no_warehouse_filter', ['as' => 'stock_transfer_no_warehouse_filter', 'uses' => 'stock_transfer\StockTransferInwardController@stock_transfer_no_warehouse_filter']);
  //for view btn popup
  Route::post('/view_products_stock_transfer', ['as' => 'view_products_stock_transfer', 'uses' => 'stock_transfer\StockTransferController@view_products_stock_transfer']);
});

