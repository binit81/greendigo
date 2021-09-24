@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<style type="text/css">
.col-md-7,.col-md-5{
    padding-left:0 !important;
    padding-right:0 !important;
}
.table thead tr.header th {

    font-size: 0.95rem !important;
}

.resetbtn{
    width:90px !important;
    padding: .175rem .35rem !important;
}
.popup_values table tbody tr td .even
{
    background : #f3f3f3 !important;
}
.popup_values table tbody tr td .even
{
    background : #ffffff !important;
}
.popup_values table tbody tr td{
    padding: 1rem 0 1rem 0 !important;
}
.tarifform-control[readonly] {
    border-color: transparent;
    background: transparent;
    color: #000;
    /*font-size: 1rem;*/
    font-weight: normal !important;

}
.form-inputtext {
    height: calc(1.89rem + 4px) !important;
    font-size: 1rem !important;
}
</style>
<?php
$billtype   =  $nav_type[0]['billtype'];
$tax_type   =  $nav_type[0]['tax_type'];
$taxname    =  $nav_type[0]['tax_title'];
$tax_title  =  $tax_type==1?$taxname:'IGST';

?>
<link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/bootstrap-datepicker/css/bootstrap-datepicker.css">

<form name="billingform" id="billingform" method="POST">
    <div class="container ml-10">

    <div class="row">
        <div class="col-md-9" style="margin-bottom:-30px;border:0px solid !important;">
        <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4" style="margin-top:-50px;">
            <div class="input-group">
               
                <span class="input-group-prepend"><label class="input-group-text mb-0 greenbg " id="addcustomer" title="Add New Customer" style="height: 32px;"><i class="fa fa-user-plus pa-0 ma-0" style="cursor:pointer;"></i></label></span>
                <input class="form-control typeahead mb-0" value="" maxlength="" type="text" name="searchcustomer" id="searchcustomer" placeholder="Customer Name/Mobile No." data-provide="typeahead" data-items="10" data-source="" autocomplete="off" style="padding:14px 10px;">
               <input type="hidden" name="sales_bill_id" id="sales_bill_id">
               <input type="hidden" name="consign_bill_id" id="consign_bill_id">
                                            <input type="hidden" name="return_bill_id" id="return_bill_id">
                                            <input type="hidden" name="customer_creditnote_id" id="customer_creditnote_id">
                                            <input type="hidden" name="customer_id" id="ccustomer_id">
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
            

            <div class="col-md-4 pa-0 ml-5" style="">
                <input type="text" class="form-control invoiceNo" placeholder="Reference" name="refname" id="refname">
            </div>
            <div class="col-md-2 rightAlign" style="font-size:13px; line-height:1.8em;">Date</div>
         
            <div class="col-md-4 pa-0">
                <input type="text" class="form-control invoiceNo" name="invoice_date" id="invoice_date" value="{{date("d-m-Y")}}">
                <input type="hidden" class="form-control mt-15" placeholder="" name="invoice_no" id="invoice_no" autocomplete="off" value="" readonly style="color:#000;">
            </div>
    </div>
    </div>
    

    <div class="row ma-0">
        <div class="col-sm-9 pa-10 ma-0" style="border:0px solid !important;">

            <div class="hk-row">
                <div class="col-md-6">
                    <div class="card pa-10">
                        <div class="card-body sales-return-box pa-0">
                                    <div class="row">
                                        <div class="col-sm-6  no-right">
                                            <h5 class="card-title" style="margin:5px 0 10px 5px !important;width:50% !important;">Bill Search
                                                
                                            </h5>
                                        </div>
                                        <div class="col-sm-6 text-right">
                                                <input type="radio" name="returntype" id="salesreturntype" value="1">&nbsp;<b style="color:#ff0000 ;">Sales</b>
                                               &nbsp;&nbsp;<input type="radio" name="returntype" id="consignreturntype" value="2">&nbsp;<b style="color:#ff0000 ;">Consign</b>
                                        </div>
                                     </div>
                                     <div class="row">
                                        <div class="col-sm-3  no-right">
                                            <label>Invoice No.</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <input type="text" name="manualbill_no" id="manualbill_no" class="form-control form-inputtext typeahead" placeholder="Bill No." data-provide="typeahead" data-items="10" data-source=""/>

                                        </div>
                                        <div class="col-sm-3">
                                            <button type="button" name="manualresetfilter" onclick="manualresetreturnfilterdata();" class="btn btn-info resetbtn" id="manualresetfilter" data-container="body" data-toggle="popover" data-placement="bottom" data-content="" data-original-title="" title="" >Reset</button>

                                        </div>
                                    </div>
                                     
                                    
                                <div class="row">
                                    <div class="col-sm-12" style="text-align:right !important;">


                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                 <div class="col-md-6">
                    <div class="card pa-10">
                        <div class="card-body sales-return-box pa-0">
                            <h5 class="card-title" style="margin:5px 0 10px 5px !important;">
                                <!-- <div class="tooltip">Missing Bill No.? Filter Product to know your bill No. -->
                                    <span class="tooltiptext">Missing Bill No.? Filter Product to know your bill No.</span>
                                <!-- </div> -->
                            </h5>
                                    <div class="row">
                                        <div class="col-sm-4  no-right">
                                            <label>Product</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" name="productsearch" id="productsearch" class="form-control form-inputtext typeahead" placeholder="Product Name / Barcode" data-provide="typeahead" data-items="10" data-source=""/>
                                        </div>
                                    </div>
                               
                        </div>
                    </div>
                </div>
                

            </div><!--hk-row-->

    <div class="hk-row">
            <div class="col-md-12">
                 <div class="card pa-0 ma-0">
                    <div class="card-body pa-10">
                        <div class="table-wrap">
                            <div class="row pa-0">
                                 <div class="col-md-6">
                                     <div class="input-group manualsearcharea" style="display:none;">
                                             <span class="input-group-prepend"><label class="input-group-text searchicon" style="height: 40px;"><i class="fa fa-search"></i></label></span>
                                             <input class="form-control form-inputtext typeahead" value="" maxlength="" type="text" name="manualproductsearch" id="manualproductsearch" placeholder="Enter Barcode/Product Code/Product Name" data-provide="typeahead" data-items="10" data-source="">


                                        </div>
                                 </div>
                                    <div class="col-md-6 rightAlign showtitems" style="display:none;"><h5 class="hk-sec-title"><small class="badge badge-soft-danger mt-15 mr-10"><b>No. of Items:</b> <span class="titems">0</span></small></h5>
                                    </div>
                                </div>


                                    <div class="table-responsive pa-0 ma-0">

                                        <table width="100%" border="0">
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
                                                <th class="pa-10 leftAlign">Barcode</th>
                                                <th>Product Name</th>
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
                                                <th class="centerAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">MRP</th>
                                                <th class="centerAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">Rate</th>
                                                <th style="width:7%">Qty</th>
                                                <th style="width:7%;<?php echo $billing_calculation_case; ?>">Disc.%</th>
                                                <th style="width:7%;<?php echo $billing_calculation_case; ?>">Disc.Amt.</th>
                                                <!-- <th style="width:7%;<?php echo $billing_calculation_case; ?>">{{$tax_title}}%</th>
                                                <th style="width:9%;<?php echo $billing_calculation_case; ?>">{{$tax_title}} Amt.</th> -->
                                                <th class="rightAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">Total Amt.</th>
                                                <th style="width:2%;"></th>
                                            </tr>
                                            </thead>
                                             <?php
                                            }
                                            else
                                            {
                                            ?>
                                                <thead>
                                                    <tr class="blue_Head">
                                                    <th class="pa-10 leftAlign">Barcode</th>
                                                    <th>Product Name</th>
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
                                                    <th>BatchNo</th>
                                                    <th class="centerAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">MRP</th>
                                                    <th class="centerAlign" style="width:8%;<?php echo $billing_calculation_case; ?>">Rate</th>
                                                    <th style="width:5%">Qty</th>
                                                    <th style="width:6%;<?php echo $billing_calculation_case; ?>">Disc.%</th>
                                                    <th style="width:6%;<?php echo $billing_calculation_case; ?>">Disc.Amt.</th>
                                                   <!--  <th style="width:6%;<?php echo $billing_calculation_case; ?>">{{$tax_title}}%</th>
                                                    <th style="width:9%;<?php echo $billing_calculation_case; ?>">{{$tax_title}} Amt.</th> -->
                                                    <th class="rightAlign" style="width:10%;<?php echo $billing_calculation_case; ?>">Total Amt.</th>
                                                    <th style="width:2%;"></th>
                                                    </tr>
                                                </thead>
                                            <?php
                                            }
                                            ?>
                                             <input type="hidden" name="counter" id="counter" value="1">
                                           <tbody id="sproduct_detail_record">



                                            </tbody>
                                        </table>
                                    </div><!--table-wrap-->
                                </div><!--table-responsive-->
                            </div>
                        </div>
                    </div>
                </div>
      <div class="hk-row">

     <div class="col-md-12">
        <?php
            if(sizeof($chargeslist) != 0)
            {
        ?>
                 <div class="card" style="margin-bottom: 1px !important;">
                    <div class="card-body pa-10">
                        <div class="table-wrap">

                                    <div class="table-responsive chargesTable">
                                       
                                        <table width="100%">
                                            <thead>
                                            <tr class="blue_head">
                                                <th class="pa-10" style="width:35%;text-align:left;">Charges</th>
                                                <th style="width:15%;">Amount</th>
                                                <th style="width:10%;text-align:right !important;">{{$tax_title}}%</th>
                                                <th style="width:10%;text-align:right !important;">{{$tax_title}} Amt.</th>
                                                <th style="width:15%;text-align:right !important;">Total Charges.</th>
                                                <th style="width:15%;text-align:right !important;">Return Charges</th>
                                            </tr>
                                            </thead>

                                           <tbody id="charges_record">

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
            <!--hk-row-->

        </div><!--col-xl-9-->
        <div class="col-sm-3 pa-0">
            <div class="hk-row">

                <div class="col-sm-12 pa-0">
                     <div class="col-sm-12 pa-0">
                    <div class="card" style="display:none;">
                        <div class="card-body pr-0 pl-0 greybg" id="paymentmethoddiv">
                             <h5 class="card-title center">Payment Method</h5>
                            @foreach($payment_methods AS $payment_methods_key=>$payment_methods_value)
                                <?php
                                     if($payment_methods_value->payment_method_id == 8)
                                     {
                                        $class  =   "font-weight:bold;font-size:16px;";
                                        ?>
                                         <div class="row" style="margin-right:2px !important;">
                                                    {{--order cash ,card,wallet--}}
                                                <div class="col-md-7 no-right">
                                                    <label for="card" style="{{$class}}">{{$payment_methods_value->payment_method_name}}</label>
                                                </div>
                                                <div class="col-sm-5">
                                                    <input type="text" value="" data-id="{{$payment_methods_value->payment_method_id}}" class="form-control mt-15 number" id="{{$payment_methods_value->html_id}}" name="{{$payment_methods_value->html_name}}" style="{{$class}}">
                                                    <input type="hidden" value="" class="form-control mt-15 number" id="sales_payment_detail{{$payment_methods_value->payment_method_id}}" name="{{$payment_methods_value->html_name}}" >
                                                </div>
                                            </div>

                                        <?php
                                     }

                                ?>


                                             @endforeach
                          </div>

                    </div>
                </div>
                </div>
  <!--*******************************************************************************************************-->
  <div class="col-sm-12 pa-0">
                    <div class="card pa-10">

                    <div class="row pl-0 ma-0" id="totalamtdiv">
                        <div class="row pa-0 ma-0">


                        <div class="col-sm-6 pa-0 rightAlign" style="font-size:12px;">Discount all items (%):</div>
                        <div class="col-sm-2 bold pr-0 row ma-0 mb-10" style=""><input type="text" class="form-control number" value="0"  id="discount_percent" name="discount_percent" style="color:#444444; text-align:right; font-weight:bold;" onkeyup="return overalldiscountpercent();"></div>
                        <div class="col-sm-4 bold pr-0 row ma-0" style=""></div>

                        <div class="col-sm-6 pa-0  rightAlign" style="font-size:12px;">Discount all items (Amt.):</div>
                        <div class="col-sm-4 bold pr-0 row ma-0 mb-10" style=""><input type="text"  class="form-control number" value="0"  id="discount_amount" name="discount_amount" style="color:#444444; text-align:right; font-weight:bold;" onkeyup="return overalldiscountamount();"></div>
                        <div class="col-sm-2 bold pr-0 row ma-0" style=""></div>

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
                            <h6 class="font-weight-normal pt-10 ml-0">Total Qty</h6>
                         <div class="row">
                            <input type="text" style="font-size: 24px;width:200px;padding-left:15px; color:#008FB3 !important" class="form-control ml-0" value="0" readonly="" id="overallqty" name="overallqty" tabindex="-1">
                          </div>
                        </div>
                        
                        <div class="{{$col_detail}} bold ma-0" style="background:#f3f3f3;<?php echo $billing_calculation_case; ?>">
                            <h6 class="font-weight-normal pt-10 ml-0 ma-0">Total Payable</h6>
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
                                         <div class="row" style="margin-right:2px !important;">
                                            <div class="col-md-12">

                                                <input type="hidden" value="" id="creditaccountid" class="form-control mt-15">
                                                <input type="hidden" value="" id="totalcreditamount" class="form-control mt-15">
                                                <input type="hidden" value="" id="totalcreditbalance" class="form-control mt-15">
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                        


                    </div>    
                    </div>
                </div>

  <!--*******************************************************************************************************-->              
                 

                    <div class="input-group newRow col-sm-12 pa-0 mb-10">
                        <div class="input-group input-group-sm mr-10 pa-0">
                            <div class="input-group-prepend">
                                <span class="input-group-text lightColors" id="inputGroup-sizing-sm" style="font-size:12px !important;height:auto !important;">Note for INTERNAL USE</span>
                            </div>
                            <textarea class="form-control" id="official_note" name="official_note" ></textarea>
                        </div>
                    </div>
                     <div class="input-group newRow col-sm-12 pa-0 mb-10">
                        <div class="input-group input-group-sm mr-10 pa-0">
                            <div class="input-group-prepend">
                                <span class="input-group-text lightColors" id="inputGroup-sizing-sm" style="font-size:12px !important;height:auto !important;">Note for CUSTOMER</span>
                            </div>
                            <textarea class="form-control" id="print_note" name="print_note"></textarea>
                        </div>
                    </div>

                 <div class="row pa-0 ma-0 pt-20">
                        <div class="input-group newRow col-sm-5 pa-0 mb-10">
                    <?php
                    if($role_permissions['permission_print']==1)
                    {
                    ?>
                        <button type="button" class="btn btn-success saveprintBtn btn-block" name="addbillingprint" id="addbillingprint"><i class="fa fa-save"></i>Save & Print</button>
                    <?php
                    }
                    ?>
                    </div>
                     <div class="col-sm-6 pa-0 pl-0 ml-15">

                    <?php
                    if($role_permissions['permission_add']==1)
                    {
                    ?>
                        <button type="button" class="btn btn-info savenewBtn btn-block" name="addbilling" id="addbilling"><i class="fa fa-save"></i>Save & New</button>
                    <?php
                    }
                    ?>
                    </div>
                </div><!--hk-row-->
        </div><!--col-xl-3-->
    </div><!--row-->
    </div>
    </div>
</form>
<link rel="stylesheet" href="{{URL::to('/')}}/public/build/css/intlTelInput.css">
<div class="modal fade" id="addcustomerpopup" style="border:1px solid !important;">
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
<div class="modal fade" id="addreturnpopup" style="border:1px solid !important;">
        <div class="modal-dialog" style="max-width:90% !important;">
         <form id="returnbillsdetails">
            <div class="modal-content" style="height:600px;overflow-y:scroll;overflow-x:none;">

               <div class="modal-header" style="Padding: 0.50rem 0.25rem 0 0.25rem !important;">
                <div class="row ma-0">
                <div class="col-sm">
                    <div class="row">
                        <div class="col-md-4 ">
                             <div class="form-group">
                            </div>
                        </div>
                          <div class="col-md-4 ">
                                <center><h5 class="modal-title">Bill Details : <span class="invoiceno"></span></h5></center>
                        </div>
                        <div class="col-md-4 ">
                             <div class="form-group"  style="float:right;">

                        </div>
                        </div>

                    </div>
                </div>

            </div>

                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
               </div>
               <br>
               <div class="popup_values" style="width:95%;margin:0 auto !important;">
                 <table border="0" frame="" width="100%">
                            <thead>
                                <tr>
                                    <th scope="col" style="width:9%;cursor: pointer;">Select</th>
                                    <th scope="col" style="width:9%;cursor: pointer;">Bill No.</th>
                                    <th scope="col" style="width:9%;cursor: pointer;">Bill Date</th>
                                    <th scope="col" style="width:13%;cursor: pointer;">Customer Name</th>
                                    <th scope="col" style="width:9%;cursor: pointer;text-align:right !important;">Qty</th>
                                    <?php
                                    if($nav_type[0]['bill_calculation']==1)
                                    {


                                    ?>
                                    <th scope="col" style="width:13%;cursor: pointer;text-align:right !important;">Taxable Value</th>
                                    <?php
                                    if($tax_type==1)
                                    {
                                        ?>
                                        <th scope="col" style="width:15%;cursor: pointer;text-align:right !important;">{{$tax_title}} Amt.</th>

                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <th scope="col" style="width:9%;cursor: pointer;text-align:right !important;">CGST Amt.</th>
                                        <th scope="col" style="width:9%;cursor: pointer;text-align:right !important;">SGST Amt.</th>
                                        <?php
                                    }
                                    ?>

                                    <th scope="col" style="width:13%;cursor: pointer;text-align:right !important;">Bill Amount</th>
                                    <?php
                                }
                                ?>
                                    <th scope="col" style="width:7%;cursor: pointer;text-align:center !important;">Action</th>

                                </tr>
                                </thead>
                                <tbody id="productdetails">
                                </tbody>
                                <tfoot>
                                 <tr>
                                 <td colpsan="9">&nbsp;</td>
                                 </tr>
                                </tfoot>
                            </table>
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
    <script src="{{URL::to('/')}}/public/dist/js/moment.min.js"></script>
    <script src="{{URL::to('/')}}/public/dist/js/daterangepicker.js"></script>
    <script type="text/javascript">

           $('.daterange').daterangepicker({


                autoUpdateInput: false,
                allowEmpty: true,

                },function(start_date, end_date) {


        $('.daterange').val(start_date.format('DD-MM-YYYY')+' - '+end_date.format('DD-MM-YYYY'));

        });

    </script>
    <!-- THis code create problem in in JS file thats why put in main File-->


<!--- Again same problem while daterangepicker fields loaded in edit times thats why placed code in Main file -->




    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>-




    <script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/sales/returnbill.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/build/js/intlTelInput.js"></script>

    <script>
    $(document).ready(function () {

        $('#bill_no').focus();
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
