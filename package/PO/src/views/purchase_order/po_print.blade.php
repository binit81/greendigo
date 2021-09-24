<?php

$show_dynamic_feature = '';
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Print Purchase Order</title>
    <meta name="description" content="A responsive bootstrap 4 admin dashboard template by hencework" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.png">

    <link rel="icon" type="image/png" href="favicon.png" />

    <!-- vector map CSS -->

    <!-- Custom CSS -->
    <link href="{{URL::to('/')}}/public/dist/css/style.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/template/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">

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

    </style>

</head>


<div class="row">
    <div class="col-xl-12" >
        <section class="hk-sec-wrapper hk-invoice-wrap" style="margin:-15px 0 0 0 !important;font-family:Tahoma, Geneva, sans-serif !important;font-size:13px !important;border:0px !important;">
            <div class="invoice-from-wrap">
                <span class="mb-35 font-weight-600" style="font-size:34px;"><center>PURCHASE ORDER</center></span>

                <div class="row" style="margin:20px 0 0 0;">
                    <div class="col-md-8 mb-20" style="width:60% !important;border:0px solid !important;font-size:16px;">
                        <img class="img-fluid invoice-brand-img d-block mb-20 pull-left" src="{{URL::to('/')}}/public/dist/img/rcslogo1.png" width="150" alt="logo" style="margin:0 5px 0 0;"/>
                        <span class="d-block font-weight-600" style="font-size:24px;"><span class="pl-10 text-dark">{{$purchase_order[0]->company->company_name}}</span></span>
                        <span class="d-block font-weight-600"><span class="pl-10 text-dark">{{strip_tags($purchase_order[0]->company->company_address)}}</span></span>
                        <span class="d-block font-weight-600"><span class="pl-10 text-dark">{{strip_tags ($purchase_order[0]->company->company_area)}} {{$purchase_order[0]->company->company_city}} - {{$purchase_order[0]->company->company_pincode}}</span></span>
                        <?php
                        $company_mobile_code =  explode(',',$purchase_order[0]->company->company_mobile_dial_code);
                        if($purchase_order[0]->company->company_mobile!='' || $purchase_order[0]->company->company_mobile!=null)
                        {
                        ?>
                        <span class="d-block font-weight-600"><span class="pl-10 text-dark">({{$company_mobile_code[0]}}){{$purchase_order[0]->company->company_mobile}}</span></span>
                        <?php
                        }
                        ?>
                        <span class="d-block font-weight-600"><span class="pl-10 text-dark">{{$purchase_order[0]->company->company_email}}</span></span>
                    </div>
                    <?php
                    $whtapp_mobile_code =  explode(',',$purchase_order[0]->company->whatsapp_mobile_dial_code);
                    ?>
                    <div class="col-md-4 mb-20" style="text-align:right !important;width:40% !important;border:0px solid !important;font-size:16px;">
                        <table style="float:right;">
                            <tr>
                                <td colspan="3" class="font-weight-600">{{strip_tags ($purchase_order[0]->company->website)}}</td>
                            </tr>
                            <?php
                            if(isset($purchase_order) && isset($purchase_order[0]['company'])
                            && $purchase_order[0]['company']['whatsapp_mobile_number']!=NULL)
                            {
                            ?>
                            <tr>
                                <td class="d-block font-weight-600"><a class="fa fa-whatsapp"></a></td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600">({{$whtapp_mobile_code[0]}}){{$purchase_order[0]->company->whatsapp_mobile_number}}</td>
                            </tr>
                            <?php

                            }

                            if(isset($purchase_order) && isset($purchase_order[0]['company'])
                            && $purchase_order[0]['company']['facebook']!=NULL)
                            {
                            ?>
                            <tr>
                                <td class="d-block font-weight-600"><a class="fa fa-facebook"></a></td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600">{{$purchase_order[0]->company->facebook}}</td>
                            </tr><?php


                            }

                            if(isset($purchase_order) && isset($purchase_order[0]['company'])
                            && $purchase_order[0]['company']['instagram']!=NULL)
                            {
                            ?>
                            <tr>
                                <td class="d-block font-weight-600"><a class="fa fa-instagram"></a></td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600">{{$purchase_order[0]->company->instagram}}</td>
                            </tr>
                            <?php


                            }
                            if(isset($purchase_order) && isset($purchase_order[0]['company'])
                            && $purchase_order[0]['company']['pinterest']!=NULL)
                            {
                            ?>
                            <tr>
                                <td class="d-block font-weight-600"><a class="fa fa-pinterest"></a></td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600">{{$purchase_order[0]->company->pinterest}}</td>
                            </tr>
                            <?php


                            }
                            ?>
                        </table>


                    </div>
                </div>
            </div>



            <div class="invoice-to-wrap pb-20">

                <div class="row">
                    <?php
                    $tax_name = "GSTIN";
                    $tax_nm = "GST";
                    $tax_currency = "&#x20b9";
                    if($nav_type[0]['tax_type'] == 1)
                    {
                        $tax_name = $nav_type[0]['tax_title'];
                        $tax_nm = $nav_type[0]['tax_title'];
                        $tax_currency = $nav_type[0]['currency_title'];
                    }
                    ?>
                    <div class="col-md-4 mb-30" style="width:50% !important;border:0px solid !important;font-size:16px;">
                        <table style="float:left;">
                            <tr>
                                <?php
                                $supplier_first_name = '';
                                $supplier_last_name = '';
                                if(isset($purchase_order[0]['supplier_gstdetail']) && $purchase_order[0]['supplier_gstdetail'] != '')
                                    {
                                        $supplier_gst =$purchase_order[0]['supplier_gstdetail'];
                                        if(isset($supplier_gst['supplier_company_info']) && $supplier_gst['supplier_company_info'] != '')
                                            {
                                                $supplier_first_name = isset($supplier_gst['supplier_company_info']['supplier_first_name'])  && $supplier_gst['supplier_company_info']['supplier_first_name'] != '' ? $supplier_gst['supplier_company_info']['supplier_first_name'] : '';
                                                $supplier_last_name = isset($supplier_gst['supplier_company_info']['supplier_first_name'])  && $supplier_gst['supplier_company_info']['supplier_first_name'] != '' ? $supplier_gst['supplier_company_info']['supplier_last_name'] : '';
                                            }
                                    }

                                ?>
                                <td class="d-block font-weight-600">Supplier</td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600"><?php echo $supplier_first_name .' '.$supplier_last_name ?></td>
                            </tr>
                            <tr>
                                <?php
                                    $suppplier_address = isset($purchase_order[0]['supplier_gstdetail']['supplier_address']) ? $purchase_order[0]['supplier_gstdetail']['supplier_address'] : '';
                                    $suppplier_area = isset($purchase_order[0]['supplier_gstdetail']['supplier_area']) ? $purchase_order[0]['supplier_gstdetail']['supplier_area'] : '';
                                    $suppplier_zip = isset($purchase_order[0]['supplier_gstdetail']['supplier_gst_zipcode']) ? $purchase_order[0]['supplier_gstdetail']['supplier_gst_zipcode'] : '';
                                    $suppplier_city = isset($purchase_order[0]['supplier_gstdetail']['supplier_gst_city']) ? $purchase_order[0]['supplier_gstdetail']['supplier_gst_city'] : '';
                                    $address = $suppplier_address.' '.$suppplier_area.' '.$suppplier_zip.' '.$suppplier_city;
                                    if($suppplier_address != '') {
                                ?>
                                <td class="d-block font-weight-600">Address</td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600"><?php echo $address?></td>
                                <?php } ?>
                            </tr>

                            <tr>
                                <td class="d-block font-weight-600"><?php echo $tax_name?></td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600">{{$purchase_order[0]['supplier_gstdetail']['supplier_gstin']}}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-4 mb-30" style="width:50% !important;border:0px solid !important;font-size:16px;">
                        <table>
                            <tr>

                                <td class="d-block font-weight-600">Delivery To</td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600">{{$purchase_order[0]['delivery_to']}}</td>
                            </tr>
                            <tr>
                                <td class="d-block font-weight-600">Address</td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600">{{$purchase_order[0]['address']}}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-4 mb-30" style="width:50% !important;border:0px solid !important;font-size:16px;">

                        <table style="float:right;">
                            <tr>
                                <td class="d-block font-weight-600">PO No.</td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600 text-right"><?php echo $purchase_order[0]['po_no'] ?></td>
                            </tr>
                            <tr>
                                <td class="d-block font-weight-600">PO Date</td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600 text-right"><?php echo $purchase_order[0]['po_date'] ?></td>
                            </tr>
                            <tr>
                                <td class="d-block font-weight-600"><?php echo $tax_name?></td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600 text-right"><?php echo $purchase_order[0]['company']['gstin'] ?></td>
                            </tr>

                            <tr>
                                <td class="d-block font-weight-600">Place</td>
                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                <td class="font-weight-600 text-right"><?php echo $purchase_order[0]['company']['state_name']['state_name'] ?></td>
                            </tr>

                        </table>

                    </div>
                </div>
            </div>


            <table width="100%" cellpadding="6" border="0" frame="box">
                <thead>
                <tr style="background:#999;border-bottom:1px #999 solid;border-top:1px #999 solid;">
                    <th class="text-dark font-12 font-weight-600" style="width:5% !important;">Sr.<br>No.</th>
                    <th class="text-dark font-12 font-weight-600" style="width:10% !important;">Barcode</th>
                    <th class="text-dark font-12 font-weight-600" style="width:10% !important;">Product Name</th>
                    <?php if($nav_type[0]['po_with_unique_barcode'] == 1) {?>
                    <th class="text-dark font-12 font-weight-600" style="width:10% !important;"><?php echo $nav_type[0]['unique_barcode_name']?></th>
                    <?php } ?>

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

                    <th class="text-dark font-12 font-weight-600" style="width:10% !important;"><?php echo $feature_value['product_features_name']?></th>
                    <?php } ?>
                    <?php
                    }
                    }
                    }
                    ?>


                    <th class="text-dark font-12 font-weight-600" style="width:10% !important;">UQC</th>
                    <?php if($nav_type[0]['po_calculation'] != 2){ ?>
                    <th class="text-right text-dark font-12 font-weight-600" style="width:10% !important;">Cost Rate</th>
                    <th class="text-right text-dark font-12 font-weight-600" style="width:5% !important;"><?php echo $tax_nm?> % </th>
                    <th class="text-right text-dark font-12 font-weight-600" style="width:5% !important;"><?php echo $tax_nm?> Amt</th>
                    <?php } ?>
                    <th class="text-right text-dark font-12 font-weight-600" style="width:5% !important;">Qty</th>
                    <th class="text-right text-dark font-12 font-weight-600 show_detail_print" style="width:5% !important;">Received Qty</th>
                    <th class="text-right text-dark font-12 font-weight-600 show_detail_print" style="width:5% !important;">Pending Qty</th>
                    <?php if($nav_type[0]['po_calculation'] != 2){ ?>
                    <th class="text-right text-dark font-12 font-weight-600" style="width:10% !important;">Total Cost Without <?php echo $tax_nm?></th>
                    <th class="text-right text-dark font-12 font-weight-600" style="width:10% !important;">Total <?php echo $tax_nm?></th>
                    <th class="text-right text-dark font-12 font-weight-600" style="width:15% !important;">Total Amount</th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>

                <?php
                $total_cost = 0;
                $total_cost_gst_amount = 0;
                $total_received_qty = 0;
                $total_pending_qty = 0;
                ?>
                @foreach($purchase_order[0]['purchase_order_detail'] AS $key=>$value)

                    <?php
                   $barcode = '';
                    if ($value != '' && $value['product']['supplier_barcode'] != " " && $value['product']['supplier_barcode'] != null)
                    {
                        $barcode = $value['product']['supplier_barcode'];
                    } else {

                    $barcode = $value['product']['product_system_barcode'];
                    }
                    $uqc_name = '';
                    if($value['product']['uqc_id'] != '' && $value['product']['uqc_id'] != null && $value['product']['uqc_id'] != 0)
                    {
                        $uqc_name = $value['product']['uqc']['uqc_shortname'];
                    }

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


                    $total_cost += $value['cost_rate'];
                    $total_cost_gst_amount += $value['cost_gst_amount'];
                    $total_received_qty += $value['received_qty'];
                    $total_pending_qty += $value['pending_qty'];
                    $key++;
                    ?>


                    <tr style="border-bottom:1px solid #C0C0C0 !important;">
                        <td class="font-weight-600 text-dark"><?php echo $key?></td>
                        <td class="font-weight-600 text-dark"><?php echo $barcode ?></td>
                        <td class="font-weight-600 text-dark">{{$value->product->product_name}}</td>
                        <?php if($nav_type[0]['po_with_unique_barcode'] == 1) {?>
                        <td class="font-weight-600 text-dark">{{$value->unique_barcode}}</td>
                        <?php } ?>
                        <?php
                            echo $feature_show_val;
                        ?>
                        <td class="font-weight-600 text-dark"><?php echo $uqc_name?></td>
                        <?php if($nav_type[0]['po_calculation'] != 2){ ?>
                        <td class="text-right font-weight-600 text-dark">{{$value->cost_rate}}</td>
                        <td class="text-right font-weight-600 text-dark">{{$value->cost_gst_percent}}</td>
                        <td class="text-right font-weight-600 text-dark">{{$value->cost_gst_amount}}</td>
                        <?php } ?>
                        <td class="text-right font-weight-600 text-dark">{{$value->qty}}</td>
                        <td class="text-right font-weight-600 text-dark show_detail_print">{{$value->received_qty}}</td>
                        <td class="text-right font-weight-600 text-dark show_detail_print">{{$value->pending_qty}}</td>
                        <?php if($nav_type[0]['po_calculation'] != 2){ ?>
                        <td class="text-right font-weight-600 text-dark">{{$value->total_cost_without_gst}}</td>
                        <td class="text-right font-weight-600 text-dark">{{$value->total_gst}}</td>
                        <td class="text-right font-weight-600 text-dark">{{$value->total_cost_with_gst}}</td>
                        <?php } ?>
                    </tr>
                @endforeach
                <tr>
                    <td class="text-dark" style="height:50px !important;">&nbsp;</td>
                    <td class="text-dark"></td>
                    <td class="text-dark"></td>
                    <?php if($nav_type[0]['po_with_unique_barcode'] == 1) {?>
                    <td class="text-dark"></td>
                    <?php }
                    if($footer_key > 0)
                        {
                    for ($i=1;$i<=$footer_key;$i++)
                        { ?>
                    <td class="text-dark"></td>
                     <?php  } } ?>
                    <td class="text-dark"></td>
                    <?php if($nav_type[0]['po_calculation'] != 2){ ?>
                    <td class="text-dark"></td>
                    <td class="text-right text-dark"></td>
                    <td class="text-right text-dark"></td>
                    <?php } ?>
                    <td class="text-right text-dark"></td>
                    <td class="text-right text-dark show_detail_print"></td>
                    <td class="text-right text-dark show_detail_print"></td>
                    <?php if($nav_type[0]['po_calculation'] != 2){ ?>
                    <td class="text-right text-dark"></td>
                    <td class="text-right text-dark"></td>
                    <td class="text-right text-dark"></td>
                    <?php } ?>
                </tr>
                </tbody>

                <tfoot style="border-bottom:1px solid #999 !important;border-top:1px solid #999 !important;">
                <tr>
                    <th colspan="3" class="font-14 font-weight-600"></th>

                    <?php  if($nav_type[0]['po_calculation'] != 2){ ?>
                    <th class="text-right font-weight-600"></th>
                    <th class="text-right font-weight-600"></th>
                    <?php } ?>
                    <?php if($nav_type[0]['po_with_unique_barcode'] == 1) {?>
                    <td class="text-dark"></td>
                    <?php }
                    if($footer_key > 0){
                    for ($i=1;$i<=$footer_key;$i++)
                    { ?>
                    <td class="text-dark"></td>
                    <?php  }  }?>

                    <?php  if($nav_type[0]['po_calculation'] != 2){ ?>
                    <th class="text-right font-weight-600"></th>
                        <?php } ?>
                    <th class="text-right font-weight-600">Total</th>
                    <th class="text-right font-weight-600">{{$purchase_order[0]['total_qty']}}</th>
                    <th class="text-right font-14 font-weight-600 show_detail_print"><?php echo $total_received_qty ?></th>
                    <th class="text-right font-14 font-weight-600 show_detail_print"><?php echo $total_pending_qty?></th>
                    <?php if($nav_type[0]['po_calculation'] != 2){ ?>
                    <th class="text-right text-dark font-14 font-weight-600">{{$purchase_order[0]['total_cost_rate']}}</th>
                    <th class="text-right text-dark font-14 font-weight-600">{{$purchase_order[0]['total_gst']}}</th>
                    <th class="text-right text-dark font-18 font-weight-600"><?php echo $tax_currency?> {{$purchase_order[0]['total_cost_price']}}</th>
                    <?php } ?>
                </tr>
                </tfoot>
            </table>

            <div class="invoice-from-wrap" style="margin:45px 0 0 0;">
                <div class="row">
                    <div class="col-md-8 mb-20" style="width:60% !important;border:0px solid !important;">

                    </div>

                    <div class="col-md-4 mb-20" style="width:40% !important;border:0px solid !important;font-size:16px;">

                    </div>

                </div>
                <div class="row">
                <div class="col-md-8 mb-20" style="width:70% !important;border:0px solid !important;margin:20px 0 0 0;">
                    <?php
                    if(isset($purchase_order) && isset($purchase_order[0])&& $purchase_order[0]['terms_condition']!=NULL)
                    {
                    ?>
                    <table style="float:left;font-size:12px;">
                        <tr>
                            <td colspan="3" class="font-weight-600" style="font-size:14px;">TERMS & CONDITIONS</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="font-weight-600"><?php echo html_entity_decode($purchase_order[0]->terms_condition); ?></td>
                        </tr>

                    </table>
                    <?php
                    }
                    ?>

                </div>
                    <?php
                    if(isset($purchase_order[0]->company->authorized_signatory_for) && $purchase_order[0]->company->authorized_signatory_for!='')
                    {
                    ?>
                <div class="col-md-4 mb-20 text-right" style="width:30% !important;border:0px solid !important;">

                    <span class="d-block font-weight-600" style="font-size:16px;">For {{$purchase_order[0]->company->authorized_signatory_for}}</span><br><br>
                    <span class="d-block font-weight-600" style="font-size:16px;">Authorized Signatory</span>



                </div>
                    <?php
                    }
                    ?>

                </div>
                <br>
                <br>
                <center><span class="d-block font-weight-600" style="font-size:18px;">{{strip_tags($purchase_order[0]->company->additional_message)}}</span></center>
            </div>



        </section>
    </div>
</div>


<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script type="text/javascript">


    <?php if($print_type == 1) { ?>
    $(".show_detail_print").hide();
    <?php } else {  ?>
    $(".show_detail_print").show();
    <?php } ?>
</script>

