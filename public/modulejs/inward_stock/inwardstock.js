
            //Disable cut copy paste
            $('body').bind('cut copy paste', function (e) {
                e.preventDefault();
            });

            //Disable mouse right click
            $("body").on("contextmenu",function(e){
                return false;
            });

//this is used in product name,product barcode and product code suggetion
$("#productsearch").typeahead({

    source: function(request, process)
    {
        var  url = "isproduct_search";
        var type = "post";
        var dataType = "json";
        var data = {
            "search_val": $("#productsearch").val(),
            "unique_barcode_inward": $("#unique_barcode_inward").val(),
            "product_type": $("#inward_type").val(),
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
                      //$(".typeahead.dropdown-menu").find('.dropdown-item').html('');
                   // $(".dropdown-menu .active").html("");
                    //$(".dropdown-menu .active").val("");
                  //  $(".typeahead dropdown-menu").hide();
                   // $(".typeahead dropdown-menu").html('');

                    //getproductdetail(data[0]['product_id']);

                    $(".typeahead.dropdown-menu .active").trigger("click");

                    return false;
                    //$(".dropdown-menu .active").trigger("click");
                }
              }
          }
          else
          {
            $(".typeahead.dropdown-menu").hide();
          }
        });
    },
   // minLength: 1,
    autoselect:true,
   // autoSelect:false,
   // typeahead-select-on-exact="true"
     afterSelect: function (item)
     {
         if(map[item] != undefined)
         {
             $('.loaderContainer').show();

             var value = item;
             var barcode = map[item]['barcode'];
             var product_name = map[item]['product_name'];
             var product_id = map[item]['product_id'];

             getproductdetail(product_id);
         }
    }
});

/*$("#productsearch").keyup(function () {
                jQuery.noConflict();
                 $(this).autocomplete({
                    autoFocus: true,
                    minLength: 1,
                    source: function (request, response) {
                        var url = "product_search";
                        var type = "POST";
                        var data = {
                            'search_val': $("#productsearch").val(),
                            'product_type': $("#inward_type").val()
                        };
                        callroute(url, type, data, function (data) {
                            var searchdata = JSON.parse(data, true);
                             if (searchdata['Success'] == "True") {
                            var result = [];
                                searchdata['Data'].forEach(function (value) {
                                    var display_barcode = '';

                                    if (value.supplier_barcode != " " && value.supplier_barcode != undefined && value.supplier_barcode != '') {
                                        display_barcode = value.supplier_barcode;
                                    } else {
                                        display_barcode = value.product_system_barcode;
                                    }
                                    if (display_barcode != undefined) {
                                        result.push({
                                            label: value.product_name + '_' + display_barcode,
                                            value: value.product_name + '_' + display_barcode,
                                            id: value.product_id
                                        });
                                    }
                                });


                                //push data into result array.and this array used for display suggetion
                                response(result);
                            }
                        });
                    },
                    //this help to call a function when select search suggetion
                    select: function (event, ui) {
                        $(".ui-helper-hidden-accessible").css('display', 'none');
                        var id = ui.item.id;
                        //call a getproductdetail function for getting product detail based on selected product from suggetion
                        getproductdetail(id);
                        },
                });
            });*/

//for unique barcode scan with po
$("#unique_barcode_search").keyup(function()
{
  var unique_barcode = $("#unique_barcode_search").val();
  if(unique_barcode !='')
  {
        $("#product_detail_record tr").each(function()
        {
            var dataid = $(this).data('id');
            var product_id = $(this).attr('id').split('product_')[1];

            var batch_no = $(this).find("#batch_no_"+product_id).html();

            if(batch_no == unique_barcode)
            {
                var poqty  = $(this).find("#po_qty_"+product_id).val();

                $(this).find("#product_qty_"+product_id).html(poqty);

                $(this).find("#product_qty_"+product_id).keyup();

                $("#unique_barcode_search").val('');
                return false;
            }
        })
  }
})


