//set datepicker to po_date
function getpodate() {

    $("#po_date").datepicker({
        autoclose: true,
        format: "dd-mm-yyyy",
        immediateUpdates: true,
        todayBtn: true,
        orientation: "bottom",
        todayHighlight: true,
    }).on('changeDate', function (selected)
    {
        var minDate = new Date(selected.date.valueOf());
        var selectedDate = (("0" + minDate.getDate()).slice(-2) + '-' + (("0" + (minDate.getMonth() + 1)).slice(-2)) + '-' + minDate.getFullYear());
        $('#delivery_date').val(selectedDate)
    }).on('keypress paste', function (e) {
        e.preventDefault();
        return false;
    }).datepicker("setDate","0");

    $("#delivery_date").datepicker({
        autoclose: true,
        format: "dd-mm-yyyy",
        immediateUpdates: true,
        todayBtn: true,
        orientation: "bottom",
        todayHighlight: true,
    }).on('changeDate', function (selected) {
            var maxDate = new Date(selected.date.valueOf());
            $('#po_date').datepicker('setEndDate', maxDate);
        }).on('keypress paste', function (e) {
        e.preventDefault();
        return false;
    }).datepicker("setDate","0");

   /* $('#po_date,#delivery_date').datepicker({
        autoclose: true,
        format: "dd-mm-yyyy",
        immediateUpdates: true,
        todayBtn: true,
        orientation: "bottom",
        todayHighlight: true
    }).datepicker("setDate", "0");*/

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
            var dataType = "";
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
                                    resultsupplier.push({
                                        label: value.supplier_company_name + ' '+ value.supplier_first_name + ' ' + last_name + '_' + suppliervalue.supplier_gstin,
                                        value: value.supplier_company_name + ' '+ value.supplier_first_name + ' ' + last_name + '_' + suppliervalue.supplier_gstin,
                                        id: value.supplier_id,
                                        supplier_gst_id: suppliervalue.supplier_gst_id,
                                        state_id: suppliervalue.state_id
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
                                    id: value.supplier_id,
                                    supplier_gst_id: '',
                                    state_id: ''
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
            var id = ui.item.id;
            var gst_id = ui.item.supplier_gst_id;
            var state_id = ui.item.state_id;
            $("#gst_id").val(gst_id);
            $("#state_id").val(state_id);
            $("#supplier_name").val(id);
            $(".ui-helper-hidden-accessible").css('display','none');
            //call a function to perform action on select of supplier
        }
    })
});


/*$("#productsearch").keyup(function ()
{
    jQuery.noConflict();

    $(this).autocomplete({
        autoFocus: true,
        minLength: 1,

        source: function (request, response)
        {
            var url = "product_search";
            var type = "POST";
            var data = {
                'search_val' : $("#productsearch").val()
            };
            callroute(url,type,data,function (data)
            {
                var searchdata = JSON.parse(data,true);

                if(searchdata['Success'] == "True")
                {
                    var result = [];
                    searchdata['Data'].forEach(function (value)
                    {
                        var display_barcode = '';

                        if(value.supplier_barcode != " " && value.supplier_barcode != undefined)
                        {
                            display_barcode = value.supplier_barcode;
                        }
                        else
                        {
                            display_barcode = value.product_system_barcode;
                        }
                        if(display_barcode != undefined)
                        {
                            result.push({
                                label: value.product_name + '_' + display_barcode,
                                value: value.product_name + '_' + display_barcode, id: value.product_id
                            });
                        }
                    });
                    //push data into result array.and this array used for display suggetion
                    response(result);
                }
            });
        },
        //this help to call a function when select search suggetion
        select: function(event,ui)
        {
            var id = ui.item.id;
            //call a getproductdetail function for getting product detail based on selected product from suggetion
            getproductdetail(id,'');

            $(".ui-helper-hidden-accessible").css('display','none');
        },
    });


});*/


