@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')

    <div class="container">



    <div class="col-xl-12">
        <div class="hk-row">
            <div class="card">
                <div class="card-body">
                    <div class="hk-row">

                    </div>

                    <div class="table-wrap">
                       <div class="table-responsive" id="referral_points_table">
                            @include("referral_points::referral_points_data")
                       </div>
                        <div class="col-md-4 pt-25">
                            <?php
                            if($role_permissions['permission_add']==1)
                            {
                            ?>
                            <button type="button" name="add_referral_point" class="btn btn-info addbutton saveBtn" id="add_referral_point" data-container="body" data-toggle="popover" data-placement="bottom" data-content="">
                                <i class="fa fa-save"></i>Add Referral</button>

                            <?php
                            }
                            ?>
                            <button type="button" name="cancelgstslab"  class="btn btn-info resetbtn" id="cancelgstslab" data-container="body" data-toggle="popover" data-placement="bottom" data-content="">Reset</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>

    <script src="{{URL::to('/')}}/public/modulejs/common.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/referral_points/referral_points.js"></script>




@endsection
