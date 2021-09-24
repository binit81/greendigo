@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<div class="container">

     <span class="commonbreadcrumbtn badge badge-danger badge-pill"
           id="searchCollapse"><i class="glyphicon glyphicon-search"></i>&nbsp;Search</span>



    <section class="hk-sec-wrapper mr-15 collapse" id="searchBox">
        <div class="hk-row">
            <div class="col-md-11">
            </div>
        </div>
        <h5 class="hk-sec-title">Stock Transfer Details Filter</h5>

        <div class="row ma-0">
            <div class="col-sm">
                <div class="row common-search">
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="text" name-attr="from_to_date" maxlength="50" autocomplete="off"
                                   name="filer_from_to" id="filer_from_to" value=""
                                   class="daterange form-control form-inputtext" placeholder="Select Date">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <input type="text" name-attr="stock_transfer_no" maxlength="50" autocomplete="off"
                                   name="stock_transfer_no_warehouse_filter" id="stock_transfer_no_warehouse_filter" value="" class="form-control form-inputtext"
                                   placeholder="Stock Transfer No.">
                        </div>
                    </div>


                    <div class="col-md-5">
                        <button type="button" class="btn searchBtn search_data" id="search_view_inward"><i
                                class="fa fa-search"></i>Search
                        </button>
                        <button type="button" name="resetfilter" onclick="reset_stock_transfer_detail();"
                                class="btn resetbtn" id="resetfilter" data-container="body" data-toggle="popover"
                                data-placement="bottom" data-content="" data-original-title="" title="">Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
   <div class="hk-row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-wrap">
                        <div class="table-responsive" id="stock_transfer_detail_table">
                       		@include('stock_transfer::stock_transfer/stock_transfer_detail_viewdata')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>

<script src="{{URL::to('/')}}/public/modulejs/Stock_Transfer/stock_transfer_detail_view.js"></script>




@endsection