$("#productsearch").typeahead({
    source: function(request, process)
    {
        var  url = "isproduct_search";
        var type = "post";
        var dataType = "json";
        var data = {
            "search_val": $("#productsearch").val(),
            "unique_barcode_inward": $("#po_with_unique_barcode").val(),
            "term": request.term
        };

        callroute(url,type,dataType,data,function (data)
        {
            objects = [];
                map = {};

                if($("#productsearch").val()!='')
                {
                    $.each(data, function(i, object)
                    {
                        map[object.label] = object;
                        objects.push(object.label);
                    });
                    process(objects);
                    if(objects!='')
                    {
                      if(objects.length === 1)
                      {
                          $("#productsearch").val('');
                          getproductdetail(data[0]['product_id']);
                          $(".dropdown-menu").hide();
                          return false;
                      }
                    }
                }
                else
                {
                    $(".dropdown-menu").hide();
                }
        });
    },

    //minLength: 1,
    autoselect:true,
    // autoSelect:false,
    // typeahead-select-on-exact="true"
    afterSelect: function (item) {
        //$('.loaderContainer').show();
        var value = item;
        var barcode = map[item]['barcode'];
        var product_name = map[item]['product_name'];
        var product_id = map[item]['product_id'];
        getproductdetail(product_id);
    }

});

