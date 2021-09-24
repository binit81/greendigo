var payment_detail_id ='';
var balance=0;
$('.debit_chck').click(function(e)
{
    var arr = [];

    var overall =   0;
    var overdetail ='';
    $('input[class=debit_chck]:checked').each(function (index)
    {
        arr.push($(this).data('id'));
    });

    $.each(arr,function(i,val)
    {
        overall += parseFloat($('#outstanding_amount_'+val+'').val());

    });


    $('#grandoverall').html(overall.toFixed(2));
    $('#total_amount_pay').val(overall.toFixed(2));
    $('#cash').val(overall.toFixed(2));



});
$('#total_amount_pay').keyup(function(e){

    var total_amount_pay  = $('#total_amount_pay').val();
    var grandoverall  = $('#grandoverall').html();

    if(Number(total_amount_pay)>Number(grandoverall))
    {
        toastr.error("Amount cannot be greater than the payment_detail_idected invoices");
        $('#total_amount_pay').val(grandoverall);
        return false;
    }

});

$('#makepayment').click(function(e) {
    var grandoverall =  $('#grandoverall').html();

    if(Number(grandoverall)==0)
    {
        toastr.error("Select Supplier for payment");
    }
    else
    {
        $("#supplier_debit_popup").modal('show');

    }
});

$("#debit_date").datepicker({
    format: 'dd-mm-yyyy',
    orientation: "bottom"
}).on('keypress paste', function (e) {
    e.preventDefault();
    return false;
});

