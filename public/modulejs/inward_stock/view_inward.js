

function viewinward(inwardid)
{

    $("#inward_popup_record").trigger('reset');
    $("#payment_div").empty();
    $("#view_inward_record").html('');
    $("#inward_payment_details").empty();
    $('#vieewpop tfoot').html('');
    $("#edit_inword_stock_in_popup").attr('onClick','');

    $("#previousrecord").attr('data-id','');
     // console.log(previousinward);
     // return false;

    $("#nextrecord").attr("data-id",'');

    var  url = "view_inward_detail";
    var type = "POST";
    var dataType = "";
    var data = {
        'inward_stock_id' : inwardid,
    };

    callroute(url,type,dataType,data,function (data)
    {
        var dta = JSON.parse(data);

        if(dta['Success'] == "True")
        {
            $("#view_inward_record").html('');
            $('#vieewpop tfoot').html('');
            $('#inward_payment_details').html('');
            var final_data = JSON.parse(dta['Data']);

            var dataval = final_data;

          $("#viewinwardpopup").modal('show');

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

            if(dta['previous']=='')
            {
              $("#previousrecord").prop('disabled',true);
              $("#previousrecord").attr("data-id",'');
            }
            else
            {
               $("#previousrecord").prop('disabled',false);
              $("#previousrecord").attr('data-id',dta['previous']);
            }

            $("#edit_inword_stock_in_popup").attr("onClick","edit_inwardstock('"+inwardid+"','"+dataval['inward_type']+"')");


            if(typeof dataval['supplier_gstdetail'] != 'undefined' && dataval['supplier_gstdetail'] != '' && dataval['supplier_gstdetail'] != null)
            {
                if (dataval['supplier_gstdetail']['supplier_gstin'] != '' && dataval['supplier_gstdetail']['supplier_gstin'] != undefined)
                {
                    $(".gstvalblock").show();
                    $(".supplier_gstin").html(dataval['supplier_gstdetail']['supplier_gstin']);
                }

                var supplier_company_info = dataval['supplier_gstdetail']['supplier_company_info'];

                if (supplier_company_info != '' && supplier_company_info != undefined)
                {
                    var supplier_first_name = supplier_company_info['supplier_first_name'];
                    var supplier_last_name = '';

                    if (supplier_company_info['supplier_last_name'] != '' && supplier_company_info['supplier_last_name'] != null)
                    {
                        supplier_last_name = supplier_company_info['supplier_last_name'];
                    }
                    $(".supplier_name").html(supplier_first_name + '  ' + supplier_last_name);
                }
            }

            if(typeof dataval['warehouse_id'] != 'undefined' && dataval['warehouse_id'] != '' && dataval['warehouse_id'] != null)
            {
                $(".supp_warehouse").text("Warehouse Name");
               $(".supplier_name").html(dataval['warehouse']['company_name']);
               $(".supplier_gstin").html(dataval['warehouse']['gstin']);

           }

            $(".invoice_no_popup").html(dataval['invoice_no']);
            $(".inward_date_popup").html(dataval['inward_date']);
            var currency_symbol = '&#x20b9';

            if(typeof dataval['supplier_payment_details'] != "undefined" && dataval['supplier_payment_details'] != '')
            {
                var payment_html = '';

                if(tax_type == 1)
                {
                    currency_symbol = currency_title;
                }
                $.each(dataval['supplier_payment_details'],function(paymentkey,paymentvalue)
                {
                    var payment_method_name = '';

                    if(typeof paymentvalue != 'undefined' && paymentvalue['payment_method '] != '')
                    {
                         payment_method_name = paymentvalue['payment_method'][0]['payment_method_name'];
                    }

                    var payment_amt = Number(paymentvalue['amount']).toFixed(decimal_points);

                    payment_html += '<tr>' +
                        '<td style="text-align:right !important;font-size:14px !important;" class="text-dark font-weight-600">'+payment_method_name+'</td>' +
                        '<td class="font-weight-600">&nbsp;:&nbsp;</td>' +
                        '<td style="text-align:right !important;font-size:14px !important;" class="text-dark font-weight-600">'+currency_symbol+' '+payment_amt+'</td>' +
                        '</tr>';
                });

                $("#inward_payment_details").append(payment_html);
            }

            if(dataval['total_qty'] != '')
            {
                $("#total_qty").html(dataval['total_qty']);
            }
            if(dataval['total_gross'] != '')
            {
                $("#total_gross").html(dataval['total_gross']);
            }
            if(dataval['total_grand_amount'] != '')
            {
                $("#total_grand_amount").html(dataval['total_grand_amount']);
            }
            if(dataval['total_grand_amount'] != '')
            {
                $("#total_grand_amount").html(dataval['total_grand_amount']);
            }
            $(".invoiceno").html(dataval['invoice_no']);

            $(".invoice_title").html('Invoice NO::');
            // console.log(dataval['invoice_no'])
            // return false;

            if(typeof dataval['inward_product_detail'] != 'undefined' && dataval['inward_product_detail'] != '' )
            {
                var product_detail_record = dataval['inward_product_detail'];
                var product_html = '';
                var base_price_total = 0;
                var igst_total = 0;
                var cgst_total = 0;
                var sgst_total = 0;
                var profit_percent_total = 0;
                var profit_amt_total = 0;
                var selling_price_total = 0;
                var offer_price_total = 0;
                var mrp_price_total = 0;
                var qty_total = 0;
                var cost_total = 0;

                var total_base_discount_percent =0;
                var total_base_discount_amount =0;
                var total_scheme_discount_percent =0;
                var total_scheme_discount_amount =0;
                var total_free_discount_percent =0;
                var total_free_discount_amount =0;
                var total_cost_rate =0;
                var free_qty_total =0;
                var total_gross_cost= 0;


                var colspan = 4;
                if(dataval['inward_type'] == 2)
                {
                    colspan = 3;
                    if(dataval['inward_with_unique_barcode'] == 1)
                    {
                        colspan = 4;
                    }
                }

                $.each(product_detail_record,function (key,value)
                {
                    var product_detail = '';
                    if(value['product_detail'] != '' && value['product_detail'] != 'undefined')
                    {
                         product_detail = value['product_detail'];
                    }
                    var product_system_barcode = '';
                    var product_name = '';
                    var product_code = '';
                    if(product_detail != '' && product_detail['product_system_barcode'] != '' && product_detail['product_system_barcode'] != null)
                    {
                         product_system_barcode = product_detail['product_system_barcode'];
                    }
                    if(product_detail != '' && product_detail['product_name'] != '' && product_detail['product_name'] != null)
                    {
                        product_name = product_detail['product_name'];
                    } if(product_detail != '' && product_detail['product_code'] != '' && product_detail['product_code'] != null)
                    {
                        product_code = product_detail['product_code'];
                    }

                    var barcode = '';
                    if(product_detail != '' && product_detail['supplier_barcode'] != " " && product_detail['supplier_barcode'] != null)
                    {
                        barcode = product_detail['supplier_barcode'];
                    }
                    else {

                        barcode = product_detail['product_system_barcode'];
                    }

                    var uqc_name = '';


                    if(product_detail != '' && product_detail['uqc_id'] != '' && product_detail['uqc_id'] != null && product_detail['uqc_id'] != 0)
                    {
                        uqc_name = product_detail['uqc']['uqc_shortname'];
                    }

                    var batch_no = '';
                    var base_price = 0;
                    var base_discount_percent = 0;
                    var base_discount_amount = 0;
                    var scheme_discount_percent = 0;
                    var scheme_discount_amount = 0;
                    var free_discount_percent = 0;
                    var free_discount_amount = 0;
                    var cost_rate = 0;
                    var profit_percent = 0;
                    var profit_amount = 0;
                    var offer_price = 0;
                    var product_mrp = 0;
                    var product_qty = 0;
                    var free_qty = 0;
                    var mfg_date = '';
                    var expiry_date = '';
                    var total_gross = 0;
                    var cost_igst_amount = 0;
                    var cost_cgst_amount = 0;
                    var cost_sgst_amount = 0;
                    var sell_price =0;
                    var cost_last =0;


                  if(value['batch_no'] != '' && value['batch_no'] != null)
                  {
                      batch_no = value['batch_no'];
                  }
                  if(value['base_price'] != '')
                  {
                      base_price = value['base_price'];
                  }
                  if(value['base_discount_percent'] != '')
                  {
                      base_discount_percent = value['base_discount_percent'];
                  }
                  if(value['base_discount_amount'] != '')
                  {
                      base_discount_amount = value['base_discount_amount'];
                  }
                  if(value['scheme_discount_percent'] != '')
                  {
                      scheme_discount_percent = value['scheme_discount_percent'];
                  }
                  if(value['scheme_discount_amount'] != '')
                  {
                      scheme_discount_amount = value['scheme_discount_amount'];
                  }if(value['free_discount_percent'] != '')
                  {
                      free_discount_percent = value['free_discount_percent'];
                  }
                  if(value['free_discount_amount'] != '')
                  {
                      free_discount_amount = Number(value['free_discount_amount']);
                  }
                  if(value['cost_rate'] != '')
                  {
                      cost_rate = value['cost_rate'];
                  }
                  if(value['profit_percent'] != '')
                  {
                      profit_percent = value['profit_percent'];
                  }
                  if(value['profit_amount'] != '')
                  {
                      profit_amount = value['profit_amount'];

                  }if(value['offer_price'] != '')
                  {
                      offer_price = value['offer_price'];
                  }if(value['product_mrp'] != '')
                  {
                      product_mrp = value['product_mrp'];
                  }if(value['product_qty'] != '')
                  {
                      product_qty = value['product_qty'];
                  }if(value['free_qty'] != '')
                  {
                      free_qty = value['free_qty'];
                  }if(value['mfg_date'] != '' && value['mfg_date'] != null)
                  {
                      mfg_date = value['mfg_date'];
                  }if(value['expiry_date'] != '' && value['expiry_date'] != null)
                  {
                      expiry_date = value['expiry_date'];
                  }if(value['total_gross'] != '')
                  {
                      total_gross = value['total_gross'];
                  }if(value['cost_igst_amount'] != '')
                  {
                      cost_igst_amount = value['cost_igst_amount'];
                  }if(value['cost_cgst_amount'] != '')
                  {
                      cost_cgst_amount = value['cost_cgst_amount'];
                  }if(value['cost_sgst_amount'] != '')
                  {
                      cost_sgst_amount = value['cost_sgst_amount'];
                  }if(value['sell_price'] != '')
                  {
                      sell_price = value['sell_price'];
                  }if(value['total_cost'] != '')
                  {
                      cost_last = value['total_cost'];
                  }


                    var feature_show_val = "";
                    if(show_dynamic_feature != '')
                    {
                        var feature = show_dynamic_feature.split(',');

                        $.each(feature,function(fea_key,fea_val)
                        {
                            //feature_show_val += '<td>'+product_detail[fea_val]+'</td>';

                            var feature_name = '';
                            if(typeof(product_detail[fea_val]) != "undefined" && product_detail[fea_val] !== null) {
                                feature_name = product_detail[fea_val];
                            }
                            feature_show_val += '<td>' + feature_name + '</td>';
                        })
                    }



                    base_price_total += base_price;
                    igst_total += cost_igst_amount;
                    cgst_total += cost_cgst_amount;
                    sgst_total += cost_sgst_amount;
                    profit_percent_total += profit_percent;

                    profit_amt_total += profit_amount;
                    selling_price_total += sell_price;
                    offer_price_total += offer_price;
                    mrp_price_total += product_mrp;
                    qty_total += product_qty;
                    cost_total += cost_last;
                    total_base_discount_percent += base_discount_percent;
                    total_base_discount_amount += base_discount_amount;
                    total_scheme_discount_percent += scheme_discount_percent;
                    total_scheme_discount_amount += scheme_discount_amount;
                    total_free_discount_percent += free_discount_percent;
                    total_free_discount_amount += free_discount_amount;
                    total_cost_rate += cost_last;
                    free_qty_total += free_qty;
                    total_gross_cost +=cost_rate;

                  var total_qty = ((Number(product_qty)) + (Number(free_qty)));
                  var total_cost = ((Number(value['cost_price'])) * (Number(total_qty)));

                    product_html += '<tr id="'+value['product_id']+'"> ';
                    product_html += '<td class="leftAlign ">'+barcode+'</td>' ;
                    product_html += '<td class="leftAlign ">'+product_name+'</td>';
                    product_html += feature_show_val;
                    product_html += '<td class="leftAlign ">'+uqc_name+'</td>';
                    product_html += '<td class="leftAlign ">'+product_code+'</td>';
                    product_html += '<td class="leftAlign  garment_case_hide show_in_unique">'+batch_no+'</td>';
                    product_html += '<td class="rightAlign inward_calculation_case">'+base_price+'</td>';
                    product_html += '<td class="rightAlign inward_calculation_case">'+base_discount_percent+'</td>';
                    product_html += '<td class="rightAlign inward_calculation_case">'+base_discount_amount+'</td>';
                    product_html += '<td class="rightAlign  garment_case_hide inward_calculation_case">'+scheme_discount_percent+'</td>';
                    product_html += '<td class="rightAlign  garment_case_hide inward_calculation_case">'+scheme_discount_amount+'</td>';
                    product_html += '<td class="rightAlign  garment_case_hide inward_calculation_case">'+free_discount_percent+'</td>';
                    product_html += '<td class="rightAlign  garment_case_hide inward_calculation_case">'+free_discount_amount+'</td>';
                    product_html += '<td class="rightAlign  garment_case_hide inward_calculation_case">'+cost_rate+'</td>';

                        if(tax_type == 1)
                        {
                            product_html +=   '<td class="rightAlign inward_calculation_case">' + cost_igst_amount + '</td>';
                        }
                        else
                        {
                            product_html +=   '<td class="rightAlign inward_calculation_case">' + cost_igst_amount + '</td>';
                            product_html +=  '<td class="rightAlign inward_calculation_case ">' + cost_cgst_amount + '</td>';
                            product_html += '<td class="rightAlign inward_calculation_case ">' + cost_sgst_amount + '</td>';
                        }
                    product_html +='<td class="rightAlign inward_calculation_case">'+profit_percent+'</td>' ;
                        product_html +='<td class="rightAlign inward_calculation_case">'+profit_amount+'</td>' ;
                        product_html +='<td class="rightAlign inward_calculation_case">'+sell_price+'</td>' ;
                        product_html +='<td class="rightAlign inward_calculation_case">'+offer_price+'</td>' ;
                        product_html +='<td class="rightAlign inward_calculation_case">'+product_mrp+'</td>' ;
                        product_html +='<td class="rightAlign ">'+product_qty+'</td>' ;
                        product_html +='<td class="rightAlign  garment_case_hide ">'+free_qty+'</td>' ;
                        product_html +='<td class="leftAlign  garment_case_hide">'+mfg_date+'</td>' ;
                        product_html +='<td class="leftAlign  garment_case_hide">'+expiry_date+'</td> ';
                        product_html +='<td class="rightAlign inward_calculation_case">'+parseFloat(cost_last).toFixed(decimal_points)+'</td>' ;
                        product_html +='</tr>';
                });

                parseFloat(total_base_discount_percent).toFixed(decimal_points);
                parseFloat(total_base_discount_amount).toFixed(decimal_points);
                parseFloat(total_scheme_discount_percent).toFixed(decimal_points);
                parseFloat(total_scheme_discount_amount).toFixed(decimal_points);
                parseFloat(total_free_discount_percent).toFixed(decimal_points);
                parseFloat(total_free_discount_amount).toFixed(decimal_points);
                parseFloat(igst_total).toFixed(decimal_points);
                parseFloat(total_cost_rate).toFixed(decimal_points);
                parseFloat(igst_total).toFixed(decimal_points);
                parseFloat(cgst_total).toFixed(decimal_points);
                parseFloat(sgst_total).toFixed(decimal_points);
                parseFloat(profit_percent_total).toFixed(decimal_points);
                parseFloat(profit_amt_total).toFixed(decimal_points);
                parseFloat(selling_price_total).toFixed(decimal_points);
                parseFloat(offer_price_total).toFixed(decimal_points);
                parseFloat(mrp_price_total).toFixed(decimal_points);

                if(total_base_discount_percent == 0)
                {
                    total_base_discount_percent = '';
                }if(total_base_discount_amount == 0)
                {
                    total_base_discount_amount = '';
                }if(total_scheme_discount_percent == 0)
                {
                    total_scheme_discount_percent = '';
                }if(total_scheme_discount_amount == 0)
                {
                    total_scheme_discount_amount = '';
                }if(total_free_discount_percent == 0)
                {
                    total_free_discount_percent = '';
                }if(total_free_discount_amount == 0)
                {
                    total_free_discount_amount = '';
                }if(total_cost_rate == 0)
                {
                    total_cost_rate = '';
                }if(igst_total == 0)
                {
                    igst_total = '';
                }if(cgst_total == 0)
                {
                    cgst_total = '';
                }if(sgst_total == 0)
                {
                    sgst_total = '';
                }if(profit_percent_total == 0)
                {
                    profit_percent_total = '';
                }if(profit_amt_total == 0)
                {
                    profit_amt_total = '';
                }if(selling_price_total == 0)
                {
                    selling_price_total = '';
                }if(offer_price_total == 0)
                {
                    offer_price_total = '';
                }if(mrp_price_total == 0)
                {
                    mrp_price_total = '';
                }

                var footer_html = '<tfoot id="footer_view_inward" style="border-bottom:1px solid #C0C0C0 !important;border-top:1px solid #C0C0C0 !important;">\n';
                footer_html += '<tr>' ;
                    //footer_html +='<th colspan="'+colspan+'" class="text-dark font-14 font-weight-600"></th>' ;

                if(show_dynamic_feature != '')
                {
                    var feature = show_dynamic_feature.split(',');

                    $.each(feature,function(fea_key,fea_val)
                    {
                        colspan++;
                    })
                }
                footer_html +='<th colspan="'+colspan+'" class="text-dark font-14 font-weight-600"></th>' ;
                    footer_html +='<th class="text-left text-dark font-14 font-weight-600">Total</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">'+base_price_total+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">'+total_base_discount_percent+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">'+total_base_discount_amount+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case">'+total_scheme_discount_percent+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case">'+total_scheme_discount_amount+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case">'+total_free_discount_percent+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case">'+total_free_discount_amount+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case">'+total_gross_cost+'</th>' ;
                    if(tax_type == 1)
                    {
                        footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">' + igst_total + '</th>';
                    }
                    else {
                        footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">' + igst_total + '</th>';
                        footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">' + cgst_total + '</th>';
                        footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">' + sgst_total + '</th>';
                    }
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">'+profit_percent_total+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">'+profit_amt_total+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">'+selling_price_total+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">'+offer_price_total+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">'+mrp_price_total+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600">'+qty_total+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide">'+free_qty_total+'</th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide"></th>' ;
                    footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide"></th>' ;
                    footer_html +='<th class="text-right text-dark font-18 font-weight-600 inward_calculation_case">'+currency_symbol+' <span id="grandtotal">'+parseFloat(total_cost_rate).toFixed(decimal_points)+'</span></th>' ;
                    footer_html +='</tr>';
                    footer_html +='</tfoot>';
                $("#view_inward_record").append(product_html);
                $("#view_inward_record").after(footer_html);

                 //COUNT TOTAL NO OF ITEMS IN POPUP
                  var totalitem = $("#view_inward_record tr").length;
                  $(".totcount").html(totalitem);
            }

            if(dataval['inward_type'] == 1)
            {
                $(".garment_case_hide").show();
            }
            else
            {
                $(".garment_case_hide").hide();
                if(dataval['inward_with_unique_barcode'] == 1)
                {
                    $(".show_in_unique").show();
                }
            }

            if(inward_calculation == 3) {
                $(".inward_calculation_case").hide();
            }
            else
            {
                $(".inward_calculation_case").show();
            }
        }
        else
        {

        }
    })
}