function getproductdetail(productid)
{
    var type = "POST";
    var url = 'po_product_detail';
    var dataType = "";
    var data = {
        "product_id" : productid
    }
    callroute(url,type,dataType,data,function(data)
    {
        var product_data = JSON.parse(data,true);

        if(product_data['Success'] == "True")
        {
            var product_html = '';
            var product_detail  = product_data['Data'][0];
            var hsncode = '';
            var cost_gst_percent = '0';
            var cost_gst_amount = '0';
            var cost_rate = '0';
            var in_stock = '0';

            if(product_detail['hsn_sac_code'] != null || product_detail['hsn_sac_code'] != undefined)
            {
                hsncode = product_detail['hsn_sac_code'];
            }
            if(product_detail['cost_rate'] != null || product_detail['cost_rate'] != undefined)
            {
                cost_rate = product_detail['cost_rate'];
            }

            if(product_detail['cost_gst_percent'] != null || product_detail['cost_gst_percent'] != undefined)
            {
                cost_gst_percent = product_detail['cost_gst_percent'];
            }
            if(product_detail['cost_gst_amount'] != null || product_detail['cost_gst_amount'] != undefined)
            {
                cost_gst_amount = product_detail['cost_gst_amount'];
            }

            if(product_detail['in_stock'] != null || product_detail['in_stock'] != undefined)
            {
                in_stock = product_detail['in_stock'];
            }

            var uqc_name = '';
            if(product_detail['uqc_id'] != '' && product_detail['uqc_id'] != null && product_detail['uqc_id'] != 0)
            {
                uqc_name = product_detail['uqc']['uqc_shortname'];
            }

            var feature_show_val = "";
            if(po_show_dynamic_feature != '')
            {
                var feature = po_show_dynamic_feature.split(',');

                $.each(feature,function(fea_key,fea_val)
                {
                    var feature_name = '';
                    if(typeof(product_detail[fea_val]) != "undefined" && product_detail[fea_val] !== null) {

                        feature_name = product_detail[fea_val];
                    }

                    feature_show_val += '<td>' + feature_name + '</td>';
                })
            }


            var rowCount = $('#po_product_detail_record tr').length;
            rowCount++;

            var product_id = product_detail['product_id'];
            var samerow = 0;
            //IF PO WITH SAME BARCODE IS 1 THEN SAME PRODUCT REPEAT OTHERWISE INCREMENT OF PRODUCT QUNTITY
            if($("#po_with_unique_barcode").val() == 0)
            {
                $("#po_product_detail_record tr").each(function ()
                {
                    var row_product_id = $(this).attr('id').split('_')[1];
                    if (row_product_id == product_id)
                    {
                        var qty = $("#qty_" + product_id).html();
                        var product_qty = ((Number(qty)) + (Number(1)));
                        $("#qty_" + product_id).html(product_qty);
                        samerow = 1;

                        var costrate = $("#cost_rate_" + product_id).html();

                        var gst_percent = $("#cost_gst_percent_" + product_id).html();

                        var gstamt = ((Number(costrate)) * (Number(gst_percent)) / (Number(100)));

                        $("#cost_gst_amount_" + product_id).html(gstamt.toFixed(4));

                        var total_cost_without_gst = ((Number(costrate)) * (Number(product_qty)));

                        $("#total_cost_without_gst_" + product_id).html(total_cost_without_gst.toFixed(4));
                        var gst_amount = $("#cost_gst_amount_" + product_id).html();
                        var total_gst = ((Number(gst_amount)) * (Number(product_qty)));
                        $("#total_gst_" + product_id).html(total_gst.toFixed(4));
                        var total_cost_with_gst = (((Number(costrate)) + (Number(gst_amount))) * (Number(product_qty)));
                        $("#total_cost_with_gst_" + product_id).html(total_cost_with_gst.toFixed(4));

                        gettotalqty();
                        return false;
                    }
                });
            }

            if(samerow == 0)
            {
                var barcode = '';
                if(product_detail['supplier_barcode'] != " " && product_detail['supplier_barcode'] != undefined && product_detail['supplier_barcode'] != null)
                {

                    barcode = product_detail['supplier_barcode'];
                }
                else
                {
                    barcode = product_detail['product_system_barcode'];
                }

                if (product_html == '')
                {
                    product_html += '<tr id="product_'+product_id +'" data-id="'+rowCount+'">' +
                        '<input type="hidden" name="purchase_order_detail_id_'+product_id+'" id="purchase_order_detail_id_'+product_id+'" value="">' +
                        '<td>' +barcode + '</td>' +
                        '<td>' + product_detail['product_name'] + '</td>' +
                        '<td>' + hsncode + '</td>' +
                        '<td class="po_with_batch_no" id="unique_barcode_'+product_id+'"></td>';

                    product_html += feature_show_val;


                    product_html += '<td >'+uqc_name+'</td>' +
                        '<td>'+in_stock+'</td>' +
                        '<td onkeypress = "return testCharacter(event);" class="number editablearea with_calculation" contenteditable="true" style="color: black;"  onkeyup="costrate(this);" id="cost_rate_' + product_id + '">' + cost_rate + '</td>' +
                        '<td class="with_calculation" readonly  id="cost_gst_percent_' + product_id + '">' + cost_gst_percent + '</td>' +
                        '<td class="with_calculation"  readonly  id="cost_gst_amount_' + product_id + '">' + cost_gst_amount + '</td>' +
                        '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addqty(this);" id="qty_' + product_id + '">'+product_detail['default_qty']+'</td>' +
                        '<td class="with_calculation" readonly id="total_cost_without_gst_' + product_id + '">0</td>' +
                        '<td class="with_calculation" readonly id="total_gst_' + product_id + '">0</td>' +
                        '<td class="with_calculation" readonly id="total_cost_with_gst_' + product_id + '">0</td>' +
                        '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;"  onclick="return getdatepickerpo(\'mfg_date_' + product_id + '\');" id="mfg_date_' + product_id + '"></td>' +
                        '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;" onclick="return getdatepickerpo(\'expiry_date_' + product_id + '\');" id="expiry_date_' + product_id + '"></td>' +
                        '<td  id="poremarksentry_' + product_id + '"><textarea name="remarks_' + product_id + '" id="remarks_' + product_id + '"></textarea></td>' +
                        '<td onclick="removeporow(' + product_detail['product_id'] + ');"><i class="fa fa-close"></i></td>' +
                        '</tr>';
                }
                else
                    {
                    product_html += product_html + '<tr id="product_'+product_id +'" data-id="'+rowCount+'">' +
                        '<input type="hidden" name="purchase_order_detail_id_'+product_id+'" id="purchase_order_detail_id_'+product_id+'" value="">' +
                        '<td>' +barcode + '</td>' +
                        '<td>' + product_detail['product_name'] + '</td>' +
                        '<td>' + hsncode + '</td>' +
                        '<td class="po_with_batch_no" id="unique_barcode_'+product_id+'"></td>';

                    product_html += feature_show_val;

                    product_html += '<td>'+uqc_name+'</td>' +
                        '<td>'+in_stock+'</td>' +
                        '<td   onkeypress = "return testCharacter(event);" class="number editablearea with_calculation" contenteditable="true" style="color: black;"  onkeyup="costrate(this);" id="cost_rate_' + product_id + '">' + cost_rate + '</td>' +
                        '<td class="with_calculation"  readonly  id="cost_gst_percent_' + product_id + '">' + cost_gst_percent + '</td>' +
                        '<td class="with_calculation" readonly  id="cost_gst_amount_' + product_id + '">' + cost_gst_amount + '</td>' +
                        '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addqty(this);" id="qty_' + product_id + '">'+product_detail['default_qty']+'</td>' +
                        '<td class="with_calculation" readonly id="total_cost_without_gst_' + product_id + '">0</td>' +
                        '<td class="with_calculation" readonly id="total_gst_' + product_id + '">0</td>' +
                        '<td class="with_calculation" readonly id="total_cost_with_gst_' + product_id + '">0</td>' +
                        '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;"  onclick="return getdatepickerpo(\'mfg_date_' + product_id + '\');" id="mfg_date_' + product_id + '"></td>' +
                        '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;" onclick="return getdatepickerpo(\'expiry_date_' + product_id + '\');" id="expiry_date_' + product_id + '"></td>' +
                        '<td  id="poremarksentry_' + product_id + '"><textarea name="remarks_' + product_id + '" id="remarks_' + product_id + '"></textarea></td>' +
                        '<td onclick="removeporow(' + product_detail['product_id'] + ');"><i class="fa fa-close"></i></td>' +
                        '</tr>';
                }
            }
        }

        $("#productsearch").val('');
        $(".odd").hide();
        $("#po_product_detail_record").prepend(product_html);

        $("#po_product_detail_record").find('tr[data-id='+rowCount+']').find("#qty_"+product_id).keyup();


        if($("#po_with_unique_barcode").val() == 0)
        {
            $(".po_with_batch_no").hide();
        }else
        {
            $(".po_with_batch_no").show();
        }

        if(po_calculation == 2)
        {
            $(".with_calculation").hide();
        }else
        {
            $(".with_calculation").show();
        }

        var totalqty = $("#po_product_detail_record tr").length;

        $(".pototalitems").html(totalqty);


    });
}

