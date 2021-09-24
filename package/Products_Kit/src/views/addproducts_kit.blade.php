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
.form-control, label {
    height: calc(1.95rem) !important;


    
}

</style>

<link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/bootstrap-datepicker/css/bootstrap-datepicker.css">
<div class="container">
<?php
    if($role_permissions['permission_add']==1)
    {
    ?>
<span class=" commonbreadcrumbtn badge badge-primary badge-pill"  id="addnewkitcollapse"><i class="glyphicon glyphicon-plus"></i>&nbsp;Add Kit Details</span>
  <?php
    }
    ?>

 <!---Start Product Kit enter section-->
<form name="productkitform" id="productkitform" method="POST">

<section id="product_block" class="collapse">
 @include('products::product/product_form')

        <div class="row">
            <div class="col-sm-12">
                <div class="hk-row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <input type="hidden" name="type" id="type" value="1" />
                               <button type="submit" name="addproductkit" class="btn btn-info saveBtn" id="addproductkit" data-container="body" data-toggle="popover" data-placement="bottom" data-content="">Add Product Kit</button>
                               
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</section>
</form>
<!---End Product Kit enter section-->
<form name="billingform" id="billingform" method="POST">
   
    <div class="row ma-0">
        <div class="col-sm-9">
        <div class="hk-row ma-0">
                <div class="col-md-12">
                    
        <!-----------------------productsdetail-->

                     <div class="card pa-0 ma-0">
                        <b style="margin:5px 0 0 10px;">Add Items in Kit</b>
                        <div class="card-body pa-10">
                            <div class="table-wrap">
                                <div class="row pa-0">
                                 <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-prepend"><label class="input-group-text" style="height: 40px;"><i class="fa fa-search"></i></label></span>
                                                <input class="form-control form-inputtext typeahead" value="" maxlength="" type="text" name="productsearch" id="productsearch" placeholder="Enter Barcode/Product Code/Product Name" data-provide="typeahead" data-items="10" data-source="">
                                             </div>
                                    </div>
                                   
                                    <div class="col-md-2 rightAlign"><h5 class="hk-sec-title showtbalance" style="margin-right:-30px; margin-top:-10px;"><small  class="badge badge-soft-success  ma-0">Total Cost Price <br><b><span class="ttotalcostprice">0</span></b></small></h5></div>
                                    <div class="col-md-2 rightAlign"><h5 class="hk-sec-title showtbalance" style="margin-right:-30px; margin-top:-10px;"><small  class="badge badge-soft-success  ma-0">Total Qty <br><b><span class="ttotalqty">0</span></b></small></h5></div>
                                    <div class="col-md-2 rightAlign"><h5 class="hk-sec-title showtbalance" style="margin-right:-30px; margin-top:-10px;"><small  class="badge badge-soft-success  ma-0">Total MRP <br><b><span class="ttotalmrp">0</span></b></small></h5></div>
                                    

                                </div>
                                        <div class="table-responsive pa-0 ma-0">
                                           
                                            <table class="table tablesaw table-bordered table-hover  mb-0"   data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>
                                                
                                          
                                                <thead>
                                                <tr class="blue_Head">
                                                    <th class="pa-10 leftAlign">Item (<span class="titems">0</span>)</th>
                                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Barcode</th>
                                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Size</th>
                                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Colour</th>
                                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">UQC</th>
                                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">InStock</th>
                                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6" class="rightAlign" style="width:10%">Cost Price</th>
                                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7" class="rightAlign" style="width:10%">MRP</th>
                                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8" class="rightAlign" style="width:10%">Rate</th>
                                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9" class="rightAlign" style="width:6%">Qty</th>
                                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10" class="rightAlign" style="width:10%">Total</th>
                                                    <th>&nbsp;</th>
                                                </tr>
                                                </thead>
                                                
                                                 
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
       
            </div>
            </div>
       
     </div>

      <input type="hidden" name="sales_bill_id" id="sales_bill_id">
      <input type="hidden" name="customer_id" id="ccustomer_id">
        <!--col-xl-9-->
        <div class="col-sm-3 pa-0">
           
            <div class="hk-row">
                <div class="col-sm-12 pa-0">
                    <div class="card pa-10" id="productdetailsDiv">
                        <div class="row pl-0 ma-0">
                            <div class="row pl-0 ma-0">
                            <div class="col-md-12 pa-0 pb-3" style="display:none;">
                            <input type="text" id="pproduct_id">                          
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Kit Selling Price</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showselling_price leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">MRP</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showmrp leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Product Name</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showproduct_name leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Note</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showproduct_note leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">SKU</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showsku_code leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Product Code</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showproduct_code leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Product Description</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showproduct_description leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">HSN</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showhsn_sac_code leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Brand</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showbrand_id leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Category</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showcategory_id leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-5">
                            <label class="form-label leftAlign">Sub Category</label>
                            </div>
                            <div class="col-md-7 pa-0">
                           <label class="showsubcategory_id leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Colour</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showcolour_id leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Size</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showsize_id leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">UQC</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showuqc_id leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Material</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showmaterial_id leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">System Barcode</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showproduct_system_barcode leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Supplier Barcode</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showsupplier_barcode leftAlign bold"></label>
                            </div>
                            <div class="col-md-5 pa-0 pb-3">
                            <label class="form-label leftAlign">Low Stock Alert</label>
                            </div>
                            <div class="col-md-7 pa-0">
                            <label class="showalert_product_qty leftAlign bold"></label>
                            </div>
                        
                        </div>
                        </div>    
                    </div>
                    
                    </div>
                </div>
        
                <div class="col-sm-12 pa-0">
                    <div class="pa-0">
                      <div class="row pa-0 ma-0">
                          <div class="col-sm-3 pa-0 pr-5">
                              
                          </div> 
                          <div class="col-sm-6 pa-0 pl-5">
                            <?php
                            if($role_permissions['permission_add']==1)
                            {
                            ?>
                              <button type="button" class="btn btn-info savenewBtn btn-block" name="addbilling" id="addbilling"><i class="fa fa-save"></i>Save & New</button>
                            <?php
                            }
                            ?>
                          </div>
                           <div class="col-sm-3 pa-0 pr-5">
                              
                          </div> 
                      </div>
                   </div>
            </div><!--hk-row-->
        </div><!--col-xl-3-->

    </div><!--row-->
    </div>
    </div>


