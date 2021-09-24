//set datepicker to debit_date

    $('#damage_date').datepicker({
        autoclose: true,
        format: "dd-mm-yyyy",
        immediateUpdates: true,
        todayBtn: true,
        orientation: "bottom",
        todayHighlight: true
    }).on('keypress paste', function (e) {
        e.preventDefault();
        return false;
    }).datepicker("setDate", "0");

//end of datepicker


$("#damage_productsearch").typeahead({
    source: function(request, process)
    {
        var  url = "normal_damage_product_search";
        var type = "post";
        var dataType = "json";
        var data = {
            search_val: $("#damage_productsearch").val(),
            term: request.term
        };
        callroute(url,type,dataType,data,function (data)
        {

                objects = [];
                map = {};
                if($("#damage_productsearch").val()!='')
                {
                    $.each(data, function(i, object)
                    {
                        map[object.label] = object;
                        objects.push(object.label);
                    });

                    process(objects);

                }
                else
                {
                    $(".typeahead.dropdown-menu").hide();
                }
        });
    },

   // minLength: 1,
    afterSelect: function (item)
    {
        var value = item;
        var supplier_gst_id = map[item]['supplier_gst_id'];
        var invoice_no = map[item]['invoice_no'];
        var barcode = map[item]['barcode'];
        var product_name = map[item]['product_name'];

        damageproductdetail_normal(barcode,product_name,invoice_no,supplier_gst_id);
        $("#damage_productsearch").val('');
    }

});


