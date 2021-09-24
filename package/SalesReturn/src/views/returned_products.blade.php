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
</style>
<script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>

     
       
<form id="viewbillform" name="viewbillform">
<div class="container ml-10">
<div class="row">
    <div class="col-xl-12">
    <div class="card">
            <div class="card-body">
                <div class="row ma-0">
                    <div class="col-sm-12 pa-0">
                        <div class="table-wrap">
                            <div class="table-responsive" id="returnproductrecord">
                                   @include('salesreturn::returned_products_data')
                            </div>
                            <input type="hidden" name="hidden_page" id="hidden_page" value="1" />
                            <input type="hidden" name="hidden_column_name" id="hidden_column_name" value="return_product_detail_id" />
                            <input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="ASC" />
                            <input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_returnproduct_detail" />
                        </div><!--table-wrap-->
                    </div>
                </div>
            </div><!--card-body-->
        </div>
      </div>
    </div>
  </div>
      </form>
  
 
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>

    <script type="text/javascript">
    $(document).ready(function(e){

            $(document).on('click', '#searchroomwisedata', function(){

                var query = {
                    from_date: $('#from_date').val(),
                    to_date : $('#to_date').val(),
                    bill_no: $('#billno').val(),
                    customerid : $('#searchcustomerdata').val(),
                    roomno:$('#roomno').val()
                }


                var url = "{{URL::to('exportproductwise_details')}}?" + $.param(query)
                window.open(url,'_blank');


            });
    });

    </script>


    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>-
  

    

    <script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/sales/returnbill.js"></script>
    

@endsection
