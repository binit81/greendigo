@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<style type="text/css">
.col-md-7,.col-md-5{
    padding-left:0 !important;
    padding-right:0 !important;
}
.table thead tr.header th {

    /*font-size: 0.95rem !important;*/
}
.table tbody tr td {

    /*font-size: 0.95rem !important;*/
}


.active {
    display: block;
    background:#D8D8D8;
    border:1px solid #CFCFCF;

}
.modal-content .form-inputtext {
    height: calc(2rem + 1px) !important;
    font-size: 1rem !important;
    margin-bottom: 0.50rem !important;
    width:80% !important;
}
.modal-content .form-control[readonly] {
    border: 1px solid #ced4da !important;
    background: transparent;
    color: #000 !important;
    font-size: 0.8rem;
    font-weight: bold;
}
#paymentmethoddiv .form-control[readonly] {
    border: 1px solid #ced4da !important;
    background: transparent;
    color: #000 !important;
    font-size: 0.8rem;
    font-weight: bold;
}
#charges_record .tarifform-control {
    border: 1px solid #ced4da !important;
    background: transparent;
    font-size: 0.9rem;
    color:#000;
}
.saveprintbtn
{
width: auto !important;
/*padding: 8px 23px !important;*/
margin-right:4px !important;
margin-bottom: 42px !important;
/*margin-top: -14px !important;*/
text-decoration: none;
display: inline-block;
float:right !important;
text-align: center;
/*font-size: 80%;*/
cursor:pointer;
}

.modifiedmrp
{
    width:70px;
    margin:-29px 0 0 0 !important;
    padding:5px 5px 6px 5px;
    border: 1px solid #ced4da;
    border-radius: 5px 0 0px 5px;
    position:relative;
    border-top:none;
    border-bottom:none;
}
</style>

<link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/bootstrap-datepicker/css/bootstrap-datepicker.css">
<link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/sweetalert/css/sweetalert.css">
<?php

    $billtype   =  $nav_type[0]['billtype'];
    $tax_type   =  $nav_type[0]['tax_type'];
    $taxname    =  $nav_type[0]['tax_title'];
    $tax_title  =  $tax_type==1?$taxname:'IGST';
    $decimal_points  =  $nav_type[0]['decimal_points'];

?>

<div class="container ml-10">

    <div class="row">
    <div class="col-md-9" style="margin-bottom:-30px;">
        <div class="row">
        <div class="col-md-4">&nbsp;</div>
        <div class="col-md-4" style="margin-top:-50px;">
            <div class="input-group">
                <span class="input-group-prepend"><label class="input-group-text mb-0 greenbg " id="addcustomer" title="Add New Customer" style="height: 32px;"><i class="fa fa-user-plus pa-0 ma-0" style="cursor:pointer;"></i></label></span>
                <input class="form-control typeahead mb-0" value="" maxlength="" type="text" name="searchcustomer" id="searchcustomer" placeholder="Customer Name/Mobile No." data-provide="typeahead" data-items="10" data-source="" autocomplete="off" style="padding:14px 10px;">
             </div>
        </div>
        <div class="col-md-4" style="margin-top:-45px;">
            <div class="customerdata" style="display:none;">
                <div class="row pa-0">
                    <div class="col-md-8 pa-0">
                        <div class="row pa-0 ma-0">
                            <div class="col-sm-3 pa-0 ma-0  no-right">
                                <label>Name</label>
                            </div>
                            <div class="col-sm-9 pa-0 ma-0">
                                <input type="text" class="form-control readonlyClass" readonly placeholder="" name="customer_name" id="customer_name" style="">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 pa-0" style="margin-left:-30px;">
                        <div class="row pa-0">
                            <div class="col-sm-5 pa-0 ma-0 no-right">
                                <label style="width:100px;">Mob#</label>
                            </div>
                            <div class="col-sm-5 pa-0 ma-0">
                                <input type="text" class="form-control number readonlyClass" readonly placeholder="" name="customer_mobile" id="customer_mobile" style="margin-left:50px; width:120px;">
                                <input type="hidden" class="form-control" placeholder="" name="customer_email" id="customer_email">
                                <textarea class="form-control mt-15" rows="3" placeholder="" name="customer_address" id="customer_address" style="display:none;"></textarea>
                                <input type="hidden" class="form-control" placeholder="" name="customer_gstin" id="customer_gstin">
                                            <input type="hidden" class="form-control mt-15" placeholder="" name="customer_state_id" id="customer_state_id">

                                            <i class="fa fa-eye opencustomerpopup" aria-hidden="true" style="color:#000; margin:-24px  0 0 175px;z-index:1000;cursor:pointer;position:relative;display:none;"></i>
                            </div>



                        </div>
                    </div>

                </div>
                 </div>
        </div>
    </div>
    </div>
    <div class="col-md-3" style="margin-top:-47px;  ">
        <div class="row" style="float:right; margin-right:-45px;">


            <div class="col-md-4 pa-0 ml-5">
                <input type="text" class="form-control invoiceNo" placeholder="Reference" name="refname" id="refname">
            </div>
            <div class="col-md-2 rightAlign" style="font-size:13px; line-height:1.8em;">Date</div>

            <div class="col-md-4 pa-0">
                <input type="text" class="form-control invoiceNo" name="invoice_date" id="invoice_date" value="{{date("d-m-Y")}}">
                <input type="hidden" class="form-control mt-15" placeholder="" name="invoice_no" id="invoice_no" autocomplete="off" value="{{$invoiceno}}" readonly style="color:#000;">
            </div>
    </div>
    </div>