function damageproductdetail_normal(barcode,product_name,invoice_no,supplier_gst_id)
{
   var type = "POST";
   var url = 'damage_product_detail_normal';
   var dataType = "";
   var data = {
       "barcode" : barcode,
       "product_name" : product_name,
       "invoice_no" : invoice_no,
       "supplier_gst_id" : supplier_gst_id
   };
   callroute(url,type,dataType,data,function(data)
   {
       var product_data = JSON.parse(data,true);

        if(product_data['Success'] == "True")
        {
            var product_html = '';
            var product_detail  = product_data['Data'][0];



           var stock = product_detail['pending_return_qty'];
          if(stock == 0 || stock == '')
          {
                toastr.error("Stock not avaiable!");
                $('#productsearch').val('');
                return false;
          }
          else
          {
              var pricehtml = '';
              var pcount    = 0;
              var sellingprice  = 0;
             // var stock = 0;
              var gst_per = 0;
              var cost_rate = 0;
              var cost_price = 0;
              var inward_product_detail_id = product_detail['inward_product_detail_id'];
              var invoice_no = product_detail['inward_stock']['invoice_no'];

              var product_code = '';
              if (product_detail['product']['product_code'] != null && product_detail['product']['product_code'] != '') {
                  product_code = product_detail['product']['product_code'];
              }
              if (product_detail['cost_price'] != null && product_detail['cost_price'] != '') {
                  cost_price = product_detail['cost_price'];
              }

              if (product_detail['cost_rate'] != null && product_detail['cost_rate'] != '')
              {
                  cost_rate = product_detail['cost_rate'];
              }

              var feature_show_val = "";
              if(damage_show_dynamic_feature != '')
              {
                  var feature = damage_show_dynamic_feature.split(',');

                  $.each(feature,function(fea_key,fea_val)
                  {
                      var feature_name = '';
                      if(typeof(product_detail['product'][fea_val]) != "undefined" && product_detail['product'][fea_val] !== null) {

                          feature_name = product_detail['product'][fea_val];
                      }
                      feature_show_val += '<td>' + feature_name + '</td>';
                  })
              }

              var total_allow_qty = stock;

              pricehtml = Number(product_detail['offer_price']).toFixed(4);

              var samerow = 0;

              var batch_no = product_detail['batch_no']==null?'':product_detail['batch_no'];

              var totalgstPercent  = 0;
              var totalgstAmount  = 0;
              var gsttype  = 0; //1=igst,2=cgst,sgst

              if($("#tax_type").val() == 1)
              {
                  gsttype = 1;
                  totalgstPercent = Number(product_detail['cost_igst_percent']);
                  totalgstAmount = Number(product_detail['cost_igst_amount']);
              }
              else {
                  if (product_detail['inward_stock']['state_id'] != $("#company_state").val())
                  {
                      totalgstPercent = Number(product_detail['cost_igst_percent']);
                      totalgstAmount = Number(product_detail['cost_igst_amount']);
                      gsttype = 1;
                  } else {
                      totalgstPercent = Number(product_detail['cost_cgst_percent']) + Number(product_detail['cost_sgst_percent']);
                      totalgstAmount = Number(product_detail['cost_cgst_amount']) + Number(product_detail['cost_sgst_amount']);
                      gsttype = 2;
                  }
              }

              $("#DamageSearchResult tr").each(function ()
              {
                  var row_inward_product_detail_id = $(this).attr('id').split('_')[1];

                  if (row_inward_product_detail_id == inward_product_detail_id)
                  {
                      var qty = $("#qty_" + inward_product_detail_id).html();
                      var product_qty = ((Number(qty)) + (Number(1)));

                      $("#qty_" + inward_product_detail_id).html(product_qty);

                      // total cost calculation
                      var totalCost = Number(cost_price) * Number(product_qty);
                      $('#totalcost_rate_'+inward_product_detail_id).html(totalCost.toFixed(4));

                      //gst calculation
                      var total_gst = Number(totalgstAmount) * Number(product_qty);
                      $("#cost_gst_amount_"+inward_product_detail_id).html(total_gst.toFixed(4));

                      // total cost with gst and qty calculation
                      var total_costprice = Number(total_gst) + Number(totalCost);
                      $("#total_cost_price_"+inward_product_detail_id).html(total_costprice.toFixed(4));

                      samerow = 1;
                  }
              });
              if (samerow == 0)
              {
                var barcode = product_detail['product']['supplier_barcode'] == null ? product_detail['product']['product_system_barcode'] : product_detail['product']['supplier_barcode'];
                var total_cost_price = (Number(cost_rate) + Number(totalgstAmount)).toFixed(4);
                  var supplier_name = '';

                if(product_detail['inward_stock']['supplier_gstdetail'] != '' && product_detail['inward_stock']['supplier_gstdetail'] != null)
                {
                    var supplier_name =product_detail['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'];
                }

                product_html += '<tr id="product_' + inward_product_detail_id + '">' +
                      //  product name
                      '<td id="supplier_name_'+inward_product_detail_id + '" name="supplier_name_'+inward_product_detail_id + '">' +supplier_name+ '</td>' +
                      '<td id="product_name_' + inward_product_detail_id + '" name="product_name_' + inward_product_detail_id + '">' + product_detail['product']['product_name'] + '</td>' +
                      //  product system barcode
                      '<td id="barcode_' + inward_product_detail_id + '" name="barcode_' + inward_product_detail_id + '">' + barcode + '' +
                      //BATCH NO
                      '<td class="batch_case_show" id="batch_no'+inward_product_detail_id+'" name="batch_no[]">'+batch_no+'</td>'+
                      //  invoice No
                      '<td id="invoice_no_' + inward_product_detail_id + '" name="invoice_no_' + inward_product_detail_id + '">' + invoice_no + '</td>' +
                      //  product code
                      '<td id="product_code_' + inward_product_detail_id + '" name="product_code_' + inward_product_detail_id + '">' + product_code + '</td>';


                product_html += feature_show_val;

                //  in stock
                product_html += '<td id="stock_' + inward_product_detail_id + '" name="stock_' + inward_product_detail_id + '">' + stock + '</td>' +
                      //  cost_rate
                      '<td class="damagehide_on_without_calculation" id="cost_rate_' + inward_product_detail_id + '" name="cost_rate_' + inward_product_detail_id + '">'+Number(cost_rate).toFixed(4)+'</td>' +
                      //  product mrp
                      '<td class="damagehide_on_without_calculation" id="mrp_' + inward_product_detail_id + '" name="mrp_' + inward_product_detail_id + '">' + pricehtml + '</td>' +
                      //  damage qty
                      '<td id="qty_' + inward_product_detail_id+'" onkeypress = "return testCharacter(event);"   class="editablearea" contenteditable="true" name="qty_' + inward_product_detail_id + '" onkeyup="return calculate_damage_qty(this);">1</td>' +
                      //  Cost rate
                      '<td  id="totalcost_rate_' + inward_product_detail_id + '" name="totalcost_rate_' + inward_product_detail_id + '" class="damagehide_on_without_calculation rightAlign">'+cost_rate+'</td>' +
                      //  GST %
                      '<td  id="cost_gst_percent_' + inward_product_detail_id + '" class="damagehide_on_without_calculation rightAlign">' + totalgstPercent + '</td>' +
                      //  GST Amount
                      '<td id="cost_gst_amount_' + inward_product_detail_id + '" class="damagehide_on_without_calculation rightAlign">' + totalgstAmount + '</td>' +
                      //  total cost
                      '<td id="total_cost_price_' + inward_product_detail_id + '" style="" class="damagehide_on_without_calculation rightAlign" name="total_cost_price_' + inward_product_detail_id + '">'+total_cost_price+'</td>' +
                      //  notes
                      '<td id="note_' + inward_product_detail_id + '" style="font-weight:bold;">' +
                      '<textarea name="damage_note_' + inward_product_detail_id + '" id="damage_note_' + inward_product_detail_id + '" style="width:80px;"></textarea></td>' +
                      '<td>' +
                      '<input type="file" name="damage_product_image_'+inward_product_detail_id+'" id="damage_product_image_'+inward_product_detail_id+'" data-id="'+inward_product_detail_id+'"  onchange="damge_product_validation(this);">' +
                      '<div id="image_block_'+inward_product_detail_id+'" style="display: none">' +
                      '<img src="" id="damage_product_preview_'+inward_product_detail_id+'" width="100%" height="200px">' +
                      '<input type="hidden" name="image_json_'+inward_product_detail_id+'" id="image_json_'+inward_product_detail_id+'">' +
                       '<input type="hidden" name="image_name_'+inward_product_detail_id+'" id="image_name_'+inward_product_detail_id+'">' +
                    '<a onclick="remove_damge_product('+inward_product_detail_id+');" class="displayright">' +
                      '<i class="fa fa-remove" style="font-size: 20px;"></i>' +
                      '</div>' +
                      '</td>' +
                    //  action
                    '<td onclick="removerow(' + inward_product_detail_id + ');"><i class="fa fa-close"></i></td>' +
                      '<input type="hidden" id="product_id' + inward_product_detail_id + '" value="' + product_detail['product_id'] + '" />' +
                      '<input type="hidden" id="gst_type_id_' + inward_product_detail_id + '" value="' + gsttype + '" />' +
                      '<input type="hidden" id="damage_product_detail_id_' + inward_product_detail_id + '" value="" />' +
                      '<input type="hidden" id="total_allow_qty_' + inward_product_detail_id + '" value="'+total_allow_qty+'" />' +
                      '</tr>';
              }
          }
    }
        $("#productsearch").val('');
        $(".odd").hide();
        $("#DamageSearchResult").prepend(product_html);
      // var batch_no = product_detail['batch_no']==null?'':product_detail['batch_no'];
        if($("#company_bill_type").val() == 3)
        {
            $('.batch_case_show').show();
        }
        else
        {
            $('.batch_case_show').hide();
        }
        if(inward_calculation == 3)
        {
            $(".damagehide_on_without_calculation").hide();
        }

        //FOR DISPLAY TOTAL NUMBERS OF ITEMS
        var total_record = $("#DamageSearchResult tr").length;
        $(".damage_total_item").html(total_record);
        totalCalculations();
   });
}


