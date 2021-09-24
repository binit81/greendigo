@include('pagetitle')

@extends('master')

@section('main-hk-pg-wrapper')
<link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/sweetalert/css/sweetalert.css">
    <div class="container">
        <div id="modelData" style=""></div>
        <div class="modal fade bs-example-modal-lg" id="ModalCarousel34" tabindex="-1" role="dialog" aria-labelledby="ModalCarousel34" style="padding-right: 17px;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body pa-0">
                        <div id="demo" class="carousel slide" data-ride="carousel">
                            <ul class="carousel-indicators"></ul>
                            <div class="carousel-inner"></div>
                            <a class="carousel-control-prev" href="#demo" data-slide="prev">
                                <span class="carousel-control-prev-icon" style="color: black"></span>
                            </a>
                            <a class="carousel-control-next" href="#demo" data-slide="next">
                                <span class="carousel-control-next-icon" style="color: black"></span>
                            </a>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
       <!--  <?php
        if($role_permissions['permission_export']==1)
        {
        ?> -->
        <!-- <span class="commonbreadcrumbtn badge badge-pill downloadBtn mr-0"  id="download_product_template"><i class="ion ion-md-download"></i>&nbsp;Download Products Template</span></a> -->
       <!--  <?php
        }
        ?> -->
              <!--<?php
            // if($role_permissions['permission_export']==1)
            {
            ?> -->
               <!--  <span class="commonbreadcrumbtn badge exportBtn badge-pill mr-0"  id="product_export"><i class="ion ion-md-download"></i>&nbsp;Download Products Data</span>
                <span class="commonbreadcrumbtn badge exportBtn badge-pill mr-0"  id="product_update_export"><i class="ion ion-md-download"></i>&nbsp;Download Products For Update</span> -->
              <!--<?php
            }
            ?> -->

        <?php
        if($role_permissions['permission_export']==1)
        {
        ?>
        <span class="commonbreadcrumbtn badge badge-pill downloadBtn mr-0" id="product_export">&nbsp<i class="fa fa-upload"></i>&nbsp;
                      Download products data</span></a>
        <?php } ?>
            <?php
            if($role_permissions['permission_add']==1)
            {
            ?>
                 <span class="commonbreadcrumbtn badge badge-primary badge-pill"  id="addnewcollapse">
                    <i class="glyphicon glyphicon-plus"></i>&nbsp;Add New Product</span>
            <?php
            }
            ?>

        <span class="commonbreadcrumbtn badge badge-danger badge-pill"  id="searchCollapse"><i class="glyphicon glyphicon-search"></i>&nbsp;Search</span>

        {{--<button type="button" name="addin_pricemaster" id="addin_pricemaster">Add Product To Price Master</button>--}}

       <!--  <?php
        if($role_permissions['permission_upload']==1) { ?>
        <span class="commonbreadcrumbtn badge exportBtn badge-pill uploadBtn mr-0"  id="upload_product_tempate">&nbsp<i class="fa fa-upload"></i>&nbsp;
        Upload Products</span>

        <span class="commonbreadcrumbtn badge exportBtn badge-pill uploadBtn mr-0"  id="upload_product_update">&nbsp<i class="fa fa-upload"></i>&nbsp;
        Upload Products for update</span>
        <?php } ?> -->