function getdatepickerpo(id)
{
    var expiry_date = id.replace('mfg_date_', 'expiry_date_');

    var idrow = id.split('_')[2];
    $("#po_product_detail_record").find('tr').find("#"+id).datepicker({
    //$("#" + id).datepicker({
        format: "dd-mm-yyyy",
        orientation: "bottom"
    }).on('changeDate', function (e)
    {
        var date = new Date($(this).datepicker('getDate'));
        var selectedDate = (("0" + date.getDate()).slice(-2) + '-' + (("0" + (date.getMonth() + 1)).slice(-2)) + '-' + date.getFullYear());
        $(this).html(selectedDate);

        if(id.startsWith('expiry_date_'))
        {
            $("#po_product_detail_record").find('tr').find("#mfg_date_"+idrow).datepicker('setEndDate', date);
        }
        if (id.startsWith('mfg_date_'))
        {
            var minDate = new Date(e.date.valueOf());
            $("#po_product_detail_record").find('tr').find('#'+expiry_date).datepicker('setStartDate', minDate);
        }
    }).on('keypress paste', function (e) {
        e.preventDefault();
        return false;
    });

}



$("#addpoprint").click(function ()
{
    //1 = print po
    addpo('1');
});

$("#addpo").click(function () {
    //0 = save and new..no print po
    addpo(0);
});