function totalCalculations()
{

    var qty = 0;
    var totalcostratewithqty = 0;
    var totalgstamtwithqty = 0;
    var totalcostpricewithqty = 0;

    $("#DamageSearchResult tr").each(function (index, e)
    {
        var inward_product_detail_id = $(this).attr('id').split('product_')[1];
        var tbl_row = $(this).data('id');

        $(this).find('td').each(function ()
        {
            //FOR CALCULATE TOTAL QTY
            if ($(this).attr('id') == "qty_" + inward_product_detail_id)
            {

                var totalqty = $(this).html();
                if (totalqty == '')
                {
                    totalqty = 0;
                }
                qty += (Number(totalqty));
            }


            //FOR CALCULATE TOTAL COST RATE WITH QTY
            if ($(this).attr('id') == "totalcost_rate_" + inward_product_detail_id)
            {
                var totalcost_rate = $(this).html();
                if ($.isNumeric(totalcost_rate))
                {
                    totalcostratewithqty += (Number(totalcost_rate));
                }
            }

            //FOR CALCULATE TOTAL GST WITH QTY
            if ($(this).attr('id') == "cost_gst_amount_"+inward_product_detail_id)
            {
                var gst_amt = $(this).html();
                if ($.isNumeric(gst_amt))
                {
                    totalgstamtwithqty += (Number(gst_amt));
                }
            }
            //FOR CALCULATE TOTAL COST PRICE WITH QTY AND GST AMOUNT
            if ($(this).attr('id') == "total_cost_price_" + inward_product_detail_id)
            {
                var totalcostprice = $(this).html();
                if ($.isNumeric(totalcostprice))
                {
                    totalcostpricewithqty += (Number(totalcostprice));
                }
            }
        });
    });


    if(qty != '' && !isNaN(qty))
    {
        $("#totqtyData").html(qty);
    }
    else
    {
        $("#totqtyData").html(0);
    }

    $("#totcostData").html(totalcostratewithqty.toFixed(decimal_points));
    $("#totgstData").html(totalgstamtwithqty.toFixed(decimal_points));
    $("#totcostpriceData").html(totalcostpricewithqty.toFixed(decimal_points));
}

