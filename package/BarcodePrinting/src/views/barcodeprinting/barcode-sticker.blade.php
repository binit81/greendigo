<style>
#OuterDivA4{
    width:13cm;
    border:0px solid;
}
body{
    font-family: arial;
}
    table tr td{
        font-size:14px !important;
    }
</style>


<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(e) {
    var data1    =   localStorage.getItem('barcode-printing-record');

    var product_data = JSON.parse(data1,true);
    var radioValue = product_data[2]['radioValue'];
    var show_dynamic_feature = product_data[2]['show_dynamic_feature'];
    var blank_label = product_data[2]['blank_label'];
    var qty_search = product_data[2]['qty_search'];
    var manually_mfg_date = product_data[2]['manually_mfg_date'];
    var manually_exp_date = product_data[2]['manually_exp_date'];
    var BarcodeTemplate = product_data[1]['BarcodeTemplate'];

    var product_features_encoded = '<?php echo $product_features?>';
    var product_features = JSON.parse(product_features_encoded);
    var dynamic_find = [];
    $.each(product_features,function (kk,vv)
    {
       if(show_dynamic_feature.indexOf(vv['html_id']) != -1){

          /* if(dynamic_find == '')
           {
               dynamic_find = '['+vv['product_features_name'].toUpperCase()+']';
           }
           else{
               dynamic_find = dynamic_find +','+ '['+vv['product_features_name'].toUpperCase()+']';
           }*/
           dynamic_find.push([vv['html_id'],vv['product_features_name']]);
       }
    });



    var url1 = "fetchTemplateData";
    var type1 = "POST";
    var dataType = "";
    var data1 = {
        'BarcodeTemplateId': product_data[1]['BarcodeTemplate']
    };
    callroute(url1, type1,dataType, data1, function (datax)
    {
        var searchdata = JSON.parse(datax, true);

        templateDataHTMLCODE1   =   '';
        if (searchdata['Success'] == "True")
        {
            var templateData                    =   searchdata['Data1'];
            var barcode_type                    =   templateData[0]['barcode_type'];
            var templateDataHTMLCODE            =   templateData[0]['template_data'];
            var template_label_width            =   templateData[0]['template_label_width'];
            var template_label_height           =   templateData[0]['template_label_height'];
            var template_label_font_size        =   templateData[0]['template_label_font_size'];
            var template_label_margin_top       =   templateData[0]['template_label_margin_top'];
            var template_label_margin_right     =   templateData[0]['template_label_margin_right'];
            var template_label_margin_bottom    =   templateData[0]['template_label_margin_bottom'];
            var template_label_margin_left      =   templateData[0]['template_label_margin_left'];
            var template_label_size_type        =   templateData[0]['template_label_size_type'];
            var layout_width                    =   templateData[0]['barcode_sheet']['layout_width'];

            var templateDataHTMLCODE22 = '';


            if(blank_label != '')
            {
                for(var i=1;i<=blank_label;i++)
                {
                    $('.printBarcodeDiv').append('<div  data-sort="A_BLANK" style="width:'+template_label_width+''+template_label_size_type+'; height:'+template_label_height+''+template_label_size_type+'; margin-top:'+template_label_margin_top+'px; margin-right:'+template_label_margin_right+'px; margin-bottom:'+template_label_margin_bottom+'px; margin-left:'+template_label_margin_left+'px; display:inline-block !important;"></div>');
                }

            }




            $.each(product_data[0],function (key,value){

                var printQty    =   value['printqty'];
                var url = "barcode_product_detail";
                var type = "POST";
                var dataType = "";
                var data = {
                    "product_id": value['product_id'],
                    "inward_id": value['inward_id'],
                    "filter_type" :product_data[2]['search_fileter_with'],
                    "qty_search" :product_data[2]['qty_search']
                }
                callroute(url,type,dataType,data,function (data1)
                {
                    var pushdata = JSON.parse(data1, true);
                    var CompanyName =   pushdata['CompanyName'];
                    var secret_code =   pushdata['secret_code'];

                    if (pushdata['Success'] == "True")
                    {
                        var templateData1  =   pushdata['Data'];
                        var product_barcode     =   '';

                        $.each(templateData1,function (key,value1)
                        {
                            var product_arr = value1["product"];
                            if(qty_search == 0)
                            {
                                product_arr = value1;

                            }
                            var supplier_barcode    = '';

                            if(product_arr['supplier_barcode'] != '' && product_arr['supplier_barcode'] != null)
                            {
                                supplier_barcode =  product_arr['supplier_barcode'];
                            }

                            var product_name        =   product_arr['product_name'];

                            var product_code   = '';
                            if(product_arr['product_code'] != '')
                            {
                                product_code = product_arr['product_code'];
                            }
                            var product_desc        =   product_arr['product_description'];
                            var pack_qty = 0;
                            if(product_data[2]['search_fileter_with'] == 1)
                            {
                                var product_mrp         =   value1['product_mrp'];
                                var offer_price         =   value1['offer_price'];
                                var product_barcode     =   product_arr['product_system_barcode'];
                                pack_qty = value1['product_qty'];
                                if(qty_search == 0)
                                {
                                    pack_qty = 0;
                                }

                                var cost_price = Number(""+value1['cost_price']+"").toFixed(0);

                            }
                            if(product_data[2]['search_fileter_with'] == 2)
                            {
                                var product_mrp         =   product_arr['product_mrp'];
                                var offer_price         =   product_arr['offer_price'];
                                var unique_barcode = '';
                                if(value1['unique_barcode'] != '' && value1['unique_barcode'] != null)
                                {
                                     unique_barcode     =   value1['unique_barcode'];
                                }
                                var product_barcode     =   unique_barcode;
                                pack_qty = value1['qty'];

                                if(qty_search == 0)
                                {
                                    pack_qty = 0;
                                }

                                var cost_price = Number(""+value1['total_cost_with_gst']+"").toFixed(0);
                            }

                           if(typeof secret_code == 'object' && secret_code != '')
                           {
                             $.each(secret_code,function(secret_key,secret_value)
                            {
                                if(cost_price.indexOf(secret_value['digit']) != -1)
                                {
                                    var regex = new RegExp(secret_value['digit'],"g");

                                    if(secret_value['digit'] == '.')
                                    {
                                        cost_price =  cost_price.replace('.', "" + secret_value['secret_code'] + "");
                                    }
                                    else {
                                        cost_price = cost_price.replace(regex, "" + secret_value['secret_code'] + "");
                                    }
                                }
                            });
                           }

                            var expiry_date  = value1['expiry_date'];
                            var mfg_date =  value1['mfg_date'];
                            var created_at =  value1['created_date'];

                            if(expiry_date==null)
                            {
                                expiry_date     =   '';
                            }
                            if(mfg_date==null)
                            {
                                mfg_date     =   '';
                            }

                            /*if(value1['product']['brand_id']=='' || value1['product']['brand_id']==null)
                            {
                                var brand_type            =   '';
                            }
                            else
                            {
                                var brand_type            =   value1['product']['brand']['brand_type'];
                            }*/

                            if(product_arr['sku_code'] =='' || product_arr['sku_code']==null)
                            {
                                var sku_code            =   '';
                            }
                            else
                            {
                                var sku_code            =   product_arr['sku_code'];
                            }

                            var z;

                            var url2 = "GenerateBarcode";
                            var type2 = "POST";
                            var dataType = "";
                            var data2 = {
                                "product_barcode": product_barcode,
                                "barcode_type": barcode_type
                            }
                            callroute(url2, type2,dataType, data2, function (datan)
                            {
                                var fetchGeneratedBarcode   = JSON.parse(datan, true);
                                var fetchGeneratedBarcode   =   fetchGeneratedBarcode['Data'];

                                var find = [
                                  '[BARCODE]',
                                  '[SUPP_BARCODE]',
                                  '[COST_PRICE]',
                                  '[MRP]',
                                  '[SKUCODE]',
                                  '[PRODUCT_NAME]',
                                  '[PRODUCT_DESC]',
                                  '[CODE]',
                                  '[OFFER_PRICE]',
                                  '[COMPANY]',
                                  '[MFG_DATE]',
                                  '[EXPIRY_DATE]',
                                  '[INV_DATE]',
                                  '[PACK_QTY]',
                                  '[PACKED_DATE]',
                                  '[BEST BEFORE]',
                                  '<p>',
                                  '</p>'
                                 ]

                                var rep                 =   [
                                    ''+fetchGeneratedBarcode+'<br /><font style="font-size:17px;">'+product_barcode+'</font>',
                                    ''+fetchGeneratedBarcode+'<br /><font style="font-size:17px;">'+supplier_barcode+'</font>',
                                    cost_price,
                                    ''+product_mrp+'',
                                    sku_code,
                                    product_name,
                                    product_desc,
                                    product_code,
                                    ''+offer_price+'',
                                    CompanyName,
                                    mfg_date,
                                    expiry_date,
                                    created_at,
                                    pack_qty,
                                    manually_mfg_date,
                                    manually_exp_date,
                                    '',
                                    ''
                                ];


                                $.each(dynamic_find,function (d_k,d_v)
                                {
                                    find.push('['+d_v[1].toUpperCase()+']');
                                    if(typeof  product_arr[d_v[0]] == 'string')
                                    {
                                        rep.push(product_arr[d_v[0]]);
                                    }
                                    else
                                    {
                                        rep.push('');
                                    }
                                });

                                templateDataHTMLCODE1    = templateDataHTMLCODE.replace(find[0],rep[0]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[1],rep[1]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[2],rep[2]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[3],rep[3]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[4],rep[4]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[5],rep[5]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[6],rep[6]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[7],rep[7]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[8],rep[8]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[9],rep[9]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[10],rep[10]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[11],rep[11]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[12],rep[12]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[13],rep[13]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[14],rep[14]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[15],rep[15]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[16],rep[16]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[17],rep[17]);
                                templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[18],rep[18]);



                                var find_index = 18;
                                $.each(dynamic_find,function (d_k,d_v)
                                {
                                    find_index++;
                                    templateDataHTMLCODE1    = templateDataHTMLCODE1.replace(find[find_index],rep[find_index]);

                                });


                                $('table tr td').css('font-size',' '+template_label_font_size+'pt ');

                                $('#OuterDivA4').css('width',layout_width);



                                for(z=1;z<=printQty;z++)
                                {
                                    $('.printBarcodeDiv')
                                        .append('<div  data-sort='+value['product_id']+' style="width:'+template_label_width+''+template_label_size_type+'; height:'+template_label_height+''+template_label_size_type+'; margin-top:'+template_label_margin_top+'px; margin-right:'+template_label_margin_right+'px; margin-bottom:'+template_label_margin_bottom+'px; margin-left:'+template_label_margin_left+'px; display:inline-block !important;">'+templateDataHTMLCODE1+'</div>')
                                        .children()
                                        .sort(function(a, b) {
                                            return $(a).data('sort') - $(b).data('sort');
                                        }).appendTo('.printBarcodeDiv');
                                }
                            });

                        });
                    }
                });
            });
        }
    });




});
</script>

<meta name="csrf-token" content="{{ csrf_token() }}" />

<div id="OuterDivA4" style="border:0px solid;">

    <div class="printBarcodeDiv"></div>

</div>

<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
<script src="{{URL::to('/')}}/public/modulejs/common.js"></script>
