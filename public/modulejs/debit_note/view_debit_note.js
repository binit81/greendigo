function view_debit_detail(debitid)
{
    $("#debit_popup_record").trigger('reset');

    $("#view_debit_record").empty();


     $("#previousrecord").attr('data-id','');

     $("#nextrecord").attr("data-id",'');

    var  url = "view_debit_detail";
    var type = "POST";
    var dataType = "";
    var data = {
        'debit_note_id' : debitid,
    };

    callroute(url,type,dataType,data,function (data)
    {
        var dta = JSON.parse(data);

        if(dta['Success'] == "True")
        {
            var final_data = JSON.parse(dta['Data']);

            var dataval = final_data;

            $("#debit_total_qty").html(dataval[0]['total_qty']);

            $("#viewdebitpopup").modal('show');


            var oldUrl = $("#print_detail_debit").attr("href"); // Get current url

            var newUrl = oldUrl.replace("param",debitid);
            $("#print_detail_debit").attr("href", newUrl); // Set herf value

            $("#debit_note_edit_popup").attr("onClick","edit_debitnote('"+debitid+"')");

            var debit_detail = '';

            //FOR DISABLE AND ENABLE NEXT AND PREVIOUS BUTTON IN POPUP
            if(dta['next'] == '')
            {
              $("#nextrecord").prop('disabled',true);
               $("#nextrecord").attr("data-id", '');
            }
            else
            {
               $("#nextrecord").prop('disabled',false);
                $("#nextrecord").attr("data-id", dta['next']);
            }

             if(dta['previous'] == '')
            {
              $("#previousrecord").prop('disabled',true);
              $("#previousrecord").attr("data-id", '');
            }
            else
            {
               $("#previousrecord").prop('disabled',false);
               $("#previousrecord").attr('data-id',dta['previous']);
            }
            $(".popup_debit_no").html(dataval[0]['debit_no']);
            $(".popup_debit_date").html(dataval[0]['debit_date']);

            if(dataval[0]['debit_product_details'] != '' && dataval[0]['debit_product_details'] != null && dataval[0]['debit_product_details'] != undefined)
            {
                $.each(dataval[0]['debit_product_details'], function (key, value)
                {
                    if (value['product'] != '' && value['product'] != 'undefined')
                    {
                        product_detail = value['product'];
                    }
                    var product_name = '';

                    if (product_detail != '' && product_detail['product_name'] != '' && product_detail['product_name'] != null) {
                        product_name = product_detail['product_name'];
                    }

                    var barcode = '';
                    if (product_detail != '' && product_detail['supplier_barcode'] != " " && product_detail['supplier_barcode'] != null) {
                        barcode = product_detail['supplier_barcode'];
                    } else {

                        barcode = product_detail['product_system_barcode'];
                    }

                    var cost_rate = '';
                    var cost_gst_percent = '';
                    var cost_gst_amount = '';
                    var qty = '';
                    var total_cost_rate = '';
                    var total_gst = '';
                    var total_cost_price = '';
                    var remarks = '';

                    if (value['cost_rate'] != '')
                    {
                        cost_rate = value['cost_rate'];
                    }
                    if (value['cost_gst_percent'] != '')
                    {
                        cost_gst_percent = value['cost_gst_percent'];
                    }
                    if (value['cost_gst_amount'] != '')
                    {
                        cost_gst_amount = value['cost_gst_amount'];
                    }
                    if (value['return_qty'] != '')
                    {
                        qty = value['return_qty'];
                    }

                    if (value['total_cost_rate'] != '')
                    {
                        total_cost_rate = value['total_cost_rate'];
                    }

                    if (value['total_gst'] != '')
                    {
                        total_gst = value['total_gst'];
                    }

                    if (value['total_cost_price'] != '')
                    {
                        total_cost_price = value['total_cost_price'];
                    }
                    if (value['remarks'] != '' && value['remarks'] != null)
                    {
                         remarks = value['remarks'];
                    }

                    var feature_show_val = "";
                    if(show_debit_dynamic_feature != '')
                    {
                        var feature = show_debit_dynamic_feature.split(',');

                        $.each(feature,function(fea_key,fea_val)
                        {
                            var feature_name = '';
                            if(typeof(product_detail[fea_val]) != "undefined" && product_detail[fea_val] !== null) {

                                feature_name = product_detail[fea_val];
                            }

                            feature_show_val += '<td>' + feature_name + '</td>';
                        })
                    }

                    debit_detail += '<tr id="' + value['product_id'] + '"> ' +
                        '<td>' + barcode + '</td>' +
                        '<td>' + product_name + '</td>';

                    debit_detail += feature_show_val;

                    debit_detail += '<td class="with_calculation_debit">' + cost_rate + '</td>' +
                        '<td class="with_calculation_debit">' + cost_gst_percent + '</td>' +
                        '<td class="with_calculation_debit">' + cost_gst_amount + '</td>' +
                        '<td>' + qty + '</td>' +
                        '<td class="with_calculation_debit">' + total_cost_rate + '</td>' +
                        '<td class="with_calculation_debit">' + total_gst + '</td>' +
                        '<td class="with_calculation_debit">' + total_cost_price + '</td>' +
                        '<td>' + remarks + '</td>' +
                        '</tr>';
                });
            }
            $("#view_debit_record").append(debit_detail);


             var totalitem = $("#view_debit_record tr").length;
                  $(".totcount").html(totalitem);


            $(".invoiceno").html(dataval[0]['debit_no']);

            $(".invoice_title").html('Debit Details::');

            if(inward_calculation == 3)
            {
                $(".with_calculation_debit").hide();
            }else
            {
                $(".with_calculation_debit").show();
            }

        }
        else
        {

        }
    })
}