function removerow(inward_product_detail_id)
{
    $("#product_"+inward_product_detail_id).remove();

    //FOR REMOVE TOTAL NUMBERS OF ITEM
    var total_record = $("#DamageSearchResult tr").length;
    $(".damage_total_item").html(total_record);
    totalCalculations();
}

function calculate_damage_qty(obj)
{
    var product_id        =   $(obj).attr('id').split('qty_')[1];
    var qty               =   $('#qty_'+product_id).html();
    var stock             =   $('#total_allow_qty_'+product_id).val();

    if(qty == '')
    {
        qty = 0;
    }

    if(Number(qty)>Number(stock))
    {
        $('#qty_'+product_id).html(Number(stock));
        toastr.error("Qty can not be greater than "+stock+" ");
    }

    var damage_qty = $("#qty_"+product_id).html();
    //CALCULATE TOTAL COST WITH QTY WITHOUT GST
    var cost_rate         =   $('#cost_rate_'+product_id).html();
    var totalcost_rate    =   Number(cost_rate) * Number(damage_qty);
    $('#totalcost_rate_'+product_id).html(totalcost_rate.toFixed(4));
    //END OF CALCULATE TOTAL COST WITH QTY


    //CALCULATE COST GST AMOUNT
    var cost_gst_percent  =   $('#cost_gst_percent_'+product_id).html();
    var cost_rate_for_gst        =   $('#totalcost_rate_'+product_id).html();
    var gst_amount         =   ((Number(cost_gst_percent)) * (Number(cost_rate_for_gst)) / Number(100));
    $('#cost_gst_amount_'+product_id).html(gst_amount.toFixed(4));
    //END OF CALCUALTE GST AMOUNT WITH QTY


    //CALCULATE TOTAL COST PRICE WITH QTY AND GST
    var total_cost_with_qty = $("#totalcost_rate_"+product_id).html();
    var total_gst_with_qty = $("#cost_gst_amount_"+product_id).html();
    var total_cost_price      =   Number(total_gst_with_qty)+ Number(total_cost_with_qty);
    $('#total_cost_price_'+product_id).html(total_cost_price.toFixed(4));
    //END OF CALCULATE TOTAL COST WITH QTY AND GST
    totalCalculations();
}


