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
     <div class="col-md-9" style="margin-bottom:-30px;">
        
    </div>
    <div class="col-md-3" style="margin-top:-47px;  ">
        <div class="row" style="float:right; margin-right:-45px;">
            

            <div class="col-md-4 pa-0 ml-5" style="">
                
            </div>
            <div class="col-md-2 rightAlign" style="font-size:13px; line-height:1.8em;">Date</div>
         
            <div class="col-md-4 pa-0">
                <input type="text" class="form-control invoiceNo" name="return_date" id="return_date" value="{{date("d-m-Y")}}">
                
            </div>
    </div>
    </div>

    <div class="row ma-0">
        <div class="col-sm-10 pa-10 pt-0 ma-0" style="border:0px solid !important;">

            <div class="hk-row">
                <div class="col-md-12">
                    <div class="card pa-10">
                        <div class="card-body sales-return-box pa-0">
                            
                                    
                                     <div class="row">
                                       
                                        <div class="col-sm-4"><b style="color:#88c241;font-size:14px;">Search</b>
                                            <input type="text" name="manualbill_no" id="manualbill_no" class="form-control form-inputtext typeahead" placeholder="Issue No." data-provide="typeahead" data-items="10" data-source=""/>

                                        </div>
                                        <div class="col-sm-5"><b style="color:#ff0000;font-size:14px;">Search by (Barcode_Product Name_Issue No.)</b><br>
                                            <input type="text" name="productsearch" id="productsearch" class="form-control form-inputtext typeahead" placeholder="Barcode / Product Name / Issue No." data-provide="typeahead" data-items="10" data-source=""/>

                                        </div>
                                        <div class="col-sm-3 mt-20">
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
                                                <th class="centerAlign" style="width:8%;<?php echo $billing_calculation_case; ?>">MRP</th>
                                                <th class="centerAlign" style="width:8%;<?php echo $billing_calculation_case; ?>">Rate</th>
                                                <th style="width:8%" class="centerAlign">In Stock</th>
                                                <th style="width:8%" class="centerAlign">Return Qty</th>
                                                <th style="width:5%;"></th>
                                            </tr>
                                            </thead>
                                             <?php
                                            }
                                            else
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
                                                    <th>BatchNo</th>
                                                    <th class="centerAlign" style="width:8%;<?php echo $billing_calculation_case; ?>">MRP</th>
                                                    <th class="centerAlign" style="width:8%;<?php echo $billing_calculation_case; ?>">Rate</th>
                                                    <th style="width:8%">In Stock</th>
                                                    <th style="width:8%">Return Qty</th>
                                                    <th style="width:5%;"></th>
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
                  <!--hk-row-->

        </div><!--col-xl-9-->
        <div class="col-sm-2 pa-0">
            <div class="hk-row">

        
  <!--*******************************************************************************************************-->
  <div class="col-sm-12 pa-0">
                    <div class="card pa-10">

                    <div class="row pl-0 ma-0" id="totalamtdiv">
                        <div class="row pa-0 ma-0">



                        <div class="col-md-12 bold rightBorder" style="background:#f3f3f3;">
                            <h6 class="font-weight-normal pt-10 ml-0">Total Qty</h6>
                         <div class="row">
                            <input type="text" style="font-size: 24px;width:200px;padding-left:15px; color:#008FB3 !important" class="form-control ml-0" value="0" readonly="" id="overallqty" name="overallqty" tabindex="-1">
                            <input type="hidden" style="font-size: 24px;width:200px;padding-left:15px; color:#008FB3 !important" class="form-control ml-0" value="" readonly="" id="store_return_id" name="store_return_id" tabindex="-1">
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
                                <span class="input-group-text lightColors" id="inputGroup-sizing-sm" style="font-size:12px !important;height:60px !important;">Note</span>
                            </div>
                            <textarea class="form-control" id="official_note" name="official_note" style="height:auto !important;"></textarea>
                        </div>
                    </div>
                    

                 <div class="row pa-0 ma-0 pt-20">
                        
                     <div class="col-sm-12 pa-0 pl-0 ml-0">

                    <?php
                    if($role_permissions['permission_add']==1)
                    {
                    ?>
                        <button type="button" class="btn btn-info savenewBtn btn-block" name="addbilling" id="addbilling"><i class="fa fa-save"></i>Save & New</button>
                    <?php
                    }
                    ?>
                    </div>
                    <div class="input-group newRow col-sm-12 pa-0 mb-10">
                    <?php
                    if($role_permissions['permission_print']==1)
                    {
                    ?>
                       <!--  <button type="button" class="btn btn-success saveprintBtn btn-block" name="addbillingprint" id="addbillingprint"><i class="fa fa-save"></i>Save & Close</button> -->
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
    <script src="{{URL::to('/')}}/public/modulejs/Store_Profile/store_return.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/build/js/intlTelInput.js"></script>

    <script>
    $(document).ready(function () {

        $('#productsearch').focus();
        
    });




</script>
@endsection