<!-- vrunda -->
        <div class="toggle_breadcrumb"><span class="commonbreadcrumbtn badge uploadBtn badge-pill upload_span_btn mr-0">&nbsp<i class="fa fa-upload"></i>&nbsp;
            Upload</span>
            <ul class="uploadspan_content">
            <?php
                if($role_permissions['permission_upload']==1) { ?>
                <li class="commonbreadcrumbtn badge exportBtn badge-pill uploadBtn mr-0" id="upload_product_tempate">&nbsp<i class="fa fa-upload"></i><span>&nbsp;Upload Products</span></li>
                       <?php } ?>
                <?php
                if($role_permissions['permission_export']==1)
                {
                ?>
                <li class="commonbreadcrumbtn badge badge-pill exportBtn mr-0"  id="download_product_template">
                    <i class="ion ion-md-download" style="margin: 0 10px; font-size: 15px"></i><span>&nbsp;Download Products Template</span></li>
                <?php
                }
                ?>
            <!--<?php
            if($role_permissions['permission_export']==1)
            {
            ?>-->
                <!-- <li class="commonbreadcrumbtn badge exportBtn badge-pill mr-0" id="product_export">&nbsp<i class="fa fa-upload"></i><span>&nbsp;
                      Download products data</span></li> -->
                       <!--  <?php } ?> -->
            </ul>
        </div>


        <?php if($role_permissions['permission_edit'] == 1) { ?>
        <div class="toggle_breadcrumb"><span class="commonbreadcrumbtn badge exportBtn badge-pill update_span_btn mr-0">&nbsp<i class="fa fa-upload"></i>&nbsp;Update</span>
            <ul class="updatespan_content">
            <?php
                if($role_permissions['permission_upload']==1) { ?>
                <li class="commonbreadcrumbtn badge exportBtn badge-pill uploadBtn mr-0" id="upload_product_update">&nbsp<i class="fa fa-upload"></i><span>&nbsp;
                       Upload Products for update</span></li>
                       <?php } ?>
                       <?php
            if($role_permissions['permission_export']==1)
            {
            ?>
                <li class="commonbreadcrumbtn badge exportBtn badge-pill mr-0" id="product_update_export">&nbsp<i class="fa fa-upload"></i><span>&nbsp;
                        Download products for update</span></li>

            <?php } ?>
            </ul>
        </div>
        <?php } ?>