</form>

<div class="modal fade" id="addproducts_features">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title dnamic_feature_title" ></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="container"></div>
                <form id="productfeaturesform">


                    <input type="hidden" name="product_features_id" value="" id="product_features_id">

                      <input type="hidden" name="dynamic_product_features" value="" id="dynamic_product_features">




                    <div class="modal-body">
                        <div class="col-md-2 parentshow" style="display:none">
                            <label class="form-label">Parent</label>
                            <select class="form-control form-inputtext" name="parent" id="parent">
                            </select>
                        </div>
                        <div class="input-group input-group-default floating-label">
                            <label class="form-label dnamic_feature_name"> </label>
                            <input class="form-control form-inputtext invalid" autocomplete="off" name="product_features_data_value"
                                   id="product_features_data_value" maxlength="100" type="text" placeholder=" ">
                        </div>


                        <span id="sizeerr" style="color: red;font-size: 15px"></span>
                    </div>

                    <div class="modal-footer"><a href="#" data-dismiss="modal" class="btn">Close</a>
                        <button type="button" id="productfeaturessave"  class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<!-- END -->

    <div class="modal fade" id="adduqcpopup">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add UQC</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="container"></div>
                <form id="uqcform">
                    <input type="hidden" name="uqc_id" value="" id="uqc_id">
                    <div class="modal-body">
                        <div class="input-group input-group-default floating-label">
                            <input class="form-control form-inputtext" autocomplete="off" name="uqc_name" id="uqc_name" maxlength="100" type="text" placeholder=" ">
                            <label class="form-label">UQC Name</label>
                        </div>
                        <div class="input-group input-group-default floating-label">
                            <input class="form-control form-inputtext" autocomplete="off" name="uqc_type" id="uqc_type" maxlength="100" type="text" placeholder=" ">
                            <label class="form-label">UQC Type</label>
                        </div>

                        <div class="input-group input-group-default floating-label">
                            <input class="form-control form-inputtext" autocomplete="off" name="uqc_shortname" id="uqc_shortname" maxlength="100" type="text" placeholder=" ">
                            <label class="form-label">UQC Name</label>
                        </div>

                        <span id="uqcerr" style="color: red;font-size: 15px"></span>
                    </div>

                    <div class="modal-footer"><a href="#" data-dismiss="modal" class="btn">Close</a>
                        <button type="submit" id="saveuqc" class="btn btn-primary">Save UQC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<link rel="stylesheet" href="{{URL::to('/')}}/public/build/css/intlTelInput.css">


    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
   
    <script type="text/javascript">
    $(document).ready(function () {
        $('#productsearch').focus();
        $('.kitbarcodelabel').html('Kit');
        $('#supplier_barcode').val('{{$supplier_barcode}}');
   
        
    });
    </script>



    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>-
    

    

    <script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/product/product.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/product/productkit.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/product/productproperties.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/common.js"></script>
   
    <script type="text/javascript" src="{{URL::to('/')}}/public/build/js/intlTelInput.js"></script>

   
@endsection