$('#cheque').keyup(function(e){

    if(($('#cheque').val())>0)
    {
        if(($('#remarks').val())=='')
        {
            toastr.error("First enter Cheque no and Bank details");
            $('#cheque').val('');
            $('#remarks').focus();
            return false;
        }
        else
        {
            var cash                        =     $('#cash').val();
            var card                        =     $('#card').val();
            var cheque                      =     $('#cheque').val();
            var net_banking                 =     $('#net_banking').val();
            var wallet                      =     $('#wallet').val();
            var outstanding_amount          =     $('#outstanding_amount').val();
            var grand_total                 =     $('#total_amount_pay').val();
            var cash_balance    =      0;


            cash_balance    =     Number(grand_total)-Number(card)-Number(cheque)-Number(net_banking)-Number(wallet);

            if(Number(cash_balance)<0)
            {
                toastr.error("Amout cannot be greater than Total Sales_amount "+grand_total);

                $('#cheque').val(0);
                cash_balance    =     Number(grand_total)-Number(net_banking)-Number(wallet)-Number(card);
                alert(cash_balance)
                $('#cash').val(cash_balance.toFixed(0));
            }
            else
            {
                alert(cash_balance)
                $('#cash').val(cash_balance);
            }
        }
    }

});
$('#net_banking').keyup(function(e){

    if(($('#net_banking').val())>0)
    {
        if(($('#remarks').val())=='')
        {
            toastr.error("First enter Bank details");
            $('#remarks').focus();
            $('#net_banking').val('');

            return false;
        }
        else
        {
            var cash                        =     $('#cash').val();
            var card                        =     $('#card').val();
            var cheque                      =     $('#cheque').val();
            var net_banking                 =     $('#net_banking').val();
            var wallet                      =     $('#wallet').val();
            var outstanding_amount          =     $('#outstanding_amount').val();
            var grand_total                 =     $('#total_amount_pay').val();
            var cash_balance    =      0;


            cash_balance    =     Number(grand_total)-Number(card)-Number(cheque)-Number(net_banking)-Number(wallet);

            if(Number(cash_balance)<0)
            {

                toastr.error("Amout cannot be greater than Total Sales_amount "+grand_total);

                $('#net_banking').val(0);
                cash_balance    =     Number(grand_total)-Number(cheque)-Number(wallet)-Number(card);
                $('#cash').val(cash_balance);
            }
            else
            {

                $('#cash').val(cash_balance);
            }
        }

    }

});
$('#card').keyup(function(e){

    var cash                        =     $('#cash').val();
    var card                        =     $('#card').val();
    var cheque                      =     $('#cheque').val();
    var net_banking                 =     $('#net_banking').val();
    var wallet                      =     $('#wallet').val();
    var outstanding_amount          =     $('#outstanding_amount').val();
    var grand_total                 =     $('#total_amount_pay').val();
    var cash_balance    =      0;

    cash_balance    =     Number(grand_total)-Number(card)-Number(cheque)-Number(net_banking)-Number(wallet);


    if(Number(cash_balance)<0)
    {
        toastr.error("Amout cannot be greater than Total Sales_amount "+grand_total);

        $('#card').val(0);
        cash_balance    =     Number(grand_total)-Number(cheque)-Number(net_banking)-Number(wallet);
        $('#cash').val(cash_balance);

    }
    else
    {

        $('#cash').val(cash_balance);
    }




});
$('#wallet').keyup(function(e){

    var cash                        =     $('#cash').val();
    var card                        =     $('#card').val();
    var cheque                      =     $('#cheque').val();
    var net_banking                 =     $('#net_banking').val();
    var wallet                      =     $('#wallet').val();
    var outstanding_amount          =     $('#outstanding_amount').val();
    var grand_total                 =     $('#total_amount_pay').val();
    var cash_balance    =      0;


    cash_balance    =     Number(grand_total)-Number(card)-Number(cheque)-Number(net_banking)-Number(wallet);


    if(Number(cash_balance)<0)
    {
        toastr.error("Amout cannot be greater than Total Sales_amount "+grand_total);

        $('#wallet').val(0);
        cash_balance    =     Number(grand_total)-Number(cheque)-Number(net_banking)-Number(card);
        $('#cash').val(cash_balance);
    }
    else
    {

        $('#cash').val(cash_balance);
    }




});
$('#cash').keyup(function(e){

    var cash                        =     $('#cash').val();
    var card                        =     $('#card').val();
    var cheque                      =     $('#cheque').val();
    var net_banking                 =     $('#net_banking').val();
    var wallet                      =     $('#wallet').val();
    var outstanding_amount          =     $('#outstanding_amount').val();
    var grand_total                 =     $('#total_amount_pay').val();
    var cash_balance    =      0;


    cash_balance    =     Number(grand_total)-Number(card)-Number(cheque)-Number(net_banking)-Number(wallet);
    $('#cash').val(cash_balance);

    if(Number(cash_balance)<0)
    {
        toastr.error("Amout cannot be greater than Total Sales_amount "+grand_total);

        $('#cash').val(0);
        cash_balance    =     Number(grand_total)-Number(cheque)-Number(net_banking)-Number(wallet)-Number(card);
        $('#cash').val(cash_balance);
    }




});
$('#total_amount_pay').keyup(function(e){
    var grand_total   =  $('#total_amount_pay').val();
    $('#card').val('');
    $('#cheque').val('');
    $('#net_banking').val('');
    $('#wallet').val('');
    $('#debit_note').val('');
    $('#cash').val(grand_total);
});