function edit_debitnote(debit_id)
{
    var  url = "edit_debit_note";
    var type = "POST";
    var dataType = "";
    var data = {
        'debit_note_id' : debit_id,

    };

    callroute(url,type,dataType,data,function (data) {
        var dta = JSON.parse(data);

        if (dta['Success'] == "True")
        {
            var url = '';
            if(dta['url'] != '' && dta['url'] != 'undefined')
            {
                url = dta['url'];
            }

            localStorage.setItem('edit_debit_note', JSON.stringify(dta['Data']));


            window.location.href = url;
        }
    });
}


$('#checkalldebit').change(function()
{
    if($(this).is(":checked")) {
        $("#debitrecord tr").each(function()
        {
            var id = $(this).attr('id');

            $(this).find('td').each(function ()
            {
                $("#delete_debit"+id).prop('checked',true);
            });
        })
    }
    else
    {
        $("#debitrecord tr").each(function(){
            var id = $(this).attr('id');
            $(this).find('td').each(function ()
            {
                $("#delete_debit"+id).prop('checked',false);
            });

        })
    }
});


$("#deletedebitnote").click(function ()
{
    /*if(confirm("Are You Sure want to delete this debit note?")) {*/

        var ids = [];

        $('input[name="delete_debit[]"]:checked').each(function()
        {
            idss = {};
            idss['debit_note_id'] = $(this).val();
            idss['inward_stock_id'] = $(this).data('id');
            idss['supplier_gst_id'] =$(this).data('attr');

            ids.push(idss)
        });

        if(ids.length > 0)
        {

             var errmsg = "Are You Sure want to delete this Debit note?";
        swal({
                title: errmsg,
                type: "warning",
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes!",
                showCancelButton: true,
                closeOnConfirm: true,
                closeOnCancel: true
            },


        function (isConfirm) {
                if (isConfirm) {

            var data = {
                "deleted_id": ids,
            };
            var dataType = "";
            var url = "debit_note_delete";
            var type = "POST";
            callroute(url, type,dataType, data, function (data)
            {
                var dta = JSON.parse(data);

                if (dta['Success'] == "True")
                {
                    toastr.success(dta['Message']);
                    resettable('debit_note_data','debitnoterecord');
                } else {
                    toastr.error(dta['Message']);
                }
            })
        }
        else
        {
            return false;
        }
    // }
    })
        }
    else
    {
        return false;
    }
});


//individual delete debit note
function delete_separate_debit_note(obj,debit_id,event)
{
    $(obj).closest('td').prev().find('[type=checkbox]').prop('checked',true);
    event.preventDefault();
    if($(obj).closest('td').prev().find('[type=checkbox]').prop('checked') == true)
    {
        setTimeout(
            function()
            {
                 $('#deletedebitnote')[0].click(); 
            }, 300);
    }

}