function addpo(printtype)
{
    //printtype  = 1 = print po
    //printtype  = 0 = save and new.. no print

    //ADD PO FUNCTION
      $("#addpo").prop('disabled', true);
      $("#addpoprint").prop('disabled', true);
        if(validate_poform('issue_po'))
        {
            $("#addpo").prop('disabled', true);
            $("#addpoprint").prop('disabled', true);
            var purchase_order = [];
            var po_product_info = [];

            po_detail = {};
            po_detail['supplier_gst_id'] = $("#gst_id").val();
            po_detail['po_with_unique_barcode'] = $("#po_with_unique_barcode").val();
            po_detail['po_no'] = $("#po_no").val();
            po_detail['po_date'] = $("#po_date").val();
            po_detail['delivery_to'] = $("#delivery_to").val();
            po_detail['address'] = $("#address").val();
            po_detail['terms_condition'] = CKEDITOR.instances.terms_condition.getData();
            po_detail['delivery_date'] = $("#delivery_date").val();
            po_detail['total_qty'] = $("#total_qty").val();
            po_detail['total_cost_rate'] = $("#total_cost_rate").val();
            po_detail['total_gst'] = $("#total_gst").val();
            po_detail['total_cost_price'] = $("#total_cost_price").val();
            po_detail['note'] = $("#po_note").val();

            //getting product row info

            $("#po_product_detail_record").each(function (index,e)
            {
                $(this).find('tr').each(function (key,keyval)
                {
                    var tr = $(this).attr('id');
                    var product_id = tr.split('product_')[1];
                    var po_product_detail = {};
                    po_product_detail['product_id'] = product_id;
                    po_product_detail['purchase_order_detail_id'] = $(this).find('#purchase_order_detail_id_'+product_id).val();
                    $(this).find('td').each(function ()
                    {
                        if ($(this).attr('id') != undefined)
                        {
                            id = $(this).attr('id').split('_'+product_id)[0];
                            if (id == 'poremarksentry')
                            {
                                values = $(this).find("#remarks_" + product_id).val();
                                po_product_detail['remarks'] = values;
                            } else {
                                values = $(this).html();
                                po_product_detail[id] = values;
                            }
                        }
                    });
                    po_product_info.push(po_product_detail);
                });
            });
            //end of getting row info

            purchase_order.push(po_detail);

            url_route = "add_purchase_order";
            var url = url_route;
            var type = "POST";
            var dataType = "";
            var data = {
                'purchase_order' : purchase_order,
                'purchase_order_detail' : po_product_info,
                'purchase_order_id' : $("#purchase_order_id").val(),
            };
            callroute(url,type,dataType,data,function (data)
            {
                $("#addpo").prop('disabled', false);
                $("#addpoprint").prop('disabled', false);
                var dta = JSON.parse(data);

                if(dta['Success'] == "True")
                {
                    toastr.success(dta['Message']);
                    $("#issue_po").trigger('reset');

                    if(printtype == 1)
                    {
                        var oldUrl = $("#print_save_po").attr("href"); // Get current url

                        var newUrl = oldUrl.replace("param",dta['purchase_order_id']);
                        $("#print_save_po").attr("href", newUrl); // Set herf value

                        document.getElementById('print_save_po').click(); // Works!
                    }

                    $("#po_product_detail_record").empty();
                    var totalqty = $("#po_product_detail_record tr").length;

                    $(".pototalitems").html(totalqty);
                    getpodate();

                    $("#po_no").val(dta['po_no']);

                    if(dta['url'] != '' && dta['url'] != 'undefined')
                    {
                        /*remove localstorage value of edit PO*/
                        localStorage.removeItem('edit_po_record');
                        setTimeout(function(){
                            window.location.href = dta['url'];
                        }, 1000);
                    }
                }
                else
                {
                    if(dta['status_code'] == 409)
                    {
                        toastr.error(dta['Message']);
                    }
                    else
                    {
                        toastr.error(dta['Message']);
                    }
                }
            })
        }
        else
        {
            $("#addpo").prop('disabled', false);
            $("#addpoprint").prop('disabled', false);
            return false;
        }
}


