<?php
$show_dynamic_feature = '';
$inward_calculation_type = $nav_type[0]['inward_calculation'];
?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Print Thermal Debit Note</title>
    <meta name="description" content="A responsive bootstrap 4 admin dashboard template by hencework" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.png">

    <link rel="icon" type="image/png" href="favicon.png" />

    <!-- vector map CSS -->

    <!-- Custom CSS -->
   <!--  <link href="{{URL::to('/')}}/public/dist/css/style.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/template/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"> -->

    <link href="{{URL::to('/')}}/public/dist/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <script type="text/javascript">window.print();</script>

<style>
ul
{
    list-style:square !important;
     margin : 0 0 0 8px !important;
    padding: 0 0 0 8px !important;
}
ul li
{
    margin : 0 0 0 0 !important;
    padding: 0 0 0 0 !important;
}
.hk-invoice-wrap .invoice-from-wrap > .row div:last-child, .hk-invoice-wrap .invoice-to-wrap > .row div:last-child {
    text-align: left !important;
}
.text-right
{
    text-align:right;
}
.font-weight-600
{
    font-weight:600;
}
.underline font-9{
    border-bottom:1px solid #C0C0C0;
}
.upperline{
    border-top:1px solid #C0C0C0;
}
.bottomdarkline{
    border-bottom:1px solid;
}
.upperdarkline
{
  border-top:1px solid;
}
.font-9{
    font-size: 9px;
}
p{
    margin-top:0px;
    margin-bottom: 2px;
}

</style>
</head>

