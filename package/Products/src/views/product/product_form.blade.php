<?php

?>
<div class="row ml-0">

                <input type="hidden" name="product_id" id="product_id" value="">
                <input type="hidden" name="type" id="type" value="">
                <input type="hidden" name="excel_file_type" id="excel_file_type" value="">
                <input type="hidden" name="inward_type" id="inward_type" value="{{$inward_type}}">
                <input type="hidden" name="encoded_product_id" id="encoded_product_id" value="">

                <div class="col-sm-12">
                    <div class="hk-row">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="form-label">Product Name</label>
                                            <input class="form-control form-inputtext invalid" value="" name="product_name" id="product_name" type="text" placeholder=" ">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Note</label>
                                            <textarea name="product_note" id="product_note" class="form-control"></textarea>
                                        </div>

                                        {{-- <div class="col-md-12">
                                            <label class="form-label">Product Type</label>
                                            <input type="radio" name="product_type" checked id="fmcgproduct" value="1">
                                            <span style="font-size: 16px;color: black">FMCG</span>
                                            <input type="radio" name="product_type" id="garmentproduct" value="2">
                                            <span style="font-size: 16px;color: black">GARMENT</span>
                                        </div>--}}
</div>
</div>
</div>
</div>
 <?php if(isset($nav_type[0]['product_calculation']) && $nav_type[0]['product_calculation'] != 3) { ?>
<div class="col-md-9">
    @include('products::product/product_calculation')
</div>
<?php } ?>
</div>
</div>