function validate_poform(frmid)
{
    var error = 0;

    var rowlength = $('#po_product_detail_record').find('tr').length;

    if(rowlength == 0)
    {
        error = 1;
        toastr.error("Select some product to PO!");
        return false;
    }
    else
    {
        $("#po_product_detail_record").each(function ()
        {
            $(this).find('tr').each(function ()
            {
                var tr = $(this).attr('id');
                var product_id = tr.split('product_')[1];

                var qty = $("#qty_" + product_id).html();

                if (qty == '' || qty == 0)
                {
                    error = 1;
                    toastr.error("Qty must be grather than 0!");

                }
                if(po_calculation == 1) {
                    var cost_rate = $("#cost_rate_" + product_id).html();

                    if (cost_rate == '' || cost_rate == 0) {
                        error = 1;
                        toastr.error("Cost Rate is not valid!");
                    }

                    var total_cost_without_gst = $("#total_cost_without_gst_" + product_id).html();

                    if (total_cost_without_gst == '' || total_cost_without_gst == 0) {
                        error = 1;
                        toastr.error("Total Cost without GST is not valid!");

                    }

                    var total_cost_with_gst = $("#total_cost_with_gst_" + product_id).html();

                    if (total_cost_with_gst == '' || total_cost_with_gst == 0) {
                        error = 1;
                        toastr.error("Total Cost with GST is not valid!");
                    }
                }

            });
        });

        if($("#po_date").val() == '')
        {
            error = 1;
            toastr.error("Select PO Date!");
        }


        if ($("#gst_id").val() == '')
        {
            error = 1;
            toastr.error("supplier name can not be empty!");

        }

        if ($("#delivery_to").val() == '')
        {
            error = 1;
            toastr.error("Delivery to title can not be empty!");

        }

        if ($("#address").val() == '')
        {
            error = 1;
            toastr.error("Address can not be empty!");

        }

        if ($("#total_qty").val() == '' || $("#total_qty").val() == 0)
        {
            error = 1;
            toastr.error("Total Qty can not be empty!");

        }

    }

    if(error == 1)
    {
        return false;
    }
    else
    {
        return true;
    }
}


//for edit inward stock

