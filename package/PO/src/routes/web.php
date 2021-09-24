<?php

Route::get('PO', function()
{
    return 'PO';
});
Route::group(['namespace'=>'Retailcore\PO\Http\Controllers','middleware' => ['web','auth']],Function()
{
//Issue PO
    Route::get('/issue_po',['as'=>'issue_po','uses' => 'purchase_order\PurchaseOrderController@index']);
    Route::get('/view_issue_po',['as'=>'issue_po','uses' => 'purchase_order\PurchaseOrderController@view_issue_po']);
    Route::get('/purchase_order_fetch_data', ['as' => 'purchase_order_fetch_data', 'uses' => 'purchase_order\PurchaseOrderController@purchase_order_fetch_data']);

    Route::post('/po_product_detail', ['as' => 'po_product_detail', 'uses' => 'purchase_order\PurchaseOrderController@po_product_detail']);
    Route::post('/add_purchase_order', ['as' => 'add_purchase_order', 'uses' => 'purchase_order\PurchaseOrderController@add_purchase_order']);
    Route::post('/view_po_detail', ['as' => 'view_po_detail', 'uses' => 'purchase_order\PurchaseOrderDetailController@view_po_detail']);
    Route::post('/edit_purchase_order', 'purchase_order\PurchaseOrderController@edit_purchase_order')->name('edit_purchase_order');
    Route::post('/delete_po', 'purchase_order\PurchaseOrderController@delete_po')->name('delete_po');
    Route::get('/po_report_export', ['as' => 'po_report_export', 'uses' => 'purchase_order\PurchaseOrderController@po_report_export']);
    Route::get('/print_po', ['as' => 'print_po', 'uses' => 'purchase_order\PurchaseOrderController@print_po']);


    Route::get('/unique_barcode_issue_po',['as'=>'unique_barcode_issue_po','uses' => 'purchase_order\PurchaseOrderController@unique_po_index']);

    //FOR DOWNLOAD PO TEMPLATE

   Route::get('po_template','purchase_order\PurchaseOrderController@po_template')->name('po_template');

   //FOR CHECKING PO DETAIL USING BARCODE AT TIME OF UPLOAD PO EXCEL
   Route::post('/po_check',['as'=>'po_check','uses'=>'purchase_order\PurchaseOrderController@po_check']);

   //get product using barcode
    Route::post('/po_barcode_detail', ['as' => 'po_barcode_detail', 'uses' => 'purchase_order\PurchaseOrderController@po_barcode_detail']);

    //FOR UNIVERSAL PO.NO SEARCH
    Route::post('/po_number_search', ['as' => 'po_number_search', 'uses' => 'purchase_order\PurchaseOrderController@po_number_search']);


});