<div class="col-sm-12">
    <div class="hk-row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5>Optional</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">SKU</label>
                            <input class="form-control form-inputtext" value="" maxlength="" autocomplete="off" type="text" name="sku_code" id="sku_code" placeholder=" ">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Product Code</label>
                            <input class="form-control form-inputtext" value="" maxlength="" autocomplete="off" type="text" name="product_code" id="product_code" placeholder=" ">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Product Description</label>
                            <input class="form-control form-inputtext" value="" maxlength="" autocomplete="off" type="text" name="product_description" id="product_description" placeholder=" ">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">HSN</label>
                            <input class="form-control form-inputtext number" value="" maxlength="" autocomplete="off" type="text" name="hsn_sac_code" id="hsn_sac_code" placeholder=" ">
                        </div>
                        <!-- <div class="col-md-2">
                            <label class="form-label">Brand</label>
                            <select class="form-control form-inputtext" name="brand_id" id="brand_id">
                            </select>
                        </div>
                        <div class="col-md-1 mt-25">

			     <a id="addbrand" class=" addmoreoption"><i class="fa fa-plus plusaddmore"></i></a>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Category</label>
                            <select class="form-control form-inputtext" onchange="getsubcategory('')" name="category_id" id="category_id">
                            </select>
                        </div>
                        <div class="col-md-1 mt-25">
                            <a id="addcategory" class=" addmoreoption"><i class="fa fa-plus plusaddmore"></i></a>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sub Category</label>
                            <select class="form-control form-inputtext" name="subcategory_id" id="subcategory_id">
                            </select>
                        </div>
                        <div class="col-md-1 mt-25">
                            <a id="addsubcategory" class=" addmoreoption"><i
                                                                 class="fa fa-plus plusaddmore"></i></a>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Colour</label>
                            <select class="form-control form-inputtext" name="colour_id" id="colour_id">
                            </select>
                        </div>
                        <div class="col-md-1 mt-25">
                            <a id="addcolour" class=" addmoreoption"><i
                                                                 class="fa fa-plus plusaddmore"></i></a>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Size</label>
                            <select class="form-control form-inputtext" name="size_id" id="size_id">
                            </select>
                        </div>
                        <div class="col-md-1 mt-25">
                           <a id="addsize" class=" addmoreoption"><i
                                                                class="fa fa-plus plusaddmore"></i></a>
                        </div> -->
                         @foreach($product_features AS  $product_features_key=>$product_features_value)
                        <div class="col-md-2">
                            <label class="form-label">{{$product_features_value->product_features_name}}</label>
                            <select class="form-control form-inputtext" id="{{$product_features_value->html_id}}" name="{{$product_features_value->html_name}}" value="" >
                                <option value="">Select {{$product_features_value->product_features_name}} </option>
                                @foreach($product_features_value->product_features_data AS  $kk=>$vv)
                                <option value="{{$vv->product_features_data_id}}">{{$vv->product_features_data_value}}</option>

                                @endforeach
                            </select>

                        </div>
                         <div class="col-md-1 mt-25">
                                 <a id="addproductfeatures" onclick="opendynamicpopup(
                                    '<?php
                                 echo $product_features_value['product_features_id']
                                 ?>','<?php echo  $product_features_value['product_features_name']?>')" class=" addmoreoption"><i class="fa fa-plus plusaddmore"></i></a>

                                 <input type="hidden" name="test_dynamic_name_<?php
                                 echo $product_features_value['product_features_id']
                                 ?>" id="test_dynamic_name_<?php
                                 echo $product_features_value['product_features_id']
                                 ?>" value="<?php echo $product_features_value['html_id']?>">
                         </div>
                         @endforeach
                        <div class="col-md-2">
                            <label class="form-label">UQC</label>
                            <select class="form-control form-inputtext" name="uqc_id" id="uqc_id">
                            </select>
                        </div>
                        <div class="col-md-1 mt-25">
                            {{--<button type="button" class="btn btn-info addmoreoption" id="adduqc" name="adduqc"><i class="fa fa-plus"></i></button>--}}
                        </div>
                        {{--<div class="col-md-3">
                            <label class="form-label">Material</label>
                            <input class="form-control form-inputtext" value="" maxlength="10"
                                   autocomplete="off" type="text" name="material_id" id="material_id"
                                   placeholder=" ">
                        </div>--}}
                    <?php if($inward_type == 1) {?>
                        <div class="col-md-3">
                            <label class="form-label">Alert Before Product Expiry(Days)</label>
                            <input class="form-control form-inputtext number" value="" maxlength="10"
                                   autocomplete="off" type="text" name="days_before_product_expiry"
                                   id="days_before_product_expiry" placeholder=" ">
                        </div>
                        <?php } ?>
                        <div class="col-md-3">
                            <label class="form-label">Product System Barcode</label>
                            <input class="form-control form-inputtext notallowinput"
                                   value="{{$system_barcode_final}}" maxlength="10" autocomplete="off"
                                   type="text" name="product_system_barcode" id="product_system_barcode"
                                   placeholder=" ">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><span class="kitbarcodelabel">Supplier</span> Barcode</label>
                            <input class="form-control form-inputtext" value=" " autocomplete="off"
                                   type="text" name="supplier_barcode" id="supplier_barcode"
                                   placeholder=" ">
                        </div>
                        {{-- <div class="col-md-2 rightAlign">
                             <label class="form-label rightAlign">Is EAN?</label>

                             <input type="radio" name="is_ean" checked id="iseanyes" value="1"> <span style="font-size: 16px;color: black"><small>Yes</small></span>

                             <input type="radio" name="is_ean" id="iseanno" value="0">
                             <span style="font-size: 16px;color: black"><small>No</small></span>
                         </div>--}}



                        {{--<div class="col-md-2">
                            <label class="form-label">Product EAN Barcode</label>
                            <input class="form-control form-inputtext" value=" " maxlength="10" autocomplete="off" type="text" name="product_ean_barcode" id="product_ean_barcode" placeholder=" ">
                        </div>--}}
                        <div class="col-md-3">
                            <label class="form-label">Low Stock Alert</label>
                            <input type="text" maxlength="10" class="form-control form-inputtext number" name="alert_product_qty" id="alert_product_qty" value="">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">MOQ (Minimum Order Quntity)</label>
                            <input type="text" maxlength="10" class="form-control form-inputtext number" name="default_qty" id="default_qty" value="">
                        </div>

                        <div class="col-md-12">
                            <div class="row" id="imageblock">
                                <div class="col-md-2 block_1" class="previews">
                                    <label class="form-label">Product Image Caption</label>
                                    <input type="text" name="imageCaption[]" id="imageCaption_1" placeholder="" /></div>
                                <div class="col-md-2 block_1">
                                    <div class="form-group">

                                        <label class="form-label">Product Image</label>
                                        <input type="file" onchange="previewandvalidation(this);" data-counter="1" accept=".png, .jpg, .jpeg" name="product_image[]" id="product_image_1" class="form-control form-inputtext productimage" value="">
                                        <div id="preview_1" class="previews" style="display: none">
                                            <a onclick="removeimgsrc('1');" class="displayright"><i class="fa fa-remove" style="font-size: 20px;"></i></a>
                                            <img src="" id="product_preview_1" name="product_preview_1" width="" height="150px">
                                        </div>
                                    </div>
                                </div>
                                <a id="addmoreimg" class=" addmoreoption"><i class="fa fa-plus plusaddmore"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="row pa-0" id="EditImagesBlock" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