$(document).ready(function ()
{
    //get a value from local storage

    if(po_calculation == 2)
    {
        $(".with_calculation").hide();
    }
    else
    {
        $(".with_calculation").show();
    }

    getpodate();

    var edit_data  = localStorage.getItem('edit_po_record');

    if(edit_data != '' && edit_data != undefined && edit_data != null)
    {
        var edit_po_data = JSON.parse(edit_data);

        var edit_po = JSON.parse(edit_po_data);

        if(edit_po['po_with_unique_barcode'] == 0)
        {
            $(".po_with_batch_no").hide();
        }else
        {
            $(".po_with_batch_no").show();
        }

        if(po_calculation == 2)
        {
            $(".with_calculation").hide();
        }
        else
        {
            $(".with_calculation").show();
        }

        $("#purchase_order_id").val(edit_po['purchase_order_id']);
        $("#po_with_unique_barcode").val(edit_po['po_with_unique_barcode']);
        $("#inward_date").val(edit_po['inward_date']);
        $("#po_date").val(edit_po['po_date']);
        $("#po_no").val(edit_po['po_no']);
        $("#gst_id").val(edit_po['supplier_gst_id']);
        $("#delivery_to").val(edit_po['delivery_to']);
        $("#address").val(edit_po['address']);
        $("#terms_condition").val(edit_po['terms_condition']);
        $("#delivery_date").val(edit_po['delivery_date']);
        $("#total_qty").val(edit_po['total_qty']);
        $("#total_cost_rate").val(edit_po['total_cost_rate']);
        $("#total_gst").val(edit_po['total_gst']);
        $("#total_cost_price").val(edit_po['total_cost_price']);
        $("#po_note").val(edit_po['note']);

        //fillup product detail row
        if(edit_po['purchase_order_detail'] != 'undefined' && edit_po['purchase_order_detail'] != '')
        {
            var po_html = '';
            $.each(edit_po['purchase_order_detail'],function (purchase_key,purchase_value)
            {

                if(purchase_value['product']['hsn_sac_code'] =='' || purchase_value['product']['hsn_sac_code'] == null)
                {
                    purchase_value['product']['hsn_sac_code'] = '';
                }

                var barcode = '';

                if(purchase_value['product'] != '' && purchase_value['product']['supplier_barcode'] != " " && purchase_value['product']['supplier_barcode'] != null)
                {
                    barcode = purchase_value['product']['supplier_barcode'];
                }
                else {

                    barcode = purchase_value['product']['product_system_barcode'];
                }

                var uqc_name = '';

                if(purchase_value['product']['uqc_id'] != '' && purchase_value['product']['uqc_id'] != null && purchase_value['product']['uqc_id'] != 0)
                {
                    uqc_name = purchase_value['product']['uqc']['uqc_shortname'];
                }
                var size_name = '';



                var unique_barcode = '';

                if(purchase_value['unique_barcode'] != '' && purchase_value['unique_barcode'] != null )
                {
                    unique_barcode = purchase_value['unique_barcode'];
                }

                if (purchase_value['mfg_date'] == null) {
                    purchase_value['mfg_date'] = '';
                }
                if (purchase_value['expiry_date'] == null) {
                    purchase_value['expiry_date'] = '';
                }

                if(purchase_value['remarks'] == '' || purchase_value['remarks'] == null)
                {
                    purchase_value['remarks'] = '';
                }


                var feature_show_val = "";
                if(po_show_dynamic_feature != '')
                {
                    var feature = po_show_dynamic_feature.split(',');

                    $.each(feature,function(fea_key,fea_val)
                    {
                        var feature_name = '';
                        if(typeof(purchase_value['product'][fea_val]) != "undefined" && purchase_value['product'][fea_val] !== null) {

                            feature_name = purchase_value['product'][fea_val];
                        }

                        feature_show_val += '<td>' + feature_name + '</td>';
                    })
                }
                //var rowCount = $('#po_product_detail_record tr').length;
                //rowCount++;

                purchase_key++;

                var product_id = purchase_value['product_id'];

                po_html += '<tr id="product_'+product_id +'" data-id="'+purchase_key+'">' +
                    '<input type="hidden" name="purchase_order_detail_id_'+product_id+'" id="purchase_order_detail_id_'+product_id+'" value="'+purchase_value['purchase_order_detail_id']+'">' +
                    '<td>' +barcode + '</td>' +
                    '<td>' + purchase_value['product']['product_name'] + '</td>' +
                    '<td>' + purchase_value['product']['hsn_sac_code'] + '</td>' +
                    '<td class="po_with_batch_no" id="unique_barcode_'+product_id+'">'+unique_barcode+'</td>';

                po_html += feature_show_val;

                po_html += '<td>'+uqc_name+'</td>' +
                    '<td>'+purchase_value['in_stock']+'</td>' +
                    '<td onkeypress = "return testCharacter(event);" class="number editablearea with_calculation" contenteditable="true" style="color: black;"  onkeyup="costrate(this);" id="cost_rate_' + product_id + '">' + purchase_value['cost_rate'] + '</td>' +
                    '<td class="with_calculation" readonly  id="cost_gst_percent_' + product_id + '">' + purchase_value['cost_gst_percent'] + '</td>' +
                    '<td class="with_calculation" readonly  id="cost_gst_amount_' + product_id + '">' + purchase_value['cost_gst_amount'] + '</td>' +
                    '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addqty(this);" id="qty_' + product_id + '">'+purchase_value['qty']+'</td>' +
                    '<td class="with_calculation" readonly id="total_cost_without_gst_' + product_id + '">'+purchase_value['total_cost_without_gst']+'</td>' +
                    '<td class="with_calculation" readonly id="total_gst_' + product_id + '">'+purchase_value['total_gst']+'</td>' +
                    '<td class="with_calculation" readonly id="total_cost_with_gst_' + product_id + '">'+purchase_value['total_cost_with_gst']+'</td>' +
                    '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;"  onclick="return getdatepickerpo(\'mfg_date_' + product_id + '\');" id="mfg_date_' + product_id + '">'+purchase_value['mfg_date']+'</td>' +
                    '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;" onclick="return getdatepickerpo(\'expiry_date_' + product_id + '\');" id="expiry_date_' + product_id + '">'+purchase_value['expiry_date']+'</td>' +
                    '<td  id="poremarksentry_' + product_id + '"><textarea name="remarks_' + product_id + '" id="remarks_' + product_id + '" >'+purchase_value['remarks']+'</textarea></td>' +
                    '<td></td>' +
                    '</tr>>';
            });
            $("#productsearch").val('');
            $(".odd").hide();
            $("#po_product_detail_record").append(po_html);

            if($("#po_with_unique_barcode").val() == 0)
            {
                $(".po_with_batch_no").hide();
            }else
            {
                $(".po_with_batch_no").show();
            }


            if(po_calculation == 2)
            {
                $(".with_calculation").hide();
            }
            else
            {
                $(".with_calculation").show();
            }

            var totalqty = $("#po_product_detail_record tr").length;

            $(".pototalitems").html(totalqty);
        }
        //end of fillup product detail row


        if(edit_po['supplier_gstdetail'] != '' && edit_po['supplier_gstdetail'] != undefined)
        {
            if(edit_po['supplier_gstdetail']['supplier_gst'] != '')
            {
                var supplier_company_info = edit_po['supplier_gstdetail']['supplier_company_info'];

                if(supplier_company_info['supplier_last_name'] == null)
                {
                    supplier_company_info['supplier_last_name'] = '';
                }

                $("#state_id").val(edit_po['supplier_gstdetail']['state_id']);

                var name_supplier = supplier_company_info['supplier_first_name'] +' '+supplier_company_info['supplier_last_name'] +'_'+edit_po['supplier_gstdetail']['supplier_gstin'];

                $("#supplier_name").val(name_supplier);
            }
        }
    }
    else
    {
        if($("#po_with_unique_barcode").val() == 0)
        {
            $("#generate_unique_barcode").hide();
            $('.po_with_batch_no').hide();
            $("#addpoprint").attr('disabled',false);
            $("#addpo").attr('disabled',false);
        }else
        {
            $("#generate_unique_barcode").show();
            $('.po_with_batch_no').show();
            $("#addpoprint").attr('disabled',true);
            $("#addpo").attr('disabled',true);
        }

        if(po_calculation == 2)
        {
            $(".with_calculation").hide();
        }
        else
        {
            $(".with_calculation").show();
        }
    }




});