//this is used for display supplier suggestion
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
            callroute(url, type,dataType, data, function (data) {
                var searchsupplier = JSON.parse(data, true);

                            if (searchsupplier['Success'] == "True") {
                                var supplier_detail = searchsupplier['Data'];

                                if (supplier_detail.length > 0) {
                                    var resultsupplier = [];

                                    supplier_detail.forEach(function (value) {
                                        if (value.supplier_gst.length > 0) {
                                            $.each(value.supplier_gst, function (supplierkey, suppliervalue) {
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
                                                    state_id: suppliervalue.state_id
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
                                                label: value.supplier_company_name + ' ' + value.supplier_first_name + ' ' + last_name,
                                                value: value.supplier_company_name + ' ' + value.supplier_first_name + ' ' + last_name,
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
                    select: function (event, ui) {
                        var id = ui.item.id;
                        var gst_id = ui.item.supplier_gst_id;
                        var state_id = ui.item.state_id;
                        $("#gst_id").val(gst_id);
                        $("#state_id").val(state_id);
                        $("#supplier_name").val(id);
                        $(".ui-helper-hidden-accessible").css('display', 'none');
                        //call a function to perform action on select of supplier
                    }
                })
            });

//function for getting product detail based on product suggestion
function getproductdetail(productid)
{
    var billing_type = $("#billing_type").val();
    var inwardtype = $("#inward_type").val();

    var border_css = '';
    if(inwardtype == 1) {
        if (billing_type == 3) {
            border_css = 'border:1px solid red';
        }
    }
    var type = "POST";
    var url = 'product_detail';
    var dataType = "";
    var data = {
        "product_id": productid,
        "unique_barcode_inward": $("#unique_barcode_inward").val(),
    };

    callroute(url,type,dataType,data, function (data)
     {

        var product_data = JSON.parse(data, true);

        if (product_data['Success'] == "True")
        {
           var product_html = '';
            var scan_product_time = moment().format("DD-MM-YYYY h:mm:ss a");



            var product_detail = product_data['Data'][0];

                        var product_code = '';
                        var cost_gst_percent = '0';
                        var profit_percent = '0';
                        var profit_amount = '0';
                        var selling_price = '0';
                        var sell_gst_percent = '0';
                        var product_mrp = '0';
                        var cost_rate = '0';
                        var offer_price = '0';
                        var sell_gst_amount = '0';
                        var cost_gst_amount = '0';
                        var extra_charge = '0';

                        if (product_detail['product_code'] != null || product_detail['product_code'] != undefined) {
                            product_code = product_detail['product_code'];
                        }
                        if (product_detail['cost_rate'] != null || product_detail['cost_rate'] != undefined) {
                            cost_rate = product_detail['cost_rate'];
                        }
                        if (product_detail['cost_gst_percent'] != null || product_detail['cost_gst_percent'] != undefined) {
                            cost_gst_percent = product_detail['cost_gst_percent'];
                        }
                        if (product_detail['extra_charge'] != null || product_detail['extra_charge'] != undefined) {
                            extra_charge = product_detail['extra_charge'];
                        }
                        if (product_detail['profit_percent'] != null || product_detail['profit_percent'] != undefined) {
                            profit_percent = product_detail['profit_percent'];
                        }
                        if (product_detail['profit_amount'] != null || product_detail['profit_amount'] != undefined) {
                            profit_amount = product_detail['profit_amount'];
                        }
                        if (product_detail['selling_price'] != null || product_detail['selling_price'] != undefined) {
                            selling_price = product_detail['selling_price'];
                        }
                        if (product_detail['sell_gst_percent'] != null || product_detail['sell_gst_percent'] != undefined) {
                            sell_gst_percent = product_detail['sell_gst_percent'];
                        }
                        if (product_detail['product_mrp'] != null || product_detail['product_mrp'] != undefined) {
                            product_mrp = product_detail['product_mrp'];
                        }
                        if (product_detail['cost_rate'] != null || product_detail['cost_rate'] != undefined) {
                            cost_rate = product_detail['cost_rate'];
                        }
                        if (product_detail['offer_price'] != null || product_detail['offer_price'] != undefined) {
                            offer_price = product_detail['offer_price'];
                        }
                        if (product_detail['sell_gst_amount'] != null || product_detail['sell_gst_amount'] != undefined) {
                            sell_gst_amount = product_detail['sell_gst_amount'];
                        }
                        if (product_detail['cost_gst_amount'] != null || product_detail['cost_gst_amount'] != undefined) {
                            cost_gst_amount = product_detail['cost_gst_amount'];
                        }

                        var inward_show_dynamic_feature = $("#show_inward_dynamic_feature").val();
                        var feature_show_val = "";
                        if(inward_show_dynamic_feature != '')
                        {
                            var feature = inward_show_dynamic_feature.split(',');
                            $.each(feature,function(fea_key,fea_val)
                            {
                                var feature_name = '';
                                if(typeof(product_detail[fea_val]) != "undefined" && product_detail[fea_val] !== null) {
                                    feature_name = product_detail[fea_val];
                                }
                                feature_show_val += '<td>' + feature_name + '</td>';
                            })
                        }

                        product_detail['default_qty'] = 1;

                        var rowCount = $('#product_detail_record tr').length;
                        rowCount++;

                        var product_id = product_detail['product_id'];
                        var samerow = 0;
                        /*if(inwardtype == 2)
                        {*/
                            $("#product_detail_record tr").each(function ()
                            {
                                var row_product_id = $(this).attr('id').split('_')[1];
                                if (row_product_id == product_id)
                                {
                                     var qty = $("#product_qty_"+product_id).html();

                                    var product_qty = ((Number(qty)) + (Number(1)));

                                    $("#product_qty_"+product_id).html(product_qty);
                                    samerow = 1;
                                    var cost_rate = $("#cost_rate_"+product_id).html();
                                    var gst_amount = $("#gst_amount_"+product_id).html();
                                    var cost_price = ((Number(cost_rate)) +  (Number(gst_amount)));

                                    var total_qty = ((Number(qty)) + (Number(1)) + (Number($("#free_qty_"+product_id).html())));
                                    var total_cost = ((Number(cost_price)) * (Number(total_qty)));

                                    $("#total_cost_"+product_id).html(total_cost.toFixed(4));
                                    $("#product_qty_" + product_id).keyup();
                                    totalcalculation();
                                    return false;
                                }
                            });
                        /*}*/

                        if (samerow == 0)
                        {
                            var barcode = '';
                            if (product_detail['supplier_barcode'] != " " && product_detail['supplier_barcode'] != undefined && product_detail['supplier_barcode'] != null) {
                                barcode = product_detail['supplier_barcode'];
                            } else {
                                barcode = product_detail['product_system_barcode'];
                            }

                            if (product_html == '')
                            {
                                product_html += '<tr id="product_' + product_id + '" data-id="' + rowCount + '">' +
                                    '<input type="hidden" name="product_scan_time_'+product_id+'" id="product_scan_time_'+product_id+'" value="'+scan_product_time+'">'+
                                    '<input type="hidden" name="inward_product_detail_id_' + product_id + '" id="inward_product_detail_id_' + product_id + '" value="">' +
                                    '<input type="hidden" name="stock_transfers_detail_id_' + product_id + '" id="stock_transfers_detail_id_' + product_id + '" value="">' +

                                    '<td onclick="removerow(' + rowCount + ');"><i class="fa fa-close"></i></td>' +
                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addproductqty(this);" id="product_qty_' + product_id + '">'+product_detail['default_qty']+'</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea garment_case_hide" contenteditable="true" style="color: black;" onkeyup="freeqty(this);" id="free_qty_' + product_id + '">0</td>' +
                                    '<td>' + barcode + '</td>' +
                                    '<td>' + product_detail['product_name'] + '</td>' +
                                    '<td>' + product_code + '</td>';

                                product_html += feature_show_val;

                                product_html +=  '<td  class="editablearea garment_case_hide show_in_unique" contenteditable="true" style="color: black;'+border_css+'" id="batch_no_' + product_id + '"></td>' +

                                    '<td   onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;"  onkeyup="baseprice(this);" id="base_price_' + product_id + '">' + cost_rate + '</td>' +

                                    '<td  onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="discountpercent(this);" id="base_discount_percent_' + product_id + '">0</td>' +

                                    '<td  class="inward_calculation_case" id="base_discount_amount_' + product_id + '">0</td>' +
                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea garment_case_hide inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="schemepercent(this);"  id="scheme_discount_percent_' + product_id + '">0</td>' +

                                    '<td  class="garment_case_hide inward_calculation_case" id="scheme_discount_amount_' + product_id + '">0</td>' +

                                    '<td class="garment_case_hide inward_calculation_case" readonly  id="free_discount_percent_' + product_id + '">0</td>' +

                                    '<td class="garment_case_hide inward_calculation_case"  readonly id="free_discount_amount_' + product_id + '">0</td>' +

                                    '<td class="inward_calculation_case"  readonly  id="cost_rate_' + product_id + '">' + cost_rate + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="costgstpercent(this);" id="gst_percent_' + product_id + '">' + cost_gst_percent + '</td>' +

                                    '<td class="inward_calculation_case"  readonly id="gst_amount_' + product_id + '">' + cost_gst_amount + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="extracharge(this);" id="extra_charge_' + product_id + '">' + extra_charge + '</td>' +

                                    '<td class="inward_calculation_case"  readonly id="profit_percent_' + product_id + '">' + profit_percent + '</td>' +

                                    '<td class="inward_calculation_case" readonly id="profit_amount_' + product_id + '">' + profit_amount + '</td>' +

                                    '<td class="inward_calculation_case" readonly id="sell_price_' + product_id + '">' + selling_price + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="sellinggstpercent(this);" id="selling_gst_percent_' + product_id + '">' + sell_gst_percent + '</td>' +

                                    '<td class="inward_calculation_case" id="selling_gst_amount_' + product_id + '">' + sell_gst_amount + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="offerprice(this);" id="offer_price_' + product_id + '">' + offer_price + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" id="product_mrp_' + product_id + '">' + product_mrp + '</td>' +

                                    '<td contenteditable="true" class="editablearea garment_case_hide" style="color: black;"  onclick="return getdatepicker(\'mfg_date_' + product_id + '\','+rowCount+');" id="mfg_date_' + product_id + '" ></td>' + '<td contenteditable="true" class="editablearea garment_case_hide" style="color: black;" onclick="return getdatepicker(\'expiry_date_' + product_id + '\','+rowCount+');" id="expiry_date_' + product_id + '"></td>' +
                                    '<td class="inward_calculation_case" readonly id="total_cost_' + product_id + '"></td>' +
                                    '</tr>';
                            } else {
                                product_html += product_html + '<tr id="product_' + product_id + '" data-id="' + rowCount + '">' +

                                    '<input type="hidden" name="product_scan_time_'+product_id+'" id="product_scan_time_'+product_id+'" value="'+scan_product_time+'">'+

                                    '<input type="hidden" name="inward_product_detail_id_' + product_id + '" id="inward_product_detail_id_' + product_id + '" value="">' +
                                    '<input type="hidden" name="stock_transfers_detail_id_' + product_id + '" id="stock_transfers_detail_id_' + product_id + '" value="">' +
                                    '<td  onclick="removerow(' + rowCount + ');"><i class="fa fa-close"></i></td>' +
                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addproductqty(this);" id="product_qty_' + product_id + '">'+product_detail['default_qty']+'</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea garment_case_hide" contenteditable="true" style="color: black;" onkeyup="freeqty(this);" id="free_qty_' + product_id + '">0</td>' +

                                    '<td>' + product_detail['product_system_barcode'] + '</td>' +

                                    '<td >' + product_detail['product_name'] + '</td>' +

                                    '<td>' + product_code + '</td>';

                                product_html += feature_show_val;

                                product_html += '<td class="editablearea garment_case_hide show_in_unique" contenteditable="true" style="color: black;'+border_css+'" id="batch_no_' + product_id + '"></td>' +

                                    '<td  onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="baseprice(this);" id="base_price_' + product_id + '">' + cost_rate + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="discountpercent(this);" id="base_discount_percent_' + product_id + '">0</td>' +

                                    '<td  class="inward_calculation_case"  id="base_discount_amount_' + product_id + '">0</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea garment_case_hide inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="schemepercent(this);"  id="scheme_discount_percent_' + product_id + '">0</td>' +

                                    '<td  class="garment_case_hide inward_calculation_case" id="scheme_discount_amount_' + product_id + '">0</td>' +

                                    '<td class="garment_case_hide inward_calculation_case" readonly  id="free_discount_percent_' + product_id + '">0</td>' +

                                    '<td class="garment_case_hide inward_calculation_case"  readonly id="free_discount_amount_' + product_id + '">0</td>' +

                                    '<td class="inward_calculation_case" readonly  id="cost_rate_' + product_id + '">' + cost_rate + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="costgstpercent(this);" id="gst_percent_' + product_id + '">' + cost_gst_percent + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" id="gst_amount_' + product_id + '">' + cost_gst_amount + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="extracharge(this);" id="extra_charge_' + product_id + '">' + extra_charge + '</td>' +

                                    '<td class="inward_calculation_case" readonly id="profit_percent_' + product_id + '">' + profit_percent + '</td>' +

                                    '<td class="inward_calculation_case"  readonly id="profit_amount_' + product_id + '">' + profit_amount + '</td>' +

                                    '<td class="inward_calculation_case" readonly id="sell_price_' + product_id + '">' + selling_price + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="sellinggstpercent(this);" id="selling_gst_percent_' + product_id + '">' + sell_gst_percent + '</td>' +

                                    '<td class="inward_calculation_case" id="selling_gst_amount_' + product_id + '">' + sell_gst_amount + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="offerprice(this);" id="offer_price_' + product_id + '">' + offer_price + '</td>' +

                                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" id="product_mrp_' + product_id + '">' + product_mrp + '</td>' +

                                    '<td contenteditable="true" class="editablearea garment_case_hide" style="color: black;"  onclick="return getdatepicker(\'mfg_date_' + product_id + '\','+rowCount+');" id="mfg_date_' + product_id + '"></td>' +

                                    '<td contenteditable="true" class="editablearea garment_case_hide" style="color: black;" onclick="return getdatepicker(\'expiry_date_' + product_id + '\','+rowCount+');" id="expiry_date_' + product_id + '"></td>' +

                                    '<td class="inward_calculation_case" readonly id="total_cost_' + product_id + '"></td>' +
                                    '</tr>';
                            }
                        }
                    }
                    $("#po_pending_show").hide();
                    $("#productsearch").val('');
                    $(".odd").hide();

                    $("#product_detail_record").prepend(product_html);

                    $("#product_detail_record").find('tr[data-id='+rowCount+']').find("#product_qty_"+product_id).keyup();

                     //COUNT TOTAL NO OF ITEMS IN POPUP
                     var totalitem = $("#product_detail_record tr").length;
                     $(".totcount").html(totalitem);

                    if ($("#inward_type").val() == 2)
                    {
                        $(".garment_case_hide").hide();

                        if($("#unique_barcode_inward").val() == '1')
                        {
                            $(".show_in_unique").show();
                        }
                    }
                    else
                    {
                        $(".garment_case_hide").show();
                    }


                    if(inward_calculation == 3) {
                        $(".inward_calculation_case").hide();
                    }
                    else
                    {
                        $(".inward_calculation_case").show();
                    }

                    if(inward_calculation == 2) {
                        $("#roundoff_offer").attr('disabled', false);
                        $("#addinwardstock").attr('disabled', true);
                    }
                });
                     $('.loaderContainer').hide();
            }

function getdatepicker(id,rowid)
{
    var expiry_date = id.replace('mfg_date_','expiry_date_');

    var idrow = id.split('_')[2];

    $("#product_detail_record").find('tr').find("#"+id).datepicker({
        format: "dd-mm-yyyy",
        orientation: "bottom"
    }).on('changeDate', function (e)
    {
        var date = new Date($(this).datepicker('getDate'));

        var selectedDate = (("0" + date.getDate()).slice(-2) + '-' + (("0" + (date.getMonth() + 1)).slice(-2)) + '-' + date.getFullYear());

        $(this).html(selectedDate);

        if(id.startsWith('expiry_date_'))
        {
            $("#mfg_date_"+idrow).datepicker('setEndDate', date);
        }

        if (id.startsWith('mfg_date_'))
        {
            var minDate = new Date(e.date.valueOf());
            $('#'+expiry_date).datepicker('setStartDate', minDate);
        }
    }).on('keypress paste', function (e) {
    e.preventDefault();
    return false;
});

}

var currentDate = new Date();

$('#inward_date,#invoice_date').datepicker({
    autoclose: true,
    format: "dd-mm-yyyy",
    immediateUpdates: true,
    todayBtn: true,
    orientation: "bottom",
    todayHighlight: true,
}).datepicker("setDate", "0").on('keydown', function (e) {
    e.preventDefault();
    return false;
});
(function ($) {
    $.fn.focusTextToEnd = function () {
        var $thisVal = this.val();
        this.val('').val($thisVal);
        this.focus();
        return this;
    }
}(jQuery));

$("#addinwardstock").click(function ()
{
    if (validate_inwardstockform('inwardstock'))
    {
        $(".loaderContainer").show();
        $("#addinwardstock").prop('disabled', true);
        var inward_detail = [];
        var product_info = [];
        var payment_info = [];
        var inward_stock_detail = {};
        var inward_stock = [];

        inward_detail['product_detail'] = product_info;
        inward_detail['supplier_payment_detail'] = payment_info;
        inward_stock_detail['supplier_gst_id'] = $("#gst_id").val();
        inward_stock_detail['state_id'] = $("#state_id").val();
        inward_stock_detail['warehouse_id'] = $("#warehouse_id").val();
        inward_stock_detail['invoice_no'] = $("#invoice_grn_no").val();
        inward_stock_detail['invoice_date'] = $("#invoice_date").val();//current date
        inward_stock_detail['inward_date'] = $("#inward_date").val();
        inward_stock_detail['total_qty'] = $("#total_qty").val();
        inward_stock_detail['total_gross'] = $("#gross_total").val();
        inward_stock_detail['total_grand_amount'] = $("#grand_total").val();
        inward_stock_detail['po_no'] = $("#po_no").val();
        inward_stock_detail['note'] = $("#note").val();
        inward_stock_detail['total_cost_igst_amount'] = 0;
        inward_stock_detail['total_cost_cgst_amount'] = 0;
        inward_stock_detail['total_cost_sgst_amount'] = 0;
        inward_stock_detail['inward_type'] = $("#inward_type").val();
        inward_stock_detail['inward_with_unique_barcode'] = $("#unique_barcode_inward").val();
        inward_stock_detail['stock_inward_type'] = $("#stock_inward_type").val();
        inward_stock_detail['due_days'] = $("#inward_unpaid_amt_due_days").val();
        inward_stock_detail['due_date'] = $("#inward_unpaid_due_date").val();

        //inward_detail['inward_stock_detail'] = inward_stock_detail;
        var igst = 0;
        var cgst = 0;
        var sgst = 0;

        //getting product row info
        var cost_rate_inward = '';
        $("#product_detail_record tr").each(function (index, e)
        {
            var rowcount = $(this).data('id');
            $("#product_detail_record").find("tr[data-id='" + rowcount + "']").each(function (key, keyval)
            {
                var product_detail = {};
                var tr = $(this).attr('id');
                var product_id = tr.split('product_')[1];

                var id = '';
                var values = '';

                product_detail['product_scan_time'] = $(this).find('#product_scan_time_' + product_id).val();
                product_detail['inward_product_detail_id'] = $(this).find('#inward_product_detail_id_' + product_id).val();
                product_detail['stock_transfers_detail_id'] = $(this).find('#stock_transfers_detail_id_' + product_id).val();
                product_detail['product_id'] = product_id;
                inward_stock['product_id'] = product_id;
                var cost_price = '';
                var company_state = $("#company_state_id").val();
                var supplier_state = $("#state_id").val();

                $(this).find('td').each(function ()
                {
                    if ($(this).attr('id') != undefined)
                    {
                        if ($(this).attr('id') == 'gst_percent_' + product_id + '')
                        {
                            var cost_rate = $("#product_detail_record").find("tr[data-id='" + rowcount + "']").find("#cost_rate_"+product_id).html();
                            var cost_gst_percent = $("#product_detail_record").find("tr[data-id='" + rowcount + "']").find("#gst_percent_" + product_id).html();
                            var cost_gst_amount = $("#product_detail_record").find("tr[data-id='" + rowcount + "']").find("#gst_amount_" + product_id).html();

                            var cost_cgst_sgst_percent = 0.00;
                            var cost_cgst_sgst_amount = 0.00;
                            var product_qty = $("#product_detail_record").find("tr[data-id='" + rowcount + "']").find("#product_qty_" + product_id).html();
                            var free_qty = $("#product_detail_record").find("tr[data-id='" + rowcount + "']").find("#free_qty_" + product_id).html();

                            product_detail['total_cost_rate_with_qty'] = ((Number(cost_rate)) * (Number(product_qty) + (Number(free_qty))));

                            var gst_cal = ''; //1=igst,2=cgst,sgst
                            if(tax_type == 1)
                            {
                                gst_cal = 1;
                            }
                            else{
                                if(company_state == supplier_state || supplier_state == '')
                                {
                                    gst_cal = 2;
                                }
                                else
                                {
                                    gst_cal = 1;
                                }
                            }

                            //if ((company_state == supplier_state || supplier_state == '') && tax_type != '2')
                            if (gst_cal == 2)
                            {
                                if (cost_gst_percent != 0)
                                {
                                    cost_cgst_sgst_percent = ((Number(cost_gst_percent)) / 2).toFixed(4);
                                    cost_cgst_sgst_amount = ((Number(cost_gst_amount)) / 2).toFixed(4);
                                }
                                product_detail['cost_igst_percent'] = 0.00;
                                product_detail['cost_igst_amount'] = 0.00;
                                product_detail['cost_cgst_percent'] = cost_cgst_sgst_percent;
                                product_detail['cost_cgst_amount'] = cost_cgst_sgst_amount;
                                product_detail['cost_sgst_percent'] = cost_cgst_sgst_percent;
                                product_detail['cost_sgst_amount'] = cost_cgst_sgst_amount;
                                product_detail['total_igst_amount_with_qty'] = 0.00;
                                product_detail['total_cgst_amount_with_qty'] = ((Number(cost_cgst_sgst_amount)) * (Number(product_qty) + (Number(free_qty))));
                                product_detail['total_sgst_amount_with_qty'] = ((Number(cost_cgst_sgst_amount)) * (Number(product_qty) + (Number(free_qty))));
                            }
                            else
                                {

                                product_detail['cost_igst_percent'] = cost_gst_percent;
                                product_detail['cost_igst_amount'] = cost_gst_amount;
                                product_detail['cost_cgst_percent'] = 0.00;
                                product_detail['cost_cgst_amount'] = 0.00;
                                product_detail['cost_sgst_percent'] = 0.00;
                                product_detail['cost_sgst_amount'] = 0.00;
                                product_detail['total_igst_amount_with_qty'] = ((Number(cost_gst_amount)) * (Number(product_qty) + (Number(free_qty))));
                                product_detail['total_cgst_amount_with_qty'] = 0.00;
                                product_detail['total_sgst_amount_with_qty'] = 0.00;
                            }
                        } else {
                            id = $(this).attr('id').split('_' + product_id)[0];
                            values = $(this).html();
                            product_detail[id] = values;
                        }
                    }
                });

                var cost_rate = $(this).find("#cost_rate_" + product_id).html();
                cost_rate_inward = ((Number(product_detail['total_cost_rate_with_qty'])) + (Number(cost_rate_inward)));
                var cost_gst_amount = $("#gst_amount_" + product_id).html();
                cost_price = ((Number(cost_rate)) + (Number(cost_gst_amount))).toFixed(4);
                product_detail['cost_price'] = cost_price;
                inward_stock_detail['cost_rate'] = cost_rate_inward;
                product_detail['supplier_gst_id'] = $("#gst_id").val();

                product_info.push(product_detail);

                igst += (((Number(product_detail['product_qty']) + (Number(product_detail['free_qty']))) * (Number(product_detail['cost_igst_amount']))));

                cgst += (((Number(product_detail['product_qty']) + (Number(product_detail['free_qty']))) * (Number(product_detail['cost_cgst_amount']))));
                sgst += (((Number(product_detail['product_qty']) + (Number(product_detail['free_qty']))) * (Number(product_detail['cost_sgst_amount']))));
            });

            inward_stock_detail['total_cost_igst_amount'] = igst.toFixed(4);
            inward_stock_detail['total_cost_cgst_amount'] = cgst.toFixed(4);
            inward_stock_detail['total_cost_sgst_amount'] = sgst.toFixed(4);
        });

        //end of getting row info
        var is_payment_clear = 1;
        //getting payment value
        $("#paymentmethoddiv").each(function ()
        {
            var paymentid = '';
            $(this).find('.paymentdiv').each(function (index, item)
            {
                var paymentarr = {};
                var paymentmethod = ($(this).find('input').attr('id'));
                var field_name = ($(this).find('input').data('field_name'));
                if ($("#" + paymentmethod).val() != '' && $("#" + paymentmethod).val() != 0)
                {
                    paymentid = $("#" + paymentmethod).data("id");

                    if (paymentid == 6)
                    {
                        is_payment_clear = 0;
                    }
                    paymentarr[field_name] = paymentid;
                    paymentarr['outstanding_payment'] = $("#outstanding_payment_" +paymentid).val();
                    paymentarr['amount'] = $("#" + paymentmethod).val();
                    paymentarr['supplier_payment_detail_id'] = $("#supplier_payment_detail_id_" + paymentid).val();
                    payment_info.push(paymentarr);
                }
            });
        });
        //end of getting payment value
        inward_stock_detail['is_payment_clear'] = is_payment_clear;
        inward_stock.push(inward_stock_detail);

        var inward_type = $("#inward_type").val();
        var url_route = '';

        if($("#unique_barcode_inward").val() == 1)
        {
            url_route = "add_unique_inward_stock";
        }
        else {
            if (inward_type == 1) {
                url_route = "add_fmcg_inward_stock";
            } else {
                url_route = "add_garment_inward_stock";
            }
        }


        var url = url_route;
        var type = "POST";
        var dataType = "";
        var data = {
            'inward_stock': inward_stock,
            'inward_product_detail': product_info,
            'supplier_payment_detail': payment_info,
            'inward_stock_id': $("#inward_stock_id").val(),
            'debit_note_id': $("#debit_note_id").val(),
            'update_offer_price': $("#update_offer_price").val(),
        };

        callroute(url,type,dataType,data, function (data)
        {
            $(".loaderContainer").hide();
            $("#addinwardstock").prop('disabled', false);
            $("#upload").attr('disabled',false);
            $("#fileUpload").attr('disabled',false);
            var dta = JSON.parse(data);

            if (dta['Success'] == "True")
            {
                $("#update_offer_price").val(0);
                toastr.success(dta['Message']);
                $("#inwardstock").trigger('reset');
                $("#product_detail_record").empty();
                $('#inward_date,#invoice_date').datepicker({
                    autoclose: true,
                    format: "dd-mm-yyyy",
                    immediateUpdates: true,
                    todayBtn: true,
                    orientation: "bottom",
                    todayHighlight: true
                }).datepicker("setDate", "0").on('keypress paste', function (e) {
                    e.preventDefault();
                    return false;
                });

                $("#po_no").attr('disabled', false);

                if (dta['url'] != '' && dta['url'] != 'undefined')
                {
                    /*remove localstorage value of edit inward*/
                    localStorage.removeItem('edit_inward_stock_record');
                    localStorage.removeItem('take_po_inward_data');
                    localStorage.removeItem('take_stock_inward_data');
                    setTimeout(function () {
                        window.location.href = dta['url'];
                    }, 1000);
                }
                else
                {
                    location.reload();
                }

            } else {
                if (dta['status_code'] == 409) {
                    /* $.each(dta['Message'],function (errkey,errval)
                     {*/
                    var errmessage = dta['Message'];
                    swal({
                            title: errmessage,
                            type: "warning",
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Yes!",
                            showCancelButton: true,
                            closeOnConfirm: false,
                            closeOnCancel: true,
                            showConfirmButton :false
                        },
                        function (isConfirm) {
                            if (isConfirm) {
                                swal({
                                        title: "If Prevoius inward MRP match with this inward then quntity will be plus on existing inward",
                                        type: "info",
                                        confirmButtonClass: "btn-danger",
                                        confirmButtonText: "Yes!",
                                        showCancelButton: true,
                                        closeOnConfirm: true,
                                        closeOnCancel: false
                                    },
                                    function (isConfirm) {
                                        if (isConfirm) {
                                            var inward_id = dta['edit_id'];
                                            edit_inward(inward_id);
                                        } else {
                                            swal("Cancelled", "You can change invoice no or supplier", "info");
                                        }
                                    });
                            } else {
                                swal("Cancelled", "You can change invoice no or supplier", "info");
                            }

                        });

                } else if (dta['status_code'] == 410) {
                    var errmsg = dta['Message'];
                    swal({
                            title: errmsg,
                            type: "warning",
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Yes!",
                            showCancelButton: true,
                            closeOnConfirm: false,
                            closeOnCancel: false
                        },
                        function (isConfirm) {
                            if (isConfirm) {
                                swal({
                                        title: "This Product Offer Price Will Be Updated for all qty with this new offer price.are you sure want to continue!",
                                        type: "info",
                                        confirmButtonClass: "btn-danger",
                                        confirmButtonText: "Yes!",
                                        showCancelButton: true,
                                        closeOnConfirm: true,
                                        closeOnCancel: false
                                    },
                                    function (isConfirm) {
                                        if (isConfirm) {
                                            $("#update_offer_price").val(1);
                                            $("#addinwardstock").click();
                                        } else {
                                            swal("Cancelled", "You can change offer price or batch no.", "info");
                                        }
                                    });
                            } else {
                                swal("Cancelled", "You can change offer price or batch no.", "info");
                            }

                        });

                } else {
                    toastr.error(dta['Message']);
                }
            }
        })
    } else {
        $("#addinwardstock").prop('disabled', false);
        return false;
    }
});

function validate_inwardstockform(frmid)
{
    var error = 0;
    var rowlength = $('#product_detail_record').find('tr').length;
    if (rowlength == 0)
    {
        error = 1;
        toastr.error("Select some product to inward!");
        return false;
    }
    else
    {
        var inwardtype = $("#inward_type").val();

        var billing_type = $("#billing_type").val();

        $("#product_detail_record tr").each(function ()
        {

            var rowcount = $(this).data('id');

                $("#product_detail_record").find("tr[data-id='" + rowcount + "']").each(function ()
                    /* $(this).find('tr').each(function ()*/ {
                    var tr = $(this).attr('id');
                    var product_id = tr.split('product_')[1];
                    if($("#unique_barcode_inward").val() != 1) {
                        var sameproductlen = $("#product_detail_record").find('tr[id="product_' + product_id + '"]').length;
                        if (sameproductlen > 1) {
                            error = 1;
                            var pname = $(this).find('td .informative').html();
                            toastr.error(pname + " have dublicate row");
                            return false;
                        }
                    }

                    var batch_no = $(this).find("#batch_no_" + product_id).html();

                if(inwardtype == 1)
                {
                    if(billing_type == 3)
                    {
                        var b_product_qty = $(this).find("#product_qty_" + product_id).html();
                        if (batch_no == '' && b_product_qty != 0)
                        {
                            error = 1;
                            $(this).css('background-color', 'red');
                            toastr.error("Batch No Can not be empty!");
                            return false;
                        }
                        else {
                            $(this).css('background-color', '');
                        }
                    }

                    var mfg_date = $(this).find("#mfg_date_" + product_id).html();
                    var expiry_date = $(this).find("#expiry_date_" + product_id).html();

                    if (mfg_date != '' || expiry_date != '')
                    {
                        //var batch_no = $(this).find("#batch_no_" + product_id).html();

                        if (batch_no == '')
                        {
                            error = 1;
                            $(this).css('background-color', 'red');
                            toastr.error("Batch No Can not be empty!");
                            return false;
                        }
                        else{
                            $(this).css('background-color', '');
                        }
                    }

                   if(mfg_date != '')
                    {
                        if (ValidateDate(mfg_date) == 0)
                        {
                            error = 1;
                            $(this).css('background-color', 'red');
                            toastr.error("Add mfg_date in format of dd-mm-YYYY!");
                        }
                        else
                        {
                            $(this).css('background-color', '');
                        }
                    }

                    if(expiry_date != '')
                    {
                        if (ValidateDate(expiry_date) == 0)
                        {
                            error = 1;
                            $(this).css('background-color', 'red');
                            toastr.error("Add expiry_date in format of dd-mm-YYYY!");
                        }
                        else
                        {
                            $(this).css('background-color', '');
                        }
                    }

                $("#product_detail_record").find("tr[id=product_" + product_id + "]").each(function ()
                {

                    var rowcnt = $(this).attr('data-id');
                    if (rowcnt != rowcount)
                    {
                        var bch_no = $(this).find('td[id=batch_no_' + product_id + ']').html();

                        if (bch_no == batch_no)
                        {
                            $(this).css('background-color', 'cadetblue');
                            error = 1;
                            toastr.error("Same Product do not have same batch no!");
                            return false;
                        }
                        else
                        {
                            $(this).css('background-color', '');
                        }
                    }
                });
                }

                if ($(this).find("#free_qty_" + product_id).html() == '')
                {
                    $(this).find("#free_qty_" + product_id).html(0);
                }

                if ($(this).find("#product_qty_" + product_id).html() == '')
                {
                    $(this).find("#product_qty_" + product_id).html(0);

                    if ($(this).find("#po_no").val() != '' && $(this).find("#po_no").val() != undefined)
                    {
                        $(this).find("#pending_qty_" + product_id).html($(this).find("#po_qty_" + product_id).val());
                    }
                }
                if ($(this).find("#extra_charge_" + product_id).html() == '')
                {
                    $(this).find("#extra_charge_" + product_id).html(0);
                }

               /* if(batch_no != '' && batch_no != undefined)
                {
                    if($("#mfg_date_"+product_id).html() != '' || $("#expiry_date_"+product_id).html() == '')
                    {
                        error = 1;
                        toastr.error("Expity Date Can not be empty!");
                        return false;
                    }
                    if($("#expiry_date_"+product_id).html() == '')
                    {
                        error = 1;
                        toastr.error("Exp Date Can not be empty!");
                        return false;
                    }
                }*/


                if(inward_calculation != 3) {
                    var total_cost = $(this).find("#total_cost_" + product_id).html();

                    if (total_cost == '') {
                        error = 1;
                        $(this).css('background-color', 'red');
                        toastr.error("Total Cost Can not be empty!");
                        return false;
                    }
                    else {
                        $(this).css('background-color', '');
                    }
                    var offer_price = $(this).find("#offer_price_" + product_id).html();

                    if (offer_price == '' || offer_price == 0) {
                        error = 1;
                        $(this).css('background-color', 'red');
                        toastr.error("Offer Price is not valid!");
                        return false;
                    }
                    else {
                        $(this).css('background-color', '');
                    }

                    var mrp = $(this).find("#product_mrp_" + product_id).html();

                    if (parseInt(mrp) < parseInt(offer_price)) {
                        error = 1;
                        $(this).css('background-color', 'red');
                        toastr.error("MRP should not less than offer price!");
                        return false;
                    }
                    else {
                        $(this).css('background-color', '');
                    }

                    if (($("#gross_total_disp").val() == '') || ($("#gross_total_disp").val() == '0')) {
                        error = 1;
                        $(this).css('background-color', 'red');
                        toastr.error("Gross total can not be empty!");
                        return false;
                    }
                    else{
                        $(this).css('background-color', '');
                    }
                    if ($("#grand_total_disp").val() == '' || $("#grand_total_disp").val() == '0') {
                        error = 1;
                        $(this).css('background-color', 'red');
                        toastr.error("Grand total can not be empty!");
                        return false;
                    }
                    else{
                        $(this).css('background-color', '');
                    }
                    if($("#stock_inward_type").val() == 0) {
                    var check_all_value_empty = $('.paymentdiv input').filter(function ()
                    {
                        return this.value != '';
                    });

                            if (check_all_value_empty.length == 0) {
                                error = 1;
                                toastr.error("Payment Can not be empty!");
                            }


                        }
                    }
                });


            if (error == 1) {
                return false;
            } else {
                return true;
            }
        });

        $("#paymentmethoddiv").each(function () {
            var paymentid = '';
            $(this).find('.paymentdiv').each(function (index, item) {
                var paymentmethod = ($(this).find('input').attr('id'));

                if (paymentmethod == "cheque" && $("#" + paymentmethod).val() != '' && $("#" + paymentmethod).val() > 0) {
                    if ($("#note").val() == '') {
                        error = 1;
                        toastr.error("Plese write cheque no and bank no.");
                        return false;
                    }
                }
            });
        });

        if ($("#stock_inward_type").val() != 2) {
            if ($("#outstanding_amount").val() > 0) {
                if ($("#inward_unpaid_amt_due_days").val() == '') {
                    error = 1;
                    toastr.error("Due days can not be empty!");
                }
                if ($("#inward_unpaid_due_date").val() == '') {
                    error = 1;
                    toastr.error("Due date can not be empty!");
                }
            }
        }
        if ($("#total_qty").val() == '' || $("#total_qty").val() == '0') {
            error = 1;
            toastr.error("Total qty can not be empty!");
            return false;
        }

        if ($("#invoice_date").val() == '') {
            error = 1;
            toastr.error("select invoice date!");
            return false;
        }

        if ($("#inward_date").val() == '') {
            error = 1;
            toastr.error("select inward date!");
            return false;
        }

        if ($("#invoice_grn_no").val() == '') {
            error = 1;
            toastr.error("Invoice/GRN No. can not be empty!");
            return false;
        }

        if($("#stock_inward_type").val() == 0) {
            if ($("#state_id").val() == '') {
                error = 1;
                toastr.error("supplier name can not be empty!");
                return false;
            }
        }

        if($("#stock_inward_type").val() == 2) {
                if ($("#warehouse_id").val() == '') {
                    error = 1;
                    toastr.error("Warehouse can not be empty!");
                    return false;
                }
            }


    }

    if (error == 1)
    {
        return false;
    } else {
        return true;
    }
}

$("#paymentdiv").click(function()
{
    var tblrow = $("#product_detail_record tr").length;

    if(tblrow > 0)
    {
        $(this).find('input').attr('disabled',false);
    }
    else
    {
        toastr.error("Inward some product with qty to pay something!");
        $(this).find('input').attr('disabled',true);
    }
});

$("#card,#cheque,#net_banking,#wallet,#cash").click(function ()
{
    var tblrow = $("#product_detail_record tr").length;

    if (tblrow > 0)
    {
        var check_all_value_empty = $('.paymentdiv input').filter(function ()
        {
                return this.value != '';
        });

        if(check_all_value_empty.length == 0)
        {
            $(this).val($("#grand_total_disp").val());
        }
    }

});

$("#card,#cheque,#net_banking,#wallet,#cash").keyup(function (e)
{

    var cash = $('#cash').val();
    var id = $(this).attr('id');
    var card = $('#card').val();
    var cheque = $('#cheque').val();
    var net_banking = $('#net_banking').val();
    var wallet = $('#wallet').val();
    var grand_total = $('#grand_total_disp').val();
    var outstanding_amount = $('#outstanding_amount').val();
    var debit_note = $('#debit_note').val();
    var cash_balance = 0;

    if(id == "cheque")
    {
        if($(this).val() > 0)
        {
            $("#note").addClass('invalid');
        }
        else
        {
            $("#note").removeClass('invalid');
        }
    }

    cash_balance = (Number(grand_total) - Number(card) - Number(cheque) - Number(net_banking) - Number(wallet) - Number(cash) - Number(debit_note));

    $('#outstanding_amount').val(cash_balance);
    var out_id = $("#outstanding_amount").data('id');

    $("#outstanding_payment_"+out_id).val(cash_balance);

    if(inward_calculation != 3) {
        if (cash_balance == '' || cash_balance == 0) {
            $("#inward_unpaid_amt_due_days").val('');
            $("#inward_unpaid_due_date").val('');
            $(".unpaid").hide();
        }
        else{
            $(".unpaid").show();
        }
    }
    if (Number(cash_balance) < 0) {
        toastr.error("Amout cannot be greater than Total Sales_amount " + grand_total);
        $('#'+id).val(0);
        cash_balance = (Number(grand_total) - Number($("#cheque").val()) - Number($("#net_banking").val()) - Number($("#wallet").val()) - Number($("#card").val()) - Number($("#cash").val()) - Number(debit_note));
        $('#outstanding_amount').val(cash_balance);
        var out_id = $("#outstanding_amount").data('id');

        $("#outstanding_payment_"+out_id).val(cash_balance);

    }
});


$('#outstanding_amount').keyup(function (e)
{
    var cash = $('#cash').val();
    var card = $('#card').val();
    var cheque = $('#cheque').val();
    var net_banking = $('#net_banking').val();
    var wallet = $('#wallet').val();
    var grand_total = $('#grand_total_disp').val();
    var outstanding_amount = $('#outstanding_amount').val();
    var cash_balance = 0;

    var id = $(this).data('id');

    if (outstanding_amount != '' && outstanding_amount != 0)
    {
        $("#outstanding_payment_" + id).val(outstanding_amount);
        $(".unpaid").show();
    }
    if (outstanding_amount == '' || outstanding_amount == 0) {
          $("#inward_unpaid_amt_due_days").val('');
            $("#inward_unpaid_due_date").val('');
        $(".unpaid").hide();
    }


    cash_balance = (Number(grand_total) - Number(card) - Number(cheque) - Number(net_banking) - Number(wallet) - Number(outstanding_amount));
    $('#cash').val(cash_balance);

    if (Number(cash_balance) < 0) {
        toastr.error("Amout cannot be greater than Total Sales_amount " + grand_total);
        $('#outstanding_amount').val(0);
        cash_balance = Number(grand_total) - Number(cheque) - Number(net_banking) - Number(wallet);
        $('#cash').val(cash_balance);
    }
});

//DEBIT NOTE
$("#debit_note").keypress(function () {
   return false;
});


$("#debit_note").focus(function ()
{
    var outstanding = $("#outstanding_amount").val();

    if(outstanding == 0 || outstanding == '')
    {
        toastr.error("Add some amount in default payment method unpaid amount!");
        return false;
    }
    else
    {
        $("#inwarddebitnotepopup").modal('show');
    }

});


$("#debit_note_no").focusout(function(){
  var type = "POST";
  var url = "get_debit_note_amount";
    var dataType = '';
  var data={
      'debit_note_no' : $("#debit_note_no").val()
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
              $("#debit_note_amount").val(debit_amount);
              $("#debit_note_amount_for_minus").val(debit_amount);
              $("#debit_note_id").val(amount_detail['debit_note_id']);
          }
          else{
              $("#debit_note_no").val('');
              toastr.error("Debit Note No. is invalid!");
              return false;
          }
      }
  });
});

$("#debit_note_issue_amount").keyup(function ()
{
    var total_amount = $("#debit_note_amount").val();

    var issue_amount = $("#debit_note_issue_amount").val();
    var minus_from = $("#debit_note_amount_for_minus").val();

    var with_minus_value = ((Number(minus_from))-(Number(issue_amount)));
    $("#debit_note_amount").val(with_minus_value);

    if(Number(issue_amount)>Number(minus_from))
    {
        toastr.error("Issue Amount can not be greater than "+ total_amount);
        $("#debit_note_issue_amount").val(0);
        $("#debit_note_amount").val(total_amount);
    }
    var inward_total_amt = $("#grand_total_disp").val();

    if(Number(issue_amount)>Number(inward_total_amt))
    {
        toastr.error("Issue Amount can not be greater than total amount "+ inward_total_amt);
        $("#debit_note_issue_amount").val(0);
        $("#debit_note_amount").val(total_amount);
    }
    var outstandingamt = $("#outstanding_amount").val();
    if(Number(issue_amount) > outstandingamt)
    {
        toastr.error("Issue Amount can not be greater than unpaid amount "+ outstandingamt);
        $("#debit_note_issue_amount").val(0);
        $("#debit_note_amount").val(minus_from);
    }


});

$("#save_debit_note").click(function ()
{
    var debit_note_issue_amt = $("#debit_note_issue_amount").val();

    if(debit_note_issue_amt != '')
    {
        $("#debit_note").val(debit_note_issue_amt);

        var id = $("#outstanding_amount").data('id');
        var outstanding_amount = ((Number($("#outstanding_amount").val())) - (Number(debit_note_issue_amt)));

        if(outstanding_amount != '' || outstanding_amount == 0)
        {
            if (debit_note_issue_amt == 0)
            {
                //var outstanding = ((Number($("#grand_total_disp").val())) - Number(outstanding_amount));
                //outstanding_amount = ((Number($("#outstanding_amount").val())) + Number(outstanding));
            }
            $("#outstanding_payment_" + id).val(outstanding_amount);
            $("#outstanding_amount").val(outstanding_amount);
        } else {
            $("#debit_note").val(0);
            toastr.error("Add some amount in default payment method unpaid amount!");
        }

        $("#inwarddebitnotepopup").modal('hide');
    }
    else
    {
        toastr.error("Fill up proper debit note detail and amount!");
    }
});
//END OF DEBIT NOTE

//for edit inward stock
$(document).ready(function ()
{
    //get a value from local storage for edit inward stock and po inward
    //$(".loaderContainer").show();
    $("#pending_qty_return").hide();
    var billing_type = $("#billing_type").val();
    var inwardtype = $("#inward_type").val();

    var border_css = '';
    if(inwardtype == 1)
    {
        if(billing_type == 3)
        {
            border_css = 'border:1px solid red';
        }
    }
    var edit_data = localStorage.getItem('edit_inward_stock_record');

    //for inward stock
    if (edit_data != '' && edit_data != undefined && edit_data != null)
    {
        $("#pending_qty_return").show();
        var edit_inward_data = JSON.parse(edit_data);
        var edit_inward = JSON.parse(edit_inward_data);

        $("#inward_stock_id").val(edit_inward['inward_stock_id']);
        $("#inward_type").val(edit_inward['inward_type']);
        $("#stock_inward_type").val(edit_inward['stock_inward_type']);
        $("#inward_date").val(edit_inward['inward_date']);
        $("#invoice_date").val(edit_inward['invoice_date']);
        $("#invoice_grn_no").val(edit_inward['invoice_no']);
        $("#unique_barcode_inward").val(edit_inward['inward_with_unique_barcode']);


        $("#validate_invoice_supplier").hide();
        if(edit_inward['warehouse_id'] != 'NULL' && edit_inward['stock_inward_type'] == 2)
        {
            $("#warehouse_name").val(edit_inward['warehouse']['company_name']);
            $("#warehouse_id").val(edit_inward['warehouse_id']);
        }

        $("#gst_id").val(edit_inward['supplier_gst_id']);
        $("#po_no").val(edit_inward['po_no']);
        $("#note").val(edit_inward['note']);
        $("#total_qty").val(edit_inward['total_qty']);
        $("#gross_total_disp").val(parseFloat(edit_inward['total_gross']).toFixed(0));
        $("#grand_total_disp").val(parseFloat(edit_inward['total_grand_amount']).toFixed(0));
        $("#gross_total").val(edit_inward['total_gross']);
        $("#grand_total").val(edit_inward['total_grand_amount']);
        $("#inward_unpaid_amt_due_days").val(edit_inward['due_days']);
        $("#inward_unpaid_due_date").val(edit_inward['due_date']);
        $("#supplier_name").attr('disabled',true);
        $("#supplier_name").css('color','black');
        $("#invoice_grn_no").attr('disabled', true);

        //fill up value to payment block
        if (edit_inward['supplier_payment_details'] != 'undefined' && edit_inward['supplier_payment_details'] != '')
        {
            $.each(edit_inward['supplier_payment_details'], function (paymentkey, paymentvalue) {
                if (paymentvalue['payment_method_id'] != '')
                {
                   if(paymentvalue['payment_method'][0]['html_id'] == "cheque")
                   {
                       $("#note").addClass('invalid');
                   }
                    $("#" + paymentvalue['payment_method'][0]['html_id']).val(paymentvalue['amount']);

                    if (paymentvalue['outstanding_payment'] != '' && paymentvalue['outstanding_payment'] != null && paymentvalue['outstanding_payment'] != 0)
                    {

                        $("#outstanding_payment_" + paymentvalue['payment_method_id']).val(paymentvalue['outstanding_payment']);
                        $(".unpaid").show();
                    }
                    $("#supplier_payment_detail_id_" + paymentvalue['payment_method_id']).val(paymentvalue['supplier_payment_detail_id']);
                }
            });
        }
        //end of fill up payment block

        //fillup product detail row

        if(edit_inward['inward_product_detail'] != 'undefined' && edit_inward['inward_product_detail'] != '') {
            var product_html = '';
            //var price_master = edit_inward['price_masters'];
            $.each(edit_inward['inward_product_detail'], function (productkey, productvalue)
            {
                if (productvalue['product_detail']['product_code'] == '' || productvalue['product_detail']['product_code'] == null) {
                    productvalue['product_detail']['product_code'] = '';
                }
                if (productvalue['batch_no'] == '' || productvalue['batch_no'] == null)
                {
                    productvalue['batch_no'] = '';
                }
                var barcode = '';
                if (productvalue['product_detail'] != '' && productvalue['product_detail']['supplier_barcode'] != " " && productvalue['product_detail']['supplier_barcode'] != null) {
                    barcode = productvalue['product_detail']['supplier_barcode'];
                } else {
                    barcode = productvalue['product_detail']['product_system_barcode'];
                }
                if (productvalue['mfg_date'] == null) {
                    productvalue['mfg_date'] = '';
                }
                if (productvalue['expiry_date'] == null) {
                    productvalue['expiry_date'] = '';
                }
                /* var rowCount = $('#product_detail_record tr').length;
                   rowCount++;*/

                productkey++;
                var edit_btch = '';
                var edit_price = '';

                if (productvalue['pending_return_qty'] == (Number(productvalue['product_qty']) + Number(productvalue['free_qty'])))
                {
                    edit_btch = 'class="editablearea" contenteditable="true"';
                    edit_price = 'class="number editablearea" contenteditable="true"';
                }

                //max qty means if some product qty sell that case user can able to add qty more than add but not allow to add qty less than sell
                var max_qty = (Number(productvalue['product_qty'] + productvalue['free_qty']) - productvalue['pending_return_qty']);

                var pending_po_qty = '';
                var pending_po_qty_show = '';
                var product_id = productvalue['product_id'];

                if (productvalue['pending_po_qty'] != undefined && productvalue['pending_po_qty'] != '' || productvalue['pending_po_qty'] == 0)
                {
                    pending_po_qty = productvalue['pending_po_qty'] + productvalue['product_qty'];
                    pending_po_qty_show = productvalue['pending_po_qty'] ;
                    $(".pending_po").show();
                }
                var gst_percent = ((Number(productvalue['cost_igst_percent'])) + (Number(productvalue['cost_cgst_percent'])) + (Number(productvalue['cost_sgst_percent'])));
                var gst_amount = ((Number(productvalue['cost_igst_amount'])) + (Number(productvalue['cost_cgst_amount'])) + (Number(productvalue['cost_sgst_amount'])));

                if(productvalue['expiry_date'] != '')
                {
                    if(ValidateDate(productvalue['expiry_date']) == 0)
                    {
                        productvalue['expiry_date'] = '';
                    }
                }

                if(productvalue['stock_transfers_detail_id'] == null)
                {
                    productvalue['stock_transfers_detail_id'] = '';
                }

                var inward_show_dynamic_feature = $("#show_inward_dynamic_feature").val();
                var feature_show_val = "";
                if(inward_show_dynamic_feature != '')
                {
                    var feature = inward_show_dynamic_feature.split(',');
                    $.each(feature,function(fea_key,fea_val)
                    {
                        var feature_name = '';
                        if(typeof(productvalue['product_detail'][fea_val]) != "undefined" && productvalue['product_detail'][fea_val] !== null) {
                            feature_name = productvalue['product_detail'][fea_val];
                        }
                        feature_show_val += '<td>' + feature_name + '</td>';
                    })
                }


                product_html += '<tr id="product_' + product_id + '" data-id="' + productkey + '">' +
                    '<input type="hidden" name="inward_product_detail_id_' + product_id + '" id="inward_product_detail_id_'+product_id+ '" value="'+productvalue['inward_product_detail_id']+'">' +
                    '<input type="hidden" name="stock_transfers_detail_id_' + product_id + '" id="stock_transfers_detail_id_'+product_id+ '" value="'+productvalue['stock_transfers_detail_id']+'">' +
                    '<td></td>' +

                    '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addproductqty(this);" id="product_qty_' + product_id + '">' + productvalue['product_qty'] + '</td>' +
                    '<input type="hidden" name="max_allow_qty_' + product_id + '" id="max_allow_qty_' + product_id + '" value="' + max_qty + '">' +
                    '<input type="hidden" name="pending_po_qty_' + product_id + '" id="pending_po_qty_' + product_id + '" value="' + pending_po_qty + '"> ' +

                    '<td onkeypress = "return testCharacter(event);" class="number editablearea garment_case_hide" contenteditable="true" style="color: black;" onkeyup="freeqty(this);" id="free_qty_' + product_id + '">' + productvalue['free_qty'] + '</td>' +
                    '<td>' + barcode + '</td>' +
                    '<td>' + productvalue['product_detail']['product_name'] + '</td>' +

                    '<td>' + productvalue['product_detail']['product_code'] + '</td>' ;


                product_html += feature_show_val;

                product_html += '<td class="garment_case_hide show_in_unique" ' + edit_btch + ' style="color: black;'+border_css+'" id="batch_no_' + product_id + '">' + productvalue['batch_no'] + '</td>' +

                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="baseprice(this);" id="base_price_' + product_id + '">' + productvalue['base_price'] + '</td>' +

                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="discountpercent(this);" id="base_discount_percent_' + product_id + '">' + productvalue['base_discount_percent'] + '</td>' +

                    '<td class="inward_calculation_case" id="base_discount_amount_' + product_id + '">' + productvalue['base_discount_amount'] + '</td>' +

                    '<td onkeypress = "return testCharacter(event);" class="number editablearea garment_case_hide inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="schemepercent(this);"  id="scheme_discount_percent_' + product_id + '">' + productvalue['scheme_discount_percent'] + '</td>' +

                    '<td class="garment_case_hide inward_calculation_case" id="scheme_discount_amount_' + product_id + '">' + productvalue['scheme_discount_amount'] + '</td>' +

                    '<td class="garment_case_hide inward_calculation_case" readonly  id="free_discount_percent_' + product_id + '">' + productvalue['free_discount_percent'] + '</td>' +

                    '<td class="garment_case_hide inward_calculation_case"  readonly id="free_discount_amount_' + product_id + '">' + Number(productvalue['free_discount_amount']).toFixed(4) + '</td>' +

                    '<td class="inward_calculation_case" readonly  id="cost_rate_' + product_id + '">' + productvalue['cost_rate'] + '</td>' +

                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="costgstpercent(this);" id="gst_percent_' + product_id + '">' + gst_percent + '</td>' +

                    '<td class="inward_calculation_case" readonly id="gst_amount_' + product_id + '">' + Number(gst_amount.toFixed(4)) + '</td>' +

                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="extracharge(this);" id="extra_charge_' + product_id + '">' + productvalue['extra_charge'] + '</td>' +

                    '<td class="inward_calculation_case" readonly id="profit_percent_' + product_id + '">' + productvalue['profit_percent'] + '</td>' +

                    '<td class="inward_calculation_case" readonly id="profit_amount_' + product_id + '">' + productvalue['profit_amount'] + '</td>' +

                    '<td class="inward_calculation_case"  readonly id="sell_price_' + product_id + '">' + productvalue['sell_price'] + '</td>' +

                    '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="sellinggstpercent(this);" id="selling_gst_percent_' + product_id + '">' + productvalue['selling_gst_percent'] + '</td>' +

                    '<td class="inward_calculation_case" id="selling_gst_amount_' + product_id + '">' + Number(productvalue['selling_gst_amount']).toFixed(4) + '</td>' +

                    '<td class="inward_calculation_case" onkeypress = "return testCharacter(event);" '+edit_price+' style="color: black;" onkeyup="offerprice(this);" id="offer_price_' + product_id + '">' + productvalue['offer_price'] + '</td>' +

                    '<td class="inward_calculation_case" onkeypress = "return testCharacter(event);" '+edit_price+' style="color: black;" id="product_mrp_' + product_id + '">' + productvalue['product_mrp'] + '</td>' +

                    '<td class="pending_po" readonly style="color: black;" id="po_pending_show_' + product_id + '">' + pending_po_qty_show + '</td>' +

                    '<td contenteditable="true" class="editablearea garment_case_hide" style="color: black;"  onclick="return getdatepicker(\'mfg_date_' + product_id + '\','+productkey+');" id="mfg_date_' + product_id + '">' + productvalue['mfg_date'] + '</td>' +

                    '<td contenteditable="true" class="editablearea garment_case_hide" style="color: black;" onclick="return getdatepicker(\'expiry_date_' + product_id + '\','+productkey+');" id="expiry_date_' + product_id + '">' + productvalue['expiry_date'] + '</td>' +

                    '<td class="inward_calculation_case" readonly id="total_cost_' + product_id + '">' + productvalue['total_cost'] + '</td>' +

                    '<td style="color: black;" id="pending_qty_' + product_id + '">' + productvalue['pending_return_qty'] + '</td>' +
                    '</tr>';
            });

            $("#productsearch").val('');
            $(".odd").hide();
            $("#product_detail_record").append(product_html);


            if (edit_inward['po_no'] != '' && edit_inward['po_no'] != null)
            {
                $("#po_pending_show").show();
                $("#supplier_name").attr('disabled',true);
                $("#supplier_name").css('color','black');
            } else {
                $("#po_pending_show").hide();
                $(".pending_po").hide();
            }

            var totalitem = $("#product_detail_record tr").length;
            $(".totcount").html(totalitem);
        }
        //end of fillup product detail row
        if (edit_inward['supplier_gstdetail'] != '' && edit_inward['supplier_gstdetail'] != undefined)
        {
            if (edit_inward['supplier_gstdetail']['supplier_gst'] != '')
            {
                var supplier_company_info = edit_inward['supplier_gstdetail']['supplier_company_info'];

                if (supplier_company_info['supplier_last_name'] == null)
                {
                    supplier_company_info['supplier_last_name'] = '';
                }

                $("#state_id").val(edit_inward['supplier_gstdetail']['state_id']);

                var name_supplier = supplier_company_info['supplier_first_name'] + ' ' + supplier_company_info['supplier_last_name'] + '_' + edit_inward['supplier_gstdetail']['supplier_gstin'];

                $("#supplier_name").val(name_supplier);
            }
        }
    }
    //end of inward stock

    //for po inward
    var po_data = localStorage.getItem('take_po_inward_data');
    if (po_data != '' && po_data != undefined && po_data != null)
    {
        $("#get_all_qty").show();
        $("#pending_qty_return").show();
        var edit_po_data = JSON.parse(po_data);
        var po_inward = JSON.parse(edit_po_data);

        //$("#inward_type").val(inward_type);

        //$("#inward_date").val(po_inward['po_date']);
        //$("#invoice_date").val(po_inward['po_date']);
        $("#stock_inward_type").val(0);
        $("#gst_id").val(po_inward['supplier_gst_id']);
        $("#po_no").val(po_inward['po_no']);
        $("#total_qty").val(po_inward['total_qty']);
        $("#inward_unpaid_amt_due_days").val(po_inward['due_days']);
        $("#inward_unpaid_due_date").val(po_inward['due_date']);
        $("#validate_invoice_supplier").hide();
        $("#supplier_name").attr('disabled',true);
        $("#supplier_name").css('color','black');
        $("#invoice_grn_no").attr('disabled', false);

        //fillup product detail row
        if (po_inward['purchase_order_detail'] != 'undefined' && po_inward['purchase_order_detail'] != '')
        {
            var product_html = '';



            $.each(po_inward['purchase_order_detail'], function (productkey,productvalue)
            {
                var pending_po_qty = '';

                if (productvalue['pending_qty'] != undefined && productvalue['pending_qty'] != '' || productvalue['pending_qty'] == 0) {
                    pending_po_qty = productvalue['pending_qty'];
                }
                if(pending_po_qty != 0)
                {
                    if (productvalue['product']['product_code'] == '' || productvalue['product']['product_code'] == null) {
                        productvalue['product']['product_code'] = '';
                    }
                    var barcode = '';

                    if (productvalue['product'] != '' && productvalue['product']['supplier_barcode'] != " " && productvalue['product']['supplier_barcode'] != null) {
                        barcode = productvalue['product']['supplier_barcode'];
                    } else {
                        barcode = productvalue['product']['product_system_barcode'];
                    }

                    /*var rowCount = $('#product_detail_record tr').length;
                    rowCount++;*/
                    productkey++;

                    var product_id = productvalue['product_id'];
                    // var max_qty = (Number(productvalue['product_qty']+productvalue['free_qty']) - productvalue['pending_return_qty']);

                    var unique_batch_no = '';
                    var po_mfg_date  = '';
                    var po_expiry_date = '';


                    if(po_with_unique_barcode == 1)
                    if(productvalue['unique_barcode'] != '' && productvalue['unique_barcode'] != null )
                    {
                        unique_batch_no = productvalue['unique_barcode'];

                        if (productvalue['mfg_date'] != null)
                        {
                            po_mfg_date =  productvalue['mfg_date'];


                        }
                        if (productvalue['expiry_date'] != null)
                        {
                            po_expiry_date = productvalue['expiry_date'];
                        }
                    }
                    var inward_show_dynamic_feature = $("#show_inward_dynamic_feature").val();

                    var feature_show_val = "";
                    if(inward_show_dynamic_feature != '')
                    {
                        var feature = inward_show_dynamic_feature.split(',');
                        $.each(feature,function(fea_key,fea_val)
                        {
                            var feature_name = '';
                            if(typeof(productvalue['product'][fea_val]) != "undefined" && productvalue['product'][fea_val] !== null) {
                                feature_name = productvalue['product'][fea_val];
                            }
                            feature_show_val += '<td>' + feature_name + '</td>';


                        })
                    }

                    product_html += '<tr id="product_' + product_id + '" data-id="' + productkey + '">' +
                        '<input type="hidden" name="inward_product_detail_id_' + product_id + '" id="inward_product_detail_id_' + product_id + '" value="">' +
                        '<input type="hidden" name="stock_transfers_detail_id_' + product_id + '" id="stock_transfers_detail_id_' + product_id + '" value="">' +
                        '<td></td>' +

                        '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addproductqty(this);" id="product_qty_' + product_id + '">0</td>' +
                        '<input type="hidden" name="po_qty_' + product_id + '" id="po_qty_' + product_id + '" value="' + productvalue['pending_qty'] + '">' +
                        /*'<input type="hidden" name="max_allow_qty_'+product_id+'" id="max_allow_qty_'+product_id+'" value="'+max_qty+'">' +*/
                        '<td onkeypress = "return testCharacter(event);" class="number editablearea garment_case_hide" contenteditable="true" style="color: black;" onkeyup="freeqty(this);" id="free_qty_' + product_id + '">0</td>' +

                        '<td>' + barcode + '</td>' +

                        '<td>' + productvalue['product']['product_name'] + '</td>' +

                        '<td>' + productvalue['product']['product_code'] + '</td>';


                    product_html += feature_show_val;

                    product_html += '<td  class="editablearea garment_case_hide show_in_unique" contenteditable="true" style="color: black;' + border_css + '" id="batch_no_' + product_id + '">'+unique_batch_no+'</td>' +

                        '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="baseprice(this);" id="base_price_' + product_id + '">' + productvalue['cost_rate'] + '</td>' +

                        '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="discountpercent(this);" id="base_discount_percent_' + product_id + '">0</td>' +

                        '<td  class="inward_calculation_case" id="base_discount_amount_' + product_id + '">0</td>' +

                        '<td onkeypress = "return testCharacter(event);" class="number editablearea garment_case_hide inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="schemepercent(this);"  id="scheme_discount_percent_' + product_id + '">0</td>' +

                        '<td  class="garment_case_hide inward_calculation_case" id="scheme_discount_amount_' + product_id + '">0</td>' +

                        '<td class="garment_case_hide inward_calculation_case" readonly  id="free_discount_percent_' + product_id + '">0</td>' +

                        '<td class="garment_case_hide inward_calculation_case"  readonly id="free_discount_amount_' + product_id + '">0</td>' +

                        '<td class="inward_calculation_case" readonly  id="cost_rate_' + product_id + '">' + productvalue['cost_rate'] + '</td>' +

                        '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="costgstpercent(this);" id="gst_percent_' + product_id + '">' + productvalue['cost_gst_percent'] + '</td>' +

                        '<td class="inward_calculation_case" readonly id="gst_amount_' + product_id + '">' + Number(productvalue['cost_gst_amount']).toFixed(4) + '</td>' +

                        '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="extracharge(this);" id="extra_charge_' + product_id + '">' + productvalue['product']['extra_charge'] + '</td>' +

                        '<td class="inward_calculation_case" readonly id="profit_percent_' + product_id + '">' + productvalue['product']['profit_percent'] + '</td>' +

                        '<td class="inward_calculation_case"  readonly id="profit_amount_' + product_id + '">' + productvalue['product']['profit_amount'] + '</td>' +

                        '<td class="inward_calculation_case" readonly id="sell_price_' + product_id + '">' + productvalue['product']['selling_price'] + '</td>' +

                        '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="sellinggstpercent(this);" id="selling_gst_percent_' + product_id + '">' + productvalue['product']['sell_gst_percent'] + '</td>' +

                        '<td class="inward_calculation_case" id="selling_gst_amount_' + product_id + '">' + Number(productvalue['product']['sell_gst_amount']).toFixed(4) + '</td>' +

                        '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" onkeyup="offerprice(this);" id="offer_price_' + product_id + '">' + productvalue['product']['offer_price'] + '</td>' +

                        '<td onkeypress = "return testCharacter(event);" class="number editablearea inward_calculation_case" contenteditable="true" style="color: black;" id="product_mrp_' + product_id + '">' + productvalue['product']['product_mrp'] + '</td>' +

                        '<td  style="color: black;" id="po_pending_show_' + product_id + '">' + pending_po_qty + '</td>' +


                        '<td contenteditable="true" class="editablearea garment_case_hide" style="color: black;"  onclick="return getdatepicker(\'mfg_date_' + product_id + '\','+productkey+');" id="mfg_date_' + product_id + '">' + po_mfg_date + '</td>' +

                        '<td contenteditable="true" class="editablearea garment_case_hide" style="color: black;" onclick="return getdatepicker(\'expiry_date_' + product_id + '\','+productkey+');" id="expiry_date_' + product_id + '">' + po_expiry_date + '</td>' +

                        '<td class="inward_calculation_case" readonly id="total_cost_' + product_id + '">0</td>' +

                        '<td readonly id="pending_qty_' + product_id + '">0</td>' +
                        '</tr>';
                }
            });
            $("#productsearch").val('');
            $(".odd").hide();
            $("#product_detail_record").append(product_html);

            var totalitem = $("#product_detail_record tr").length;
            $(".totcount").html(totalitem);

            $("#product_detail_record tr").each(function () {
                var product_id = $(this).attr('id').split('product_')[1];
                $("#base_price_" + product_id).keyup();
                $("#selling_gst_percent_" + product_id).keyup();
            });
        }
        //end of fillup product detail row
        if (po_inward['supplier_gstdetail'] != '' && po_inward['supplier_gstdetail'] != undefined) {
            if (po_inward['supplier_gstdetail']['supplier_gst'] != '') {
                var supplier_company_info = po_inward['supplier_gstdetail']['supplier_company_info'];

                if (supplier_company_info['supplier_last_name'] == null) {
                    supplier_company_info['supplier_last_name'] = '';
                }

                $("#state_id").val(po_inward['supplier_gstdetail']['state_id']);

                var name_supplier = supplier_company_info['supplier_first_name'] + ' ' + supplier_company_info['supplier_last_name'] + '_' + po_inward['supplier_gstdetail']['supplier_gstin'];

                $("#supplier_name").val(name_supplier);
                $("#supplier_name").attr('disabled',true);
                $("#supplier_name").css('color','black');
            }
        }


        if(po_inward['po_with_unique_barcode'] == 1)
        {
            $(".unique_scan").show();
        }
        else
        {
            $(".unique_scan").hide();
        }
    }
    //end of po inward


    //for take stock transfer inward
    var edit_stock_inward_data = localStorage.getItem('take_stock_inward_data');

    if (edit_stock_inward_data != '' && edit_stock_inward_data != undefined && edit_stock_inward_data != null)
    {
       // $("#pending_qty_return").show();
        $("#get_all_qty").show();
        var edit_stock_inward_data = JSON.parse(edit_stock_inward_data);
        var edit_stock_inward = JSON.parse(edit_stock_inward_data);
        $("#inward_stock_id").val('');
        $("#inward_type").val();
        $("#po_no").val(edit_stock_inward['stock_transfer_no']);
        $("#invoice_grn_no").val(edit_stock_inward['stock_transfer_no']);
        //$("#invoice_grn_no").attr('disabled',true);
        $("#gst_id").val('');
        $("#warehouse_name").val(edit_stock_inward['warehouse']['company_name']);
        $("#warehouse_id").val(edit_stock_inward['company_id']);
        $("#stock_inward_type").val(2);
        //$("#note").val(edit_stock_inward['note']);
        //$("#total_qty").val(edit_stock_inward['total_qty']);
        //$("#gross_total_disp").val(parseFloat(edit_stock_inward['total_gross']).toFixed(0));
        //$("#grand_total_disp").val(parseFloat(edit_stock_inward['total_grand_amount']).toFixed(0));
        //$("#gross_total").val(edit_stock_inward['total_gross']);
        //$("#grand_total").val(edit_stock_inward['total_grand_amount']);
        //$("#inward_unpaid_amt_due_days").val(edit_stock_inward['due_days']);
        //$("#inward_unpaid_due_date").val(edit_stock_inward['due_date']);


        //fillup product detail row

        if(edit_stock_inward['stock_transfer_detail'] != 'undefined' && edit_stock_inward['stock_transfer_detail'] != '') {
            $.each(edit_stock_inward['stock_transfer_detail'], function (productkey, productvalue)
            {
                var product_html = '';
                if (productvalue['product_data']['product_code'] == '' || productvalue['product_data']['product_code'] == null) {
                    productvalue['product_data']['product_code'] = '';
                }
                if (productvalue['batch_no'] == '' || productvalue['batch_no'] == null) {
                    productvalue['batch_no'] = '';
                }
                var barcode = '';
                if (productvalue['product_data'] != '' && productvalue['product_data']['supplier_barcode'] != " " && productvalue['product_data']['supplier_barcode'] != null) {
                    barcode = productvalue['product_data']['supplier_barcode'];
                } else {
                    barcode = productvalue['product_data']['product_system_barcode'];
                }
                if (productvalue['mfg_date'] == null) {
                    productvalue['mfg_date'] = '';
                }
                if (productvalue['expiry_date'] == null) {
                    productvalue['expiry_date'] = '';
                }
                /* var rowCount = $('#product_detail_record tr').length;
                   rowCount++;*/

                productkey++;


                //max qty means if some product qty sell that case user can able to add qty more than add but not allow to add qty less than sell
                //var max_qty =  productvalue['pending_rcv_qty'];
                var max_qty = 0;

                var pending_po_qty = '';
                var pending_po_qty_show = '';
                var product_id = productvalue['product_id'];

                if (productvalue['pending_rcv_qty'] != undefined && productvalue['pending_rcv_qty'] != '' || productvalue['pending_rcv_qty'] == 0)
                {
                    pending_po_qty = productvalue['pending_rcv_qty'] ;
                    pending_po_qty_show = productvalue['pending_rcv_qty'] ;
                    $(".pending_po").show();
                }
                var gst_percent = ((Number(productvalue['cost_igst_percent'])) + (Number(productvalue['cost_cgst_percent'])) + (Number(productvalue['cost_sgst_percent'])));
                var gst_amount = ((Number(productvalue['cost_igst_amount'])) + (Number(productvalue['cost_cgst_amount'])) + (Number(productvalue['cost_sgst_amount'])));

                if(productvalue['expiry_date'] != '')
                {
                    if(ValidateDate(productvalue['expiry_date']) == 0)
                    {
                        productvalue['expiry_date'] = '';
                    }
                }

                var inward_show_dynamic_feature = $("#show_inward_dynamic_feature").val();
                var feature_show_val = "";
                if(inward_show_dynamic_feature != '')
                {
                    var feature = inward_show_dynamic_feature.split(',');
                    $.each(feature,function(fea_key,fea_val)
                    {
                        var feature_name = '';
                        if(typeof(productvalue['product_data'][fea_val]) != "undefined" && productvalue['product_data'][fea_val] !== null) {
                            feature_name = productvalue['product_data'][fea_val];
                        }
                        feature_show_val += '<td>' + feature_name + '</td>';
                    })
                }


                product_html += '<tr id="product_' + product_id + '" data-id="' + productkey + '">' +
                    '<input type="hidden" name="inward_product_detail_id_' + product_id + '" id="inward_product_detail_id_' + product_id + '" value="">' +
                    '<input type="hidden" name="stock_transfers_detail_id_' + product_id + '" id="stock_transfers_detail_id_' + product_id + '" value="'+productvalue['stock_transfers_detail_id']+'">' +

                    '<td></td>' +
                    '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addproductqty(this);" id="product_qty_' + product_id + '">0</td>' +
                    '<input type="hidden" name="max_allow_qty_' + product_id + '" id="max_allow_qty_' + product_id + '" value="' + max_qty + '">' +
                    '<input type="hidden" name="pending_po_qty_' + product_id + '" id="pending_po_qty_' + product_id + '" value="' + pending_po_qty + '"> ' +

                    '<td onkeypress = "return testCharacter(event);" class="number editablearea garment_case_hide" contenteditable="true" style="color: black;" onkeyup="freeqty(this);" id="free_qty_' + product_id + '">' + productvalue['free_qty'] + '</td>' +

                    '<td>' + barcode + '</td>' +
                    '<td>' + productvalue['product_data']['product_name'] + '</td>' +

                    '<td>' + productvalue['product_data']['product_code'] + '</td>';

                product_html += feature_show_val;

                product_html += '<td class="garment_case_hide show_in_unique" style="color: black;'+border_css+'" id="batch_no_' + product_id + '">' + productvalue['batch_no'] + '</td>' +

                    '<td  class="number inward_calculation_case"  style="color: black;"  id="base_price_' + product_id + '">' + productvalue['base_price'] + '</td>' +

                    '<td  class="number inward_calculation_case" style="color: black;" id="base_discount_percent_' + product_id + '">' + productvalue['base_discount_percent'] + '</td>' +

                    '<td class="inward_calculation_case" id="base_discount_amount_' + product_id + '">' + productvalue['base_discount_amount'] + '</td>' +

                    '<td  class="number garment_case_hide inward_calculation_case" style="color: black;" onkeyup="schemepercent(this);"  id="scheme_discount_percent_' + product_id + '">' + productvalue['scheme_discount_percent'] + '</td>' +

                    '<td class="garment_case_hide inward_calculation_case" id="scheme_discount_amount_' + product_id + '">' + productvalue['scheme_discount_amount'] + '</td>' +

                    '<td class="garment_case_hide inward_calculation_case" readonly  id="free_discount_percent_' + product_id + '">' + productvalue['free_discount_percent'] + '</td>' +

                    '<td class="garment_case_hide inward_calculation_case"  readonly id="free_discount_amount_' + product_id + '">' + Number(productvalue['free_discount_amount']).toFixed(4) + '</td>' +

                    '<td class="inward_calculation_case" readonly  id="cost_rate_' + product_id + '">' + productvalue['cost_rate'] + '</td>' +

                    '<td class="number inward_calculation_case" style="color: black;" onkeyup="costgstpercent(this);" id="gst_percent_' + product_id + '">' + gst_percent + '</td>' +

                    '<td class="inward_calculation_case" readonly id="gst_amount_' + product_id + '">' + Number(gst_amount.toFixed(4)) + '</td>' +

                    '<td  class="number inward_calculation_case" style="color: black;" id="extra_charge_' + product_id + '">0</td>' +

                    '<td class="inward_calculation_case" readonly id="profit_percent_' + product_id + '">' + productvalue['profit_percent'] + '</td>' +

                    '<td class="inward_calculation_case" readonly id="profit_amount_' + product_id + '">' + productvalue['profit_amount'] + '</td>' +

                    '<td class="inward_calculation_case"  readonly id="sell_price_' + product_id + '">' + productvalue['sell_price'] + '</td>' +

                    '<td  class="number inward_calculation_case"  style="color: black;"  id="selling_gst_percent_' + product_id + '">' + productvalue['selling_gst_percent'] + '</td>' +

                    '<td class="inward_calculation_case" id="selling_gst_amount_' + product_id + '">' + Number(productvalue['selling_gst_amount']).toFixed(4) + '</td>' +

                    '<td class="inward_calculation_case"   style="color: black;"  id="offer_price_' + product_id + '">' + productvalue['offer_price'] + '</td>' +

                    '<td class="inward_calculation_case"  style="color: black;" id="product_mrp_' + product_id + '">' + productvalue['product_mrp'] + '</td>' +

                    '<td class="pending_po" readonly style="color: black;" id="po_pending_show_' + product_id + '">' + pending_po_qty_show + '</td>' +


                    '<td class="garment_case_hide" style="color: black;"  id="mfg_date_' + product_id + '">' + productvalue['mfg_date'] + '</td>' +

                    '<td class="garment_case_hide" style="color: black;"  id="expiry_date_' + product_id + '">' + productvalue['expiry_date'] + '</td>' +

                    '<td class="inward_calculation_case" readonly id="total_cost_' + product_id + '">' + productvalue['total_cost'] + '</td>' +

                    '</tr>';

                $("#product_detail_record").append(product_html);

                var totalitem = $("#product_detail_record tr").length;
                $(".totcount").html(totalitem);

                $("#product_detail_record").find("tr[data-id='" + productkey + "']").find("#base_price_"+product_id).keyup();
            });

            $("#productsearch").val('');
            $(".odd").hide();



        }
        //end of fillup product detail row
        $("#productsearch").attr('disabled',true);
    }
    //end of stock transfer inward

    if($("#po_no").val() != '' && $("#po_no").val() != 0)
     {

         $("#productsearch").attr('disabled',true);
         /*if($("#stock_inward_type").val() == 2)
         {*/
             $(".pymentandtotalblock").show();
             $("#validate_invoice_supplier").hide();
       /*  }*/
     }

     else
     {
         if($("#invoice_grn_no").val() == '')
         {
             $("#productsearch").attr('disabled',true);
             $(".pymentandtotalblock").hide();
         }
         else {
             if($("#validate_invoice_supplier").is(":visible"))
             {
                 $("#productsearch").attr('disabled',true);
                 $(".pymentandtotalblock").hide();
             }
             else {
                 $("#productsearch").attr('disabled', false);
                 $(".pymentandtotalblock").show();
             }
         }
     }

    /*var inward_heading = $("#inward_type").val();
     if (inward_heading == 1) {
        $(".inward_heading").html("FMCG Inward Stock");
    } else {
        $(".inward_heading").html("Garment Inward Stock");
    }*/

    if ($("#inward_type").val() == 2)
    {
        $(".garment_case_hide").hide();
        if($("#unique_barcode_inward").val() == 1)
        {
            $(".show_in_unique").show();
        }
    } else {
        $(".garment_case_hide").show();
    }

    if ($("#stock_inward_type").val() == 2)
    {
        $(".warehouse_show").show();
        $(".warehouse_hide").hide();
    } else
     {
        $(".warehouse_show").hide();
        $(".warehouse_hide").show();
    }
    if (inward_calculation == 3) {
        $(".inward_calculation_case").hide();
    } else {
        $(".inward_calculation_case").show();
    }


         if ($("#outstanding_amount").val() != '' && $("#outstanding_amount").val() != null && $("#outstanding_amount").val() != 0)
         {
            $(".unpaid").show();
         }else{
            $(".unpaid").hide();
         }


    $(".loaderContainer").hide();
});


$("#validate_invoice_supplier").click(function()
{
    var error = 0;
    if($("#invoice_grn_no").val() == '')
    {
        error = 1;
        toastr.error("Invoice No can not be empty!");
    }
    if($("#gst_id").val() == '')
    {
        error = 1;
        toastr.error("Supplier can not be empty!");
    }
    if(error == 0)
    {
        check_valid_supplier_and_invoice();
    }
});


function check_valid_supplier_and_invoice()
{
    var url = "validate_inward";
    var type = "POST";
    var dataType = "";
    var data = {
        "invoice_no" : $("#invoice_grn_no").val(),
        "gst_id" : $("#gst_id").val()
    }
    callroute(url,type,dataType,data,function(data){
                var dta = JSON.parse(data);
                if(dta['Success']=="False")
                {
                    toastr.error(dta['Message']);
                }
                else
                {
                    $(".alert_form_status").val(1);
                    $("#validate_invoice_supplier").hide();
                    $("#productsearch").attr('disabled', false);
                    $(".pymentandtotalblock").show();
                    $("#supplier_name").attr('disabled',true);
                    $("#supplier_name").css('color','black');
                    $("#invoice_grn_no").attr('disabled', true);

                    if($("#unique_barcode_inward").val() == 1)
                    {
                        $(".hide_when_unique").hide();
                        $("#addinwardstock").attr('disabled', true);
                    }
                }
    })
}

//this function used when same invoiceno is repeat and client want to update on this invoice no
function edit_inward(stockid) {
    var url = "edit_inward_stock";
    var type = "POST";
    var dataType = '';
    var data = {
        'inward_stock_id': stockid,
        'inward_type' : $("#inward_type").val(),
    };
    callroute(url, type,dataType, data, function (data)
    {
        var dta = JSON.parse(data);

        if (dta['Success'] == "True")
        {
            var border_css = '';
            if($("#inward_type").val() == 1) {
                if ($("#billing_type").val() == 3)
                {
                    border_css = 'border:1px solid red';
                }
            }

            var edit_inward_data = dta['Data'];

            var edit_inward = JSON.parse(edit_inward_data);

            //  var edit_inward = JSON.parse(edit_inward_data);

            $("#inward_unpaid_amt_due_days").val(edit_inward['due_days']);
            $("#inward_unpaid_due_date").val(edit_inward['due_date']);

            $("#po_pending_show").hide();

            if (edit_inward != '' && edit_inward != undefined && edit_inward != null)
            {
                $("#inward_stock_id").val(edit_inward['inward_stock_id']);
                $("#inward_type").val(edit_inward['inward_type']);
                $("#inward_date").val(edit_inward['inward_date']);
                $("#invoice_date").val(edit_inward['invoice_date']);
                $("#invoice_grn_no").val(edit_inward['invoice_no']);
                $("#gst_id").val(edit_inward['supplier_gst_id']);
                $("#po_no").val(edit_inward['po_no']);
                $("#note").val(edit_inward['note']);
                $("#total_qty").val(edit_inward['total_qty']);
                $("#gross_total_disp").val(parseFloat(edit_inward['total_gross']).toFixed(0));
                $("#grand_total_disp").val(parseFloat(edit_inward['total_grand_amount']).toFixed(0));
                $("#gross_total").val(edit_inward['total_gross']);
                $("#grand_total").val(edit_inward['total_grand_amount']);

                //fill up value to payment block
                if (edit_inward['supplier_payment_details'] != 'undefined' && edit_inward['supplier_payment_details'] != '') {
                    $.each(edit_inward['supplier_payment_details'], function (paymentkey, paymentvalue) {
                        if (paymentvalue['payment_method_id'] != '') {
                            $("#" + paymentvalue['payment_method'][0]['html_id']).val(paymentvalue['amount']);

                            if (paymentvalue['outstanding_payment'] != '' && paymentvalue['outstanding_payment'] != null) {
                                $("#outstanding_payment_" + paymentvalue['payment_method_id']).val(paymentvalue['outstanding_payment']);
                            }

                            $("#supplier_payment_detail_id_" + paymentvalue['payment_method_id']).val(paymentvalue['supplier_payment_detail_id']);
                        }
                    });
                }
                //end of fill up payment block

                //fillup product detail row
                if (edit_inward['inward_product_detail'] != 'undefined' && edit_inward['inward_product_detail'] != '') {

                    var product_html = '';
                    //var price_master = edit_inward['price_masters'];
                    var rowCount = $('#product_detail_record tr').length;
                    $.each(edit_inward['inward_product_detail'], function (productkey, productvalue) {
                        var product_id = productvalue['product_id'];
                        var samerow_edit = 0;

                        if ($("#product_detail_record").find("#product_" + product_id).length > 0) {
                            var product_mrp = $("#product_" + product_id).find("#product_mrp_" + product_id).html();

                            if (product_mrp == productvalue['product_mrp']) {
                                $("#product_" + product_id).find("#inward_product_detail_id_" + product_id).val(productvalue['inward_product_detail_id']);
                                samerow_edit = 1;
                            }
                        }

                        if (samerow_edit == 1) {
                            var qty = $("#product_qty_" + product_id).html();
                            var free_qty = $("#free_qty_" + product_id).html();
                            var product_qty = ((Number(qty)) + (Number(productvalue['product_qty'])));
                            var product_free_qty = ((Number(free_qty)) + (Number(productvalue['free_qty'])));
                            $("#product_qty_" + product_id).html(product_qty);
                            $("#free_qty_" + product_id).html(product_free_qty);
                            samerow_edit = 1;
                            var cost_rate = $("#cost_rate_" + product_id).html();
                            var gst_amount = $("#gst_amount_" + product_id).html();
                            var cost_price = ((Number(cost_rate)) + (Number(gst_amount)));

                            var total_qty = ((Number(qty)) + (Number(productvalue['product_qty'])) + (Number($("#free_qty_" + product_id).html())) + (Number(productvalue['free_qty'])));
                            var total_cost = ((Number(cost_price)) * (Number(total_qty)));

                            $("#total_cost_" + product_id).html(total_cost.toFixed(4));
                            totalcalculation();
                            return false;
                        } else {
                            if (productvalue['product_detail']['product_code'] == '' || productvalue['product_detail']['product_code'] == null) {
                                productvalue['product_detail']['product_code'] = '';
                            }
                            if (productvalue['batch_no'] == '' || productvalue['batch_no'] == null) {
                                productvalue['batch_no'] = '';
                            }

                            var barcode = '';
                            if (productvalue['product_detail'] != '' && productvalue['product_detail']['supplier_barcode'] != " " && productvalue['product_detail']['supplier_barcode'] != null) {
                                barcode = productvalue['product_detail']['supplier_barcode'];
                            } else {

                                barcode = productvalue['product_detail']['product_system_barcode'];
                            }
                            if (productvalue['mfg_date'] == null) {
                                productvalue['mfg_date'] = '';
                            }
                            if (productvalue['expiry_date'] == null) {
                                productvalue['expiry_date'] = '';
                            }


                            var inward_show_dynamic_feature = $("#show_inward_dynamic_feature").val();
                            var feature_show_val = "";
                            if(inward_show_dynamic_feature != '')
                            {
                                var feature = inward_show_dynamic_feature.split(',');
                                $.each(feature,function(fea_key,fea_val)
                                {
                                    var feature_name = '';
                                    if(typeof(productvalue['product_detail'][fea_val]) != "undefined" && productvalue['product_detail'][fea_val] !== null) {
                                        feature_name = productvalue['product_detail'][fea_val];
                                    }
                                    feature_show_val += '<td>' + feature_name + '</td>';
                                })
                            }


                            rowCount++;
                            var gst_percent = ((Number(productvalue['cost_igst_percent'])) + (Number(productvalue['cost_cgst_percent'])) + (Number(productvalue['cost_sgst_percent'])));
                            var gst_amount = ((Number(productvalue['cost_igst_amount'])) + (Number(productvalue['cost_cgst_amount'])) + (Number(productvalue['cost_sgst_amount'])));
                            var product_id = productvalue['product_id'];

                            var max_qty = (Number(productvalue['product_qty'] - productvalue['pending_return_qty']));

                            product_html += '<tr id="product_' + product_id + '" style="background:cadetblue" data-id="' + rowCount + '">' +
                                '<input type="hidden" name="inward_product_detail_id_' + product_id + '" id="inward_product_detail_id_' + product_id + '" value="' + productvalue['inward_product_detail_id'] + '">' +
                                '<input type="hidden" name="stock_transfers_detail_id_' + product_id + '" id="stock_transfers_detail_id_' + product_id + '" value="">' +
                                '<td></td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addproductqty(this);" id="product_qty_' + product_id + '">' + productvalue['product_qty'] + '</td>' +
                                '<input type="hidden" name="max_allow_qty_' + product_id + '" id="max_allow_qty_' + product_id + '" value="' + max_qty + '">' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea garment_case_hide" contenteditable="true" style="color: black;" onkeyup="freeqty(this);" id="free_qty_' + product_id + '">' + productvalue['free_qty'] + '</td>' +
                                '<td>' + barcode + '</td>' +
                                '<td>' + productvalue['product_detail']['product_name'] + '</td>' +
                                '<td>' + productvalue['product_detail']['product_code'] + '</td>';

                            product_html += feature_show_val;

                            product_html += '<td class="editablearea garment_case_hide show_in_unique" contenteditable="true" style="color: black;'+border_css+'" id="batch_no_' + product_id + '">' + productvalue['batch_no'] + '</td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="baseprice(this);" id="base_price_' + product_id + '">' + productvalue['base_price'] + '</td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="discountpercent(this);" id="base_discount_percent_' + product_id + '">' + productvalue['base_discount_percent'] + '</td>' +
                                '<td class=""  id="base_discount_amount_' + product_id + '">' + productvalue['base_discount_amount'] + '</td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea garment_case_hide" contenteditable="true" style="color: black;" onkeyup="schemepercent(this);"  id="scheme_discount_percent_' + product_id + '">' + productvalue['scheme_discount_percent'] + '</td>' +
                                '<td class="garment_case_hide"  id="scheme_discount_amount_' + product_id + '">' + productvalue['scheme_discount_amount'] + '</td>' +
                                '<td class="garment_case_hide" readonly  id="free_discount_percent_' + product_id + '">' + productvalue['free_discount_percent'] + '</td>' +
                                '<td class="garment_case_hide"  readonly id="free_discount_amount_' + product_id + '">' + productvalue['free_discount_amount'] + '</td>' +
                                '<td  readonly  id="cost_rate_' + product_id + '">' + productvalue['cost_rate'] + '</td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="costgstpercent(this);" id="gst_percent_' + product_id + '">' + gst_percent + '</td>' +
                                '<td  readonly id="gst_amount_' + product_id + '">' + Number(gst_amount.toFixed(4)) + '</td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="extracharge(this);" id="extra_charge_' + product_id + '">' + productvalue['extra_charge'] + '</td>' +
                                '<td  readonly id="profit_percent_' + product_id + '">' + productvalue['profit_percent'] + '</td>' +
                                '<td  readonly id="profit_amount_' + product_id + '">' + productvalue['profit_amount'] + '</td>' +
                                '<td  readonly id="sell_price_' + product_id + '">' + productvalue['sell_price'] + '</td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="sellinggstpercent(this);" id="selling_gst_percent_' + product_id + '">' + productvalue['selling_gst_percent'] + '</td>' +
                                '<td  id="selling_gst_amount_' + product_id + '">' + productvalue['selling_gst_amount'] + '</td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="offerprice(this);" id="offer_price_' + product_id + '">' + productvalue['offer_price'] + '</td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" id="product_mrp_' + product_id + '">' + productvalue['product_mrp'] + '</td>' +
                                /*'<td style="color: black;" id="pending_qty_' + product_id + '">' + productvalue['pending_return_qty'] + '</td>' +*/

                                '<td contenteditable="true" class="editablearea garment_case_hide" style="color: black;"  onclick="return getdatepicker(\'mfg_date_' + product_id + '\','+productkey+');" id="mfg_date_' + product_id + '">' + productvalue['mfg_date'] + '</td>' +
                                '<td contenteditable="true" class="editablearea garment_case_hide" style="color: black;" onclick="return getdatepicker(\'expiry_date_' + product_id + '\','+productkey+');" id="expiry_date_' + product_id + '">' + productvalue['expiry_date'] + '</td>' +
                                '<td readonly id="total_cost_' + product_id + '">' + productvalue['total_cost'] + '</td>' +
                                '</tr>';
                        }
                    });

                    $("#productsearch").val('');
                    $(".odd").hide();
                    $("#product_detail_record").append(product_html);

                    if ($("#inward_type").val() == 2) {
                        $(".garment_case_hide").hide();
                    } else {
                        $(".garment_case_hide").show();
                    }
                }
                //end of fillup product detail row

                if (edit_inward['supplier_gstdetail'] != '' && edit_inward['supplier_gstdetail'] != undefined) {
                    if (edit_inward['supplier_gstdetail']['supplier_gst'] != '') {
                        var supplier_company_info = edit_inward['supplier_gstdetail']['supplier_company_info'];

                        if (supplier_company_info['supplier_last_name'] == null) {
                            supplier_company_info['supplier_last_name'] = '';
                        }
                        $("#state_id").val(edit_inward['supplier_gstdetail']['state_id']);
                        var name_supplier = supplier_company_info['supplier_first_name'] + ' ' + supplier_company_info['supplier_last_name'] + '_' + edit_inward['supplier_gstdetail']['supplier_gstin'];

                        $("#supplier_name").val(name_supplier);
                    }
                }

                totalcalculation();
            }
        }
    });
}


$(document).on('click', '#downloadtmpate', function () {
    var inward_type = $("#inward_type").val();

    var query = {
        inward_type: inward_type,
        unique_inward: $("#unique_barcode_inward").val()
    };
    var url = "inward_template?" + $.param(query)
    window.open(url, '_blank');
});

$("#inward_unpaid_due_date").datepicker({
    format:'dd-mm-yyyy',
    startDate: '+1d',
    todayHighlight:false,
}).on('changeDate',function(ev){
    var date_get = new Date();
    var date = $("#inward_unpaid_due_date").val();
    var supplier_date = date.split('-');

    var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
    var firstDate = new Date(supplier_date[2],supplier_date[1],supplier_date[0]);
    var secondDate = new Date(date_get.getFullYear(),(date_get.getMonth()+1),date_get.getDate());

    var diffDays = Math.round(Math.abs((firstDate.getTime() - secondDate.getTime())/(oneDay)));

    if(diffDays != '' && diffDays != 0 ) {
        $("#inward_unpaid_amt_due_days").val(diffDays);
    }else
    {
        $("#inward_unpaid_amt_due_days").val('');
        $("#inward_unpaid_due_date").val('');
    }

}).on('keydown keypress paste', function (e) {
    e.preventDefault();
    return false;
});


$('#inward_unpaid_amt_due_days').keyup(function(e){

                var due_days   =  $('#inward_unpaid_amt_due_days').val();

                if(due_days!='' && due_days!=0)
                {
                    var fut_Date  = DateHelper.format(DateHelper.addDays(new Date(), Number(due_days)));
                    $('#inward_unpaid_due_date').val(fut_Date);
                }
                else
                {
                    $("#inward_unpaid_amt_due_days").val('');
                    $("#inward_unpaid_due_date").val('');
                }

            });


//for generate unique batch no in inward stock

            $("#generate_unique_batch").click(function()
            {


                var total_row = $("#product_detail_record tr").length;
                if(total_row == 0)
                {
                    toastr.error("Select some product to inward.");
                    return false;
                }
                else
                {
                    if(unique_batch_no == '')
                    {
                        var unique_batchno = '1000000000';
                    }
                    else
                    {
                        var unique_batchno = unique_batch_no.split('I')[1];
                    }



                    $("#product_detail_record tr").each(function()
                    {
                        var row_id = $(this).attr('id').split('_')[1];

                        unique_batchno++;

                        if($(this).find('#batch_no_'+row_id).html() == '')
                        {
                            $(this).find("#batch_no_"+row_id).html("I"+unique_batchno);
                        }
                    });
                    $("#generate_unique_batch").hide();
                    $("#addinwardstock").attr('disabled', false);
                    //$(".po_with_batch_no").css('display','none');


                }
            });

            // WHEN CLICK ON THIS ALL PENDING RETURN QTY WILL COPIED IN ADD QTY

            $("#get_all_qty").click(function()
            {
                if($(this).is(':checked'))
                {
                    $(".loaderContainer").show();
                    $("#product_detail_record tr").each(function ()
                    {
                        var dataid = $(this).data('id');
                        $("#product_detail_record").find("tr[data-id='" + dataid + "']").each(function (){
                                var ids = $(this).attr('id');
                                var product_id = ids.split('product_')[1];

                                var all_qty = $(this).find('#po_pending_show_'+product_id).html();
                                if($.isNumeric(all_qty) && all_qty != '')
                                {
                                    $(this).find("#product_qty_"+product_id).html(all_qty);
                                    $(this).find("#product_qty_"+product_id).keyup();
                                }
                        });
                    });
                    $(".loaderContainer").hide();
                }
                else {
                    $(".loaderContainer").show();
                    $("#product_detail_record tr").each(function ()
                    {
                        var dataid = $(this).data('id');
                        $("#product_detail_record").find("tr[data-id='" + dataid + "']").each(function (){
                            var ids = $(this).attr('id');
                            var product_id = ids.split('product_')[1];

                           $(this).find("#product_qty_"+product_id).html(0);
                           $(this).find("#product_qty_"+product_id).keyup();
                        });
                    });
                    $(".loaderContainer").hide();
                }

            });

