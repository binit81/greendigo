@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper') 
<link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/sweetalert/css/sweetalert.css">
<script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>
<form id="viewbillform" name="viewbillform">
<div class="container ml-10">
<div class="row">
    <div class="col-xl-12">

      <span class="commonbreadcrumbtn badge badge-pill downloadBtn mr-0"  id="download_bill_template"><i class="ion ion-md-download"></i>&nbsp;Download Bills Template</span></a>

    <?php
    if($role_permissions['permission_export']==1)
    {
    ?>
      <span class="commonbreadcrumbtn badge exportBtn badge-pill "  id="billingexport"><i class="ion ion-md-download"></i>&nbsp;&nbsp;Download Bills Data </span>
 <?php
    }
    ?>

    <?php
    if($role_permissions['permission_upload']==1)
    {
    ?>

       <span class="commonbreadcrumbtn badge  uploadBtn badge-pill "  id="collapseuploadBtn" >&nbsp<i class="fa fa-upload"></i>&nbsp;Upload Bills Excel</span>
          <?php
    }
    ?>
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
                        <div class="col-md-2 ">                            
                             <div class="form-group">
                              <input type="text" name-attr="customerid" name="searchcustomerdata" id="searchcustomerdata" class="form-control form-inputtext" placeholder="By Customer Name / Mobile"/>
                              
                            </div>
                        </div>
                        
                        <div class="col-md-2 ">
                            <div class="form-group">
                              <input type="text" name-attr="billno" name="billno" id="billno" class="form-control form-inputtext" placeholder="Bill No." data-provide="typeahead" data-items="10" data-source=""/>
                            </div>
                        </div>
                         <div class="col-md-2 ">
                            <div class="form-group">
                              <input type="text" name-attr="reference_name" name="reference_name" id="reference_name" class="form-control form-inputtext" placeholder="Reference" data-provide="typeahead" data-items="10" data-source=""/>
                            </div>
                        </div>
                         <div class="col-md-2 ">
                            <div class="form-group">
                              <input type="text" name-attr="employee_name" name="employee_name" id="employee_name" class="form-control form-inputtext" placeholder="Employee Name" data-provide="typeahead" data-items="10" data-source=""/>
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
                        <div class="col-md-3">
                         <button type="button" class="btn btn-info searchBtn search_data"><i class="fa fa-search"></i>Search</button>
                         <button type="button" name="resetfilter" onclick="resetfilterdata();"
                                    class="btn resetbtn" id="resetfilter">Reset</button>
                    </div>
                    </div>
                </div>

            </div>

    </section>
     <section class="hk-sec-wrapper collapse" id="collapseuploadDiv" style="padding: 0.8rem 1.5rem 0 1.5rem !important;margin-top:5px !important;margin-bottom:5px !important;">


            <div class="row ma-0">
                <div class="col-sm">
                    <div class="row common-search">
                        <div class="col-md-3">                           
                             <div class="form-group">
                              <input type="file" class="" id="salesfileUpload"  accept=".xlsx, .xls" /> 
                            </div>
                        </div>
                        <div class="col-md-2 ">                            
                             <div class="form-group">
                              <button type="button"  class="btn btn-info btn-block" name="upload" id="uploadsales"><i class="ion ion-md-cloud-upload"></i>&nbsp;Upload</button>
                              
                            </div>
                        </div>
                        

                        <div class="col-md-3 ">
                            <div class="form-group">
                             
                            </div>
                        </div>
                        <div class="col-md-3">
                        
                    </div>
                    </div>
                </div>
               
            </div>
                
    </section>

    <div class="row ma-0 mt-10">
                        <div class="col-md-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-5">
                                        <div>
                                          <span class="d-block font-14 text-dark font-weight-500 greencolor">
                                              Report from <span class="fromdate">{{date("d-m-Y")}}</span> to <span class="todate">{{date("d-m-Y")}}</span> 
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="d-block display-4 text-dark mb-5"><span
                                                    class="totalinvoice">{{$count}}</span> <span class="invoiceLabel">Invoices</span> </span>


                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    if($nav_type[0]['bill_calculation']==1)
                    {


                    ?>
                        <div class="col-md-9 pr-0">
                           <div class="card card-sm">
                            <div class="card-body">
                                <table border="0" style="width:100% !important;" cellpadding="1">
                                  <tr>
                                    <td class="centerAlign greencolor" class="centerAlign">Taxable Amount</td>
                                     <?php
                                    if($tax_type==1)
                                    {
                                      ?>
                                      <td class="centerAlign greencolor">{{$taxname}} Amount</td>
                                     <?php
                                    }
                                    else
                                    {
                                      ?>
                                       <td class="centerAlign greencolor">CGST Amount</td>
                                       <td class="centerAlign greencolor">SGST Amount</td>
                                       <td class="centerAlign greencolor">IGST Amount</td>
                                      <?php
                                    }
                                      ?>                                   
                                   
                                    <td class="centerAlign greencolor">Grand Total</td>
                                  </tr>
                                  <tr>
                                    <td class="centerAlign"><h5><span class="taxabletariff">0</span></h5></td>
                                    <?php
                                    if($tax_type==1)
                                    {
                                      ?>
                                      <td class="centerAlign"><h5><span class="overalligst">0</span></h5></td>
                                     <?php
                                    }
                                    else
                                    {
                                      ?>
                                      <td class="centerAlign"><h5><span class="overallcgst">0</span></h5></td>
                                      <td class="centerAlign"><h5><span class="overallsgst">0</span></h5></td>
                                      <td class="centerAlign"><h5><span class="overalligst">0</span></h5></td>

                                      <?php
                                    }
                                      ?>
                                      <td class="centerAlign"><h5><span class="overallgrand">0</span></h5></td>
                                  </tr>
                                </table>
                                <hr/>
                                <table border="0" style="width:100% !important;" cellpadding="1">
                                  <tr>
                                    <td width="14%" class="centerAlign greencolor">Cash</td>
                                    <td width="14%" class="centerAlign greencolor">Card</td>
                                    <td width="14%" class="centerAlign greencolor">Cheque</td>
                                    <td width="14%" class="centerAlign greencolor">Wallet</td>
                                    <td width="14%" class="centerAlign greencolor">Unpaid Amt.</td>
                                    <td width="14%" class="centerAlign greencolor">Net Banking</td>
                                    <td width="14%" class="centerAlign greencolor">Credit Note</td>
                                  </tr>
                                  <tr>
                                    <td class="centerAlign"><h5><span class="cash">0</span></h5></td>
                                    <td class="centerAlign"><h5><span class="showcard">0</span></h5></td>
                                    <td class="centerAlign"><h5><span class="cheque">0</span></h5></td>
                                    <td class="centerAlign"><h5><span class="wallet">0</span></h5></td>
                                    <td class="centerAlign"><h5><span class="unpaidamt">0</span></h5></td>
                                    <td class="centerAlign"><h5><span class="netbanking">0</span></h5></td>
                                    <td class="centerAlign"><h5><span class="creditnote">0</span></h5></td>
                                  </tr>
                                </table>
                           </div>
                          </div>
                          </div>
                     <?php
                        }
                        ?>
                        
                            </div>
                        </div>

                    </div>
                
    </section>

    <div class="hk-row pa-15">
  <div class="col-sm-12">
    <div class="card">
            <div class="card-body">
                <div class="row ma-0">
                    <div class="col-sm-12 pa-0">
                        <div class="table-wrap">
                          <div class="table-responsive" id="viewbillrecord">
                           @include('salesreport::view_bill_data')
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
               @include('salesreport::view_bill_popup')
               </div>
               <div class="rpopup_values">
               @include('salesreport::view_returnbill_popup')
               </div>

        </div>
        </form>  
          
          </div>
        </form>
        </div>
    </div>  
       



    <script type="text/javascript">
    $(document).ready(function(e){

      //console.log(bill_excel_column_check);

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

      <script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>
      <script src="{{URL::to('/')}}/public/bower_components/sweetalert/js/sweetalert.min.js"></script>
      <script src="{{URL::to('/')}}/public/modulejs/sales/viewbill.js"></script>
      <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/xlsx.full.min.js"></script>


@endsection
</html>