//this is used for display supplier suggestion
$("#supplier_name").keyup(function ()
{
    jQuery.noConflict();
    $(this).autocomplete({
        autoFocus: true,
        minLength: 1,
        source: function (request, response)
        {
            var url = "supplier_search";
            var type = "POST";
            var dataType = '';
            var data = {
                'search_val': $("#supplier_name").val()
            };
            callroute(url, type,dataType, data, function (data)
            {
                var searchsupplier = JSON.parse(data, true);

                if (searchsupplier['Success'] == "True")
                {
                    var supplier_detail = searchsupplier['Data'];

                    if (supplier_detail.length > 0)
                    {
                        var resultsupplier = [];

                        supplier_detail.forEach(function (value)
                        {
                            if(value.supplier_gst.length > 0)
                            {
                                $.each(value.supplier_gst,function (supplierkey,suppliervalue)
                                {
                                    var last_name = '';
                                    if(value.supplier_last_name != '' && value.supplier_last_name != null)
                                    {
                                        last_name = value.supplier_last_name;
                                    }
                                    else
                                    {
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
                                        label: value.supplier_company_name + ' '+ value.supplier_first_name + ' ' + last_name + suppliervalue.supplier_gstin,
                                        value: value.supplier_company_name + ' '+ value.supplier_first_name + ' ' + last_name  + suppliervalue.supplier_gstin,
                                        supplier_gst_id: suppliervalue.supplier_gst_id
                                    });
                                });
                            }
                            else
                            {
                                var last_name = '';
                                if(value.supplier_last_name != '' && value.supplier_last_name != null)
                                {
                                    last_name = value.supplier_last_name;
                                }
                                else
                                {
                                    last_name = '';
                                }
                                resultsupplier.push({
                                    label: value.supplier_company_name + ' '+ value.supplier_first_name + ' ' + last_name ,
                                    value: value.supplier_company_name + ' '+ value.supplier_first_name + ' ' + last_name ,
                                    supplier_gst_id: ''

                                });
                            }

                        });
                        //push data into result array.and this array used for display suggetion
                        response(resultsupplier);
                    }
                }
            });
        }, //this help to call a function when select search suggetion
        select: function (event, ui)
        {

            var gst_id = ui.item.supplier_gst_id;

            $("#supplier_gst_id").val(gst_id);

            $(".ui-helper-hidden-accessible").css('display','none');
            //call a function to perform action on select of supplier
        }
    })
});

//THIS IS USED TO SHOW PREVIOUS AND NEXT RECORD IN POPUP
$('#previousrecord').click(function(e){
var debit_id = $("#previousrecord").attr('data-id');
view_debit_detail(debit_id);

});


$('#nextrecord').click(function(e){

var debit_id = $("#nextrecord").attr('data-id');

view_debit_detail(debit_id);

});


$("#search_view_debit").click(function () {
    debit_filter();
});

function debit_filter()
{

    var debit_no = $('#debit_no').val();
    var data = {

        debit_no : debit_no,
        supplier_gst_id : $("#supplier_gst_id").val()
    };

    var page = $("#hidden_page").val();
    var sort_type = $("#hidden_sort_type").val();
    var sort_by = $("#hidden_column_name").val();
    fetch_data('debit_note_fetch_data',page,sort_type,sort_by,data,'debitnoterecord');
}

function resetdebitfilterdata()
{
    $("#debit_no").val('');
    $("#supplier_gst_id").val('');
    $("#supplier_name").val('');

    debit_filter();
}


//this is used for display Debit No suggestion
$("#debit_no").keyup(function ()
{
    jQuery.noConflict();

    $(this).autocomplete({
        autoFocus: true,
        minLength: 1,
        source: function (request, response)
        {
            var url = "debit_number_search";
            var type = "POST";
            var dataType = "";
            var data = {
                'search_val' : $("#debit_no").val()
            };
            callroute(url,type,dataType,data,function (data)
            {
                var searchdata = JSON.parse(data,true);

                if(searchdata['Success'] == "True")
                {
                    var result = [];
                    searchdata['Data'].forEach(function (value)
                    {
                        result.push({
                            label: value.debit_no,
                            value: value.debit_no
                        });
                    });
                    response(result);
                }
            });
        },
        //this help to call a function when select search suggetion
        select: function(event,ui)
        {
            $(".ui-helper-hidden-accessible").css('display','none');
        }
    });
});


