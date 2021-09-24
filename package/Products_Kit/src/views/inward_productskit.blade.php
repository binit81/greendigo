@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<style type="text/css">
.hk-sec-wrapper .form-control{
	height: auto !important;
	line-height: 1 !important;	
}
.table td, .table th{
	padding: .2rem !important;
}
.form-control[readonly]{
	border-color:#ced4da !important;
	background:#fff !important;
	color:#324148 !important;
	width:50px !important;
}


</style>

<link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/sweetalert/css/sweetalert.css">

<form method="post" name="inward_productskit" id="inward_productskit" enctype="multipart/form-data">
<div class="container ml-20 mt-0">
<div class="row">


    <input type="hidden" id="product_id" name="product_id" value="" />
    <input type="hidden" id="inward_stock_id" name="inward_stock_id" value="" />
    <input type="hidden" id="inward_product_detail_id" name="inward_product_detail_id" value="" />
    <input type="hidden" id="update_offer_price" name="update_offer_price" value="0" />
    
   
    <div class="col-xl-9"> 
        <section class="hk-sec-wrapper">
            <div class="row ma-0 pa-0 mb-0">
               
                <div class="col-sm-6">
                    <div class="input-group">                       
                        <span class="input-group-prepend">
                            <label class="input-group-text" style="height: 40px;"><i class="fa fa-search"></i></label></span>
                        <input class="form-control form-inputtext typeahead" value="" maxlength="" type="text" name="productkitsearch" id="productkitsearch" placeholder="Barcode_ProductName" data-provide="typeahead" data-items="20" data-source="" style="height: 40px !important;">
                     </div>
                </div>

                <div class="col-sm-2">
                    <div class="input-group">                  
                    <input class="form-control form-inputtext invalid number" value="" type="text" name="inward_qty" id="inward_qty" placeholder="Enter Kit Qty"  style="height: 40px !important;" onchange="return calculateqty();">
                    <input class="form-control form-inputtext invalid number" value="" type="hidden" name="oldinward_qty" id="oldinward_qty" style="height: 40px !important;">
                    <input class="form-control form-inputtext invalid number" value="" type="hidden" name="pending_qty" id="pending_qty" style="height: 40px !important;">
                    <input class="form-control form-inputtext invalid number" value="0" type="hidden" name="max_allowqty" id="max_allowqty" style="height: 40px !important;">
                    </div>
                </div>
                <div class="col-sm-4 rightAlign">
                    <h5 class="hk-sec-title">
                            <small class="badge badge-soft-danger mt-15 mr-10"><b>No. of Items:</b>
                                <span class="titems">0</span></span>
                            </small>
                        </h5>
                </div>
        </div>
        </section>
    <!--  data-toggle="tooltip" data-placement="top" data-original-title="Cost Price" -->
  
        <section class="hk-sec-wrapper">
            <b>Kit Items</b> 
            <div class="table-wrap">
            <div class="table-responsive">

                      
                <table class="table tablesaw table-bordered table-hover table-striped mb-0 mt-15" data-tablesaw-no-labels>
                    <thead >
                        <tr class="blue_Head">
                            
                             <?php
                                           
                                if($nav_type[0]['billtype'] == 3)
                                {
                                    ?>
                                    <th scope="col" class="pa-0" data-tablesaw-sortable-col data-tablesaw-priority="1">
                                        <span class="bold itemfocus"><span class="titems">0</span></span>
                                        <span class="plural">Item</span>   
                                    </th>
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Barcode</th>
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Size</th>
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Colour</th>
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">UQC</th>
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Batch No.</th>
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7" class="rightAlign">In Stock</th>                                    
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8" class="rightAlign">Qty</th>
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9" class="rightAlign">Total Qty</th>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <th scope="col" class="pa-10" data-tablesaw-sortable-col data-tablesaw-priority="1" >
                                        <span class="bold itemfocus"><span class="titems">0</span></span>
                                        <span class="plural">Item</span> 
                                    </th>
                            <th  scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Barcode</th>
                            <th  scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Size</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Colour</th>
                            <th  scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">UQC</th>
                            <th  scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6" class="rightAlign">In Stock</th>
                            <th  scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7" class="rightAlign">Qty</th>
                            <th  scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8" class="rightAlign">Total Qty</th>
                                    <?php
                                }
                              
                             ?>
                           
                            
                        </tr>
                    </thead>
                    <tbody id="kitSearchResult">
                       
                    </tbody>
                </table>
            </div>
        </div>
        </section>
    </div>
<?php
$tax_label = 'GST';

    if($nav_type[0]['tax_type'] == 1)
    {
        $tax_label = $nav_type[0]['tax_title'];
    }