$("#add_supplier_payment").click(function (e)
{
    var arraydetail = [];
    $(this).prop('disabled', true);
    var array = [];
    $('#outstanding_detail tr').each(function()
    {
       var row_id = $(this).data('id');
        if($(this).find('#check_'+row_id).is(':checked'))
        {
            if(row_id != undefined && row_id != '')
            {
                var arrayItem = {};
                arrayItem['supplier_payment_detail_id'] = row_id;
                arrayItem['inward_stock_id'] = $(this).find("#inward_stock_id_"+row_id).val();
                array.push(arrayItem);
            }
        }
    });

    arraydetail['debit_detail'] = array;

    var supplier_receipt = [];
    var paymentdetail = {};

    supplier_receipt.push({
        total_amount_pay : $("#total_amount_pay").val(),
        debit_date : $("#debit_date").val(),
        remarks : $("#remarks").val(),
        receipt_no : $("#receipt_no").val(),
        supplier_gst_id : $("#supplier_gst_id").val(),
        total : $(".ledgerbalance").html(),
    });


    var parr =[];
    $("#paymentmethoddiv").each(function()
    {
        var paymentarr = {};
        $(this).find('.row').each(function (index,item)
        {
            var paymentmethod = ($(this).find('input').attr('id'));

            if($("#"+paymentmethod).val() != '' && $("#"+paymentmethod).val() != 0)
            {
                var paymentid = $("#"+paymentmethod).data("id");
                   var supplier_debit_note_id = '';
                if(paymentid == 9)
                {
                    supplier_debit_note_id = $("#supplier_debit_note_id").val()
                }
                parr.push({
                    id: paymentid,
                    value: $("#"+paymentmethod).val(),
                    supplier_debit_note_id : supplier_debit_note_id
                });
            }
        });
    });

    arraydetail['payment_detail'] = parr;

    var data={
        'debit_detail' : array,
        'payment_detail' : parr,
        'supplier_receipt' : supplier_receipt
    };
    var dataType = "";
    var  url = "save_supplier_debitdetail";
    var type = "POST";
    callroute(url,type,dataType,data,function (data)
    {
        $("#add_supplier_payment").prop('disabled', true);
        var dta = JSON.parse(data);

        if(dta['Success'] == "True")
        {

            toastr.success(dta['Message']);
            window.location.href = dta['url'];


            //$("#sproduct_detail_record").empty('');

        }
        else
        {
            $("#add_supplier_payment").prop('disabled', true);
            toastr.error(dta['Message']);
        }
    })


});