<!-- vrunda -->


        <form name="productform" id="productform"  method="post" enctype="multipart/form-data">
            <meta name="csrf-token" content="{{ csrf_token() }}" />
        <section id="product_block" class="collapse">

            @include('products::product/product_form')
        <div class="row">
            <div class="col-sm-12">
                <div class="hk-row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                               {{-- <input type="hidden" name="type" id="type" value="1" />--}}
                                <button type="submit" name="addproduct" class="btn btn-info saveBtn" id="addproduct" data-container="body" data-toggle="popover" data-placement="bottom" data-content="">Save</button>
                                <button type="button" name="resetproduct" onclick="resetproductdata();" class="btn btn-info resetbtn" id="resetproduct" data-container="body" data-toggle="popover" data-placement="bottom" data-content="">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </form>

    <section class="hk-sec-wrapper collapse" id="filterarea_block">
        <div id="">
            <div class="hk-row common-search">
                <div class="col-md-2 pb-10">
                    <div class="form-group">
                        <input type="text" name-attr="product_name" maxlength="50" autocomplete="off" name="product_name_filter" id="product_name_filter" value="" class="form-control form-inputtext" placeholder="Product Name">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <input type="text" name-attr="barcode" maxlength="50" autocomplete="off" name="barcode_filter" id="barcode_filter" value="" class="form-control form-inputtext" placeholder="Barcode">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <input type="text" name-attr="product_code" maxlength="50" autocomplete="off" name="pcode_filter" id="pcode_filter" value="" class="form-control form-inputtext" placeholder="Product Code">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <input type="text" name-attr="sku_code" maxlength="50" autocomplete="off" name="skucode_filter" id="skucode_filter" value="" class="form-control form-inputtext" placeholder="SKU">
                    </div>
                </div>
                <!-- <div class="col-md-2">
                    <div class="form-group">
                        <select name-attr="brand_id" class="form-control form-inputtext" name="brand_id_filter" id="brand_id_filter"></select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select name-attr="category_id" class="form-control form-inputtext" onchange="getsubcategory_filter()" name="category_id_filter" id="category_id_filter"></select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select name-attr="subcategory_id" class="form-control form-inputtext" name="subcategory_id_filter" id="subcategory_id_filter"></select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select name-attr="colour_id" class="form-control form-inputtext" name="colour_id_filter" id="colour_id_filter"></select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select name-attr="size_id" class="form-control form-inputtext" name="size_id_filter" id="size_id_filter"></select>
                    </div>
                </div> -->

                 @foreach($product_features AS  $product_features_key=>$product_features_value)
                        <div class="col-md-2">
                            <!-- <label class="form-label">{{$product_features_value->product_features_name}}</label> -->
                            <select class="form-control form-inputtext" name-attr="{{$product_features_value->html_id}}" id="{{$product_features_value->html_id}}" name="{{$product_features_value->html_name}}" value="" >
                                <option value="">Select {{$product_features_value->product_features_name}} </option>
                                @foreach($product_features_value->product_features_data AS  $kk=>$vv)
                                <option value="{{$vv->product_features_data_id}}">{{$vv->product_features_data_value}}</option>

                                @endforeach
                            </select>

                        </div>
                         @endforeach
                <div class="col-md-2">
                    <div class="form-group">
                        <select name-attr="uqc_id" class="form-control form-inputtext" name="uqc_id_filter" id="uqc_id_filter"></select>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-info searchBtn search_data"  id="search_product"><i class="fa fa-search"></i>Search</button>
                    <button type="button" name="resetfilter" onclick="resetproductfilterdata();" class="btn btn-info resetbtn" id="resetfilter" data-container="body" data-toggle="popover" data-placement="bottom" data-content="" data-original-title="" title="">Reset</button>
                </div>
            </div>
        </div>

    </section>

    <section class="hk-sec-wrapper" id="productmaintable">

        <div class="hk-row">
            <div class="col-md-2">
                <?php
                if($role_permissions['permission_delete']==1)
                {
                ?>
                    <a id="deleteproduct" name="deleteproduct"><i class="fa fa-trash cursor" style="font-size: 20px;color: red;margin-left: 20px"></i></a>
                <?php
                }
                ?>
            </div>
        </div>


        <div class="table-wrap">
            <div class="table-responsive" id="productrecord">
                @include('products::product/product_data')
            </div>
        </div><!--table-wrap-->

    </section>
        <div id="styleSelector">
    </div>

   <!--  <div class="modal fade" id="addbrandpopup">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Brand</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="container"></div>
                <form id="brandform">
                    <input type="hidden" name="brand_id" value="" id="brand_id">
                    <div class="modal-body">

                        <label class="form-label">Brand Name</label>
                        <input class="form-control form-inputtext" autocomplete="off" name="brand_type" id="brand_type"
                               maxlength="100" type="text" placeholder=" ">
                    </div>

                    <div class="modal-footer">
                        <a href="#" data-dismiss="modal" class="btn">Close</a>
                        <button type="button" id="savebrand" class="btn btn-info">Save Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addcategorypopup">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Category</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="container"></div>
                <form id="categoryform">


                    <input type="hidden" name="category_id" onchange="getsubcategory('');" value="" id="category_id">
                    <div class="modal-body">

                        <label class="form-label">Category Name</label>
                        <input class="form-control form-inputtext" autocomplete="off" name="category_name"
                               id="category_name" maxlength="100" type="text" placeholder=" ">


                    </div>

                    <div class="modal-footer"><a href="#" data-dismiss="modal" class="btn">Close</a>
                        <button type="button" id="savecategory" class="btn btn-info">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addsubcategorypopup">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Subcategory</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="container"></div>
                <form id="subcategoryform">


                    <input type="hidden" name="subcategory_id" value="" id="subcategory_id">
                    <div class="modal-body">


                        <select class="form-control form-inputtext" name="popcategory_id" id="popcategory_id">
                        </select>


                        <label class="form-label">Subcategory Name</label>
                        <input class="form-control form-inputtext" autocomplete="off" name="subcategory_name"
                               id="subcategory_name" maxlength="100" type="text" placeholder=" ">


                    </div>

                    <div class="modal-footer"><a href="#" data-dismiss="modal" class="btn">Close</a>
                        <button type="button" id="savesubcategory" class="btn btn-info">Save Subcategory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addcolourpopup">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Colour</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="container"></div>
                <form id="colourform">


                    <input type="hidden" name="colour_id" value="" id="colour_id">
                    <div class="modal-body">
                        <label class="form-label">Colour Name</label>
                        <input class="form-control form-inputtext" autocomplete="off" name="colour_name"
                               id="colour_name" maxlength="100" type="text" placeholder=" ">


                    </div>
                    <div class="modal-footer"><a href="#" data-dismiss="modal" class="btn">Close</a>
                        <button type="button" id="savecolour" class="btn btn-info">Save Colour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addsizepopup">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Size</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="container"></div>
                <form id="sizeform">


                    <input type="hidden" name="size_id" value="" id="size_id">
                    <div class="modal-body">
                        <div class="input-group input-group-default floating-label">
                            <label class="form-label"> Size Name</label>
                            <input class="form-control form-inputtext" autocomplete="off" name="size_name"
                                   id="size_name" maxlength="100" type="text" placeholder=" ">

                        </div>

                        <span id="sizeerr" style="color: red;font-size: 15px"></span>
                    </div>

                    <div class="modal-footer"><a href="#" data-dismiss="modal" class="btn">Close</a>
                        <button type="button" id="savesize" class="btn btn-primary">Save Size</button>
                    </div>
                </form>
            </div>
        </div>
    </div> -->