$('#saveDamageProducts').click( function(e)
{
    $("#saveDamageProducts").prop('disabled',true);
    if(validate_damage_form('damage_used_product'))
    {
        $("#saveDamageProducts").prop('disabled',true);

        var damage_product_array = [];
        var damage_type = $("input[name='DamageType']:checked").val();
        if (damage_type != '')
        {
            $('#DamageSearchResult tr').each(function ()
            {
             var arrayItem = {};

               var rowid = $(this).attr('id');

               if(rowid != undefined && rowid != '')
               {
                  var product_row = rowid.split('product_')[1];


                  if(product_row != '' && product_row != undefined)
                  {
                    var qty = $('#qty_' + product_row).html();

                    arrayItem['product_id'] =$('#product_id'+ product_row).val();
                    arrayItem['damage_product_detail_id'] = '';
                    arrayItem['damage_product_detail_id'] = $('#damage_product_detail_id_'+product_row).val();
                    arrayItem['inward_product_detail_id'] = product_row;
                    arrayItem['product_cost_rate'] = $('#cost_rate_' + product_row).html();

                    var cost_gst_percent = $("#cost_gst_percent_"+product_row).html();
                    var cost_gst_amount = $("#cost_gst_amount_"+product_row).html();

                 if($("#gst_type_id_"+product_row).val() == 1)
                 {
                    //IGST AMOUNT AND PERCENT
                     var cost_gst_amount_per_product = ((Number(cost_gst_amount)) / qty).toFixed(4);
                    arrayItem['product_cost_igst_percent'] = $("#cost_gst_percent_"+product_row).html();
                    arrayItem['product_cost_igst_amount'] = cost_gst_amount_per_product;
                    arrayItem['product_cost_cgst_percent'] = 0;
                    arrayItem['product_cost_cgst_amount'] = 0;
                    arrayItem['product_cost_sgst_percent'] = 0;
                    arrayItem['product_cost_sgst_amount'] = 0;
                    arrayItem['product_cost_igst_amount_with_qty'] = cost_gst_amount;
                 }
                 else
                 {
                    //CGST AND SGST AMOUNT AND PERCENT

                    cost_gst_amount  = ((Number(cost_gst_amount)) / (Number(qty)));

                    var cost_cgst_sgst_percent = ((Number(cost_gst_percent)) / 2).toFixed(4);
                    var cost_cgst_sgst_amount = ((Number(cost_gst_amount)) / 2).toFixed(4);
                    arrayItem['product_cost_igst_percent'] = 0;
                    arrayItem['product_cost_igst_amount'] = 0;
                    arrayItem['product_cost_cgst_percent'] = cost_cgst_sgst_percent;
                    arrayItem['product_cost_cgst_amount'] = cost_cgst_sgst_amount;
                    arrayItem['product_cost_sgst_percent'] = cost_cgst_sgst_percent;
                    arrayItem['product_cost_sgst_amount'] = cost_cgst_sgst_amount;
                    arrayItem['product_cost_cgst_amount_with_qty'] = ((Number(cost_cgst_sgst_amount)) * (Number(qty)));
                    arrayItem['product_cost_sgst_amount_with_qty'] = ((Number(cost_cgst_sgst_amount)) * (Number(qty)));

                 }
                    arrayItem['product_total_cost_rate'] = $("#totalcost_rate_"+product_row).html();
                    arrayItem['product_total_gst_amount'] = $("#cost_gst_amount_"+product_row).html();
                    //arrayItem['product_cost_rate_with_qty'] = $("#totalcost_rate_"+product_row).html();
                    arrayItem['product_total_cost_price'] = $("#total_cost_price_"+product_row).html();
                    arrayItem['product_mrp'] = $("#mrp_"+product_row).html();
                    arrayItem['product_damage_qty'] = qty;
                    arrayItem['product_notes'] = $("#damage_note_"+product_row).val();

                    arrayItem['image'] = $("#image_json_"+product_row).val();
                    arrayItem['image_name'] = $("#image_name_"+product_row).val();

                 }
               }

                damage_product_array.push(arrayItem);
            });

            var damage_detail = {};


            damage_detail['damage_product_id'] =  $('#damage_product_id').val();
            damage_detail['damage_type_id'] =  $("input[name='DamageType[]']:checked").val();
            damage_detail['damage_no'] = $('#damage_no').val();
            damage_detail['damage_total_qty'] = $('#totqtyData').html();
            damage_detail['damage_total_cost_rate'] = $('#totcostData').html();
            damage_detail['damage_total_gst'] = $('#totgstData').html();
            damage_detail['damage_total_cost_price'] = $('#totcostpriceData').html();
            damage_detail['damage_date'] = $("#damage_date").val();

            var url = "SaveDamageProducts";
            var type = "POST";
            var dataType = "";
            var data = {
                'damage_detail' : damage_detail,
                'damage_product_detail' : damage_product_array,
            };
            callroute(url, type,dataType, data, function (data)
            {
                var dta = JSON.parse(data);
                $("#saveDamageProducts").prop('disabled',false);
                toastr.success(dta['Message']);
                $("#damage_used_product").trigger('reset');
                $("#DamageSearchResult").empty();
                $('#damage_date').datepicker({
                    autoclose: true,
                    format: "dd-mm-yyyy",
                    immediateUpdates: true,
                    todayBtn: true,
                    orientation: "bottom",
                    todayHighlight: true
                }).datepicker("setDate", "0");



                if (dta['url'] != '' && dta['url'] != 'undefined') {

                    localStorage.removeItem('edit_damage_record');

                    setTimeout(function () {
                        window.location.href = dta['url'];
                    }, 1000);
                }

                //FOR DISPLAY TOTAL NUMBERS OF ITEMS
                var total_record = $("#DamageSearchResult tr").length;
                $(".damage_total_item").html(total_record);
            });
         }
    }else
    {
        $("#saveDamageProducts").prop('disabled',false);
        return false;
    }
});

