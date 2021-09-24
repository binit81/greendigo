@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<style type="text/css">
.col-md-7,.col-md-5{
    padding-left:0 !important;
    padding-right:0 !important;
}
.table thead tr.header th {
}
.table tbody tr td {
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
#product_detail_record .floating-input, #product_detail_record select, #charges_record .floating-input{
    padding: 5px;
    width:90%;
    border-radius: 5px;
    background: #f3f3f3;
    font-weight: normal;
}
#product_detail_record{
    font-size: 14px;
}
</style>
<link rel="stylesheet" href="{{URL::to('/')}}/public/build/css/intlTelInput.css">
<link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/bootstrap-datepicker/css/bootstrap-datepicker.css">

<div class="container">
    <div class="row">
	    <div class="col-md-9" style="margin-bottom:-30px;">
	        <div class="row">

	    	</div>
	    </div>
	    <div class="col-md-3" style="margin-top:-47px;">
	        <div class="row" style="float:right; margin-right:-55px;">
	         	<div class="col-md-2 rightAlign" style="font-size:13px; line-height:1.8em;">Date</div>
	            <div class="col-md-4 pa-0">
                    <input class="form-control invalid invoiceNo" value="" autocomplete="off" type="text" name="transfer_date" id="transfer_date" placeholder=" ">
	                <input type="hidden" class="form-control mt-15" placeholder="" name="invoice_no" id="invoice_no" autocomplete="off" value="" readonly style="color:#000;">
	            </div>

	    	</div>
	    </div>
	</div>
	<form name="stocktransfer" id="stocktransfer" method="POST">
		<input type="hidden" name="stock_transfer_id" id="stock_transfer_id" value="">
		<input type="hidden" name="warehouse_id" id="warehouse_id" value="">
		<input type='hidden' class='form-control' value='0.00' id='total_igst' name='total_igst'>
		<input type="hidden" class="form-control" value="0.00" id="total_sellingprice" name="total_sellingprice">
		<input type="hidden" class="form-control" value="0.00" id="total_offerprice" name="total_offerprice">
	    <div class="row ma-0">
	        <div class="col-sm-9">
	        	<div class="hk-row">
	                <div class="col-md-12">
	                    <div class="card pa-0 ma-0">
	                        <div class="card-body pa-10">
	                            <div class="table-wrap">
	                                <div class="row pa-5">
	                                 	<div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-prepend">
                                                	<label class="input-group-text searchicon" style="height: 40px;"><i class="fa fa-search"></i></label>
                                                </span>
                                            	<input class="form-control form-inputtext typeahead" value="" maxlength="" type="text" name="stockproductsearch" id="stockproductsearch" placeholder="Enter Barcode/Product Code/Product Name" data-provide="typeahead" data-items="10" data-source="">
                                            </div>
	                                    </div>
	                                    <div class="col-md-2">
	                                        <select class="form-control form-inputtext invalid"
	                                                style="" name="store_id"
	                                                id="store_id">
	                                                <option value="">Select Store</option>
	                                                @foreach($get_store AS $storekey=>$storevalue)
                                               			<option value="{{$storevalue->store_id}}">{{$storevalue->company_profile->full_name}}</option>
	                                                @endforeach
	                                        </select>
	                                    </div>
	                                </div>
                                    <div class="table-responsive pa-0 ma-0">
                                        <table width="100%" border="0">
                                            <thead>
                                                <tr class="blue_Head">
                                                    <th class="pa-10 leftAlign"><span class="bold itemfocus"><span class="titems">0</span></span><span class="plural">Item</span></th>
                                                    <th>Barcode</th>
                                                    <th>Size</th>
                                                    <th>Colour</th>
                                                    <th>UQC</th>
                                                    <th>Stock</th>
													<th>Batch No</th>
                                                    <th class="rightAlign" style="width:10%">Offer price</th>
                                                    <th class="rightAlign" style="width:10%">Rate</th>
                                                    <th class="center" style="width:5%">Qty.</th>
                                                    <th class="rightAlign" style="width:10%">Total</th>
                                                    <th>&nbsp;</th>
                                                </tr>
                                            </thead>
                                           	<tbody id="product_detail_record">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
	                        </div>
	                	</div>
	                </div>
	            </div>
			</div>
			<div class="col-sm-3 pa-0">
				<div class="hk-row">
			    	<div class="col-sm-12 pa-0">
			        	<div class="card pa-10">
			        		<div class="row pl-0 ma-0" id="totalamtdiv">
			            		<div class="row pa-0 ma-0">
			            			<div class="col-sm-6 bold rightBorder" style="background:#f3f3f3;">
			                			<h6 class="bold pt-10 ml-0">Total Qty</h6>
						            	<div class="row">
						                	<input type="text" style="font-size: 24px;width:200px;padding-left:15px;" class="form-control mt-15 ml-0 greencolor" value="0" readonly="" id="overallqty" name="overallqty" tabindex="-1">
						            	</div>
						        	</div>
			            			<div class="col-sm-6 bold ma-0" style="background:#f3f3f3;">
			                			<h6 class="bold pt-10 ml-0 ma-0">Total Payable</h6>
					                	<div class="row ma-0">
					                    	<input type="hidden" style="font-size: 24px;" class="form-control mt-15" value="0.00" readonly="" id="ggrand_total" name="ggrand_total">
					                    	<input type="text" style="font-size: 24px;width:100px; position:unset;padding-left:10px;" class="form-control mt-5 ml-0 mb-5 pb-10 redcolor cursor redinformative popover" value="0.00" readonly="" id="sggrand_total" tabindex="-1" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="bottom" data-content="" title="" data-html="true"  tabindex="0">
				            		    </div>
			            			</div>
			       				</div>
			    			</div>
						</div>
						<div class="row pa-0 pt-20 ma-0">
						    <div class="col-sm-6 pa-0 pr-5">
						        <button type="button" class="btn btn-success saveBtn btn-block" name="addbillingprint" id="addbillingprint"><i class="fa fa-print"></i>Save & Print</button>
						    </div>
						    <div class="col-sm-6 pa-0 pl-5">
						        <button type="button" class="btn btn-info savenewBtn btn-block" name="addstock" id="addstock"><i class="fa fa-save"></i>Save & New</button>
						    </div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>


<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>

<script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>
<script src="{{URL::to('/')}}/public/modulejs/Stock_Transfer/stock_transfer.js"></script>

<script src="{{URL::to('/')}}/public/modulejs/common.js"></script>
@endsection