$("#searchcustomerdata").keyup(function ()
{

    jQuery.noConflict();
    if($("#searchcustomerdata").val().length >= 1) {

        $("#searchcustomerdata").autocomplete({
            autoFocus: true,
            minLength: 1,
            source: function (request, response) {
                var url = "customer_search";
                var type = "POST";
                var dataType = '';
                var data = {
                    'search_val': $("#searchcustomerdata").val()
                };
                callroute(url,type,dataType,data, function (data) {


                    var searchdata = JSON.parse(data, true);
                    var html = '';
                    if (searchdata['Success'] == "True") {

                        var result = [];
                        searchdata['Data'].forEach(function (value) {
                            result.push({
                                label: value.customer_name + '_' + value.customer_mobile,
                                value: value.customer_name + '_' + value.customer_mobile,
                                id: value.customer_id
                            });
                        });

                        //push data into result array.and this array used for display suggetion
                        response(result);

                    }
                });
            },
            //this help to call a function when payment_detail_idect search suggetion
            payment_detail_idect: function (event, ui) {
                var id = ui.item.id;
                //call a getproductdetail function for getting product detail based on payment_detail_idected product from suggetion


            }
        });
    }
    else
    {
        $("#searchcustomerdata").empty();
    }

});
function deletereceipt(obj)
{
    if(confirm("Are You Sure want to delete this Credit Receipt?")) {

        var id                        =     $(obj).attr('id');
        var billid                    =     $(obj).attr('id').split('deletereceipt_')[1];


        if(billid.length > 0)
        {
            var data = {
                "deleted_id": billid
            };
            var url = "receipt_delete";
            var dataType = '';
            var type = "POST";
            callroute(url, type,dataType, data, function (data) {

                var dta = JSON.parse(data);

                if (dta['Success'] == "True")
                {
                    toastr.success(dta['Message']);
                    $("#viewbillform").trigger('reset');
                    resettable('viewreceipt_data');

                } else {
                    toastr.error(dta['Message']);
                }
            })
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}



//DEBIT NOTE

$("#debit_note").keypress(function () {
    return false;
});
$("#debit_note").focus(function ()
{
    var cash = $("#cash").val();

    if(cash == 0 || cash == '')
    {
        toastr.error("Add some amount in default payment method cash amount!");
        return false;
    }
    else
    {
        $("#supplierdebitnotepopup").modal('show');
    }
});


$("#supplier_debit_note").focusout(function(){
    var type = "POST";
    var url = "get_debit_note_amount";
    var dataType = '';
    var data={
        'debit_note_no' : $("#supplier_debit_note").val()
    }

    callroute(url,type,dataType,data,function (data) {

        var dta = JSON.parse(data);

        if(dta['Success']=="True")
        {
            var amount_detail = dta['Data'];

            if(amount_detail != null && amount_detail['total_cost_price'] != undefined && amount_detail['used_amount'] != undefined)
            {
                var edit_time_debit_add = $("#debit_note").val();
                var debit_amount = (((Number(amount_detail['total_cost_price']))-(Number(amount_detail['used_amount'])) + Number(edit_time_debit_add)));

                if(debit_amount == 0)
                {
                    toastr.error("This Debit note all amount was used!Select some other Debit Note!");
                    return false;
                }
                $("#supplier_debit_note_amount").val(debit_amount);
                $("#supplier_debit_note_amount_for_minus").val(debit_amount);
                $("#supplier_debit_note_id").val(amount_detail['debit_note_id']);
            }
            else{
                $("#debit_note_no").val('');
                toastr.error("Debit Note No. is invalid!");
                return false;
            }
        }
    });
});

$("#supplier_debit_note_issue_amount").keyup(function ()
{
    var total_amount = $("#supplier_debit_note_amount").val();

    var issue_amount = $("#supplier_debit_note_issue_amount").val();
    var minus_from = $("#supplier_debit_note_amount_for_minus").val();

    var with_minus_value = ((Number(minus_from))-(Number(issue_amount)));
    $("#supplier_debit_note_amount").val(with_minus_value);

    if(Number(issue_amount)>Number(minus_from))
    {
        toastr.error("Issue Amount can not be greater than "+ total_amount);
        $("#supplier_debit_note_issue_amount").val(0);
        $("#supplier_debit_note_amount").val(total_amount);
    }
    var inward_total_amt = $("#grand_total_disp").val();

    if(Number(issue_amount)>Number(inward_total_amt))
    {
        toastr.error("Issue Amount can not be greater than total amount "+ inward_total_amt);
        $("#supplier_debit_note_issue_amount").val(0);
        $("#supplier_debit_note_amount").val(total_amount);
    }
    var outstandingamt = $("#outstanding_amount").val();
    if(Number(issue_amount) > outstandingamt)
    {
        toastr.error("Issue Amount can not be greater than unpaid amount "+ outstandingamt);
        $("#supplier_debit_note_issue_amount").val(0);
        $("#supplier_debit_note_amount").val(minus_from);
    }


});

$("#supplier_save_debit_note").click(function ()
{
    var debit_note_issue_amt = $("#supplier_debit_note_issue_amount").val();

    if(debit_note_issue_amt != '')
    {
        $("#debit_note").val(debit_note_issue_amt);

        var outstanding_amount = ((Number($("#cash").val())) - (Number(debit_note_issue_amt)));

        if(outstanding_amount != '' || outstanding_amount == 0)
        {
            if (debit_note_issue_amt == 0)
            {
                var outstanding = ((Number($("#cash").val())) - Number(outstanding_amount));
                outstanding_amount = ((Number($("#cash").val())) + Number(outstanding));
            }

            $("#cash").val(outstanding_amount);
        } else {
            $("#supplier_debit_note").val(0);
            toastr.error("Add some amount in default payment method unpaid amount!");
        }

        $("#supplierdebitnotepopup").modal('hide');
    }
    else
    {
        toastr.error("Fill up proper debit note detail and amount!");
    }
});
//END OF DEBIT NOTE


function showdetails_supplier(obj){
    // var id                       =     $(obj).attr('id');
    // var salesid                  =     $(obj).attr('id').split('down_')[1];
    

    var company_id = $('#company_id').val();
    var gst_id = $(obj).attr('id').split('_')[1];
   $('#showsupplierd_'+gst_id).toggle();
    
 
    $('#down_'+gst_id).hide();
    $('#up_'+gst_id).show();

    var type = "POST";
    var url = "view_amount_payable_detail";
    var dataType = '';
    var data={
        'gst_id' : gst_id,
        'company_id' : company_id
    };


    callroute(url,type,dataType,data,function (data) {
             var response = JSON.parse(data);

    if (response['Success'] == "True")
        {

        var product_html = '';
        product_html += '<td colspan="8" style="hover:#ffffff !important;"><table width="100%" border="0" style="margin:-20px 0 0 0 !important;"><tr>' +
                    '<td width="15%" style="vertical-align:top !important;" class="rightAlign">' +
                    '<span class="iconify" data-icon="mdi-subdirectory-arrow-right" data-inline="false" style="font-size: 80px;margin:-25px 0 0 0;color:#DDDDDD;"></span>' +
                    '</td>' +
                    '<td width="85%">' +
                    '<table class="table mb-0 detailTable" style="width:95%;float:right;" cellpadding="4">' +
                    '<thead>' +
                    '<tr>' +
                    '<th scope="col" style="width:12%;cursor: pointer;font-size:14px;" class="leftAlign">Invoice No.</th>' +
                    '<th scope="col" style="width:12%;cursor: pointer;font-size:14px;" class="leftAlign">Invoice Date</th>' +
                    '<th scope="col" style="width:12%;cursor: pointer;font-size:14px;" class="leftAlign">Due Date</th>' +
                    '<th scope="col" style="width:12%;cursor: pointer;text-align:right;font-size:14px;">Ground Amt.</th>' +
                    '<th scope="col" style="width:12%;cursor: pointer;text-align:right;font-size:14px;">Paid Amt.</th>' +
                    '<th scope="col" style="width:12%;cursor: pointer;text-align:right;font-size:14px;">Remaining Amt.</th>' +
                    '<th scope="col" style="width:12%;cursor: pointer;font-size:14px;" class="centerAlign">Days Left</th>' +
                    '</tr>' +
                    '</thead>' +
                    '<tbody id="">' ;

              var supplier_data = response['Data'];
              var currentTime = new Date();
              var day = currentTime.getDate();
              var month = currentTime.getMonth() + 1;
              var year = currentTime.getFullYear();

        response['Data'].forEach(function (value)
            {
               
            var amount = value['amount'];
            var paid_amt =value['amount'] - value['outstanding_payment'];
            var remaining_amt = amount - paid_amt
            var date_get = new Date();
            var due_date =  value['inward_stock']['due_date'].split('-');
            var oneDay = 24*60*60*1000;
            var firstDate = new Date(due_date[2],due_date[1],due_date[0]);
            var secondDate = new Date(date_get.getFullYear(),(date_get.getMonth()+1),date_get.getDate());

            var diffDays = Math.round(Math.abs((firstDate.getTime() - secondDate.getTime())/(oneDay)));

            product_html += '<tr><td class="leftAlign">' + value['inward_stock']['invoice_no'] + '</td>' +
            '<td class="leftAlign">' + value['inward_stock']['invoice_date'] + '</td>' +
            '<td class="leftAlign">' + value['inward_stock']['due_date'] + '</td>'  +
            '<td class="rightAlign">' + amount + '</td>' +
            '<td class="rightAlign">' +  paid_amt + '</td>' +
            '<td class="rightAlign">' + remaining_amt + '</td>' +
            '<td class="centerAlign">' + diffDays + '</td>'+
            '</tr>' ;

             });
        }

        product_html += '</tbody>' +
                '</table>' +
                '</td>' +
            '</tr>' +
            '</table>' +
            '</td>';

              $("#showdetail"+gst_id).append(product_html);
    });


}
function hidedetails_supplier(obj){


    var gst_id = $(obj).attr('id').split('_')[1];


    $('#showsupplierd__'+gst_id).toggle();
      $('#down_'+gst_id).show();
    $('#up_'+gst_id).hide();
    var product_html = '';
   product_html += '';
   $("#showdetail"+gst_id).html('');
}
$("#supplier_name").keyup(function () {

    jQuery.noConflict();
    $(this).autocomplete({
        autoFocus: true,
        minLength: 1,
        source: function (request, response) {
            var url = "supplier_search";
            var type = "POST";
            var dataType = "";
            var data = {
                'search_val': $("#supplier_name").val()
            };
//console.log(data);
            callroute(url, type,dataType, data, function (data) {
                var searchsupplier = JSON.parse(data, true);
// console.log(searchsupplier);
                            if (searchsupplier['Success'] == "True") {
                                var supplier_detail = searchsupplier['Data'];
                                if (supplier_detail.length > 0) {
                                    var resultsupplier = [];
//console.log(supplier_detail);
                                    supplier_detail.forEach(function (value) {
                                        if (value.supplier_gst.length > 0) {
                                            $.each(value.supplier_gst, function (supplierkey, suppliervalue) {
                                                //alert(value.supplier_company_name);
                                                var last_name = '';
                                                if (value.supplier_last_name != '' && value.supplier_last_name != null) {
                                                    last_name = value.supplier_last_name;
                                                } else {
                                                    last_name = '';
                                                }
                                                if(suppliervalue.supplier_gstin == null){
                                                    suppliervalue.supplier_gstin = '';
                                                }
                                                else
                                                {
                                                    suppliervalue.supplier_gstin =  '_' + suppliervalue.supplier_gstin;
                                                }

                                                resultsupplier.push({
                                                    label: value.supplier_company_name + ' ' + value.supplier_first_name + ' ' + last_name  + suppliervalue.supplier_gstin,
                                                    value: value.supplier_company_name + ' ' + value.supplier_first_name + ' ' + last_name  + suppliervalue.supplier_gstin,
                                                    id: value.supplier_id,
                                                    supplier_gst_id: suppliervalue.supplier_gst_id,
                                                    supplier_company_name: value.supplier_company_name,
                                                    supplier_gstin: suppliervalue.supplier_gstin,
                                                    //supplier_store_idd: value.supplier_store_idd,

                                                });
                                            });
                                        } else {
                                            var last_name = '';
                                            if (value.supplier_last_name != '' && value.supplier_last_name != null) {
                                                last_name = value.supplier_last_name;
                                            } else {
                                                last_name = '';
                                            }
                                            resultsupplier.push({
                                                label: value.supplier_company_name + '_' + value.supplier_first_name + ' ' + last_name,
                                                value: value.supplier_company_name + '_' + value.supplier_first_name + ' ' + last_name,
                                                id: value.supplier_id,
                                                supplier_gst_id: '',
                                                supplier_company_name: '',
                                                supplier_gstin: '',
                                                
                                            });
                                        }

                                    });
                                    //push data into result array.and this array used for display suggetion
                                    response(resultsupplier);
                                }
                            }
                        });
                    }, //this help to call a function when select search suggetion
                    select: function (event, ui) {
                        var id = ui.item.id;
                        var gst_id = ui.item.supplier_gst_id;
                        var supplier_company_name = ui.item.supplier_company_name;
                        var supplier_gstin = ui.item.supplier_gstin;
                        //var store_id = store_id;
                        //$("#supplier_name").val(id);
                        $("#supplier_gst_id").val(gst_id);
                        $("#supplier_company_name").val(supplier_company_name);
                        $("#supplier_gstin").val(supplier_gstin);
                        //$("#store_id").val(store_id);
                       //alert(supplier_company_name);
                        $(".ui-helper-hidden-accessible").css('display', 'none');
                        //call a function to perform action on select of supplier
                    }
                })
            });
$(document).on('click', '#exportpaymentsummarydata', function(){   

        var query = {
            supplier : $('#supplier_gst_id').val(),
        }
         var url = "exportpaymentsummary_data?" + $.param(query)
         window.open(url,'_blank');             
     

});

// function store_wise_amount()
// {
//    //onchange="store_wise_amount()"
//     var store_id    =   $('#store_id').val();

//     var url = "view_store_wise_amount";
//     var type = "POST";
//     var dataType = "";
//     var data = {
//         "store_id": store_id,
//     }

//     callroute(url,type,dataType,data,function (data)
//     {

//        //var dta = JSON.parse(data);

//         if(data['Success'] == "True")
//         {

//             toastr.success(data['Message']);
//             window.location.href = data['url'];


//             //$("#sproduct_detail_record").empty('');

//         }
//         else
//         {
           
//             toastr.error(data['Message']);
//         }
//     })

// }

function reset_search_data(){
    $(".common-search").find('input,select,hidden').val('');
   $("#supplier_name").val();
   $("#company_id").val();
   $("#hidden_page").val(1);


   resettable('datewise_supplierpaymentdetail','supplier_payment_data_app');
}