function removeporow(productid)
{
    $("#product_"+productid).remove();
    var totalqty = $("#po_product_detail_record tr").length;


    $(".pototalitems").html(totalqty);
    gettotalqty();
}



$("#generate_unique_barcode").click(function()
{
    var total_row = $("#po_product_detail_record tr").length;
    if(total_row == 0)
    {
        toastr.error("Select some product to purchase.");
        return false;
    }
    else
    {
        if(batch_no == '')
        {
            toastr.error("Your unique barcode not generated due to some technical problem.please contact technical team!");
            return false;
        }
        var batchno = batch_no.split('U')[1];

        $("#po_product_detail_record tr").each(function()
        {
            var row_id = $(this).attr('id').split('_')[1];

            batchno++;

            //var btch = $(this).find('#unique_barcode_'+row_id).html();

            if($(this).find('#unique_barcode_'+row_id).html() == '')
            {
                $(this).find("#unique_barcode_"+row_id).html("U"+batchno);
            }
        });
        $("#generate_unique_barcode").hide();
        //$(".po_with_batch_no").css('display','none');

        $("#addpoprint").attr('disabled',false);
        $("#addpo").attr('disabled',false);
    }
});



//FOR DOWNLOAD PO TEMPLATE ACCORDING TO UNIQUE_TYPE..
//1 = PO WITH UNIQUE BARCODE .. 0 =PO WITHOUT UNIQUE BARCODE
$(document).on('click', '#downloadpotmpate', function () {
    var query = {
        po_with_unique_barcode: $("#po_with_unique_barcode").val()
    };
    var url = "po_template?" + $.param(query)
    window.open(url, '_blank');
});

//FOR UPLOAD PO TEMPLATE
$("#upload_po_tmpate").click(function ()
{
    jQuery.noConflict();
    $("#upload_po_popup").modal('show');
});




