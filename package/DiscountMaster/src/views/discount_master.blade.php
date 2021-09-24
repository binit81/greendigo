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
.tablesaw-sortable-switch, .tablesaw-modeswitch{
    display: none;
}

.tablesaw tr td{
    white-space: nowrap;
}
.table td, .table th{
  padding: .5rem !important;
}
#exTab1 .tab-content {
  color : white;
  /*background-color: #428bca;*/
  padding : 5px 15px;
}

#exTab2 h3 {
  color : black;
  /*background-color: #428bca;*/
  padding : 5px 15px;
}
.tab-content .active {
    display: block;
    background: none !important;
    border: 1px solid #CFCFCF;
}
.form-inputtext {
    margin-bottom: 0px !important;
}

</style>


<script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
<div class="container">
   <span class="commonbreadcrumbtn badge exportBtn badge-pill uploadBtn mr-0"  id="view_flatproducts_lists"><i class="fa fa-eye"></i>View Flat Discount Products List</span></a>
   
    <form id="viewbillform" name="viewbillform">



    <div class="card">
            <div class="card-body pr-0 pl-0">

                <div class="container"></div>

                <div id="exTab2" class="container"> 
                    <ul class="nav nav-tabs">
                        <li>
                             <a  href="#flatdiscount" data-toggle="tab" class="active show">Flat Discount</a>
                        </li>
                        <li>
                            <a href="#buygetone" data-toggle="tab">Buy X Get Y</a>
                        </li>
                        
                    </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="flatdiscount">
                        <div class="col-xl-12">
                          <form method="post" id="FlatDiscountForm" action="">
                                 @include('discountmaster::flat_discount')    
                          </form>
                          
                        </div>
                    </div>
                    <div class="tab-pane" id="buygetone">
                        <div class="col-xl-12 pa-0">                       
                                  @include('discountmaster::buyxgety')                          
                        </div>
                        
                    </div>
                   
                    </div>
                </div>
             </div>


            </div><!--card-body-->
        </div>

    </div>
</div>
</div>
   </form>         



        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
         <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>
        <script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>
        <script src="{{URL::to('/')}}/public/modulejs/product/product.js"></script>
        <script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
        <script src="{{URL::to('/')}}/public/modulejs/DiscountMaster/discount_master.js"></script>



@endsection
</html>