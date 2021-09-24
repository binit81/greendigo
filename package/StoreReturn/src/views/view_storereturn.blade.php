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
        <span class="commonbreadcrumbtn badge badge-danger badge-pill"  id="collapseBtn"><i class="glyphicon glyphicon-search"></i>&nbsp;&nbsp;Search </span>
    <section class="hk-sec-wrapper collapse" id="collapseDiv" style="padding: 0.8rem 1.5rem 0 1.5rem !important;margin-top:5px !important;margin-bottom:5px !important;">
     
       
            <div class="row ma-0 mb-10">
                <div class="col-sm">
                    <div class="row common-search">
                        <div class="col-md-3">                           
                             <div class="form-group">
                              <input type="text" name-attr="from_to_date" name="fromtodate" id="fromtodate" class="daterange form-control form-inputtext"  placeholder="Select Date"/>   
                                             
                                      
                            </div>
                        </div>
                        
                        
                        
                        <div class="col-md-3">
                         <button type="button" class="btn btn-info searchBtn search_data"><i class="fa fa-search"></i>Search</button>
                         <button type="button" name="resetfilter" onclick="resetfilterdata();"
                                    class="btn resetbtn" id="resetfilter">Reset</button>
                    </div>
                    </div>
                </div>

            </div>

    </section>
    <div class="card">
            <div class="card-body">
                <div class="row ma-0">
                    <div class="col-sm-12 pa-0">
                        <div class="table-wrap">
                            <div class="table-responsive" id="returnproductrecord">
                                   @include('storereturn::view_storereturn_data')
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
                                <input type="hidden" name="view_bill_type" id="view_bill_type" value="1">
                                @include('commonpopupheader')
                                <div class="col-md-4">
                                     <div class="form-group"  style="float:right;">
                                        <span style="width:150px;float:right;border:1xpx solid;">Action : <span class="editdeleteIcons"></span> </span>
                                    </div>
                                </div>
                           
                            </div>
                        </div>
                   
                    </div>
                
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
               </div>
               <br>
               <div class="popup_values">
               @include('storereturn::view_storereturn_popup')
               </div>
              

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
            $('#collapseBtn').click(function(e){
              $('#collapseDiv').slideToggle();
            });
             $('#collapseuploadBtn').click(function(e){
              $('#collapseuploadDiv').slideToggle();
            });
    });

    </script>
    

    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>-
  

    

    <script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/Store_Profile/viewstore_return.js"></script>
    

@endsection
