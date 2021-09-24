@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<style type="text/css">
.display-4{
    font-size:1.5rem !important;
}

.pa-0
{
    padding:2px !important;
}
.tablesaw-sortable-switch, .tablesaw-modeswitch{
    display: none;
}
.table thead tr.header th {
   
    font-size: 0.95rem !important;
}
.table tbody tr td {
   
    font-size: 0.92rem !important;
}
.tablesaw tr td{
    white-space: nowrap;
}
.table td{
  padding: .5rem !important;
}

</style>

<div class="container ml-10">

  <?php
    if($role_permissions['permission_export']==1)
    {
    ?>
    <span class="commonbreadcrumbtn badge exportBtn badge-pill mr-10" id="exportstockdata"><i class="ion ion-md-download"></i>&nbsp;Download Stock Report Excel</span>
 <?php
    }
    ?>
    <span class="commonbreadcrumbtn badge badge-danger badge-pill "  id="searchCollapse"><i class="glyphicon glyphicon-search"></i>&nbsp;Search</span>
<div class="row">
    <div class="col-xl-12"> 
    <section class="hk-sec-wrapper collapse" id="searchbox" style="padding: 0.8rem 1.5rem 0 1.5rem !important;">
        <!-- <center><h4 class="hk-sec-title"><b>Stock Report</b></h4></center> -->
        <!-- <h5 class="hk-sec-title">Stock Filter</h5> -->
       <form>
            <div class="row ma-0 common-search">
                <div class="col-xl-12">
                    <div class="row">
                        <div class="col-sm-3 ">                           
                             <div class="form-group">
                              <input type="text" name-attr="from_to_date" name="fromtodate" id="fromtodate" class="daterange form-control form-inputtext" placeholder="Select Date"/>
                                                   
                                      
                            </div>
                        </div>
                        <div class="col-sm-2 ">                            
                             <div class="form-group">
                              <input type="text" name-attr="productsearch" name="productsearch" id="productsearch" class="form-control form-inputtext" placeholder="By Barcode / Product Name" data-provide="typeahead" data-items="10" data-source="">
                              
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
                       
                        <div class="col-sm-3 ">
                            <button type="button" class="btn btn-info searchBtn search_data"><i class="fa fa-search"></i>Search</button>
                        
                         <!-- <button type="button" class="btn btn-success exportBtn" id="exportstockdata" style="float:right;">Export To Excel</button> -->
                         
                    </div>
                    </div>
                </div>
               
            </div>
       
                    
                </form>
    </section>


    
                       
                        <div class="col-xl-12">
                            <div class="card-group hk-dash-type-3 pa-0">
                                 <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Products</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totalproducts">{{$count}}</span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Opening</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="opening">{{$totopening}}</span></span>
                                        </div>
                                    </div>
                                </div>
                                 
                                <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Inward</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totalinwardqty">{{$currinward}}</span></span>
                                        </div>
                                    </div>
                                </div>
                                 <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Sold</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totalsoldqty">{{$currsold}}</span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Total F. Bill</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totalfranqty">{{$currfranqty}}</span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Transfer</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totaltransfer">{{$currstransfer}}</span></span>
                                        </div>
                                    </div>
                                </div>
                               
                                <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Restock</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totalrestockqty">{{$currrestock}}</span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Damage</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totaldamageqty">{{$ttotaldamage}}</span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Used</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totalusedqty">{{$currusedqty}}</span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">T Supp-Return</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totalsupprqty">{{$currsupprqty}}</span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Consign</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totalconsign">{{$pendingcurrconsignqty}}</span></span>
                                        </div>
                                    </div>
                                </div>
                                 <div class="card card-sm">
                                    <div class="card-body pa-0">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                                <span class="d-block font-15 text-dark font-weight-500 greencolor">In Stock</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block display-4 text-dark mb-5"><span class="totalinstock">{{$totstock}}</span></span>
                                        </div>
                                    </div>
                                </div>
                                
                           

                            </div>
                        </div>

                    


    <div class="card card-sm">
            <div class="card-body">
                <div class="row ma-0">
                    <div class="col-sm-12 pa-0">
                        <div class="table-wrap">
                            <div class="table-responsive" id="view_stock_record">
                             @include('salesreport::view_stockreport_data')
                           </div>
                        </div><!--table-wrap-->
                    </div>
                </div>
            </div><!--card-body-->
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

    <script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>    
    <script src="{{URL::to('/')}}/public/modulejs/stock/viewstock.js"></script>
   

@endsection
