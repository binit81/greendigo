@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<div class="container">
   <div class="hk-row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-wrap">
                        <div class="table-responsive" id="view_store_data">
                        @include('store_profile::store_profile/view_store_data')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewstorepopup" style="border:1px solid !important;">
    <div class="modal-dialog" style="max-width:90% !important;">
        <form id="storeform">
            <div class="modal-content">
                <div class="modal-header" style="Padding: 0.50rem 0.25rem 0 0.25rem !important;">
                    <div class="row ma-0">
                        <div class="col-sm">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <button class="btn btn-primary" id="rpreviousinvoice" style="color:#fff !important;cursor:pointer;" type="button">Previous</button>
                                        <button class="btn btn-primary" id="rnextinvoice" style="color:#fff !important;cursor:pointer;" type="button">Next</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <center><h5 class="modal-title">Store Details : <span class="invoiceno"></span></h5></center>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" style="float:right;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
                </button>
                </div>
                <br>
                <div id="store_values">
                    @include('store_profile::store_profile/view_popup')
                </div>
                <br>
            </div>
        </form>
    </div>
</div>
@endsection
<script src="{{URL::to('/')}}/public/modulejs/Store_Profile/store_profile.js"></script>
<script src="{{URL::to('/')}}/public/template/bootstrap/dist/js/bootstrap.min.js"></script>
