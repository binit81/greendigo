@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>

<div class="container ml-20" style="width:97%">


	<span class="commonbreadcrumbtn badge badge-primary badge-pill" onclick="addnewFeature()"><i class="glyphicon glyphicon-plus"></i>&nbsp;Add New Feature</span>


	<!-- Feature Popup -->
	<div class="modal fade" id="featurePopup" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="featurePopup" aria-hidden="true">
	    <div class="modal-dialog modal-lg" role="document">
	    	<form method="post" id="addEditFeature" enctype="multipart/form-data">
		        <div class="modal-content">
		            <div class="modal-header">
		                <h5 class="modal-title"></h5>
		                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		                    <span aria-hidden="true">×</span>
		                </button>
		            </div>
		            <div class="modal-body">
		                <div class="row">
		                    <div class="col-md-12">

		                    	<div class="row">
		                            <div class="col-md-3 rightAlign">Type:</div>
		                            <div class="col-md-9 leftAlign form-inputtext">
		                                <input type="radio" name="feature_type" id="feature_type_1" value="1" checked>&nbsp;Product Feature
		                                <input type="radio" name="feature_type" id="feature_type_2" value="2">&nbsp;Pages
		                            </div>
		                        </div>

		                        <div class="row Featurelocation " style="display:none;">
		                            <div class="col-md-3 rightAlign">Location:</div>
		                            <div class="col-md-9 leftAlign form-inputtext">
		                                <input type="radio" name="feature_location" id="feature_location_1" value="1">&nbsp;Top Menu
		                                <input type="radio" name="feature_location" id="feature_location_2" value="2" checked>&nbsp;Main Menu
		                                <input type="radio" name="feature_location" id="feature_location_3" value="3">&nbsp;Footer Menu
		                            </div>
		                        </div>

		                    	<div class="row FeatureSubMenu">
		                            <div class="col-md-3 rightAlign">Select Relation:</div>
		                            <div class="col-md-9">
		                                <select name="parentChild" id="parentChild" class="form-control form-inputtext">
		                                	<option value="">Main Item Root</option>
		                                	@foreach($result AS $resultkey => $value)
		                                	<option value="{{$value->product_features_id}}" style="font-weight:bold;">{{$value->product_features_name}}</option>
		                                		@foreach($value->product_features_data AS $resultkey_1 => $value_1)
		                                		<option value="{{$value->product_features_id}}_{{$value_1->product_features_data_id}}" disabled> --- {{$value_1->product_features_data_value}}</option>
		                                		@endforeach
		                                	@endforeach
		                                </select>
		                            </div>
		                        </div>

		                        <div class="row Featurelevel1">
		                            <div class="col-md-3 rightAlign">HTML Name:</div>
		                            <div class="col-md-9">
		                                <input type="text" name="html_name" id="html_name" class="form-control form-inputtext invalid" placeholder="Html Name" value="dynamic_">
		                            </div>
		                        </div>

		                        <div class="row Featurelevel1">
		                            <div class="col-md-3 rightAlign">HTML ID:</div>
		                            <div class="col-md-9">
		                                <input type="text" name="html_id" id="html_id" class="form-control form-inputtext invalid" value="dynamic_" placeholder="HTML ID">
		                            </div>
		                        </div>

		                        <div class="row">
		                            <div class="col-md-3 rightAlign">Display Name:</div>
		                            <div class="col-md-9">
		                                <input type="text" name="product_features_name" id="product_features_name" class="form-control form-inputtext invalid" placeholder="Display Name">
		                            </div>
		                        </div>

		                        <div class="row">
		                            <div class="col-md-3 rightAlign">Display URL:</div>
		                            <div class="col-md-9">
		                                <input type="text" name="feature_url" id="feature_url" class="form-control form-inputtext invalid" readonly style="border:1px solid #ddd; color:#333333;" placeholder="Display URL">
		                            </div>
		                        </div>

		                        <div class="row Featurelevel2" style="display:none;">
		                            <div class="col-md-3 rightAlign">Image:</div>
		                            <div class="col-md-9">
		                                <input type="file" class="form-control form-inputtext" name="product_features_data_image" id="product_features_data_image">
		                            </div>
		                        </div>

		                        <div class="row Featurelevel2" style="display:none;">
		                            <div class="col-md-3 rightAlign">Banner Image:</div>
		                            <div class="col-md-9">
		                                <input type="file" class="form-control form-inputtext" name="product_features_banner_image" id="product_features_banner_image">
		                            </div>
		                        </div>

		                        <div class="row">
		                            <div class="col-md-3 rightAlign">Content:</div>
		                            <div class="col-md-9">
		                                <textarea class="form-control" name="feature_content" id="feature_content"></textarea>
		                            </div>
		                        </div>

		                        <div class="row FeatureUrls" style="margin-top:10px !important;">
		                            <div class="col-md-3 rightAlign">Show Feature in Pages:</div>
		                            <div class="col-md-9">
		                                <input type="text" name="show_feature_url" id="show_feature_url" class="form-control form-inputtext" placeholder="for multiple in (,) commas">
		                            </div>
		                        </div>

		                    </div>

		                  
		                </div>

		            </div>
		            <div class="modal-footer">

		                <div class="col-md-6" style="text-align:right">
		                	<input type="hidden" class="alertStatus" value="0" />
		                    <input type="hidden" name="product_features_id" id="product_features_id" />
		                    <input type="hidden" name="product_features_data_id" id="product_features_data_id" />

		                    <button type="submit" name="pageBtn" id="pageBtn" class="btn btn-primary">Submit</button>
		                </div>
		            </div>
		        </div>
	    	</form>
	    </div>
	</div>
	<!-- Feature Popup -->

	<div class="row pa-0 ma-0">
	    <div class="col-sm pa-0">
	        <div class="table-wrap">
	            <div class="table-responsive">
					@include('ecommerce::ecommerce/view_features_data')
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

<style type="text/css">
.modal-dialog
{
	max-width: 80% !important;
}
</style>

<script src="{{URL::to('/')}}/public/modulejs/eCommerce/eCommerce.js"></script>
@endsection