<!-- DYNAMIC POPUP FOR ADD PRODUCT FEATURES -->

    {{--popup for get pwd when want to update mrp and offer price--}}
        <div class="modal fade" id="get_pwd">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Verification Process</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="container"></div>
                    <form id="brandform">
                        <div class="modal-body">
                            <label class="form-label">Enter Your Current Login Password</label>
                            <input class="form-control form-inputtext" autocomplete="off" name="current_pwd" id="current_pwd" maxlength="100" type="text" placeholder=" ">
                        </div>

                        <div class="modal-footer">
                            <a href="#" data-dismiss="modal" class="btn">Close</a>
                            <button type="button" id="check_pwd" class="btn btn-info">Continue</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


<div class="modal fade" id="addproducts_features">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title dnamic_feature_title" ></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="container"></div>
                <form id="productfeaturesform">

                    <input type="hidden" name="product_features_id" value="" id="product_features_id">

                    <input type="hidden" name="dynamic_product_features" value="" id="dynamic_product_features">

                    <div class="modal-body">
                        <div class="col-md-2 parentshow" style="display:none">
                            <label class="form-label">Parent</label>
                            <select class="form-control form-inputtext" name="parent" id="parent"></select>
                        </div>
                        <div class="input-group input-group-default floating-label">
                            <label class="form-label dnamic_feature_name"> </label>
                            <input class="form-control form-inputtext invalid" autocomplete="off" name="product_features_data_value" id="product_features_data_value" maxlength="100" type="text" placeholder=" ">
                        </div>

                       {{-- <div class="input-group input-group-default floating-label">
                            <label class="form-label product_features_data_url">Product Features Data Url</label>
                            <input class="form-control form-inputtext" autocomplete="off" name="product_features_data_url"
                                   id="product_features_data_url" maxlength="100" type="text" placeholder=" ">
                        </div>

                        <div class="input-group input-group-default floating-label">
                            <label class="form-label product_features_data_url">Feature Content</label>
                            <input class="form-control form-inputtext" autocomplete="off" name="feature_content"
                                   id="feature_content" maxlength="100" type="text" placeholder=" ">
                        </div>

                        <div class="input-group input-group-default floating-label">
                            <label class="form-label product_features_data_url">Product Features Data Image</label>
                        <input type="file" name="product_features_data_image" id="product_features_data_image" onchange="productfeature_image_validation(this);">
                            <div class="imgblock" id="image_block" style="display: none">
                          <img src="" id="productfeature_image_preview" class="src_feature"  width="100%" height="200px">

                          <input type="hidden" class="json_val" name="image_json" id="image_json">
                            <input type="hidden" class="img_name" name="image_name" id="image_name">
                            </div>
                        </div>


                        <div class="input-group input-group-default floating-label">
                            <label class="form-label product_features_data_url">Product Features Banner Image</label>
                            <input type="file" name="product_features_banner_image" id="product_features_banner_image" onchange="productfeature_image_validation(this);">
                            <div class="imgblock" id="image_block" style="display: none">
                                <img src="" id="productfeature_image_preview" class="src_feature"  width="100%" height="200px">

                                <input type="hidden" class="json_val" name="banner_image_json" id="banner_image_json">
                                <input type="hidden" class="img_name" name="banner_image_name" id="banner_image_name">
                            </div>
                        </div>--}}

                        <span id="sizeerr" style="color: red;font-size: 15px"></span>
                    </div>

                    <div class="modal-footer"><a href="#" data-dismiss="modal" class="btn">Close</a>
                        <button type="button" id="productfeaturessave"  class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<!-- END -->

    <div class="modal fade" id="adduqcpopup">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add UQC</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="container"></div>
                <form id="uqcform">
                    <input type="hidden" name="uqc_id" value="" id="uqc_id">
                    <div class="modal-body">
                        <div class="input-group input-group-default floating-label">
                            <input class="form-control form-inputtext" autocomplete="off" name="uqc_name" id="uqc_name" maxlength="100" type="text" placeholder=" ">
                            <label class="form-label">UQC Name</label>
                        </div>
                        <div class="input-group input-group-default floating-label">
                            <input class="form-control form-inputtext" autocomplete="off" name="uqc_type" id="uqc_type" maxlength="100" type="text" placeholder=" ">
                            <label class="form-label">UQC Type</label>
                        </div>
                        <div class="input-group input-group-default floating-label">
                            <input class="form-control form-inputtext" autocomplete="off" name="uqc_shortname" id="uqc_shortname" maxlength="100" type="text" placeholder=" ">
                            <label class="form-label">UQC Name</label>
                        </div>
                        <span id="uqcerr" style="color: red;font-size: 15px"></span>
                    </div>
                    <div class="modal-footer"><a href="#" data-dismiss="modal" class="btn">Close</a>
                        <button type="button" id="saveuqc" class="btn btn-primary">Save UQC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


        <div class="modal fade" id="upload_products_popup" style="border:1px solid !important;">
            <div class="modal-dialog" style="max-width:30% !important;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Products(Excel File)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <span class="badge badge-pill downloadBtn mt-10" style="width:54%;cursor:pointer;color:#ffffff;margin-left: 178px" id="download_product_template">
                        <i class="ion ion-md-download"></i>&nbsp;Download Products Template</span>
                    <br>
                    <div class="row">
                        <div class="col-sm">
                            <div class="row">
                                <div class="card">
                                    <div class="card-body">
                                        <input type="file" class="" id="productsfileUpload"  accept=".xlsx, .xls" />
                                        <button type="button"  class="btn btn-info btn-block mt-10 uploadBtn" name="uploadproducts" id="uploadproducts" >
                                            <i class="ion ion-md-cloud-upload"></i>&nbsp;Upload</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>


        <div class="modal fade" id="product_type_popup">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Product Type</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="container"></div>
                    <form>
                    <div class="col-md-12">
                        <input type="radio" name="product_type"  id="regular_product" value="1">
                        <span style="font-size: 16px;color: black">Regular Product</span>
                        <input type="radio" name="product_type" id="unique_product" value="3">
                        <span style="font-size: 16px;color: black">Unique Product</span>
                    </div>
                    </form>
                </div>
            </div>
        </div>


        <div class="modal fade" id="upload_products_update_popup" style="border:1px solid !important;">
            <div class="modal-dialog" style="max-width:30% !important;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Products For Update(Excel File)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <span class="badge badge-pill downloadBtn mt-10" style="width:54%;cursor:pointer;color:#ffffff;margin-left: 178px"  id="product_update_export">
                        <i class="ion ion-md-download"></i>Download Products For Update</span>
                    <br>
                    <div class="row">
                        <div class="col-sm">
                            <div class="row">
                                <div class="card">
                                    <div class="card-body">
                                        <input type="file" class="" id="productsupdatefileUpload"  accept=".xlsx, .xls" />
                                        <button type="button"  class="btn btn-info btn-block mt-10 uploadBtn" name="uploadproductsupdate" id="uploadproductsupdate" >
                                            <i class="ion ion-md-cloud-upload"></i>&nbsp;Upload</button>
                                    </div>
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

    <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/xlsx.full.min.js"></script>
    <script src="{{URL::to('/')}}/public/bower_components/sweetalert/js/sweetalert.min.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/product/product.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/product/productproperties.js"></script>
@endsection

