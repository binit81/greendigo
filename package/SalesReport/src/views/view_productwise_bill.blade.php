@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<style type="text/css">
.display-4{
    font-size:1.5rem !important;
}
.table thead tr.header th {
   
    font-size: 0.95rem !important;
}
.table tbody tr td {
   
    font-size: 0.92rem !important;
}
.table td, .table th{
  padding: .70rem !important;
}
.typeahead {
  width: 300px;
  margin-top: 3px;
  padding: 8px 0;
  background-color: #fff;
  border: 1px solid #ccc;
  border: 1px solid rgba(0, 0, 0, 0.2);
  -webkit-border-radius: 8px;
     -moz-border-radius: 8px;
          border-radius: 8px;
  -webkit-box-shadow: 0 5px 10px rgba(0,0,0,.2);
     -moz-box-shadow: 0 5px 10px rgba(0,0,0,.2);
          box-shadow: 0 5px 10px rgba(0,0,0,.2);
}

.active {
    display: block;
    background:#D8D8D8;
    border:1px solid #CFCFCF;

}
.tablesaw-sortable-switch, .tablesaw-modeswitch{
    display: none;
}

.tablesaw tr td{
    white-space: nowrap;
}
.table td{
  padding: .5rem !important;
}
.color{
    color:#fff !important;
}
</style>


<script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>


<div class="container ml-0">
    
    <?php
    if($role_permissions['permission_export']==1)
    {
    ?>
        <span class="commonbreadcrumbtn badge exportBtn badge-pill mr-10"  id="searchroomwisedata"><i class="ion ion-md-download"></i>&nbsp;Download Productwise Bill Excel </span>
    <?php
    }
    ?>
    
     <span class="commonbreadcrumbtn badge badge-danger badge-pill" id="searchCollapse"><i class="glyphicon glyphicon-search"></i>&nbsp;Search</span>

   
    

   <!--  <button type="button" class="btn exportBtn ml-20 btn-xs mt-10 mb-0" id="searchroomwisedata"><i class="ion ion-md-download"></i>&nbsp;Export To Excel</button> -->
<div class="row">
    <div class="col-xl-12">
    <section class="hk-sec-wrapper collapse" id="searchbox" style="padding: 0.8rem 1.5rem 0 1.5rem !important;">
        <!-- <center><h4 class="hk-sec-title"><b>Product Wise Sale Report</b></h4></center> -->
        <!-- <h5 class="hk-sec-title">Bill Details Filter</h5> -->
       <form>
            <div class="row ma-0 common-search">
                <div class="col-xl-12">
                    <div class="row ma-0 pa-0">
                        <div class="col-sm-3">                           
                             <div class="form-group">
                              <input type="text"  name-attr="from_to_date" name="from_to_date" id="from_to_date" class="daterange form-control form-inputtext" placeholder="Select Date"/>     
                                      
                            </div>
                        </div>
                        <div class="col-sm-3">                            
                             <div class="form-group">
                              <input type="text" name-attr="customerid" name="searchcustomerdata" id="searchcustomerdata" class="form-control form-inputtext" placeholder="By Customer Name / Mobile"/>
                              
                            </div>
                        </div>
                        
                        <div class="col-sm-2">
                            <div class="form-group">
                              <input type="text"  name-attr="billno" name="billno" id="billno" class="form-control form-inputtext" placeholder="Bill No."/>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                              <input type="text"  name-attr="barcode" name="productsearch" id="productsearch" class="form-control form-inputtext" placeholder="Product Name / Barcode"/>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                              <input type="text"  name-attr="productcode" name="productcode" id="pcode_filter" class="form-control form-inputtext" placeholder="Product Code"/>
                            </div>
                        </div>
                         @foreach($product_features AS  $product_features_key=>$product_features_value)
                        <div class="col-md-2">
                            <!-- <label class="form-label">{{$product_features_value->product_features_name}}</label> -->
                            <select class="form-control form-inputtext" name-attr="{{$product_features_value->html_id}}" id="{{$product_features_value->html_id}}" name="{{$product_features_value->html_name}}" value="" >
                                <option value="">Select {{$product_features_value->product_features_name}} </option>
                                @foreach($product_features_value->product_features_data AS  $kk=>$vv)
                                <option value="{{$vv->product_features_data_id}}">{{$vv->product_features_data_value}}</option>

                                @endforeach
                            </select>

                        </div>
                         @endforeach
                        <div class="col-sm-2">
                           <div class="form-group">
                              <input type="text"  name-attr="reference_name" name="reference_name" id="reference_name" class="form-control form-inputtext" placeholder="Reference"/>
                            </div>
                         
                    </div>
                    <?php
                         if(sizeof($get_store)!=0)
                         {
                         ?>     <div class="col-md-2">
                                <select class="form-control form-inputtext" name-attr="store_name" style="" name="store_id" id="store_id">
                                <option value="">Select Store</option>
                                @foreach($get_store AS $storekey=>$storevalue)
                                    <option value="{{$storevalue->store_id}}">{{$storevalue->company_profile->full_name}}</option>
                                @endforeach
                                </select>
                                </div>
                         <?php
                         }
                         ?>
                    <div class="col-sm-12 rightAlign">
                            <button type="button" class="btn btn-info search_data searchBtn"><i class="fa fa-search"></i>Search</button>
                            <button type="button" name="resetfilter" onclick="resetproductfilterdata();"
                                    class="btn resetbtn" id="resetfilter">Reset</button>
                        
                         
                         
                    </div>
                    </div>
                </div>
               
            </div>
       
                    
                </form>
    </section>

    
                       
                        <div class="col-md-12">
                            <div class="card-group hk-dash-type-3 ">
                                 
                                 
                                       <div class="card card-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Taxable Amount</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="taxabletariff"></span></span>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                if($tax_type==1)
                                {
                                    ?>
                                    
                                <div class="card card-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">{{$taxname}} Amount</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totaligst"></span></span>
                                        </div>
                                    </div>
                                </div>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <div class="card card-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">CGST Amount</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totalcgst"></span></span>
                                        </div>
                                    </div>
                                </div>
                                 <div class="card card-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">SGST Amount</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totalsgst"></span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">IGST Amount</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totaligst"></span></span>
                                        </div>
                                    </div>
                                </div>
                                    <?php
                                }
                                ?>
                                 
                                <div class="card card-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Grand Total</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark font-weight-600 mb-5"><span class="grandtotal"></span></span>
                                        </div>
                                    </div>
                                </div>

                
           

            </div>
        </div>

                  

    <div class="col-md-12">
    <div class="card" >
            <div class="card-body">
                <div class="row ma-0">
                    <div class="col-sm-12 pa-0">
                        <div class="table-wrap">
                             <div class="table-responsive" id="viewbillproductsrecord">
                                @include('salesreport::view_productwise_bill_data')
                             </div>
                        </div><!--table-wrap-->
                    </div>
                </div>
            </div><!--card-body-->
      
    </div>
    </div>
 </div>
</div>
</div>

    <script type="text/javascript">
    $(document).ready(function(e){
        $('#searchCollapse').click(function(e){
            $('#searchbox').slideToggle();
        })
    })
    </script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>

<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>
      
       
<script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>
<script src="{{URL::to('/')}}/public/modulejs/sales/viewbill.js"></script>
    

@endsection