<form name="billingform" id="billingform" method="POST" style="width:100%">

    <div class="row ma-0">
        <div class="col-sm-9">


        <div class="hk-row">
                <div class="col-md-12">
                     <div class="card pa-0 ma-0">
                        <div class="card-body pa-10">
                            <div class="table-wrap">
                                <div class="row pa-0">

                                        <?php
                                       if($billtype != 1 && $billtype!=2 && $billtype!=3)
                                       {
                                        ?>

                                               <div class="col-md-8" style="display:none;">
                                                 <div class="input-group">
                                                     <span class="input-group-prepend"><label class="input-group-text searchicon" style="height: 40px;"><i class="fa fa-search"></i></label></span>
                                                    <input class="form-control form-inputtext typeahead" value="" maxlength="" type="text" name="fastproductsearch" id="fastproductsearch" placeholder="Fast Barcode Scanning" data-provide="typeahead" data-items="10" data-source="">&nbsp;&nbsp;
                                                     <input class="form-control form-inputtext typeahead" value="" maxlength="" type="text" name="productsearch" id="productsearch" placeholder="Enter Barcode/Product Code/Product Name" data-provide="typeahead" data-items="10" data-source="">

                                                 </div>

                                                </div>

                                      <?php
                                        }

                                            ?>
                                             <div class="col-md-6">
                                                 <div class="input-group">
                                                     <span class="input-group-prepend"><label class="input-group-text searchicon" style="height: 40px;"><i class="fa fa-search"></i></label></span>

                                                     <input class="form-control form-inputtext typeahead" value="" maxlength="" type="text" name="productsearch" id="productsearch" placeholder="Enter Barcode/Product Code/Product Name" data-provide="typeahead" data-items="10" data-source="">

                                                 </div>

                                                </div>
                                                 <div class="col-md-2"></div>


                                    <div class="col-md-4 rightAlign"><h5 class="hk-sec-title showtbalance" style="display:none; margin-right:-30px; margin-top:10px;"><small  class="badge badge-soft-danger ma-0">Outstanding Amount: <b><span class="tcusbalance">0</span></b></small></h5></div>



                                </div>
                                        <div class="table-responsive pa-0 ma-0">

                                            <table width="100%" border="0">
                                            <!-- <table class="table mb-0" style="margin:10px 0 0 0;font-size:0.92rem !important;" border="0"> -->

                                            <?php
                                                 if($nav_type[0]['bill_calculation']==1)
                                                 {
                                                        $billing_calculation_case  = "";
                                                 }
                                                 else
                                                 {
                                                        $billing_calculation_case  = "display:none;";
                                                 }
                                                if($billtype == 1 || $billtype==2)
                                                {
                                            ?>
                                                <thead>
                                                <tr class="blue_Head">
                                                    <th class="pa-10 leftAlign"> <span class="bold itemfocus"><span class="titems">0</span></span><span class="plural">Item</span></th>
                                                    <th>Barcode</th>
                                                    <?php
                                                    $show_dynamic_feature = '';
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

                                                                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist"><?php echo $feature_value['product_features_name']?></th>
                                                                        <?php
                                                                    } ?>
                                                                    <?php
                                                               }
                                                          }
                                                     }
                                                    ?>
                                                    <th>UQC</th>
                                                    <th>Stock</th>
                                                    <th class="centerAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">MRP</th>
                                                    <th class="rightAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">Rate</th>
                                                    <th class="rightAlign" style="width:5%">Qty.</th>
                                                    <th class="rightAlign" style="width:6%;<?php echo $billing_calculation_case; ?>">Disc %</th>
                                                    <th class="rightAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">Disc Amt.</th>
                                                    <th class="rightAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">Total</th>
                                                    <th>&nbsp;</th>
                                                </tr>
                                                </thead>
                                                <?php
                                                }
                                                else
                                                {
                                                    ?>
                                                <thead>
                                                <tr class="blue_Head">
                                                    <th class="pa-10 leftAlign" style="width:12%"><span class="plural">Item</span> <span class="bold">(<span class="titems">0</span>)</span></th>
                                                    <th>Barcode</th>
                                                     <?php
                                                    $show_dynamic_feature = '';
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

                                                                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist"><?php echo $feature_value['product_features_name']?></th>
                                                                        <?php
                                                                    } ?>
                                                                    <?php
                                                               }
                                                          }
                                                     }
                                                    ?>
                                                    <th>UQC</th>
                                                    <th>Batch No.</th>
                                                    <th>Stock</th>
                                                    <th class="centerAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">MRP</th>
                                                    <th class="rightAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">Rate</th>
                                                    <th class="rightAlign" style="width:5%">Qty.</th>
                                                    <th class="rightAlign" style="width:6%;<?php echo $billing_calculation_case; ?>">Disc %</th>
                                                    <th class="rightAlign" style="width:8%;<?php echo $billing_calculation_case; ?>">Disc Amt.</th>
                                                    <th class="rightAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">Total</th>
                                                    <th>&nbsp;</th>
                                                </tr>
                                                </thead>
                                                    <?php
                                                }
                                                ?>

                                               <tbody id="sproduct_detail_record">
                                                </tbody>
                                            </table>
                                        </div><!--table-wrap-->
                                    </div><!--table-responsive-->
                                </div>
                            </div>
                        </div>
                    </div>

  <!--hk-row-->
   <div class="hk-row">

     <div class="col-md-12">
        <?php
            if(sizeof($chargeslist) != 0)
            {
        ?>
            <div class="card">
                    <div class="card-body pa-10">
                        <div class="table-wrap">
                               <div class="row">

                                    <div class="col-md-3 rightAlign"></div>
                                    <div class="col-md-3 rightAlign"></div>
                                    <div class="col-md-2 rightAlign"></div>


                                </div>
                                    <div class="table-responsive chargesTable">

                                        <table width="100%">
                                            <thead>
                                            <tr class="blue_head">
                                                <th class="pa-10" style="width:38%">Particulars (Additional Charges)</th>
                                                <th style="width:15%" class="rightAlign">Amount</th>
                                                <th style="width:15%" class="rightAlign">{{$tax_title}} %</th>
                                                <th style="width:15%" class="rightAlign">{{$tax_title}} Amt.</th>
                                                <th style="width:15%" class="rightAlign">Total Amt.</th>
                                                <th style="width:2%">&nbsp;</th>

                                            </tr>
                                            </thead>

                                           <tbody id="charges_record">
                                           @foreach($chargeslist AS $charges_key=>$charges_value)
                                           <tr id="charges_{{$charges_value['product_id']}}">
                                                <td class="pa-10" id="chargesname_{{$charges_value['product_id']}}" style="text-align:left !important; font-weight:bold;">{{$charges_value['product_name']}}
                                                    <input value="" type="hidden" id="csales_product_id_{{$charges_value['product_id']}}" name="csales_product_id[]" class="" >
                                                    <input value="{{$charges_value['product_id']}}" type="hidden" id="cproductid_{{$charges_value['product_id']}}" name="cproductid[]" class="" >
                                                </td>
                                                <td class="bold rightAlign"  id="chargesamtdetails_{{$charges_value['product_id']}}">
                                                    <input type="text" id="chargesamt_{{$charges_value['product_id']}}" onkeyup="return addcharges(this);" value="" class="floating-input tarifform-control number chargesamt" name="chargesamt[]" style="width:70%">
                                                    <input type="hidden" id="cqty_{{$charges_value['product_id']}}" class="floating-input tarifform-control number" name="cqty[]" value="1">
                                                </td>
                                                <td id="csprodgstperdetails_{{$charges_value['product_id']}}" style="text-align:right !important;">
                                                    <input type="text" id="csprodgstper_{{$charges_value['product_id']}}"  value="{{$charges_value['sell_gst_percent']}}" onkeyup="return chargesgst(this);"  class="floating-input tarifform-control number chargesamt" style="width:50%"></td>
                                                <td id="csprodgstamt_{{$charges_value['product_id']}}" style="text-align:right !important;font-size:0.9rem !important;"></td>
                                                <td id="cprodgstper_{{$charges_value['product_id']}}" style="display:none;" name="prodgstper[]">{{$charges_value['sell_gst_percent']}}</td>
                                                <td id="cprodgstamt_{{$charges_value['product_id']}}" style="display:none;" name="prodgstamt[]"></td>
                                                <td id="ctotalamount_{{$charges_value['product_id']}}" style="font-weight:bold;display:none;" class="ctotalamount" name="ctotalamount[]"></td>
                                                <td id="cstotalamountdetails_{{$charges_value['product_id']}}" style="font-weight:bold;text-align:right !important;"><input type="text" id="cstotalamount_{{$charges_value['product_id']}}" onkeyup="return taddcharges(this);"  value="" class="floating-input tarifform-control number chargesamt" style="width:50%;font-weight:bold;"></td>
                                                <td>&nbsp;</td>
                                            </tr>

                                            @endforeach

                                            </tbody>
                                        </table>
                                    </div><!--table-wrap-->
                        </div><!--table-responsive-->
                    </div>
                </div>
           <?php
            }
        ?>
            </div>
            </div>

     </div>

      <input type="hidden" name="sales_bill_id" id="sales_bill_id">
                        <input type="hidden" name="customer_id" id="ccustomer_id">




        <!--col-xl-9-->
        <div class="col-sm-3 pa-0">


            <div class="hk-row">

                <div class="col-sm-12 pa-0">
                    <div class="card pa-10">

                    <div class="row pl-0 ma-0" id="totalamtdiv">
                        <div class="row pa-0 ma-0">


                        <div class="col-sm-6 pa-0 rightAlign" style="font-size:12px;<?php echo $billing_calculation_case; ?>">Discount all items (%):</div>
                        <div class="col-sm-2 bold pr-0 row ma-0" style="<?php echo $billing_calculation_case; ?>"><input type="text" class="form-control mt-15 number" value="0"  id="discount_percent" name="discount_percent" style="color:#444444; text-align:right; font-weight:bold;" onkeyup="return overalldiscountpercent();"></div>
                        <div class="col-sm-4 bold pr-0 row ma-0" style="<?php echo $billing_calculation_case; ?>"></div>

                        <div class="col-sm-6 pa-0  rightAlign" style="font-size:12px;<?php echo $billing_calculation_case; ?>">Discount all items (Amt.):</div>
                        <div class="col-sm-4 bold pr-0 row ma-0" style="<?php echo $billing_calculation_case; ?>"><input type="text"  class="form-control mt-15 number" value="0"  id="discount_amount" name="discount_amount" style="color:#444444; text-align:right; font-weight:bold;" onkeyup="return overalldiscountamount();"></div>
                        <div class="col-sm-2 bold pr-0 row ma-0" style="<?php echo $billing_calculation_case; ?>"></div>

                        <div class="row" style="display:none;">
                        <input type="text"  class="form-control mt-15 number" value="0"  id="roomwisediscount_amount" name="roomwisediscount_amount" style="color:#444444; text-align:right; font-weight:bold;">
                        </div>

                        <?php
                         if($nav_type[0]['bill_calculation']==1)
                         {
                                $billing_calculation_case  = "";
                                $col_detail = 'col-sm-6';
                         }
                         else
                         {
                                $billing_calculation_case  = "display:none;";
                                $col_detail = 'col-sm-12';
                         }
                         ?>

                        <div class="{{$col_detail}} bold rightBorder" style="background:#f3f3f3;">
                            <h6 class="bold pt-10 ml-0">Total Qty</h6>
                         <div class="row">
                            <input type="text" style="font-size: 24px;width:200px;padding-left:15px;" class="form-control ml-0 greencolor" value="0" readonly="" id="overallqty" name="overallqty" tabindex="-1">
                          </div>
                        </div>

                        <div class="{{$col_detail}} bold ma-0" style="background:#f3f3f3;<?php echo $billing_calculation_case; ?>">
                            <h6 class="bold pt-10 ml-0 ma-0">Total Payable</h6>
                            <div class="row ma-0">
                                <input type="hidden" style="font-size: 24px;" class="form-control mt-15" value="0.00" readonly="" id="ggrand_total" name="ggrand_total">
                                <input type="text" style="font-size: 24px;width:100px; position:unset;padding-left:2px;" class="form-control mt-5 ml-0 mb-5 pb-10 redcolor cursor redinformative popover" value="0.00" readonly="" id="sggrand_total" tabindex="-1" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="bottom" data-content="" title="" data-html="true"  tabindex="0">
                            </div>
                                <div class="popoverbody pa-10 mt-35" style="display:none;">
                                <?php
                                    if($tax_type==1)
                                    {
                                        $display  =   "display:none;";
                                    }
                                    else
                                    {
                                        $display  =   "";
                                    }
                                 ?>
                                    <div class="row pa-0">
                                        <div class="col-sm-12 pa-0 ma-0">
                                            <div class="row ml-15 pa-0">
                                                <div class="col-sm-7 pa-0 ma-0" style="text-align:right;">
                                                    <b>Sub Total:</b>
                                                </div>
                                                <div class="col-sm-5 pa-0 ma-0 leftAlign">
                                                    <input type="hidden" class="form-control" value="0.00" readonly="" id="totalwithout_gst" name="totalwithout_gst">
                                                    <input type="text" class="form-control bold" value="0.00" readonly="" id="showtotalwithout_gst" tabindex="-1">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 pa-0 ma-0" style="{{$display}}">
                                            <div class="row ml-15 pa-0">
                                                <div class="col-sm-7 pa-0 ma-0" style="text-align:right;">
                                                    <b>CGST:</b>
                                                </div>
                                                <div class="col-sm-5 pa-0 ma-0 leftAlign">
                                                    <input type='hidden' class='form-control' value='0.00' readonly='' id='total_cgst' name='total_cgst'><input type='text' class='form-control' value='0.00' readonly='' id='showtotal_cgst' tabindex='-1'>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 pa-0 ma-0" style="{{$display}}">
                                            <div class="row ml-15 pa-0">
                                                <div class="col-sm-7 pa-0 ma-0" style="text-align:right;">
                                                    <b>SGST:</b>
                                                </div>
                                                <div class="col-sm-5 pa-0 ma-0 leftAlign">
                                                    <input type='hidden' class='form-control' value='0.00' readonly='' id='total_sgst' name='total_sgst'><input type='text' class='form-control' value='0.00' readonly='' id='showtotal_sgst' tabindex='-1'>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 pa-0 ma-0">
                                            <div class="row ml-15 pa-0">
                                                <div class="col-sm-7 pa-0 ma-0" style="text-align:right;">
                                                    <b>Item Disc:</b>
                                                </div>
                                                <div class="col-sm-5 pa-0 ma-0 leftAlign">
                                                    <input type='text' class='form-control' value='0.00' readonly='' id='prodwise_discountamt' name='prodwise_discountamt' tabindex='-1'>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 pa-0 ma-0">
                                            <div class="row ml-15 pa-0">
                                                <div class="col-sm-7 pa-0 ma-0" style="text-align:right;">
                                                    <b>Total {{$tax_title}}:</b>
                                                </div>
                                                <div class="col-sm-5 pa-0 ma-0 leftAlign">
                                                    <input type='text' class='form-control' value='0.00' readonly='' id='total_igst' name='total_igst' tabindex='-1'>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 pa-0 ma-0">
                                            <div class="row ml-15 pa-0">
                                                <div class="col-sm-7 pa-0 ma-0" style="text-align:right;">
                                                    <b>Net Amount:</b>
                                                </div>
                                                <div class="col-sm-5 pa-0 ma-0 leftAlign">
                                                    <input type='hidden' class='form-control' value='0.00' readonly='' id='sales_total' name='sales_total'><input type='text' class='form-control' value='0.00' readonly='' id='showsales_total' tabindex='-1'>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 pa-0 ma-0">
                                            <div class="row ml-15 pa-0">
                                                <div class="col-sm-7 pa-0 ma-0" style="text-align:right;">
                                                    <b>Grand Total:</b>
                                                </div>
                                                <div class="col-sm-5 pa-0 ma-0 leftAlign">
                                                    <input type='hidden' class='form-control' value='0.00' readonly='' id='grand_total' name='grand_total'><input type='text' class='form-control' value='0.00' readonly='' id='showgrand_total' tabindex='-1'>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 pa-0 ma-0">
                                            <div class="row ml-15 pa-0">
                                                <div class="col-sm-7 pa-0 ma-0" style="text-align:right;">
                                                    <b>Addt. Charges:</b>
                                                </div>
                                                <div class="col-sm-5 pa-0 ma-0 leftAlign">
                                                    <input type='hidden' class='form-control' value='0.00' readonly='' id='charges_total' name='charges_total'><input type='text' class='form-control' value='0.00' readonly='' id='scharges_total' tabindex='-1'>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>


                            </div>
                        </div>



                    </div>
                    </div>
                </div>
            </div>



                <div class="col-sm-12 pa-0" style="display:none;">
                    <div class="card">
                        <div class="card-body pr-0 pl-0 greenbg">
                        <?php
                            if($tax_type==1)
                            {
                                $display  =   "display:none;";
                            }
                            else
                            {
                                $display  =   "";
                            }
                        ?>
                            <div class="row" style="margin-right:2px !important;display:{{$display}}">
                                <div class="col-md-7 no-right">
                                    <label>CGST</label>
                                </div>
                                <div class="col-md-5">

                                </div>
                            </div><!--row-->
                            <div class="row" style="margin-right:2px !important;display:{{$display}}">
                                <div class="col-md-7 no-right">
                                    <label>SGST</label>
                                </div>
                                <div class="col-md-5">


                                </div>
                            </div><!--row-->
                            <div class="row" style="margin-right:2px !important;">
                                <div class="col-md-7 no-right">
                                    <label>Item Discount</label>
                                </div>
                                <div class="col-md-5">


                                </div>
                            </div>
                            <div class="row" style="margin-right:2px !important;display:none;">
                                <div class="col-md-7 no-right">
                                    <label>Total {{$tax_title}}</label>
                                </div>
                                <div class="col-md-5">

                                </div>
                            </div>

                            <div class="row" style="margin-right:2px !important;">
                                <div class="col-md-7 no-right">
                                    <label>Net Amount</label>
                                </div>
                                     <div class="col-md-5">



                                </div>
                            </div><!--row-->


                            <?php
                            if(sizeof($chargeslist) != 0)
                            {
                                $tdisplay   = "";
                            }
                            else
                            {
                                $tdisplay   = "display:none";
                            }
                            ?>
                            <div class="row" style="margin-right:2px !important;{{$tdisplay}}">
                                <div class="col-md-7 no-right">
                                    <label>Grand Total</label>
                                </div>
                                <div class="col-md-5">


                                </div>
                            </div>
                            <div class="row" style="margin-right:2px !important;{{$tdisplay}}">
                                <div class="col-md-7 no-right">
                                    <label>Additional Charges</label>
                                </div>
                                <div class="col-md-5">


                                </div>
                            </div>


                             <div class="row" style="margin-right:2px !important;">
                                <div class="col-md-4 no-right">
                                    <input type="hidden" value="" id="creditaccountid" class="form-control mt-15">

                                </div>
                                <div class="col-md-4">
                                    <input type="text" value="" id="editcreditnoteid" class="form-control mt-15">
                                     <input type="hidden" value="" id="editcreditnotepaymentid" class="form-control mt-15">
                                     <input type="hidden" value="" id="editcreditnoteamount" class="form-control mt-15">
                                </div>
                                <div class="col-md-4">

                                    <input type="hidden" value="" id="creditbalcheck" class="form-control mt-15">
                                    <input type="hidden" value="" id="creditbalance" class="form-control mt-15">
                                </div>
                            </div>

                        </div>
                    </div>
                </div><!--col-md-12-->


                <div class="col-sm-12 pa-0">
                    <div class="pa-0 mb-20">

                        <div class="pa-0" id="paymentmethoddiv">

                            <div class="row pa-0 ma-0" style="<?php echo $billing_calculation_case; ?>">
                              @foreach($payment_methods AS $payment_methods_key=>$payment_methods_value)
                                <?php
                                      if($payment_methods_value->html_name == 'outstanding_amount')
                                         {
                                            $class  =   "font-weight:bold;";
                                         }
                                         else
                                         {
                                            $class = '';
                                         }
                                ?>

                                <div class="input-group newRow col-sm-6 pa-0 mb-10">
                                    <div class="input-group input-group-sm mr-10 pa-0">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" style="{{$class}}" id="inputGroup-sizing-sm">{{$payment_methods_value->payment_method_name}}</span>
                                        </div>
                                        <input type="text" data-id="{{$payment_methods_value->payment_method_id}}" class="form-control  number" aria-label="Small" aria-describedby="inputGroup-sizing-sm" id="{{$payment_methods_value->html_id}}" name="{{$payment_methods_value->html_name}}" style="{{$class}}">
                                        <input type="hidden" value="" class="form-control mt-15 number" id="sales_payment_detail{{$payment_methods_value->payment_method_id}}" name="{{$payment_methods_value->html_name}}" >
                                    </div>
                                </div>






                                             @endforeach
                                            </div>
                                     </div>
                                    <div class="card-body pa-0 ma-0">

                                           <div class="row chequedetails" style="display:none;">
                                                <div class="col-md-7 no-right">
                                                    <label for="card">Cheque No.</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control mt-15 number" id="chequeno" name="chequeno" placeholder="">
                                                </div>
                                            </div>
                                            <div class="row chequedetails" style="display:none;">
                                                <div class="col-md-7 no-right">
                                                    <label for="card">Bank Name</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control mt-15" id="bankname" name="bankname" placeholder="">
                                                </div>
                                            </div>

                                            <div class="row netbankingdetails" style="display:none;">
                                                <div class="col-md-7 no-right">
                                                    <label for="card">Net Bank Name</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control mt-15" id="netbankname" name="netbankname" placeholder="">
                                                </div>
                                            </div>
                                             <div class="row outstandingdetails" style="display:none;">
                                                <div class="col-md-7 no-right">
                                                    <label for="card">Due Days</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control mt-15 number" id="duedays" name="duedays" placeholder="Enter Days">
                                                </div>
                                            </div>
                                            <div class="row outstandingdetails" style="display:none;">
                                                <div class="col-md-7 no-right">
                                                    <label for="card">Due Date</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control mt-15" id="duedate" name="duedate" placeholder="Choose Date" readonly style="color:#000 !important;" tabindex="-1">
                                                </div>
                                            </div>
                                </div>


                    </div>
                     <div class="input-group newRow col-sm-12 pa-0 mb-10">
                        <div class="input-group input-group-sm mr-10 pa-0">
                            <div class="input-group-prepend">
                                <span class="input-group-text lightColors" id="inputGroup-sizing-sm" style="font-size:12px !important;">Note for INTERNAL USE</span>
                            </div>
                            <textarea class="form-control" id="official_note" name="official_note"></textarea>
                        </div>
                    </div>
                     <div class="input-group newRow col-sm-12 pa-0 mb-10">
                        <div class="input-group input-group-sm mr-10 pa-0">
                            <div class="input-group-prepend">
                                <span class="input-group-text lightColors" id="inputGroup-sizing-sm" style="font-size:12px !important;">Note for CUSTOMER</span>
                            </div>
                            <textarea class="form-control" id="print_note" name="print_note"></textarea>
                        </div>
                    </div>




                    <div class="row pa-0 pt-20 ma-0">
                        <div class="input-group newRow col-sm-5 pa-0 mb-10">
                            <?php
                            if($role_permissions['permission_print']==1)
                            {
                            ?>
                                <span class="saveprintbtn btn btn-success saveprintbtn btn-block" name="addbillingprint" id="addbillingprint"><i class="fa fa-print"></i>Save & Print </span>
                            <?php
                            }
                            ?>
                        </div> 
                        <div class="col-sm-6 pa-0 pl-0 ml-15">
                            <?php
                            if($role_permissions['permission_add']==1)
                            {
                            ?>
                            <button type="button" class="savenewBtn btn btn-info savenewBtn btn-block" name="addbilling" id="addbilling"><i class="fa fa-save"></i>Save & New </button>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>






        </div><!--hk-row-->
        </div><!--col-xl-3-->

    </div><!--row-->
    </div>
    </div>


    <div class="modal fade" id="addcreditpopup" style="border:1px solid !important;">
        <div class="modal-dialog" style="max-width:30% !important;">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Credit Note Details</h5>
                    <button type="button" class="close" id="creditnoteclose" data-dismiss="modal" aria-hidden="true">×</button>
                 </div>
                 <br>
                  <div class="row">
                        <div class="col-sm">
                            <div class="row">
                                <div class="col-md-5">
                                    <label class="form-label" style="text-align:right !important;">Credit Note No.</label>
                                </div>

                                <div class="col-md-7">
                                     <input type="text" placeholder="Enter Credit Note No." name="creditnoteno" id="creditnoteno" class="form-control form-inputtext" [typeaheadSelectFirstItem]="false" data-provide="typeahead" data-items="10" data-source="">
                                     <input type="hidden" name="creditnote_id" id="creditnote_id" value=""  class="form-control form-inputtext">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" style="text-align:right !important;">Credit Amount</label>
                                </div>

                                <div class="col-md-7">
                                     <input type="text" name="creditnote_amount" id="creditnote_amount" value=""  class="form-control form-inputtext" placeholder="" readonly>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" style="text-align:right !important;">Issue Amount</label>
                                </div>

                                <div class="col-md-7">
                                     <input type="text" name="issue_amount" id="issue_amount" value=""  class="form-control form-inputtext" placeholder="">
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <button type="button" id="savecredit" name="savecredit" class="btn btn-info">Add</button>


                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">&nbsp;
                            </div>
                        </div>

                    </div>

        </div>


          </div>
        </form>
        </div>
    </div>
</form>
<link rel="stylesheet" href="{{URL::to('/')}}/public/build/css/intlTelInput.css">

<div class="modal fade" id="addcustomerpopup" data-backdrop="static" data-keyboard="false" style="border:1px solid !important;">
        <div class="modal-dialog">
         <form id="customerform">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Customer Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                 </div>

                    @include('sales::customer_popup')
        </div>
        </form>

          </div>
        </form>
        </div>
    </div>
<div class="modal fade" id="productdetailpopup" style="border:1px solid !important;">
 <div class="modal-dialog">
         <form id="productform">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Product Detail</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                 </div>
                 <div class="productpopup_values">
                    @include('sales::product_popup');
                 </div>
        </div>
        </form>

          </div>
        </form>
        </div>
    </div>


    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
    <script>
        var bill_show_dynamic_feature = "<?php echo $show_dynamic_feature ?>";

    </script>
    <script type="text/javascript">


    $(document).ready(function () {
        $('#productsearch').focus();


    });
    </script>
    <!-- THis code create problem in in JS file thats why put in main File-->


<!--- Again same problem while daterangepicker fields loaded in edit times thats why placed code in Main file -->

<?php
if($billtype == 1 || $billtype == 2)
{

?>
<script type="text/javascript">
$(document).ready(function () {

    //get a value from local storage
   var edit_data  = localStorage.getItem('edit_consignbill_record');


   if(edit_data != '' && edit_data != undefined && edit_data != null)
   {
      $('.loaderContainer').show();
      var customer_address  =  '';
      var customer_gstin  =  '';
      var customer_stateid  =  '';
      var customer_name = '';
      var customer_mobile = '';
      var customer_email='';
      var customer_ref = '';
      var customer_duedays='';

      $('#sproduct_detail_record').html('');
       var edit_billdata = JSON.parse(edit_data);

      if(edit_billdata['customer_address_detail']!= null && edit_billdata['customer_address_detail']!= '' && edit_billdata['customer_address_detail']['customer_address']!= null && edit_billdata['customer_address_detail']['customer_address']!= undefined)
      {
                customer_address  =  edit_billdata['customer_address_detail']['customer_address'];
      }
      if(edit_billdata['customer_address_detail']!= null && edit_billdata['customer_address_detail']!= '' && edit_billdata['customer_address_detail']['customer_gstin']!= null && edit_billdata['customer_address_detail']['customer_gstin']!= undefined)
      {
                customer_gstin  =  edit_billdata['customer_address_detail']['customer_gstin'];
      }
      if(edit_billdata['customer_address_detail']!= null && edit_billdata['customer_address_detail']!= '' && edit_billdata['customer_address_detail']['state_id']!= null && edit_billdata['customer_address_detail']['state_id']!= undefined)
      {
                customer_stateid  =  edit_billdata['customer_address_detail']['state_id'];
      }
      if(edit_billdata['customer']!= null && edit_billdata['customer']!= '' && edit_billdata['customer']['customer_name']!= null && edit_billdata['customer']['customer_name']!= undefined)
      {
                customer_name  =  edit_billdata['customer']['customer_name'];
      }
      if(edit_billdata['customer']!= null && edit_billdata['customer']!= '' && edit_billdata['customer']['customer_mobile']!= null && edit_billdata['customer']['customer_mobile']!= undefined)
      {
                customer_mobile  =  edit_billdata['customer']['customer_mobile'];
      }
      if(edit_billdata['customer']!= null && edit_billdata['customer']!= '' && edit_billdata['customer']['customer_email']!= null && edit_billdata['customer']['customer_email']!= undefined)
      {
                customer_email  =  edit_billdata['customer']['customer_email'];
      }
      if(edit_billdata['reference']!= null && edit_billdata['reference']!= '' && edit_billdata['reference']['reference_name']!= null && edit_billdata['reference']['reference_name']!= undefined)
      {
                customer_ref  =  edit_billdata['reference']['reference_name'];
      }
     if(edit_billdata['customer']!= null && edit_billdata['customer']!= '' && edit_billdata['customer']['outstanding_duedays']!= '' && edit_billdata['customer']['outstanding_duedays']!= 0)
      {
                customer_duedays  =  edit_billdata['customer']['outstanding_duedays'];
      }

       $('.customerdata').show();
       $('#ccustomer_id').val(edit_billdata['customer_id']);
       $("#sales_bill_id").val(edit_billdata['consign_bill_id']);
       $("#invoice_date").val(edit_billdata['bill_date']);
       $("#invoice_no").val(edit_billdata['bill_no']);
       $("#invoice_ref").val(edit_billdata['bill_reference']);
       $("#discount_percent").val(edit_billdata['discount_percent']);
       $("#discount_amount").val(edit_billdata['discount_amount']);
       $("#roomwisediscount_amount").val(edit_billdata['productwise_discounttotal']);
       $('#official_note').val(edit_billdata['official_note']);
       $('#print_note').val(edit_billdata['print_note']);

        $("#customer_name").val(customer_name);
        $("#customer_mobile").val(customer_mobile);
        $("#customer_email").val(customer_email);
        $("#customer_address").val(customer_address);
        $("#customer_gstin").val(customer_gstin);
        $("#customer_state_id").val(customer_stateid);
        $("#refname").val(customer_ref);


    if(edit_billdata['customer_creditnote']!='' && edit_billdata['customer_creditnote'] != 'undefined' && edit_billdata['customer_creditnote'] != null)
     {
    //      $("#editcreditnotepaymentid").val(edit_billdata['creditnote_payment']['creditnote_payment_id']);
         $("#editcreditnoteid").val(edit_billdata['customer_creditnote']['customer_creditnote_id']);
    //      $("#creditnote_amount").val(edit_billdata['creditnote_payment']['creditnote_amount']);
    //      $("#issue_amount").val(edit_billdata['creditnote_payment']['used_amount']);
    //      $("#creditnoteno").val(edit_billdata['creditnote_payment']['customer_creditnote']['creditnote_no']);
    //      $("#creditnote_id").val(edit_billdata['creditnote_payment']['customer_creditnote_id']);
    //      $("#editcreditnoteamount").val(edit_billdata['creditnote_payment']['used_amount']);

     }


     // if(edit_billdata['customer_creditaccount']!='' && edit_billdata['customer_creditaccount'] != 'undefined' && edit_billdata['customer_creditaccount'] != null)
     // {

     //    $("#creditaccountid").val(edit_billdata['customer_creditaccount']['customer_creditaccount_id']);

     //    if(edit_billdata['customer_creditaccount']['credit_amount'] == edit_billdata['customer_creditaccount']['balance_amount'])
     //    {
     //        $("#creditbalcheck").val(0);
     //        $('#creditbalance').val(edit_billdata['customer_creditaccount']['credit_amount']);

     //    }
     //    else
     //    {
     //        $("#creditbalcheck").val(1);
     //        $('#creditbalance').val(edit_billdata['customer_creditaccount']['credit_amount']);
     //    }
     // }


       if(edit_billdata['consign_payment_detail'] != 'undefined' && edit_billdata['consign_payment_detail'] != '')
       {
           $.each(edit_billdata['consign_payment_detail'],function (paymentkey,paymentvalue)
           {

            if(paymentvalue['payment_method_id'] != '')
               {
                        if(paymentvalue['payment_method'][0]['payment_method_id'] == 6)
                        {
                            $('#duedays').val(customer_duedays);
                            $('#duedate').val(paymentvalue['bankname']);
                        }
                        $("#"+paymentvalue['payment_method'][0]['html_id']).val(paymentvalue['total_bill_amount']);
                        $("#sales_payment_detail"+paymentvalue['payment_method'][0]['payment_method_id']).val(paymentvalue['sales_bill_payment_detail_id']);

               }

           });
       }



       //fillup product detail row
       if(edit_billdata['consign_products_detail'] != 'undefined' && edit_billdata['consign_products_detail'] != '')
       {

           var product_html = '';
            var pcount    = 0;
            var sellingprice  = 0;
            var stock = 0;
            var costprice = 0;

       $.each(edit_billdata['consign_products_detail'],function (billkey,billvalue)
       {

            if(billvalue['product_type'] == 1)
            {
                console.log(billvalue);
               var pricehtml = '';
                     $.each(billvalue['product']['editprice_master'],function (key,value)
                    {

                        if(value['price_master_id']==billvalue['price_master_id'])
                        {

                            pricehtml += '<option value='+value['price_master_id']+' selected>'+value['offer_price']+'</option>';
                            stock     =   value['product_qty'];
                            costprice =   value['costprice'];
                        }
                        else
                        {
                            pricehtml += '<option value='+value['price_master_id']+'>'+value['offer_price']+'</option>';
                        }
                        pcount++;
                    });
                            var product_id                  =   billvalue['product_id'];

                            var sellingprice                =   Number(billvalue['sellingprice_before_discount']);
                            var qty                         =   billvalue['qty'];
                            var discount_percent            =   billvalue['discount_percent'];
                            var gst_per                     =   billvalue['igst_percent'];

                            var totalsellingwgst             =     Number(sellingprice) * Number(qty);
                            var sellingdiscount              =     (Number(sellingprice) * Number(discount_percent) / 100).toFixed(4);
                            var gst_amount                   =     (Number(sellingprice-sellingdiscount) * Number(gst_per) / 100).toFixed(4);

                            var totaldiscount                =      Number(sellingdiscount) * Number(qty);

                            var discountedamt                =      Number(totalsellingwgst) - Number(totaldiscount);

                            var mrp                          =     Number(totaldiscount) + Number(gst_amount);

                            var totalgst                     =      Number(gst_amount) * Number(qty);


                            var sellingwithgst                =      Number(discountedamt) + Number(totalgst);
                            var showsellingprice              =     Number(billvalue['sellingprice_before_discount']).toFixed(2);
                            var showigst_amount              =       Number(billvalue['igst_amount']).toFixed(2);
                            var showtotalamount              =      Number(billvalue['total_amount']).toFixed(2);

                            if(billvalue['product']['supplier_barcode']!='' && billvalue['product']['supplier_barcode']!=null)
                            {
                              var barcode     =     billvalue['product']['supplier_barcode'];
                            }
                            else
                            {
                              var barcode     =     billvalue['product']['product_system_barcode'];
                            }



                            if(billvalue['product']['uqc']!=null)
                            {
                                uqc_name   = billvalue['product']['uqc']['uqc_name'];
                            }
                            else
                            {
                                uqc_name   = '';
                            }

                            var feature_show_val = "";
                                if(bill_show_dynamic_feature != '')
                                {
                                    var feature = bill_show_dynamic_feature.split(',');

                                    $.each(feature,function(fea_key,fea_val)
                                    {
                                        var feature_name = '';

                                        if(typeof(billvalue['product'][fea_val]) != "undefined" && billvalue['product'][fea_val] !== null) {

                                            feature_name = billvalue['product'][fea_val];
                                            //console.log(feature_name);
                                        }

                                        feature_show_val += '<td>' + feature_name + '</td>';
                                    })
                                }

               product_html += '<tr id="product_' + product_id + '">' +
               '<td class="pt-15 pb-15" id="product_name_'+product_id+'" name="product_name[]"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);"><span class="informative">'+billvalue['product']['product_name']+'</span></a></td>'+
                        '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+barcode+'</a></td>';
                        product_html += feature_show_val;
                        product_html += '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+uqc_name+'</a></td>'+

                        '<td id="roomnoval_'+product_id+'" style="display:none;">'+
                        '<input value="'+billvalue['product']['product_system_barcode']+'" type="hidden" id="barcodesel_'+product_id+'" name="barcode_sel[]">'+
                        '<input value="'+billvalue['consign_products_detail_id']+'" type="hidden" id="sales_product_id_'+product_id+'" name="sales_product_id[]" class="" >'+
                        '<input value="'+billvalue['product_id']+'" type="hidden" id="productid_'+product_id+'" name="productid[]" class="allbarcode" >'+

                        '</td>'+

                        '<td id="stock_'+product_id+'" name="stock[]">'+stock+'</td>'+
                        '<td id="sellingmrp_'+product_id+'" class="billing_calculation_case">'+
                            '<select style="width:100%;border: 1px solid #ced4da;" name="mrp[]" id="mrp_'+product_id+'" onchange="return filterprice_detail(this);">'+
                                pricehtml+
                            '</select>'+
                            '<input type="text" class="modifiedmrp" id="modifiedmrp_'+product_id+'" value="'+billvalue['mrp']+'" onkeyup="altermrp(this);"/>'+'<input type="hidden" id="oldpricemasterid_'+product_id+'" name="oldpricemasterid[]"  value="'+billvalue['price_master_id']+'" >'+
                                '<input type="hidden" id="inwardids'+product_id+'" name="inwardids[]"  value="'+billvalue['inwardids']+'" >'+
                                '<input type="hidden" id="inwardqtys'+product_id+'" name="inwardqtys[]"  value="'+billvalue['inwardqtys']+'" >'+
                          '</td>'+
                        '<td id="sellingpricewgst_'+product_id+'" class="billing_calculation_case">'+
                        '<input type="text" id="showsellingwithoutgst_'+product_id+'" class="floating-input tarifform-control number" value="'+showsellingprice+'" readonly>'+
                        '<input type="hidden" id="sellingwithoutgst_'+product_id+'" class="floating-input form-control number tsellingwithoutgst" name="tsellingwithoutgst[]"  value="'+billvalue['sellingprice_before_discount']+'" >'+
                        '<input type="hidden" id="costprice_'+product_id+'" class="floating-input tarifform-control number" value="'+costprice+'" readonly tabindex="-1">'+
                        '</td>'+
                        '<td id="sellingqty_'+product_id+'">'+
                        '<input type="text" id="qty_'+product_id+'" class="floating-input tarifform-control number totqty" value="'+billvalue['qty']+'" name="qty[]" onkeyup="return calqty(this);">'+
                        '<input type="hidden" id="oldqty_'+product_id+'" class="floating-input tarifform-control number" value="'+billvalue['qty']+'" name="oldqty[]">'+
                        '</td>'+
                        '<td id="sellingdiscountper_'+product_id+'" class="billing_calculation_case">'+'<input type="text" id="proddiscper_'+product_id+'" class="floating-input tarifform-control number" value="'+billvalue['discount_percent']+'" name="proddiscper[]" onkeyup="return caldiscountper(this);">'+
                        '<input type="text" id="overalldiscper_'+product_id+'" class="floating-input tarifform-control number" value="'+billvalue['overalldiscount_percent']+'" name="proddiscper[]" style="display:none;">'+'</td>'+
                        '<td id="sellingdiscountamt_'+product_id+'" class="billing_calculation_case">'+'<input type="text" id="mrpproddiscamt_'+product_id+'" class="floating-input tarifform-control number mrppproddiscamt" value="'+billvalue['mrpdiscount_amount']+'" onchange="return mrpcaldiscountamt(this);">'+'<input type="text" id="proddiscamt_'+product_id+'" class="floating-input tarifform-control number pproddiscamt" value="'+billvalue['discount_amount']+'" name="proddiscamt[]" onkeyup="return caldiscountamt(this);" style="display:none;">'+
                        '<input type="text" id="overalldiscamt_'+product_id+'" class="floating-input tarifform-control number overallpproddiscamt" value="'+billvalue['overalldiscount_amount']+'" name="proddiscamt[]" style="display:none;">'+'<input type="text" id="overallmrpdiscamt_'+product_id+'" class="floating-input tarifform-control number" value="'+billvalue['overallmrpdiscount_amount']+'" name="overallmrpdiscamt[]" style="display:none;">'+'</td>'+

                        '<td style="display:none;" id="totalsellingwgst_'+product_id+'" class="totalsellingwgst" name="totalsellingwgst[]">'+discountedamt+'</td>'+
                        '<td style="display:none;" id="totalsellinggst_'+product_id+'" class="totalsellinggst">'+sellingwithgst+'</td>'+
                        '<td id="sprodgstper_'+product_id+'" style="text-align:right !important; display:none;" class="sprodgstper">'+billvalue['igst_percent']+'</td>'+
                        '<td id="sprodgstamt_'+product_id+'" style="text-align:right !important; display:none;">'+showigst_amount+'</td>'+
                        '<td id="prodgstper_'+product_id+'" style="display:none;" name="prodgstper[]">'+billvalue['igst_percent']+'</td>'+
                        '<td id="prodgstamt_'+product_id+'" style="display:none;" class="totalgstamt" name="prodgstamt[]">'+billvalue['igst_amount']+'</td>'+

                        '<td id="totalamount_'+product_id+'" style="font-weight:bold;display:none;" class="tsellingaftergst" name="totalamount[]">'+billvalue['total_amount']+'</td>'+
                        '<td id="stotalamount_'+product_id+'" style="font-weight:bold;text-align:right !important;" class="billing_calculation_case">'+showtotalamount+'</td>'+
                        '<td onclick="editremoverow(' + product_id + ');"><i class="fa fa-close"></i></td>' +
                        '</tr>';

                }


        });



           $(".odd").hide();
           $("#sproduct_detail_record").append(product_html);
           if(Number(bill_calculation)==1)
             {
                $('.billing_calculation_case').show();
             }
             else
             {
                $('.billing_calculation_case').hide();
             }
              var srrno  = 0;
              $('.totqty').each(function(e){
                  var ssrno  = 0;
                  if($(this).val()!='')
                  {
                      srrno++;
                  }
              });

              $('.titems').html(srrno);

   }
       //end of fillup product detail row
       totalcalculation();
       $('.loaderContainer').hide();

   }
});
</script>

<?php
}
 if($billtype == 3)
 {
    ?>
<script type="text/javascript">
$(document).ready(function () {

    //get a value from local storage
   var edit_data  = localStorage.getItem('edit_bill_record');


   if(edit_data != '' && edit_data != undefined && edit_data != null)
   {
    $('.loaderContainer').show();
      var customer_address  =  '';
      var customer_gstin  =  '';
      var customer_stateid  =  '';
      var customer_name = '';
      var customer_mobile = '';
      var customer_email='';
      var customer_ref='';
      var customer_duedays ='';

      $('#sproduct_detail_record').html('');
       var edit_billdata = JSON.parse(edit_data);

      if(edit_billdata['customer_address_detail']!= null && edit_billdata['customer_address_detail']!= '' && edit_billdata['customer_address_detail']['customer_address']!= null && edit_billdata['customer_address_detail']['customer_address']!= undefined)
      {
                customer_address  =  edit_billdata['customer_address_detail']['customer_address'];
      }
      if(edit_billdata['customer_address_detail']!= null && edit_billdata['customer_address_detail']!= '' && edit_billdata['customer_address_detail']['customer_gstin']!= null && edit_billdata['customer_address_detail']['customer_gstin']!= undefined)
      {
                customer_gstin  =  edit_billdata['customer_address_detail']['customer_gstin'];
      }
      if(edit_billdata['customer_address_detail']!= null && edit_billdata['customer_address_detail']!= '' && edit_billdata['customer_address_detail']['state_id']!= null && edit_billdata['customer_address_detail']['state_id']!= undefined)
      {
                customer_stateid  =  edit_billdata['customer_address_detail']['state_id'];
      }
      if(edit_billdata['customer']!= null && edit_billdata['customer']!= '' && edit_billdata['customer']['customer_name']!= null && edit_billdata['customer']['customer_name']!= undefined)
      {
                customer_name  =  edit_billdata['customer']['customer_name'];
      }
      if(edit_billdata['customer']!= null && edit_billdata['customer']!= '' && edit_billdata['customer']['customer_mobile']!= null && edit_billdata['customer']['customer_mobile']!= undefined)
      {
                customer_mobile  =  edit_billdata['customer']['customer_mobile'];
      }
     if(edit_billdata['customer']!= null && edit_billdata['customer']!= '' && edit_billdata['customer']['customer_email']!= null && edit_billdata['customer']['customer_email']!= undefined)
      {
                customer_email  =  edit_billdata['customer']['customer_email'];
      }
      if(edit_billdata['reference']!= null && edit_billdata['reference']!= '' && edit_billdata['reference']['reference_name']!= null && edit_billdata['reference']['reference_name']!= undefined)
      {
                customer_ref  =  edit_billdata['reference']['reference_name'];
      }
      if(edit_billdata['customer']!= null && edit_billdata['customer']!= '' && edit_billdata['customer']['outstanding_duedays']!= '' && edit_billdata['customer']['outstanding_duedays']!= 0)
      {
                customer_duedays  =  edit_billdata['customer']['outstanding_duedays'];
      }

       $('.customerdata').show();
       $('#ccustomer_id').val(edit_billdata['customer_id']);
       $("#sales_bill_id").val(edit_billdata['consign_bill_id']);
       $("#invoice_date").val(edit_billdata['bill_date']);
       $("#invoice_no").val(edit_billdata['bill_no']);
       $("#invoice_ref").val(edit_billdata['bill_reference']);
       $("#discount_percent").val(edit_billdata['discount_percent']);
       $("#discount_amount").val(edit_billdata['discount_amount']);
       $("#roomwisediscount_amount").val(edit_billdata['productwise_discounttotal']);

        $("#customer_name").val(customer_name);
        $("#customer_mobile").val(customer_mobile);
        $("#customer_email").val(customer_email);
        $("#customer_address").val(customer_address);
        $("#customer_gstin").val(customer_gstin);
        $("#customer_state_id").val(customer_stateid);
        $("#refname").val(customer_ref);
        $('#official_note').val(edit_billdata['official_note']);
       $('#print_note').val(edit_billdata['print_note']);


    if(edit_billdata['customer_creditnote']!='' && edit_billdata['customer_creditnote'] != 'undefined' && edit_billdata['customer_creditnote'] != null)
     {
    //      $("#editcreditnotepaymentid").val(edit_billdata['creditnote_payment']['creditnote_payment_id']);
          $("#editcreditnoteid").val(edit_billdata['customer_creditnote']['customer_creditnote_id']);
    //      $("#creditnote_amount").val(edit_billdata['creditnote_payment']['creditnote_amount']);
    //      $("#issue_amount").val(edit_billdata['creditnote_payment']['used_amount']);
    //      $("#creditnoteno").val(edit_billdata['creditnote_payment']['customer_creditnote']['creditnote_no']);
    //      $("#creditnote_id").val(edit_billdata['creditnote_payment']['customer_creditnote_id']);
    //      $("#editcreditnoteamount").val(edit_billdata['creditnote_payment']['used_amount']);

     }


     // if(edit_billdata['customer_creditaccount']!='' && edit_billdata['customer_creditaccount'] != 'undefined' && edit_billdata['customer_creditaccount'] != null)
     // {

     //    $("#creditaccountid").val(edit_billdata['customer_creditaccount']['customer_creditaccount_id']);

     //    if(edit_billdata['customer_creditaccount']['credit_amount'] == edit_billdata['customer_creditaccount']['balance_amount'])
     //    {
     //        $("#creditbalcheck").val(0);
     //        $('#creditbalance').val(edit_billdata['customer_creditaccount']['credit_amount']);

     //    }
     //    else
     //    {
     //        $("#creditbalcheck").val(1);
     //        $('#creditbalance').val(edit_billdata['customer_creditaccount']['credit_amount']);
     //    }
     // }


       if(edit_billdata['consign_payment_detail'] != 'undefined' && edit_billdata['consign_payment_detail'] != '')
       {
           $.each(edit_billdata['consign_payment_detail'],function (paymentkey,paymentvalue)
           {

            if(paymentvalue['payment_method_id'] != '')
               {

                        if(paymentvalue['payment_method'][0]['payment_method_id'] == 6)
                        {
                            $('#duedays').val(customer_duedays);
                            $('#duedate').val(paymentvalue['bankname']);
                        }
                        $("#"+paymentvalue['payment_method'][0]['html_id']).val(paymentvalue['total_bill_amount']);
                        $("#sales_payment_detail"+paymentvalue['payment_method'][0]['payment_method_id']).val(paymentvalue['sales_bill_payment_detail_id']);

               }

           });
       }


//console.log(edit_billdata['sales_product_detail']);
       //fillup product detail row
       if(edit_billdata['consign_products_detail'] != 'undefined' && edit_billdata['consign_products_detail'] != '')
       {

           var product_html = '';
            var pcount    = 0;
            var sellingprice  = 0;
            var stock = 0;
            var costprice = 0;

       $.each(edit_billdata['consign_products_detail'],function (billkey,billvalue)
       {
            if(billvalue['product_type'] == 1)
            {

               var pricehtml = '';


                            pricehtml += '<option value='+billvalue['price_master_id']+' selected>'+billvalue['batchprice_master']['offer_price']+'</option>';
                            stock     =   billvalue['batchprice_master']['product_qty'];
                            costprice     =   billvalue['batchprice_master']['costprice'];

                        pcount++;

                            var product_id                  =   billvalue['price_master_id'];

                            var sellingprice                =   Number(billvalue['sellingprice_before_discount']);
                            var qty                         =   billvalue['qty'];
                            var discount_percent            =   billvalue['discount_percent'];
                            var gst_per                     =   billvalue['igst_percent'];

                            var totalsellingwgst             =     Number(sellingprice) * Number(qty);
                            var sellingdiscount              =     (Number(sellingprice) * Number(discount_percent) / 100).toFixed(4);
                            var gst_amount                   =     (Number(sellingprice-sellingdiscount) * Number(gst_per) / 100).toFixed(4);

                            var totaldiscount                =      Number(sellingdiscount) * Number(qty);

                            var discountedamt                =      Number(totalsellingwgst) - Number(totaldiscount);

                            var mrp                          =     Number(totaldiscount) + Number(gst_amount);

                            var totalgst                     =      Number(gst_amount) * Number(qty);


                            var sellingwithgst                =      Number(discountedamt) + Number(totalgst);
                            var showsellingprice              =     Number(billvalue['sellingprice_before_discount']).toFixed(2);
                            var showigst_amount              =       Number(billvalue['igst_amount']).toFixed(2);
                            var showtotalamount              =      Number(billvalue['total_amount']).toFixed(2);

                             if(billvalue['product']['supplier_barcode']!='' && billvalue['product']['supplier_barcode']!=null)
                            {
                              var barcode     =     billvalue['product']['supplier_barcode'];
                            }
                            else
                            {
                              var barcode     =     billvalue['product']['product_system_barcode'];
                            }

                            if(billvalue['product']['colour_id']!=null)
                            {
                                colour_name   = billvalue['product']['colour']['colour_name'];
                            }
                            else
                            {
                                colour_name   = '';
                            }

                            if(billvalue['product']['size_id']!=null)
                            {
                                size_name   = billvalue['product']['size']['size_name'];
                            }
                            else
                            {
                                size_name   = '';
                            }

                            if(billvalue['product']['uqc']!=null)
                            {
                                uqc_name   = billvalue['product']['uqc']['uqc_name'];
                            }
                            else
                            {
                                uqc_name   = '';
                            }

               product_html += '<tr id="product_' + product_id + '">' +
               '<td class="pt-15 pb-15" id="product_name_'+product_id+'" name="product_name[]"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);"><span class="informative">'+billvalue['product']['product_name']+'</span></a></td>'+
                        '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+barcode+'</a></td>'+
                        '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+colour_name+'</a></td>'+
                        '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+size_name+'</a></td>'+
                        '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+uqc_name+'</a></td>'+

                        '<td id="roomnoval_'+product_id+'" style="display:none;">'+
                        '<input value="'+billvalue['product']['product_system_barcode']+'" type="hidden" id="barcodesel_'+product_id+'" name="barcode_sel[]">'+
                        '<input value="'+billvalue['consign_products_detail_id']+'" type="hidden" id="sales_product_id_'+product_id+'" name="sales_product_id[]" class="" >'+
                        '<input value="'+billvalue['product_id']+'" type="hidden" id="productid_'+product_id+'" name="productid[]" class="allbarcode" >'+

                        '</td>'+
                       '<td id="batchno_'+product_id+'">'+billvalue['batchprice_master']['batch_no']+'</td>'+
                        '<td id="stock_'+product_id+'" name="stock[]">'+stock+'</td>'+
                        '<td id="sellingmrp_'+product_id+'" class="billing_calculation_case">'+
                            '<select style="width:100%;border: 1px solid #ced4da;" name="mrp[]" id="mrp_'+product_id+'" onchange="return filterprice_detail(this);">'+
                                pricehtml+
                            '</select>'+
                            '<input type="text" class="modifiedmrp" id="modifiedmrp_'+product_id+'" value="'+billvalue['mrp']+'" onkeyup="altermrp(this);"/>'+'<input type="hidden" id="oldpricemasterid_'+product_id+'" name="oldpricemasterid[]"  value="'+billvalue['price_master_id']+'" >'+
                                '<input type="hidden" id="inwardids'+product_id+'" name="inwardids[]"  value="'+billvalue['inwardids']+'" >'+
                                '<input type="hidden" id="inwardqtys'+product_id+'" name="inwardqtys[]"  value="'+billvalue['inwardqtys']+'" >'+
                          '</td>'+
                        '<td id="sellingpricewgst_'+product_id+'" class="billing_calculation_case">'+
                        '<input type="text" id="showsellingwithoutgst_'+product_id+'" class="floating-input tarifform-control number" value="'+showsellingprice+'" readonly>'+
                        '<input type="hidden" id="sellingwithoutgst_'+product_id+'" class="floating-input form-control number tsellingwithoutgst" name="tsellingwithoutgst[]"  value="'+billvalue['sellingprice_before_discount']+'" >'+
                        '<input type="hidden" id="costprice_'+product_id+'" class="floating-input tarifform-control number" value="'+costprice+'" readonly tabindex="-1">'+
                        '</td>'+
                        '<td id="sellingqty_'+product_id+'">'+
                        '<input type="text" id="qty_'+product_id+'" class="floating-input tarifform-control number totqty" value="'+billvalue['qty']+'" name="qty[]" onkeyup="return calqty(this);">'+
                        '<input type="hidden" id="oldqty_'+product_id+'" class="floating-input tarifform-control number" value="'+billvalue['qty']+'" name="oldqty[]">'+
                        '</td>'+
                        '<td id="sellingdiscountper_'+product_id+'" class="billing_calculation_case">'+'<input type="text" id="proddiscper_'+product_id+'" class="floating-input tarifform-control number" value="'+billvalue['discount_percent']+'" name="proddiscper[]" onkeyup="return caldiscountper(this);">'+
                        '<input type="text" id="overalldiscper_'+product_id+'" class="floating-input tarifform-control number" value="'+billvalue['overalldiscount_percent']+'" name="proddiscper[]" style="display:none;">'+'</td>'+
                        '<td id="sellingdiscountamt_'+product_id+'" class="billing_calculation_case">'+'<input type="text" id="mrpproddiscamt_'+product_id+'" class="floating-input tarifform-control number mrppproddiscamt"  value="'+billvalue['mrpdiscount_amount']+'" onchange="return mrpcaldiscountamt(this);">'+'<input type="text" id="proddiscamt_'+product_id+'" class="floating-input tarifform-control number pproddiscamt" value="'+billvalue['discount_amount']+'" name="proddiscamt[]" onkeyup="return caldiscountamt(this);" style="display:none;">'+
                        '<input type="text" id="overalldiscamt_'+product_id+'" class="floating-input tarifform-control number overallpproddiscamt" value="'+billvalue['overalldiscount_amount']+'" name="proddiscamt[]" style="display:none;">'+'<input type="text" id="overallmrpdiscamt_'+product_id+'" class="floating-input tarifform-control number" value="'+billvalue['overallmrpdiscount_amount']+'" name="overallmrpdiscamt[]" style="display:none;">'+'</td>'+

                        '<td style="display:none;" id="totalsellingwgst_'+product_id+'" class="totalsellingwgst" name="totalsellingwgst[]">'+discountedamt+'</td>'+
                        '<td style="display:none;" id="totalsellinggst_'+product_id+'" class="totalsellinggst">'+sellingwithgst+'</td>'+
                        '<td id="sprodgstper_'+product_id+'" style="text-align:right !important; display:none;" class="sprodgstper">'+billvalue['igst_percent']+'</td>'+
                        '<td id="sprodgstamt_'+product_id+'" style="text-align:right !important; display:none;">'+showigst_amount+'</td>'+
                        '<td id="prodgstper_'+product_id+'" style="display:none;" name="prodgstper[]">'+billvalue['igst_percent']+'</td>'+
                        '<td id="prodgstamt_'+product_id+'" style="display:none;" class="totalgstamt" name="prodgstamt[]">'+billvalue['igst_amount']+'</td>'+

                        '<td id="totalamount_'+product_id+'" style="font-weight:bold;display:none;" class="tsellingaftergst" name="totalamount[]">'+billvalue['total_amount']+'</td>'+
                        '<td id="stotalamount_'+product_id+'" style="font-weight:bold;text-align:right !important;" class="billing_calculation_case">'+showtotalamount+'</td>'+
                        '<td onclick="editremoverow(' + product_id + ');"><i class="fa fa-close"></i></td>' +
                        '</tr>';

                }


        });



           $(".odd").hide();
           $("#sproduct_detail_record").append(product_html);
           if(Number(bill_calculation)==1)
             {
                $('.billing_calculation_case').show();
             }
             else
             {
                $('.billing_calculation_case').hide();
             }
                var srrno  = 0;
              $('.totqty').each(function(e){
                  var ssrno  = 0;
                  if($(this).val()!='')
                  {
                      srrno++;
                  }
              });

              $('.titems').html(srrno);

   }
       //end of fillup product detail row
       totalcalculation();
       $('.loaderContainer').hide();

   }
});
</script>
    <?php
 }
?>


    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>
     <script src="{{URL::to('/')}}/public/bower_components/sweetalert/js/sweetalert.min.js"></script>




    <script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>
    <?php
    if($tax_type==1)
    {
            ?>
            <script src="{{URL::to('/')}}/public/modulejs/sales/salesbill.js"></script>
            <?php
    }
    else
    {

       if($billtype == 1)
       {
            ?>
            <script src="{{URL::to('/')}}/public/modulejs/sales/salesbill.js"></script>
            <?php
       }
       if($billtype == 2)
       {
        ?>
        <script src="{{URL::to('/')}}/public/modulejs/sales/gstrange_salesbill.js"></script>
        <?php

       }
       if($billtype == 3)
       {
        ?>
        <script src="{{URL::to('/')}}/public/modulejs/sales/batchsalesbill.js"></script>
        <?php

       }
   }
    ?>

    <script src="{{URL::to('/')}}/public/modulejs/sales/consignbill_common.js"></script>

    <script type="text/javascript" src="{{URL::to('/')}}/public/build/js/intlTelInput.js"></script>

    <script>
    $(document).ready(function () {
    selectdiacode();
    });


    function selectdiacode()
    {
        var input = document.querySelector("#pcustomer_mobile");
        window.intlTelInput(input, {
            initialCountry: mobile_dial_code,
            separateDialCode: true,
            utilsScript: "{{URL::to('/')}}/public/build/js/utils.js",
        });
    }




</script>
@endsection
