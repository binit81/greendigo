<?php
/**
 * Created by PhpStorm.
 * User: Hemaxi
 * Date: 13/3/19
 * Time: 11:45 AM
 */

?>
@include('pagetitle')
@extends('master')
@section('main-hk-pg-wrapper')

    <?php
    $tax_name = "GST";
    if($nav_type[0]['tax_type'] == 1)
    {
        $tax_name = $nav_type[0]['tax_title'];
    }
    ?>
        <div class="col-xl-8">

            <div class="hk-row">
                <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="hk-sec-title">Add Product Age Range</h5>

        <form name="productagerangeform" id="productagerangeform">
            <input type="hidden" name="productage_range_id" id="productage_range_id">



                <div class="col-sm">

                    <div class="row">

                        <div class="col-md-3">
                            <label class="form-label">Range From(Days)</label>
                            <input type="text" name="range_from" id="range_from" value="" maxlength="3" class="form-control form-inputtext number invalid" placeholder="" autofocus>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Range To(Days)</label>
                            <input type="text" name="range_to" id="range_to" value="" maxlength="3" class="form-control form-inputtext number invalid" placeholder="">
                        </div>


                        <div class="col-md-6 pt-25">
                    <?php
                        if($role_permissions['permission_add']==1)
                        {
                        ?>
                        <button type="button" name="addagerange" class="btn btn-info addbutton saveBtn" id="addagerange" data-container="body" data-toggle="popover" data-placement="bottom" data-content=""><i class="fa fa-save"></i>Add Age Range</button>

                    <?php
                        }
                        ?>
                        <button type="button" name="cancelagerange"  class="btn btn-info resetbtn" id="cancelagerange" data-container="body" data-toggle="popover" data-placement="bottom" data-content="">Reset</button>
                    </div>
                    </div>
                </div>


         </form>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="hk-row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="hk-sec-title">Product Age Range List</h5>
                            <div class="row" >
                                <div class="col-md-9">
                                    <?php
                                    if($role_permissions['permission_delete']==1)
                                    {
                                    ?>
                                        <a name="deleteagerange" id="deleteagerange"><i class="fa fa-trash" style="font-size: 20px;color: red;margin-left: 20px"></i></a>
                                        <?php
                                    }
                                    ?>
                                </div>

                                <div class="col-md-3 common-search">
                                    <div class="form-group">
                                        <!-- <input type="text" name-attr="serach" name="serach" id="serach" placeholder="search in records" class="form-control form-inputtext number" /> -->
                                    </div>
                                </div>
                            </div>

                         <div class="table-wrap">
                             <div class="table-responsive" id="tableagerangerecord">
                             @include('productage_range::productage_rangedata')
                             </div>
                        </div><!--table-wrap-->
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

    <script src="{{URL::to('/')}}/public/modulejs/productage_range/productage_range.js"></script>
@endsection