function validate_damage_form(frmid)
{
    var error = 0;
    var totqtyData = $('#totqtyData').html();


    if (totqtyData == 0 || isNaN(totqtyData))
    {
        error = 1;
        toastr.error('select atleast one product to proceed');
    }

    if(error === 1)
    {
        return false;
    }else {
        return true
    }

}


$(document).ready(function(e)
{
    if(inward_calculation == 3)
    {
        $(".damagehide_on_without_calculation").hide();
    }

    var data   =  localStorage.getItem('edit_damage_record');
    if(data!=null)
    {
        var damage  =   JSON.parse(data);

        var damage_record = damage[0];
        var DamageType  =   damage_record['damage_type_id'];

        $("input[name=DamageType][value="+DamageType+"]").attr('checked', 'checked');

        $("#damage_no").val(damage_record['damage_no']);
        $("#damage_product_id").val(damage_record['damage_product_id']);
        $("#totqtyData").html(damage_record['damage_total_qty']);
        $("#totcostData").html(damage_record['damage_total_cost_rate']);
        $("#totgstData").html(damage_record['damage_total_gst']);
        $("#totcostpriceData").html(damage_record['damage_total_cost_price']);
        var product_html = '';
        if(damage_record['damageproduct_detail'] != '' && damage_record['damageproduct_detail'] != undefined)
        {
            $.each(damage_record['damageproduct_detail'], function (key, value)
            {
                var inward_product_detail_id = value['inward_product_detail']['inward_product_detail_id'];

                var barcode = value['product']['supplier_barcode'] == null ? value['product']['product_system_barcode'] : value['product']['supplier_barcode'];

                var invoice_no = value['inward_product_detail']['inward_stock']['invoice_no'];

                var product_code = '';
                if (value['product']['product_code'] != null && value['product']['product_code'] != '') {
                    product_code = value['product']['product_code'];
                }

                if (value['cost_price'] != null && value['cost_price'] != '') {
                    cost_price = value['cost_price'];
                }
                if (value['cost_rate'] != null && value['cost_rate'] != '')
                {
                    cost_rate = product_detail['cost_rate'];
                }
                var stock = value['inward_product_detail']['pending_return_qty'];

                var total_allow_qty = Number(stock) + Number(value['product_damage_qty']);

                var pricehtml = Number(value['inward_product_detail']['offer_price']).toFixed(4);

                var gsttype  = 0; //1=igst,2=cgst,sgst

                var totalgstPercent  = 0;
                var totalgstAmount  = 0;

                if(value['inward_product_detail']['inward_stock']['state_id'] !=$("#company_state").val())
                {
                    totalgstPercent = Number(value['inward_product_detail']['cost_igst_percent']);
                    totalgstAmount = Number(value['inward_product_detail']['cost_igst_amount']);
                    gsttype = 1;
                } else {
                    totalgstPercent = Number(value['inward_product_detail']['cost_cgst_percent']) + Number(value['inward_product_detail']['cost_sgst_percent']);
                    totalgstAmount = Number(value['inward_product_detail']['cost_cgst_amount']) + Number(value['inward_product_detail']['cost_sgst_amount']);
                    gsttype = 2;
                }
                var batch_no = value['inward_product_detail']['batch_no']==null?'':value['inward_product_detail']['batch_no'];

                if(value['product_notes'] == null)
                {
                    value['product_notes'] = '';
                }

                var supplier_name = '';
                if(value['inward_product_detail']['inward_stock']['supplier_gstdetail'] != '' && value['inward_product_detail']['inward_stock']['supplier_gstdetail'] != null)
                {
                     supplier_name =value['inward_product_detail']['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'];
                }

                var image_block = 'none';
                var image_src = '';

                if(value['image'] != '' && value['image'] != 'NULL')
                {
                    image_block = 'show';
                   image_src = DAMAGE_USED_PRODUCT_IMAGE+''+value['image'];
                }

                var feature_show_val = "";
                if(damage_show_dynamic_feature != '')
                {
                    var feature = damage_show_dynamic_feature.split(',');

                    $.each(feature,function(fea_key,fea_val)
                    {
                        var feature_name = '';
                        if(typeof(value['product'][fea_val]) != "undefined" && value['product'][fea_val] !== null)
                        {
                            feature_name = value['product'][fea_val];
                        }
                        feature_show_val += '<td>' + feature_name + '</td>';
                    })
                }

                product_html += '<tr id="product_' + inward_product_detail_id + '">' +
                    //  product name
                    '<td id="supplier_name_' + inward_product_detail_id + '" name="supplier_name_' + inward_product_detail_id + '">' + supplier_name + '</td>' +
                    '<td id="product_name_' + inward_product_detail_id + '" name="product_name_' + inward_product_detail_id + '">' + value['product']['product_name'] + '</td>' +
                    //  product system barcode
                    '<td id="barcode_' + inward_product_detail_id + '" name="barcode_' + inward_product_detail_id + '">' + barcode + '' +
                    //BATCH NO
                    '<td class="batch_case_show" id="batch_no'+inward_product_detail_id+'" name="batch_no[]">'+batch_no+'</td>'+
                    //  invoice No
                    '<td id="invoice_no_' + inward_product_detail_id + '" name="invoice_no_' + inward_product_detail_id + '">' + invoice_no + '</td>' +
                    //  product code
                    '<td id="product_code_' + inward_product_detail_id + '" name="product_code_' + inward_product_detail_id + '">' + product_code + '</td>';

                    product_html += feature_show_val;

                    //  in stock
                    product_html += '<td id="stock_' + inward_product_detail_id + '" name="stock_' + inward_product_detail_id + '">' + stock + '</td>' +
                    //  cost_rate
                    '<td class="damagehide_on_without_calculation" id="cost_rate_' + inward_product_detail_id + '" name="cost_rate_' + inward_product_detail_id + '">'+value['product_cost_rate']+'</td>' +
                    //  product mrp
                    '<td class="damagehide_on_without_calculation" id="mrp_' + inward_product_detail_id + '" name="mrp_' + inward_product_detail_id + '">' + pricehtml + '</td>' +
                    //  damage qty
                    '<td id="qty_' + inward_product_detail_id + '" onkeypress = "return testCharacter(event);" class="editablearea" contenteditable="true" name="qty_' + inward_product_detail_id + '" onkeyup="return calculate_damage_qty(this);">'+value['product_damage_qty']+'</td>' +
                    //  Cost rate
                    '<td id="totalcost_rate_' + inward_product_detail_id + '" name="totalcost_rate_' + inward_product_detail_id + '" class="damagehide_on_without_calculation rightAlign">'+value['product_total_cost_rate']+'</td>' +
                    //  GST %
                    '<td id="cost_gst_percent_' + inward_product_detail_id + '" class="damagehide_on_without_calculation rightAlign">'+totalgstPercent+'</td>' +
                    //  GST Amount
                    '<td id="cost_gst_amount_' + inward_product_detail_id + '" class="damagehide_on_without_calculation rightAlign">' + value['product_total_gst_amount'] + '</td>' +
                    //  total cost
                    '<td id="total_cost_price_' + inward_product_detail_id + '" style="" class="damagehide_on_without_calculation rightAlign" name="total_cost_price_' + inward_product_detail_id + '">'+value['product_total_cost_price']+'</td>' +
                    //  notes
                    '<td id="note_' + inward_product_detail_id + '" style="font-weight:bold;">' +
                    '<textarea name="damage_note_' + inward_product_detail_id + '" id="damage_note_' + inward_product_detail_id + '" style="width:80px;">'+value['product_notes']+'</textarea></td>' +

                    '<td>' +
                    '<input type="file" name="damage_product_image_'+inward_product_detail_id+'" id="damage_product_image_'+inward_product_detail_id+'" data-id="'+inward_product_detail_id+'" d onchange="damge_product_validation(this);">' +
                    '<div id="image_block_'+inward_product_detail_id+'" style="display: '+image_block+'">' +
                    '<img src="'+image_src+'" id="damage_product_preview_'+inward_product_detail_id+'" width="100%" height="200px">' +
                    '<input type="hidden" name="image_json_'+inward_product_detail_id+'" id="image_json_'+inward_product_detail_id+'">' +
                    '<input type="hidden" name="image_name_'+inward_product_detail_id+'" id="image_name_'+inward_product_detail_id+'" value="'+value['image']+'">' +
                    '<a onclick="remove_damge_product('+inward_product_detail_id+');" class="displayright">' +
                    '<i class="fa fa-remove" style="font-size: 20px;"></i>' +
                    '</div>' +
                    '</td>' +
                    //  action
                     '<td data-original-title="To remove this product add 0 as Qty in this row." data-container="body" data-toggle="tooltip" data-placement="bottom" title=""><i class="fa fa-eye"></i></td>'+
                    '<input type="hidden" id="product_id' + inward_product_detail_id + '" value="' + value['product_id'] + '" />' +
                    '<input type="hidden" id="gst_type_id_' + inward_product_detail_id + '" value="' + gsttype + '" />' +
                    '<input type="hidden" id="damage_product_detail_id_' + inward_product_detail_id + '" value="' + value['damage_product_detail_id'] + '" />' +
                    '<input type="hidden" id="total_allow_qty_' + inward_product_detail_id + '" value="'+total_allow_qty+'" />' +
                    '</tr>';




            });
        }

        $('#DamageSearchResult').prepend(product_html);

        //FOR DISPLAY TOTAL NUMBERS OF ITEMS
        var total_record = $("#DamageSearchResult tr").length;
        $(".damage_total_item").html(total_record);

        if(inward_calculation == 3)
        {
            $(".damagehide_on_without_calculation").hide();
        }


        if($("#company_bill_type").val() == 3)
        {
            $('.batch_case_show').show();
        }
        else
        {
            $('.batch_case_show').hide();
        }
    }
});