<!-- <div class="row">
    <div class="col-xl-12" >
        <section class="" style="width:100%;border:0px solid;font-family:Tahoma, Geneva, sans-serif !important;font-size:13px !important"> -->
            <div style="width:100%;border:0px solid;font-family:Tahoma, Geneva, sans-serif !important;font-size:13px !important">
                <span class="font-weight-200" style="font-size:18px;"><center>DEBIT NOTE<br><img src="http://localhost/retailcore_v/public/dist/img/rcslogo1.png" width="50"></center></span>
                <div style="margin:5px 0 0px 0;width:100%;border:0px solid;float:left;">
                    <div style="border:0px solid !important;font-size:12px;float:left !important;">
                       <!--  <img class="img-fluid invoice-brand-img d-block mb-20 pull-left" src="{{URL::to('/')}}/public/dist/img/rcslogo1.png" width="150" alt="logo" style="margin:0 5px 0 0;"/> -->
                        <!-- <center> -->
                        <span class="font-weight-200" style="font-size:18px;"><span class="pl-10 text-dark">{{$debit_note[0]->company->company_name}}</span></span>
                        <!-- </center> --><br>
                        <span class="font-weight-200 font-9"><span class="text-dark">{{strip_tags($debit_note[0]->company->company_address)}}</span></span><br>
                        <span class="font-weight-200 font-9"><span class="text-dark">{{strip_tags ($debit_note[0]->company->company_area)}} {{$debit_note[0]->company->company_city}} - {{$debit_note[0]->company->company_pincode}}</span></span><br>
                        <?php
                        $company_mobile_code =  explode(',',$debit_note[0]->company->company_mobile_dial_code);
                        if($debit_note[0]->company->company_mobile!='' || $debit_note[0]->company->company_mobile!=null)
                        {
                        ?>
                        <span class="d-block font-weight-200 font-9"><span class="pl-10 text-dark">({{$company_mobile_code[0]}}){{$debit_note[0]->company->company_mobile}}</span></span><br>
                        <?php
                        }
                        ?>
                        <span class="d-block font-weight-200 font-9"><span class="pl-10 text-dark">{{$debit_note[0]->company->company_email}}</span></span><br>
                    <!-- </div> -->
                    <?php
                    $whtapp_mobile_code =  explode(',',$debit_note[0]->company->whatsapp_mobile_dial_code);
                    ?>
                    <!-- <div style="text-align:right !important;width:40% !important;border:0px solid !important;font-size:16px; float:right;"> -->
                        <table>
                            <tr>
                                <td colspan="3" class="font-weight-200 font-9">{{strip_tags ($debit_note[0]->company->website)}}</td>
                            </tr>
                            <?php
                            if(isset($debit_note) && isset($debit_note[0]['company'])
                            && $debit_note[0]['company']['whatsapp_mobile_number']!=NULL)
                            {
                            ?>
                            <tr>
                                <td class="d-block font-weight-200 font-9"><a class="fa fa-whatsapp"></a></td>
                                <td class="font-weight-200 font-9 font-9">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200 font-9 font-9">({{$whtapp_mobile_code[0]}}){{$debit_note[0]->company->whatsapp_mobile_number}}</td>
                            </tr>
                            <?php
                            }

                            if(isset($debit_note) && isset($debit_note[0]['company']) && $debit_note[0]['company']['facebook']!=NULL)
                            {
                            ?>
                            <tr>
                                <td class="d-block font-weight-200 font-9"><a class="fa fa-facebook"></a></td>
                                <td class="font-weight-200 font-9">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200 font-9">{{$debit_note[0]->company->facebook}}</td>
                            </tr><?php


                            }

                            if(isset($debit_note) && isset($debit_note[0]['company'])
                            && $debit_note[0]['company']['instagram']!=NULL)
                            {
                            ?>
                            <tr>
                                <td class="d-block font-weight-200 font-9"><a class="fa fa-instagram"></a></td>
                                <td class="font-weight-200 font-9">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200 font-9">{{$debit_note[0]->company->instagram}}</td>
                            </tr>
                            <?php


                            }
                            if(isset($debit_note) && isset($debit_note[0]['company'])
                            && $debit_note[0]['company']['pinterest']!=NULL)
                            {
                            ?>
                            <tr>
                                <td class="d-block font-weight-200 font-9"><a class="fa fa-pinterest"></a></td>
                                <td class="font-weight-200 font-9">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200 font-9">{{$debit_note[0]->company->pinterest}}</td>
                            </tr>
                            <?php


                            }
                            ?>
                        </table>


                    </div>
                </div>
            <!-- </div> -->
            <?php
            $tax_currency = '&#8377;';
            $tax_title = 'GST';
            $tax = 'GSTIN';

            if($nav_type[0]['tax_type'] == 1)
            {
             $tax = $nav_type[0]['tax_title'];
            $tax_title = $nav_type[0]['tax_title'];
            $tax_currency = $nav_type[0]['currency_title'];
            }
            ?>

            <div class="" style="margin:2px 0 0 0px !important;width:100%;border:0px solid;float:left;">

                <!-- <div class="row"> -->

                   <!--  <div class="col-md-7 mb-30" style="width:50% !important;border:0px solid !important;font-size:16px;"> -->
                        <table style="float:left; font-size:9px;">

                            <tr>
                                <?php
                                $suppplier_name = isset($debit_note[0]['supplier_gstdetail']['supplier_company_info']) ? $debit_note[0]['supplier_gstdetail']['supplier_company_info']['supplier_company_name'] : '';

                                ?>
                                <td class="d-block font-weight-200">Supplier Name</td>
                                <td class="font-weight-200">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200"><?php echo $suppplier_name?></td>
                            </tr>

                            <tr>
                                <?php
                                $suppplier_address = isset($debit_note[0]['supplier_gstdetail']['supplier_address']) ? $debit_note[0]['supplier_gstdetail']['supplier_address'] : '';
                                $suppplier_area = isset($debit_note[0]['supplier_gstdetail']['supplier_area']) ? $debit_note[0]['supplier_gstdetail']['supplier_area'] : '';
                                $suppplier_zip = isset($debit_note[0]['supplier_gstdetail']['supplier_gst_zipcode']) ? $debit_note[0]['supplier_gstdetail']['supplier_gst_zipcode'] : '';
                                $suppplier_city = isset($debit_note[0]['supplier_gstdetail']['supplier_gst_city']) ? $debit_note[0]['supplier_gstdetail']['supplier_gst_city'] : '';
                                $address = $suppplier_address.$suppplier_area.$suppplier_zip.$suppplier_city;
                                ?>
                                <td class="d-block font-weight-200">Address</td>
                                <td class="font-weight-200">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200"><?php echo $address?></td>
                            </tr>

                            <tr>
                                <td class="d-block font-weight-200"><?php echo $tax?></td>
                                <td class="font-weight-200">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200">{{$debit_note[0]['supplier_gstdetail']['supplier_gstin']}}</td>
                            </tr>
                        <!-- </table> -->
                    <!-- </div> -->

                    <!-- <div class="col-md-5 mb-30" style="width:50% !important;border:0px solid !important;font-size:16px;"> -->

                       <!--  <table style="float:right;"> -->
                            <tr>
                                <td class="d-block font-weight-200">Debit Note Number</td>
                                <td class="font-weight-200">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200 text-right"><?php echo $debit_note[0]['debit_no'] ?></td>
                            </tr>
                            <tr>
                                <td class="d-block font-weight-200">Debit Note Date</td>
                                <td class="font-weight-200">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200 text-right"><?php echo $debit_note[0]['debit_date'] ?></td>
                            </tr>
                            <tr>
                                <td class="d-block font-weight-200"><?php echo $tax?></td>
                                <td class="font-weight-200">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200 text-right"><?php echo $debit_note[0]['company']['gstin']?></td>
                            </tr>

                            <tr>
                                <td class="d-block font-weight-200">Place</td>
                                <td class="font-weight-200">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200 text-right"><?php echo $debit_note[0]['company']['state_name']['state_name']?></td>
                            </tr>

                            <tr>
                                <td class="d-block font-weight-200">Invoice Number</td>
                                <td class="font-weight-200">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200 text-right"><?php echo $debit_note[0]['inward_stock']['invoice_no']?></td>
                            </tr>

                            <tr>
                                <td class="d-block font-weight-200">Invoice Date</td>
                                <td class="font-weight-200">&nbsp;:&nbsp;</td>
                                <td class="font-weight-200 text-right"><?php echo $debit_note[0]['inward_stock']['invoice_date']?></td>
                            </tr>

                        </table>

                   <!--  </div> -->
                <!-- </div> -->
            </div>


            <table width="100%" cellpadding="6" cellspacing="0" border="0" frame="box" style="margin:10px 0 10px 0 !important;float:left;border:1px solid !important;border-collapse: collapse;">
                <thead>
                <tr class="background">
                    <th class="text-left text-dark font-9 font-weight-600 bottomdarkline" style="width:5% !important;text-align: left;">Sr.<br>No.</th>
                    <th class="text-dark font-9 font-weight-200 bottomdarkline" style="width:10%; text-align: left;">Barcode</th>
                    <th class="text-dark font-9 font-weight-200 bottomdarkline" style="width:10%; text-align: left;">Product Name</th>
                    

                    <?php
                    if (isset($product_features) && $product_features != '' && !empty($product_features))
                    {

                    foreach ($product_features AS $feature_key => $feature_value)
                    {

                    if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
                    {

                    $search =$urlData['breadcrumb'][0]['nav_url'];

                    if (strstr($feature_value['show_feature_url'],$search) )
                    {
                    if($show_dynamic_feature == '')
                    {
                        $show_dynamic_feature =$feature_value['html_id'];
                    }
                    else
                    {
                        $show_dynamic_feature = $show_dynamic_feature.','.$feature_value['html_id'];
                    }
                    ?>

                    <th class="text-left text-dark font-9 font-weight-200 bottomdarkline" style="width:10%;"><?php echo $feature_value['product_features_name']?></th>
                    <?php } ?>
                    <?php
                    }
                    }
                    }
                    ?>

                    <th class="text-right text-dark font-9 font-weight-200 hide_on_without_calculation bottomdarkline" style="width:10%;">Cost Rate</th>
                    <th class="text-right text-dark font-9 font-weight-200 bottomdarkline" style="width:5%;">Qty</th>
                    <th class="text-right text-dark font-9 font-weight-200 hide_on_without_calculation bottomdarkline" style="width:10%;">Taxable Amount</th>
                    
                    <th class="text-right text-dark font-9 font-weight-200 hide_on_without_calculation bottomdarkline" style="width:14%;">Total Amount</th>
                </tr>
                </thead>
                <tbody>

                <?php
                $total_cost = 0;
                $total_cost_gst_amount = 0;
                $total_igst_amount = 0;
                $total_cgst_amount = 0;
                $total_sgst_amount = 0;
                ?>
                @foreach($debit_note[0]['debit_product_details'] AS $key=>$value)
                    <?php
                    $barcode = '';
                    if ($value != '' && $value['product']['supplier_barcode'] != " " && $value['product']['supplier_barcode'] != null)
                    {
                        $barcode = $value['product']['supplier_barcode'];
                    } else {

                        $barcode = $value['product']['product_system_barcode'];
                    }

                    $total_cost += $value['cost_rate'];
                    $total_cost_gst_amount += $value['cost_gst_amount'];


                    $total_igst_amount += $value['total_igst_amount_with_qty'];
                    $total_cgst_amount += $value['total_cgst_amount_with_qty'];
                    $total_sgst_amount += $value['total_sgst_amount_with_qty'];

                    $key++;

                    $feature_show_val = "";
                    $footer_key = 0;
                    if($show_dynamic_feature != '')
                    {
                        $feature = explode(',',$show_dynamic_feature);

                        foreach($feature AS $fea_key=>$fea_val)
                        {
                            $feature_show_val .= '<td>'.$value['product'][$fea_val].'</td>';
                            $footer_key++;
                        }
                    }

                    ?>

                    <tr style="border-bottom:1px solid #C0C0C0 !important;">
                        <td class="font-weight-600 text-dark underline font-9"><?php echo $key?></td>
                        <td class="font-weight-200 text-dark underline font-9"><?php echo $barcode ?></td>
                        <td class="font-weight-200 text-dark underline font-9">{{$value->product->product_name}}</td>
                        
                        <?php
                        echo $feature_show_val;
                        ?>
                        <td class="text-right font-weight-200 text-dark hide_on_without_calculation underline font-9">{{$value->cost_rate}}</td>
                        <td class="text-right font-weight-200 text-dark underline font-9">{{$value->return_qty}}</td>
                        <td class="text-right font-weight-200 text-dark hide_on_without_calculation underline font-9">{{$value->total_cost_rate}}</td>
                        
                        <td class="text-right font-weight-200 text-dark hide_on_without_calculation underline font-9">{{$value->total_cost_price}}</td>
                    </tr>
                @endforeach
               <!--  <tr> -->
                    <!-- <td class="text-dark" style="height:50px !important;">&nbsp;</td> -->
                    <!-- <td class="text-dark" style=""></td> -->
                   <!--  <td class="text-dark"></td> -->
                    <!-- <td class="text-dark"></td> -->
                    <?php
                    if($footer_key > 0)
                    {
                    for ($i=1;$i<=$footer_key;$i++)
                    { ?>
                    <!-- <td class="text-dark"></td> -->
                <?php } } ?>
                    <!-- <td class="text-dark hide_on_without_calculation"></td>
                    <td class="text-right text-dark hide_on_without_calculation"></td>
                    <td class="text-right text-dark"></td>
                    
                    <td class="text-right text-dark hide_on_without_calculation"></td>
                </tr>
                </tbody> -->

                <tfoot style="border-bottom:1px solid #999 !important;border-top:1px solid #999 !important;">
                <tr>
                    <th colspan="<?php echo $footer_key+ 3 ?>" class="font-9 font-weight-200 upperdarkline"></th>

                    <th  class="font-9 font-weight-200 text-right upperdarkline">Total</th>
                    <!-- <th class="text-right font-weight-200 hide_on_without_calculation upperdarkline"></th> -->
                    <th class="font-9 text-right font-weight-200 upperdarkline">{{$debit_note[0]['total_qty']}}</th>
                    <th class="font-9 text-right font-weight-200 hide_on_without_calculation upperdarkline">{{$debit_note[0]['total_cost_rate']}}</th>
                    
                    <th class="font-9 text-right text-dark font-18 font-weight-200 hide_on_without_calculation upperdarkline"><?php echo $tax_currency?> {{$debit_note[0]['total_cost_price']}}</th>
                </tr>
                </tfoot>
            </table>

            <!-- <div class="invoice-from-wrap" style="margin:45px 0 0 0;"> -->
                <!-- <div class="row">
                    <div class="col-md-8 mb-20" style="width:60% !important;border:0px solid !important;"></div>
                    <div class="col-md-4 mb-20" style="width:40% !important;border:0px solid !important;font-size:16px;"></div>
                </div> -->
               <!--  <div class="row">

                </div> -->
                <br>
                <br>
                <center> <span class="d-block font-weight-200" style="font-size:9px;margin:5px 0 0 0;float:left">{{strip_tags($debit_note[0]->company->additional_message)}}</span> </center><br>
          <!--   </div> -->


            <!-- <div class="invoice-from-wrap hide_on_without_calculation" style="margin:25px 0 0 0;"> -->
                <!-- <div class="row"> -->
                    <!-- <div class="col-md-8 mb-20" style="width:60% !important;border:0px solid !important;"> -->
                        <span class="font-weight-600" style="font-size:10px;margin:5px 0 0 0;float:left;"><--------GST Breakup Details--------></span>
                        <table width="100%" cellpadding="6" cellspacing="0" frame="box" style="margin:20px 0 30px 0 !important;float:left;border:1px solid !important;font-size:9px">
                            <thead>
                            <tr style="border-bottom:1px #999 solid;border-top:1px #999 solid;">
                                <th class="text-dark font-9 font-weight-200 bottomdarkline" style="width:8% !important;text-align:left;"><?php echo  $tax_title ?> %</th>
                                <th class="text-right text-dark font-9 font-weight-200 bottomdarkline" style="width:16% !important;">Taxable Amt.</th>
                                <?php if($nav_type[0]['tax_type'] == 1) { ?>
                                <th class="text-right text-dark font-9 font-weight-200 bottomdarkline interstate" style="width:16% !important;"><?php echo $tax_title ?><br><small>Rate & Amt.</small></th>
                                <?php } else {if($debit_gst_breakup[0]['cost_igst_percent'] == 0 || $debit_gst_breakup[0]['cost_igst_percent'] == '') { ?>
                                <th class="text-right text-dark font-9 font-weight-200 bottomdarkline intrastate" style="width:16% !important;">CGST<br><small>Rate & Amt.</small></th>
                                <th class="text-right text-dark font-9 font-weight-200 bottomdarkline intrastate" style="width:16% !important;">SGST<br><small>Rate & Amt.</small></th>
                                <?php } else { ?>
                                <th class="text-right text-dark font-9 font-weight-200 bottomdarkline interstate" style="width:16% !important;">IGST<br><small>Rate & Amt.</small></th>
                                <?php } }?>
                                <th class="text-right text-dark font-9 font-weight-200 bottomdarkline" style="width:26% !important;">Total Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $final_total_taxable_value = 0;
                            $final_total_cgst_value = 0;
                            $final_total_sgst_value = 0;
                            $final_total_igst_value = 0;
                            $final_total_grand = 0;
                            foreach($debit_gst_breakup AS $gst_key=>$gst_value)
                            {
                            $final_total_taxable_value += $gst_value['total_taxable_value'];
                            $final_total_cgst_value += $gst_value['total_cgst_amount_with_qty'];
                            $final_total_sgst_value += $gst_value['total_sgst_amount_with_qty'];
                            $final_total_igst_value += $gst_value['total_igst_amount_with_qty'];
                            $final_total_grand += $gst_value['total_grand'];
                            ?>
                            <tr style="border-bottom:1px solid #C0C0C0 !important;">
                                <td class="text-dark font-weight-200">{{round($gst_value->cost_gst_percent,2)}}%</td>
                                <td class="text-right text-dark font-weight-200">{{round($gst_value->total_taxable_value,2)}}</td>
                                <?php if($debit_gst_breakup[0]['cost_igst_percent'] == 0 || $debit_gst_breakup[0]['cost_igst_percent'] == '') { ?>
                                <td class="text-right text-dark font-weight-200 intrastate">{{round($gst_value->cost_cgst_percent,2)}}%<br>{{round($gst_value->total_cgst_amount_with_qty,2)}}</td>
                                <td class="text-right text-dark font-weight-200 intrastate">{{round($gst_value->cost_sgst_percent  ,2)}}%<br>{{round($gst_value->total_sgst_amount_with_qty,2)}}</td>
                                <?php } else { ?>
                                <td class="text-right text-dark font-weight-200 interstate">{{round($gst_value->cost_igst_percent,2)}}%<br>{{round($gst_value->total_igst_amount_with_qty,2)}}</td>
                                <?php } ?>
                                <td class="text-right text-dark font-weight-200 tottotamt">{{round($gst_value->total_grand,2)}}</td>
                            </tr>
                            </tbody>
                            <?php } ?>
                            <tfoot style="border-bottom:1px solid #999 !important;border-top:1px solid #999 !important;">
                            <tr>
                                <th class="text-right font-9 font-weight-200 upperdarkline">Total</th>
                                <th class="text-right text-dark font-9 font-weight-200 upperdarkline"><?php echo $final_total_taxable_value?></th>
                                <?php if($debit_gst_breakup[0]['cost_igst_percent'] == 0 || $debit_gst_breakup[0]['cost_igst_percent'] == '') { ?>
                                <th class="text-right text-dark font-9 font-weight-200 upperdarkline intrastate"><?php echo $final_total_cgst_value?></th>
                                <th class="text-right text-dark font-9 font-weight-200 upperdarkline intrastate"><?php echo $final_total_sgst_value?></th>
                                <?php } else { ?>
                                <th class="text-right text-dark font-9 font-weight-200 upperdarkline interstate"><?php echo $final_total_igst_value?></th>
                                <?php } ?>
                                <th class="text-right text-dark font-18 font-weight-200 upperdarkline"><?php echo $tax_currency?> <?php echo $final_total_grand?></th>
                            </tr>
                            </tfoot>
                        </table>
                    <!-- </div> -->
                    <div style="float:left;">
                        <?php
                        if(isset($debit_note) && isset($debit_note[0]['company'])&& $debit_note[0]['company']['terms_and_condition']!=NULL)
                        {
                        ?>
                        <table class="font-9" style="float:left;">
                            <tr>
                                <td colspan="3" class="font-weight-600">TERMS & CONDITIONS</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="font-weight-600"><?php echo html_entity_decode($debit_note[0]->company->terms_and_condition); ?></td>
                            </tr>

                        </table>
                        <?php
                        }
                        ?>
                   <!--  </div> -->

                   <!--  <div style="border:0px solid !important;float:right"> -->
                        <?php
                        if(isset($debit_note[0]->company->authorized_signatory_for) && $debit_note[0]->company->authorized_signatory_for!='')
                        {
                        ?>
                        <span class="d-block font-weight-600 font-9">For {{$debit_note[0]->company->authorized_signatory_for}}</span><br>
                        <span class="d-block font-weight-200 font-9">Authorized Signatory</span>
                        <?php
                        }
                        ?>
                    </div>

                <!-- </div> -->
            </div>


        <!-- </section>
    </div>
</div> -->
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>

<script>

    <?php
    if($inward_calculation_type == 3)
    { ?>
        $(".hide_on_without_calculation").hide();
    <?php } ?>
    </script>
