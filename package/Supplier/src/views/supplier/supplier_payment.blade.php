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
            table tr:hover {
                    background: #ffffff !important;
            }

        </style>

        <div class="container">
          <span class="commonbreadcrumbtn badge exportBtn badge-pill mr-10"  id="exportpaymentsummarydata"><i class="ion ion-md-download"></i>&nbsp;Download Supplier Payment Summary</span>   
          <span class="commonbreadcrumbtn badge  viewbtn badge-pill " id="supplierpayment_receipt"><i class="ion ion-md-apps"></i>&nbsp;View Supplier Payment Receipt</span>
          <span class="commonbreadcrumbtn badge badge-danger badge-pill " id="searchCollapse"><i class="glyphicon glyphicon-search"></i>&nbsp;Search</span>

        <form id="viewbillform" name="viewbillform">

         <section class="hk-sec-wrapper collapse" id="searchbox" style="padding: 0.8rem 1.5rem 0 1.5rem !important;">
       
       
        
            
       
            <div class="row ma-0 common-search">
                <div class="col-sm">
                    <div class="row">
                      
                        <div class="col-md-3 ">                            
                             <div class="form-group">
                             <input type="text invalid" name="supplier_name" id="supplier_name" class="form-control form-inputtext" placeholder="By Company / Supplier Name"/>
                             <input type="hidden" name-attr="supplier_gst_id" name="supplier_gst_id" id="supplier_gst_id" value="">
                             <input type="hidden" name-attr="supplier_company_name" name="supplier_company_name" id="supplier_company_name" value="">
                             <input type="hidden" name-attr="supplier_gstin" name="supplier_gstin" id="supplier_gstin" value="">
                            </div>
                        </div>
                          
                          <?php
                          if($role_permissions['userType']==1)
                          {
                              ?>
                                <div class="col-md-3 ">  
                                  <select class="form-control form-inputtext" name-attr="company_id" name="company_id" id="company_id">
                                       <option value="">Select Store</option>
                                              @foreach($get_store AS $storekey=>$storevalue)
                                                   <option value="{{$storevalue->company_profile->company_id}}">{{$storevalue->company_profile->full_name}}</option>
                                              @endforeach
                                                <!-- <option value="">all</option> -->
                                  </select>       
                               </div>
                          <?php 
                          }
                          else
                          {
                              ?><input type="hidden" name="store_id" id="store_id" value="" /><?php
                          }
                          
                      ?>
                       <div class="col-md-3 ">
                           <div class="form-group">
                               <button type="button" class="btn btn-info searchBtn search_data" id="filterpayment"><i class="fa fa-search"></i>Search</button>
                               <button type="button" name="resetfilter" onclick="reset_search_data();" class="btn btn-info resetbtn" id="resetfilter" data-container="body" data-toggle="popover" data-placement="bottom" data-content="" data-original-title="" title="">Reset</button> 
                            </div>
                        </div>
                        <div class="col-md-3">
                         
                         
                    </div>
                    </div>
                </div>
               
            </div>
       

                
    </section>  

          <!--   <section class="hk-sec-wrapper" style="padding: 0.8rem 1.5rem 0 1.5rem !important;margin-top:5px !important;margin-bottom:5px !important;">
                <center><h4 class="hk-sec-title"><b>Supplier Payable Summary</b></h4></center>
                    {{--<h5 class="hk-sec-title">Filter</h5>--}}

               {{-- <div class="row ma-0">
                    <div class="col-sm">
                        <div class="row">

                            <div class="col-md-3 ">
                                <div class="form-group">
                                    <input type="text" name="invoice_no" id="invoice_no" class="form-control form-inputtext" placeholder="Invoice No."/>
                                </div>
                            </div>
                            <div class="col-md-3 ">
                                <div class="form-group">
                                    <button type="button" class="btn btn-info" id="filter_invoice_supplier_debitnote">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>--}}{{-- <div class="row ma-0">
                    <div class="col-sm">
                        <div class="row">

                            <div class="col-md-3 ">
                                <div class="form-group">
                                    <input type="text" name="invoice_no" id="invoice_no" class="form-control form-inputtext" placeholder="Invoice No."/>
                                </div>
                            </div>
                            <div class="col-md-3 ">
                                <div class="form-group">
                                    <button type="button" class="btn btn-info" id="filter_invoice_supplier_debitnote">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>--}}

            </section> -->
 <div class="hk-row pa-15">
                        
                        <div class="col-md-12">
                            <div class="card-group hk-dash-type-3 ">
                                 <div class="card card-sm">
                                    <div class="card-body">
                                       <div class="d-flex justify-content-between mb-5">
                                        <div>
                                          <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Invoices</span>
                                       </div>
                                      </div>
                                        <div>
                                         <span class="d-block display-4 text-dark mb-5"><span class="totinvoices"></span></span>
                                       </div>
                                    </div>
                                </div>
                               

                                   <div class="card card-sm">
                                      <div class="card-body">
                                         <div class="d-flex justify-content-between mb-5">
                                            <div>
                                              <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Unpaid Amt.</span>
                                           </div>
                                          </div>
                                            <div>
                                             <span class="d-block display-4 text-dark mb-5"><span class="outstanding_detail"></span></span>
                                           </div>
                                    </div>
                                  </div>
                                <div class="card card-sm">
                                    <div class="card-body">
                                         <div class="d-flex justify-content-between mb-5">
                                          <div>
                                            <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Received Amt.</span>
                                         </div>
                                        </div>
                                          <div>
                                           <span class="d-block display-4 text-dark mb-5"><span class="pending_amt"></span></span>
                                         </div>
                                    </div>
                                </div>
                                <div class="card card-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-5">
                                            <div>
                                              <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Balance Amt.</span>
                                           </div>
                                        </div>
                                        <div>
                                           <span class="d-block display-4 text-dark mb-5"><span class="amount_payable"></span></span>
                                         </div>
                                      </div>
                                    </div>
                                </div>
                             
                   

                        </div>
              </div>

 <div class="hk-row pa-15">
                        
                        <div class="col-md-12">
              <div class="card">
                <div class="card-body pr-0 pl-0">
                    <div class="row ma-0">
                        <div class="col-sm-12">
                            <div class="table-wrap">
                                <div class="table-responsive" id="supplier_payment_data_app">

                                  @include('supplier::supplier.supplier_payment_data')
                                    <!-- <table class="table tablesaw view-bill-screen table-hover pb-30 dtr-inline">
                                        <thead>
                                        <tr>
                                            <th scope="col" class="billsorting centerAlign bold" data-tablesaw-priority="persist">View</th>
                                            <th scope="col" class="billsorting bold" data-sorting_type="asc" data-column_name="supplier_company_name"  data-tablesaw-sortable-col data-tablesaw-priority="1">Supplier Company<span id="supplier_company_name_icon"></span></th>
                                            <th scope="col" class="billsorting bold" data-sorting_type="asc" data-column_name="supplier_first_name" data-tablesaw-sortable-col data-tablesaw-priority="2">Supplier Name<span id="supplier_first_name_icon"></span></th>
                                            <th scope="col" class="billsorting centerAlign bold" data-sorting_type="asc" data-column_name="supplier_company_mobile_no" data-tablesaw-sortable-col data-tablesaw-priority="3">Mobile No.<span id="supplier_company_mobile_no  _icon"></span></th>
                                            <th scope="col" class="billsorting centerAlign bold" data-sorting_type="asc" data-column_name=""data-tablesaw-sortable-col data-tablesaw-priority="4">Outstanding Amount</th>
                                            <th scope="col" class="billsorting rightAlign bold" data-sorting_type="asc" data-column_name=""data-tablesaw-sortable-col data-tablesaw-priority="5">Paid Amount</th>
                                            <th scope="col" class="billsorting rightAlign bold" data-sorting_type="asc" data-column_name="" data-tablesaw-sortable-col data-tablesaw-priority="6">Amount Payable</th>
                                            <th scope="col" class="billsorting centerAlign bold" data-sorting_type="asc" data-column_name="productwise_discounttotal" data-tablesaw-sortable-col data-tablesaw-priority="7">Action<span id="discount_amount_icon"></span></th>
                                        </tr>
                                        </thead>
                                        <tbody id="view_bill_record"> -->
                                        
                                        <!-- </tbody>
                                    </table>
                                    <input type="hidden" name="hidden_page" id="hidden_page" value="1" />
                                    <input type="hidden" name="hidden_column_name" id="hidden_column_name" value="supplierid" />
                                    <input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
                                    <input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_supplierpaymentdetail" /> -->
                                </div>
                            </div><!--table-wrap-->
                        </div>
                    </div>
                </div><!--card-body-->
            </div>
        </div>
    </div>


        </form>

        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
        <script src="{{URL::to('/')}}/public/dist/js/iconify.min.js"></script>
        <script src="{{URL::to('/')}}/public/modulejs/supplier/supplier_debit.js"></script>
<script type="text/javascript">
$(document).ready(function(e){

    $('#supplierpayment_receipt').click(function(e){
        window.location = 'supplier_payment_receipt';

    });
    // $('#suppliercredit_summary').click(function(e){
    //     window.location = 'customer_credit_summary';

    // });
    $('#searchCollapse').click(function(e){
            $('#searchbox').slideToggle();
        })
});
</script>
@endsection

</html>