function damge_product_validation(input)
{
    var imageid = $(input).attr('id');
    var inward_product_detail_id = $(input).data('id');

    var validExtensions = ['png','jpg','jpeg']; //array of valid extensions
    var fileName = input.files[0].name;
    var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
    if ($.inArray(fileNameExt, validExtensions) == -1) {
        input.type = '';
        input.type = 'file';
        $('#damage_product_preview_'+inward_product_detail_id).attr('src',"");
        alert("Only these file types are accepted : "+validExtensions.join(', '));
    }
    else
    {
        if (input.files && input.files[0])
        {
            var filerdr = new FileReader();
            filerdr.onload = function (e)
            {
                $("#image_block_"+inward_product_detail_id).show();
                $("#damage_product_preview_"+inward_product_detail_id).attr('src', e.target.result);


                $("#image_json_"+inward_product_detail_id).val(e.target.result);
                $("#image_name_"+inward_product_detail_id).val(fileName);
            };
            filerdr.readAsDataURL(input.files[0]);
        }
    }
}

function remove_damge_product(inward_product_id)
{
    $('#damage_product_preview_'+inward_product_id).attr('src', '');
    $('#damage_product_image_'+inward_product_id).val('');
    $('#image_name_'+inward_product_id).val('');
    $("#image_block_"+inward_product_id).hide();
}
