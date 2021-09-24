@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>

<!-- Page Popup -->
<div class="modal fade" id="PagePopup" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="PagePopup" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
    	<form method="post" id="addEditPage">
	        <div class="modal-content">
	            <div class="modal-header">
	                <h5 class="modal-title">Edit Page</h5>
	                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	                    <span aria-hidden="true">×</span>
	                </button>
	            </div>
	            <div class="modal-body">
	                <div class="row">
	                    <div class="col-md-12">

	                        <div class="row">
	                            <div class="col-md-3 rightAlign">Page Name:</div>
	                            <div class="col-md-9">
	                                <input type="text" name="product_features_name" id="product_features_name" class="form-control form-inputtext" placeholder="Page Name">
	                            </div>
	                        </div>

	                        <div class="row">
	                            <div class="col-md-3 rightAlign">Page URL:</div>
	                            <div class="col-md-9">
	                                <input type="text" name="feature_url" id="feature_url" class="form-control form-inputtext" readonly placeholder="Page URL">
	                            </div>
	                        </div>

	                        <div class="row">
	                            <div class="col-md-3 rightAlign">Page Content:</div>
	                            <div class="col-md-9">
	                                <textarea class="form-control" name="feature_content" id="feature_content"></textarea>
	                            </div>
	                        </div>

	                    </div>

	                  
	                </div>

	            </div>
	            <div class="modal-footer">

	                <div class="col-md-6" style="text-align:right">
	                	<input type="hidden" class="alertStatus" value="0" />
	                    <input type="hidden" name="product_features_id" id="product_features_id" />
	                    <input type="hidden" name="html_name" id="html_name" class="form-control form-inputtext" placeholder="HTML Name">
	                   	<input type="hidden" name="html_id" id="html_id" class="form-control form-inputtext" placeholder="HTML ID">

	                    <button type="submit" name="pageBtn" id="pageBtn" class="btn btn-primary">Save</button>
	                </div>
	            </div>
	        </div>
    	</form>
    </div>
</div>
<!-- Page Popup -->

<div class="container ml-20" style="width:97%">
	<div class="row pa-0 ma-0">
	    <div class="col-sm pa-0">
	        <div class="table-wrap">
	            <div class="table-responsive">
					@include('ecommerce::ecommerce/view_pages_data')
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="{{URL::to('/')}}/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>

<script type="text/javascript">
    CKEDITOR.replace('feature_content', {
    height: ['180px']
});
    CKEDITOR.config.allowedContent = true;
</script>

<style>
.form-control[readonly]{
	border: #ddd 1px solid !important; 
	background: transparent; 
	color: #000000; 
	font-weight: normal;
}
.modal-dialog
{
	max-width: 80% !important;
}
</style>

<script src="{{URL::to('/')}}/public/modulejs/eCommerce/eCommerce.js"></script>
@endsection