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
  padding: .5rem !important;
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
.color{
    color:#fff !important;
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
.uploadBtn
{
  padding:3px 9px !important;
  font-size:12px !important;
  border-radius:50px !important;
}

</style>
<script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>

     
       
<form id="viewbillform" name="viewbillform">
<div class="container ml-10">
<div class="row">
    <div class="col-xl-12">
    <div class="card">
            <div class="card-body pr-0 pl-0">
                <div class="row ma-0">
                    <div class="col-sm-12 pa-0">
                         <div class="table-wrap">
                          <div class="table-responsive" id="viewdeletedbillrecord">
                           @include('salesreport::restore_deleted_billsdata')
                         </div>
                        </div><!--table-wrap-->
                    </div>
                </div>
            </div><!--card-body-->
        </div>
      </div>
    </div>
  </div>
</form>
 <div class="modal fade" id="addcustomerpopup" style="border:1px solid !important;">
        <div class="modal-dialog" style="max-width:90% !important;">
         <form id="customerform">
            <div class="modal-content">                
               
               <div class="modal-header" style="Padding: 0.50rem 0.25rem 0 0.25rem !important;">
                <div class="row ma-0">
                <div class="col-sm">
                    <div class="row">
                      
                        <div class="col-md-4">                            
                             <div class="form-group">
                            
                            </div>
                        </div>
                          <div class="col-md-4">                           
                       
                                <center><h5 class="modal-title">Bill Details : <span class="invoiceno"></span></h5></center>


                            
                        </div>
                        <div class="col-md-4">
                             <div class="form-group"  style="float:right;">
                             
                        </div>
                        </div>
                       
                    </div>
                </div>
               
            </div>
                
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
               </div>
               <br>
               <div class="popup_values">
               @include('salesreport::view_deletedbill_popup')
               </div>

        </div>
        </form>  
          
          </div>
        </form>
        </div>
    </div>  
 
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
    <script src="{{URL::to('/')}}/public/modulejs/sales/viewbill.js"></script>
    

@endsection
