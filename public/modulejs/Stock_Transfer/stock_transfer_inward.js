$('#searchCollapse').click(function(e){

    $('#searchBox').slideToggle();
});

function reset_stock_transfer_inward()
{
    $("#filer_from_to").val('');
    $("#stock_transfer_no_filter").val('');

    resettable('stock_transfer_inward_fetch_data','viewstockinward');
}

function view_stock_transfer(stock_transfer_id)
{
    $("#stock_inward_popup_record").trigger('reset');
    $("#view_stock_inward_record").html('');
    $('#view_stock_inward_pop tfoot').html('');
    $("#previousrecord").attr('data-id','');
    $("#nextrecord").attr("data-id",'');

    var  url = "view_stock_inward_detail";
    var type = "POST";
    var dataType = "";
    var data = {
        'stock_transfer_id' : stock_transfer_id,
    };
    callroute(url,type,dataType,data,function (data)
    {
        var dta = JSON.parse(data);

        if(dta['Success'] == "True")
        {
            $("#view_stock_inward_record").html('');
            $('#view_stock_inward_pop tfoot').html('');

            var dataval = JSON.parse(dta['Data']);

            $("#viewstockinwardpopup").modal('show');

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



            $(".transfer_no_popup").html(dataval['stock_transfer_no']);
            $(".transfer_date_popup").html(dataval['stock_transfer_date']);

            $(".invoice_title").html('Invoice NO::');


            if(typeof dataval['stock_transfer_detail'] != 'undefined' && dataval['stock_transfer_detail'] != '' )
            {
                var product_detail_record = dataval['stock_transfer_detail'];
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

                var currency_symbol = '&#x20b9';
                if(tax_type == 1)
                {
                    currency_symbol = currency_title;
                }

                $.each(product_detail_record,function (key,value)
                {
                    var product_detail = '';
                    if(value['product_data'] != '' && value['product_data'] != 'undefined')
                    {
                        product_detail = value['product_data'];
                    }

                    var product_system_barcode = '';
                    var product_name = '';
                    var hsn_sac_code = '';

                    if(product_detail != '' && product_detail['product_system_barcode'] != '' && product_detail['product_system_barcode'] != null)
                    {
                        product_system_barcode = product_detail['product_system_barcode'];
                    }
                    if(product_detail != '' && product_detail['product_name'] != '' && product_detail['product_name'] != null)
                    {
                        product_name = product_detail['product_name'];
                    } if(product_detail != '' && product_detail['hsn_sac_code'] != '' && product_detail['hsn_sac_code'] != null)
                    {
                    hsn_sac_code = product_detail['hsn_sac_code'];
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


                    $uqc_name = '';
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

                    var total_qty = ((Number(product_qty)) + (Number(free_qty)));
                    var total_cost = ((Number(value['cost_price'])) * (Number(total_qty)));

                    product_html += '<tr id="'+value['product_id']+'"> ';
                    product_html += '<td class="leftAlign ">'+barcode+'</td>' ;
                    product_html += '<td class="leftAlign ">'+product_name+'</td>';
                    product_html += '<td class="leftAlign ">'+uqc_name+'</td>';
                    product_html += '<td class="leftAlign ">'+hsn_sac_code+'</td>';
                    product_html += '<td class="leftAlign  garment_case_hide">'+batch_no+'</td>';
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
                footer_html +='<th colspan="'+3+'" class="text-dark font-14 font-weight-600"></th>' ;
                footer_html +='<th class="text-left text-dark font-14 font-weight-600">Total</th>' ;
                footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">'+base_price_total+'</th>' ;
                footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">'+total_base_discount_percent+'</th>' ;
                footer_html +='<th class="text-right text-dark font-14 font-weight-600 inward_calculation_case">'+total_base_discount_amount+'</th>' ;
                footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case">'+total_scheme_discount_percent+'</th>' ;
                footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case">'+total_scheme_discount_amount+'</th>' ;
                footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case">'+total_free_discount_percent+'</th>' ;
                footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case">'+total_free_discount_amount+'</th>' ;
                footer_html +='<th class="text-right text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case">'+total_cost_rate+'</th>' ;
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
                $("#view_stock_inward_record").append(product_html);
                $("#view_stock_inward_record").after(footer_html);

                //COUNT TOTAL NO OF ITEMS IN POPUP
                var totalitem = $("#view_stock_inward_record tr").length;
                $(".totcount").html(totalitem);
            }

            if(dataval['inward_type'] == 1)
            {
                $(".garment_case_hide").show();
            }
            else
            {
                $(".garment_case_hide").hide();
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



function take_transfer_inward(stock_transfer_id)
{
    var  url = "take_stock_inward";
    var type = "POST";
    var dataType = "";
    var data = {
        'stock_transfer_id' : stock_transfer_id
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

            localStorage.setItem('take_stock_inward_data', JSON.stringify(dta['Data']));

            window.location.href = url;
        }
    });
}