function edit_inwardstock(stockid,inward_type)
{
    var  url = "edit_inward_stock";
    var type = "POST";
    var dataType = "";
    var data = {
        'inward_stock_id' : stockid,
        'inward_type' : inward_type,
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
           localStorage.setItem('edit_inward_stock_record',JSON.stringify(dta['Data']));

            window.location.href = url;
        }
    });
}

function delete_inwardstock(stock_id)
{
    // if(confirm("Are You Sure want to delete this inward?")) {

      var errmsg = "Are You Sure want to delete this inward?";
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
        var url = "delete_inward_stock";
        var type = "POST";
        var dataType = "";
        var data = {
            'inward_stock_id': stock_id,
        };
        callroute(url, type,dataType, data, function (data) {
            var dta = JSON.parse(data);

            if (dta['Success'] == "True")
            {
                toastr.success(dta['Message']);
                resettable('inward_fetch_data','viewinwardrecord');

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

// $("#filer_from_to").daterangepicker().val('');

function resetinwardfilterdata()
{
    $("#filer_from_to").val('');
    $("#invoice_no_filter").val('');
    $("#supplier_name").val('');
    $("#supplier_id").val('');
    resettable('inward_fetch_data','viewinwardrecord');
}

$(document).on('click', '#inward_stock_export', function(){

    var filter_date = $('#filer_from_to').val();

    var from_date = '';
    var to_date = '';

    var separate_date = filter_date.split(' - ');
    if(separate_date[0] != undefined)
    {
        from_date = separate_date[0];
    }

    if(separate_date[1] != undefined)
    {
        to_date = separate_date[1];
    }
    var query = {
        from_date: from_date,
        to_date : to_date,
        invoice_no : $("#invoice_no_filter").val(),
        supplier_name : $("#supplier_id").val()
    };


    var url = "inward_stock_export?" + $.param(query)
    window.open(url,'_blank');


});

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
            var data = {
                'search_val': $("#supplier_name").val()
            };
            var dataType = '';
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
                                        label: value.supplier_first_name + ' ' + last_name + suppliervalue.supplier_gstin,
                                        value: value.supplier_first_name + ' ' + last_name + suppliervalue.supplier_gstin,
                                        supplier_gst_id: suppliervalue.supplier_gst_id,
                                    });
                                });
                            }
                            else
                            {
                                resultsupplier.push({
                                    label: value.supplier_first_name + ' ' + last_name ,
                                    value: value.supplier_first_name + ' ' + last_name ,
                                    supplier_gst_id: '',
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

            $("#supplier_id").val(gst_id);
            $(".ui-helper-hidden-accessible").css('display','none');
            //call a function to perform action on select of supplier
        }
    })
});


$('#nextrecord').click(function(e){

var inward_id = $("#nextrecord").attr('data-id');

viewinward(inward_id);

});

$('#previousrecord').click(function(e){

var inward_id = $("#previousrecord").attr('data-id');
viewinward(inward_id);

});


$('#searchCollapse').click(function (e) {
    $('#searchBox').slideToggle();
    resetinwardfilter_data();
});

function resetinwardfilter_data()
{
    $("#filer_from_to").val('');
    $("#invoice_no_filter").val('');
    $("#supplier_name").val('');
    $("#supplier_id").val('');

}
