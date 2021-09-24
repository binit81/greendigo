<?php

Route::get('Supplier', function()
{
    return 'Supplier';
});
Route::group(['namespace'=>'Retailcore\Supplier\Http\Controllers','middleware' => ['web','auth']],Function()
{
//SUPPLIER
    Route::get('/supplier', 'supplier\SupplierCompanyInfoController@index')->name('supplier');
    Route::post('/add_supplier', ['as' => 'add_supplier', 'uses' => 'supplier\SupplierCompanyInfoController@add_supplier']);
    Route::post('/supplier_edit', ['as' => 'supplier_edit', 'uses' => 'supplier\SupplierCompanyInfoController@supplier_edit']);
    Route::get('/supplier_data', 'supplier\SupplierCompanyInfoController@supplier_data')->name('supplier_data');
    Route::post('/supplier_delete', ['as' => 'supplier_delete', 'uses' => 'supplier\SupplierCompanyInfoController@supplier_delete']);
    Route::get('/supplier_payment', 'supplier\SupplierOutstandingDetailController@index')->name('supplier_payment');
    Route::get('/supplier_debit_fetch_data', 'supplier\SupplierPaymentreceiptsController@supplier_debit_fetch_data')->name('supplier_debit_fetch_data');
    Route::get('/supplier_fetch_data', ['as' => 'supplier_fetch_data', 'uses' => 'supplier\SupplierCompanyInfoController@supplier_fetch_data']);
    //supplier outstanding payment
    Route::get('/list_outstanding_payment', ['as' => 'list_outstanding_payment', 'uses' => 'supplier\SupplierOutstandingDetailController@list_outstanding_payment']);
    Route::post('/save_supplier_debitdetail', ['as' => 'save_supplier_debitdetail', 'uses' => 'supplier\SupplierPaymentDetailController@save_supplier_debitdetail']);
    Route::get('/supplier_payment_receipt', ['as' => 'supplier_payment_receipt', 'uses' => 'supplier\SupplierPaymentreceiptsController@index']);
    Route::post('/supplier_payment_delete', ['as' => 'supplier_payment_delete', 'uses' => 'supplier\SupplierPaymentreceiptsController@supplier_payment_delete']);
    Route::get('/supplier_debit_receipt_refresh', 'supplier\SupplierPaymentreceiptsController@supplier_debit_receipt_refresh')->name('supplier_debit_receipt_refresh');
    Route::post('/view_amount_payable_detail', ['as' => 'view_amount_payable_detail', 'uses' => 'supplier\SupplierOutstandingDetailController@view_amount_payable_detail']);

     //FOR GETTING DEPENDENCY RECORD OF SUPPLIER
    Route::post('/supplier_dependency',['as' => 'supplier_dependency','uses' => 'supplier\SupplierCompanyInfoController@supplier_dependency']);
    //END OF GETTING DEPENDENCY RECORD OF SUPPLIER


     //DOWNLOAD SUPPlIER TEMPALTE
    Route::get('supplier_tmpate','supplier\SupplierCompanyInfoController@download_supplier_tmpate')->name('download_supplier_tmpate');


    //for add supplier using excel
    Route::post('/supplier_check',['as'=>'supplier_check','uses'=>'supplier\SupplierCompanyInfoController@supplier_check']);
    //for search supplier payment
    Route::get('datewise_supplierpaymentdetail', ['as' => 'datewise_supplierpaymentdetail', 'uses' => 'supplier\SupplierOutstandingDetailController@datewise_supplierpaymentdetail']);
    //for export supplier payment detail
    Route::get('exportpaymentsummary_data', ['as' => 'exportpaymentsummary_data', 'uses' => 'supplier\SupplierOutstandingDetailController@exportpaymentsummary_data']);
});