?>
    <div class="col-xl-3">
        <section class="hk-sec-wrapper">
            <div class="table-wrap">
                <div class="table-responsive">
                    <b>Other Details</b> 
                   <table border="0" class="table table-striped mb-0">
                    <?php 
                    $style = '';
                     if($nav_type[0]['inward_type'] == 2)
                     {
                            $style = 'display:none';
                     }
                    ?>
                     <tr style="{{$style}}">
                        <td class="leftAlign">Batch No.:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control" id="batch_no" style="width:100%;" value=""></td>
                    </tr>
                     <tr height="35" style="{{$style}}">
                        <td class="leftAlign">Mfg Date:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control" id="mfg_date" name="mfg_date" style="width:100%;" value="" placeholder="DD-MM-YYYY"></td>
                    </tr>
                    <tr height="35" style="{{$style}}">
                        <td class="leftAlign">Exp Date:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control" id="expiry_date" name="expiry_date" style="width:100%;" value="" placeholder="DD-MM-YYYY"></td>
                    </tr>
                     <tr>
                        <td class="leftAlign" width="50%">Inward Date:</td>
                        <td class="rightAlign" width="10%"></td>
                        <td width="40%"><input type="text" class="form-control" id="inward_date" style="width:100%;" value="{{date("d-m-Y")}}"><input type="hidden" class="form-control" id="inward_type" name="inward_type" style="width:100%;" value=""></td>
                    </tr>
                    
                    
                     <tr style="display:none;">
                        <td class="leftAlign">Cost Rate:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control rightAlign" id="cost_rate" name="cost_rate" style="width:100%;" value="0.00"></td>
                    </tr>
                     <tr style="display:none;">
                        <td class="leftAlign">Cost <?php echo $tax_label?>%:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control rightAlign" id="cost_gst_percent" name="cost_gst_percent" style="width:100%;" value="0.00"></td>
                    </tr>
                    <tr style="display:none;">
                        <td class="leftAlign"><?php echo $tax_label?> Amt.</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control rightAlign" id="cost_gst_amount" name="cost_gst_amount" style="width:100%;" value="0.00"></td>
                    </tr>
                    <tr style="display:none;">
                        <td class="leftAlign">Cost Price:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control rightAlign" id="cost_price" name="cost_price" style="width:100%;" value="0.00"></td>
                    </tr>
                    <tr style="display:none;">
                        <td class="leftAlign">Profit%:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input value="0" type="hidden" name="extra_charge" id="extra_charge" placeholder=" ">
                            <input type="text" class="form-control rightAlign" id="profit_percent" name="profit_percent" style="width:100%;" value="0.00"></td>
                    </tr>
                    <tr style="display:none;">
                        <td class="leftAlign">Profit Amt.:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control rightAlign" id="profit_amount" name="profit_amount" style="width:100%;" value="0.00"></td>
                    </tr>
                    <tr>
                        <td class="leftAlign">Selling Price:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control rightAlign" id="selling_price" name="selling_price" style="width:100%;" value="0.00"></td>
                    </tr>
                    <tr>
                        <td class="leftAlign">Selling GST%:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control rightAlign" id="sell_gst_percent" name="sell_gst_percent" style="width:100%;" value="0.00"></td>
                    </tr>
                    <tr>
                        <td class="leftAlign">GST Amt.:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control rightAlign" id="sell_gst_amount" name="sell_gst_amount" style="width:100%;" value="0.00"></td>
                    </tr>
                    <tr>
                        <td class="leftAlign">Offer Price:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control rightAlign" id="offer_price" name="offer_price" style="width:100%;" value="0.00"></td>
                    </tr>
                    <tr>
                        <td class="leftAlign">MRP:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"><input type="text" class="form-control rightAlign" id="product_mrp" name="product_mrp" style="width:100%;" value="0.00"></td>
                    </tr>
                    <tr height="35">
                        <td class="leftAlign">Total QTY:</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign" id="totqtyData"><b>0.00</b></td>
                    </tr>
                   
                     <tr height="35">
                        <td class="leftAlign">&nbsp;</td>
                        <td class="rightAlign"></td>
                        <td class="rightAlign"></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <!-- Save Button -->
                            <?php
                            if($role_permissions['permission_add']==1)
                            {
                            ?>
                                <button type="button" id="saveInwardProducts" name="saveInwardProducts" class="btn savenewBtn btn-block" style="color:#ffffff;"><i class="fa fa-save"></i>Save &amp; New</button>
                            <?php
                            }
                            ?>
                            <!-- Save Button -->
                        </td>
                    </tr>
                   </table>
                </div>
            </div>
        </section>
    </div>

</div>
</div>

</form>


    <script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>
    <script src="{{URL::to('/')}}/public/bower_components/sweetalert/js/sweetalert.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>

    <script src="{{URL::to('/')}}/public/modulejs/product/productkit.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/product/inward_productkit.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/product/product_calculation.js"></script>
        
@endsection

