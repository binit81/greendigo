@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<style type="text/css">
.hk-sec-wrapper .form-control{
	height: auto !important;
	line-height: 2 !important;
}
.table td, .table th{
	padding: .5rem !important;
}
.form-control[readonly]{
	border-color:#ced4da !important;
	background:#fff !important;
	color:#324148 !important;
	width:50px !important;
}
.ui-helper-hidden-accessible{
    display: none !important;
}
.barcode_color{
    color:#dd3f08;
}

.modal-dialog {
    max-width: 90% !important;
}

#cke_1_contents{
    height:150px !important;
}

.htmltojpg_data{
    border:#ddd 1px solid;
    padding:10px;
    border-radius: 5px;
}
.htmltojpg_data tr td{
    font-size: 12px;
}



</style>

<script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>

<link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/bootstrap-datepicker/css/bootstrap-datepicker.css">

<div class="container ml-20">
<div class="row">

<div class="col-xl-5">




    <section class="hk-sec-wrapper">

        <input type="hidden" name="selectedTemplateID" id="selectedTemplateID" value="" />

        <h5 class="hk-sec-title">&nbsp;&nbsp;&nbsp;Select Barcode Label</h5>
        <div class="row searchBarcodeData pa-0 ma-0">
        @foreach($barcode_template as $i=> $value)
            <div class="col-md-6 form-group" style="min-height:120px;" onclick="stickerClick('<?php echo $value['barcode_template_id']?>')">
                <div class="custom-control custom-radio mb-5">
                    <input id="labelTypeName<?php echo $value['barcode_template_id']?>" name="labelTypeName" class="custom-control-input" value="<?php echo $value['barcode_template_id']?>" type="radio">
                    <label class="custom-control-label" for="labelTypeName<?php echo $value['barcode_template_id']?>" style="text-align:left;line-height:1 !important;"><?php echo $value['template_name']?>&nbsp;&nbsp;
                        <br>
                        <small><?php echo $value['barcode_sheet']['label_name']?> (<?php echo $value['barcode_sheet']['label_tagline']?> Blocks)</small>

                        <input type="hidden" id="barcode_sheet_id_<?php echo $value['barcode_template_id']?>" name="barcode_sheet_id_<?php echo $value['barcode_template_id']?>" value="<?php echo $value['barcode_sheet_id']?>" />

                        <input type="hidden" id="label_tagline_<?php echo $value['barcode_template_id']?>" value="<?php echo $value['barcode_sheet']['label_tagline']?>" />

                        <div class="mt-5 BarcodePreview pa-5">
                        <?php
                            $barcode    =   DNS1D::getBarcodeSVG('11', 'C39');
                            $find       =   array('[BARCODE]','[SUPP_BARCODE]');
                            $rep        =   array($barcode,$barcode);
                            echo $data  =   str_replace($find,$rep,$value['template_data']);
                        ?>
                        </div>
                    </label>

                </div>
            </div>
        @endforeach

        </div>

        <div class="row">

            <?php
            if($role_permissions['permission_add']==1)
            {
            ?>
                <div class="col-auto col-md-12 pa-0 ma-0"><button type="button" onclick="templateDesigner()" class="btn btn-primary mt-5 ml-0 pull-right"><i class="fa fa-pencil"></i>Edit / Create Template</button></div>
            <?php
            }
            ?>
        </div>


    </section>
</div>


<div class="col-xl-7">
<section class="hk-sec-wrapper barcode-tab">
    <h5 class="hk-sec-title">&nbsp;&nbsp;&nbsp;Search Products</h5>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="product-search" data-toggle="tab" href="#product_profile_search_tab" role="tab" aria-controls="profile"
               aria-selected="false">Any product</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="inward-search" data-toggle="tab" href="#inward_search_tab" role="tab" aria-controls="home"
               aria-selected="true">Only in stock</a>
        </li>


    </ul>

    <div class="tab-content mt-15" id="myTabContent">
        <div class="tab-pane fade" id="inward_search_tab" role="tabpanel" aria-labelledby="inward-search">
            <div class="row pa-0 ma-0" >
                <div class="col-sm pa-0">
                    <div class="form-row align-items-center filtercls">
                        <div class="col-md-4 mb-10">
                            <label class="sr-only" for="inlineFormInput">Name</label>
                            <input type="text" name="fromtodate" id="fromtodate" class="daterange form-control" placeholder="Select Inward Date"/>
                            <input type="hidden" name="from_date" id="from_date">
                            <input type="hidden" name="to_date" id="to_date">
                        </div>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="fBarcode" id="fBarcode" onkeyup="return f_barcode_fil(this)" class="form-control" placeholder="From Barcode"/>
                        </div>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="tBarcode" id="tBarcode" onkeyup="return t_barcode_fil(this)" class="form-control" placeholder="To Barcode"/>
                        </div>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="productName" id="productName" onkeyup="return product_name_type(this)" class="form-control" placeholder="By Product Name"/>
                        </div>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="productCode" id="productCode" onkeyup="return product_code_fil(this)" class="form-control" placeholder="By Product code"/>
                        </div>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="supplier_barcode" id="supplier_barcode" onkeyup="return supplier_barcode_fil(this)" class="form-control" placeholder="By Supplier Barcode"/>
                        </div>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="invoiceNo" id="invoiceNo" onclick="return invoice_no_fil(this)" class="form-control" placeholder="By Invoice No."/>
                        </div>

                        <?php
                        $show_dynamic_feature = '';
                        $dynamic_template = '';
                        $dynamic_table_col = '';
                        $footer_cnt = 0;
                        if (isset($product_features) && $product_features != '' && !empty($product_features))
                        {
                        foreach ($product_features AS $feature_key => $feature_value)
                        {
                        if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
                        {
                        $search =$urlData['breadcrumb'][0]['nav_url'];

                        if (strstr($feature_value['show_feature_url'],$search) )
                        {
                        $footer_cnt ++;
                        if($show_dynamic_feature == '')
                        {
                            $show_dynamic_feature =$feature_value['html_id'];
                        }
                        else
                        {
                            $show_dynamic_feature = $show_dynamic_feature.','.$feature_value['html_id'];
                        }

                        $dynamic_template .= '<div class="col-md-12 mb-0 ml-0">'.$feature_value['product_features_name'].': <b class="barcode_color cursor">['.strtoupper($feature_value['product_features_name']).']</b></div>';
                        $dynamic_table_col .= '<th>'.$feature_value['product_features_name'].'</th>';
                        ?>

                        <div class="col-md-4 mb-10">
                            <select class="form-control form-inputtext barcode-print-option" name-attr="{{$feature_value->html_id}}" id="{{$feature_value->html_id}}" name="{{$feature_value->html_name}}" value="" >
                                <option value="">Select {{$feature_value->product_features_name}} </option>
                                @foreach($feature_value->product_features_data AS  $kk=>$vv)
                                    <option value="{{$vv->product_features_data_id}}">{{$vv->product_features_data_value}}</option>
                                @endforeach
                            </select>
                        </div>
                        <?php } ?>
                        <?php
                        }
                        }
                        }
                        ?>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="skucode" id="skucode" onclick="return skucode_fil(this)" class="form-control" placeholder="By SKU Code"/>
                        </div>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="po_no_filter" id="po_no_filter"  onclick="return po_fil(this)" class="form-control" placeholder="By PO No."/>
                        </div>

                        <div class="col-md-12">
                            <button type="button" id="SearchBtn"  onclick="search_btn(1,this);" name="SearchBtn" class="btn btn-primary searchBtn mt-1 pull-right"><i class="fa fa-search"></i>Search</button>
                            <button type="button" id="resetBtn" name="resetBtn" class="btn btn-primary resetBtn mt-1 mr-10 pull-right"></i>Reset</button>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="tab-pane fade show active" id="product_profile_search_tab" role="tabpanel" aria-labelledby="product-search">
            <div class="row pa-0 ma-0" >
                <div class="col-sm pa-0">
                    <div class="form-row align-items-center filtercls">


                        <div class="col-md-4 mb-10">
                            <input type="text" name="fBarcode" id="fBarcode" onkeyup="return f_barcode_fil(this)" class="form-control" placeholder="From Barcode"/>
                        </div>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="tBarcode" id="tBarcode" onkeyup="return t_barcode_fil(this)" class="form-control" placeholder="To Barcode"/>
                        </div>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="productName" id="productName" onkeyup="return product_name_type(this)" class="form-control" placeholder="By Product Name"/>
                        </div>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="productCode" id="productCode" onkeyup="return product_code_fil(this)" class="form-control" placeholder="By Product code"/>
                        </div>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="supplier_barcode" id="supplier_barcode" onkeyup="return supplier_barcode_fil(this)" class="form-control" placeholder="By Supplier Barcode"/>
                        </div>



                        <?php
                        $show_dynamic_feature = '';
                        $dynamic_template = '';
                        $dynamic_table_col = '';
                        $footer_cnt = 0;
                        if (isset($product_features) && $product_features != '' && !empty($product_features))
                        {
                        foreach ($product_features AS $feature_key => $feature_value)
                        {
                        if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
                        {
                        $search =$urlData['breadcrumb'][0]['nav_url'];

                        if (strstr($feature_value['show_feature_url'],$search) )
                        {
                        $footer_cnt ++;
                        if($show_dynamic_feature == '')
                        {
                            $show_dynamic_feature =$feature_value['html_id'];
                        }
                        else
                        {
                            $show_dynamic_feature = $show_dynamic_feature.','.$feature_value['html_id'];
                        }

                        $dynamic_template .= '<div class="col-md-12 mb-0 ml-0">'.$feature_value['product_features_name'].': <b class="barcode_color cursor">['.strtoupper($feature_value['product_features_name']).']</b></div>';
                        $dynamic_table_col .= '<th>'.$feature_value['product_features_name'].'</th>';
                        ?>


                        <div class="col-md-4 mb-10">
                            <select class="form-control form-inputtext barcode-print-option" name-attr="{{$feature_value->html_id}}" id="{{$feature_value->html_id}}" name="{{$feature_value->html_name}}" value="" >
                                <option value="">Select {{$feature_value->product_features_name}} </option>
                                @foreach($feature_value->product_features_data AS  $kk=>$vv)
                                    <option value="{{$vv->product_features_data_id}}">{{$vv->product_features_data_value}}</option>
                                @endforeach
                            </select>
                        </div>
                        <?php } ?>
                        <?php
                        }
                        }
                        }
                        ?>

                        <div class="col-md-4 mb-10">
                            <input type="text" name="skucode" id="skucode" onkeyup="skucode_fil(this)" class="form-control" placeholder="By SKU Code"/>
                        </div>

                        <div class="col-md-12">
                            <button type="button" id="SearchBtn"  onclick="search_btn(0,this);" name="SearchBtn" class="btn btn-primary searchBtn mt-1 pull-right"><i class="fa fa-search"></i>Search</button>
                            <button type="button" id="resetBtn" name="resetBtn" class="btn btn-primary resetBtn mt-1 mr-10 pull-right"></i>Reset</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>





<!-- Template Designer-->
<div class="modal fade" id="templateDesigner" tabindex="-1" role="dialog" aria-labelledby="templateDesigner" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select from Templates</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="">
                <div class="row">
                <?php
                    if(sizeof($barcode_template)!=0)
                    {
                ?>
                        @foreach($barcode_template as $i=> $value)

                            <div class="col-md-4">
                                <div id="htmltojpg_data<?php echo $value['barcode_template_id']?>" class="htmltojpg_data mb-10">
                                <?php
                                $barcode    =   DNS1D::getBarcodeSVG('11', 'C39');
                                $find       =   array('[BARCODE]','[SUPP_BARCODE]');
                                $rep        =   array($barcode,$barcode);
                                echo $value['template_name'].' - '.$value['barcode_sheet']['label_name'].' ('.$value['barcode_sheet']['label_tagline'].' Blocks)';
                                echo $data  =   str_replace($find,$rep,$value['template_data']);
                                ?>
                            </div>

                            <input type="hidden" name="" id="template_id<?php echo $value['barcode_template_id']?>" size="3" value="<?php echo $value['barcode_template_id']?>" />

                            <!-- Edit Template Icon -->
                            <?php
                            if($role_permissions['permission_edit']==1)
                            {
                            ?>
                                <button class="btn btn-icon btn-icon-circle btn-secondary btn-xs" data-toggle="modal" data-target="#EditTemplateDesigner" data-dismiss="modal" onclick="editTemplate('<?php echo $value['barcode_template_id']?>')">
                                <span class="btn-icon-wrap"><i class="icon-pencil"></i></span>
                                </button>
                            <?php
                            }
                            ?>
                            <!-- Edit Template Icon -->

                            <!-- Delete Template Icon -->
                            <?php
                            if($role_permissions['permission_delete']==1)
                            {
                            ?>
                                <button class="btn btn-icon btn-icon-circle btn-danger btn-xs" onclick="deleteTemplate('<?php echo $value['barcode_template_id']?>')">
                                <span class="btn-icon-wrap"><i class="icon-trash"></i></span>
                            </button>
                            <?php
                            }
                            ?>
                            <!-- Delete Template Icon -->
                        </div>
                        @endforeach
                <?php
                    }
                    else
                    {
                        echo 'no template found...';
                    }
                ?>
            </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-12">
                    <?php
                    if($role_permissions['permission_add']==1)
                    {
                    ?>
                        <button type="button" onclick="CreateTemplateDesigner()" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Create New Template</button>
                    <?php
                    }
                    ?>
                </div>
                <!-- <div class="col-md-6" style="text-align:right">
                    <button type="button" name="saveBarcodeTemplateToUser" id="saveBarcodeTemplateToUser" class="btn btn-primary savebtn"><i class="fa fa-save"></i>Save changes</button>
                </div> -->
            </div>
        </div>
    </div>
</div>
<!-- Template Designer-->

<!-- Create Template Designer-->
<div class="modal fade" id="CreateTemplateDesigner" tabindex="-1" role="dialog" aria-labelledby="CreateTemplateDesigner" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Template Designer <small class="redcolor">(red marked fields are mendatory)</small></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row" id="TemplateEditorData">

                            <div class="col-md-3 mb-10">
                                <select name="PrintBarcodeSheets" id="PrintBarcodeSheets" class="form-control form-inputtext invalid">
                                    <option value="">Paper Sheet</option>
                                    @foreach($barcode_sheet as $i=> $value)
                                        <option value="<?php echo $value['barcode_sheet_id']?>"><?php echo $value['label_name']?> (<?php echo $value['label_tagline']?>)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <select name="PrintBarcodeType" id="PrintBarcodeType" class="form-control form-inputtext  invalid">
                                    <option value="">Barocode Type</option>
                                    <option value="C128">C128</option>
                                    <option value="C128A">C128A</option>
                                    <option value="C128B">C128B</option>
                                    <option value="C128C">C128C</option>
                                    <option value="EAN13">EAN13</option>
                                    <!-- <option value="CODE11">CODE11</option> -->
                                    <option value="C39">C39</option>
                                    <!-- <option value="C39+">C39+</option>
                                    <option value="C39E">C39E</option>
                                    <option value="C39E+">C39E+</option>
                                    <option value="C93">C93</option>
                                    <option value="S25">S25</option>
                                    <option value="S25+">S25+</option>
                                    <option value="I25">I25</option>
                                    <option value="MSI+">MSI+</option>
                                    <option value="POSTNET">POSTNET</option> -->
                                    <option value="QRCODE">QRCODE</option>
                                    <!-- <option value="PDF417">PDF417</option>
                                    <option value="DATAMATRIX">DATAMATRIX</option> -->
                                </select>
                            </div>

                            <div class="col-md-5">
                                <input type="text" name="template_name" id="template_name" class="form-control form-inputtext invalid" placeholder="Template Name">
                            </div>

                            <br clear="all" />

                            <div class="col-md-12">
                                <textarea name="template_data" id="template_data" class="form-control form-inputtext"></textarea>
                            </div>

                            <br clear="all" />

                            <div class="col-md-3 mt-10">
                                <select name="label_size_type" id="label_size_type" class="form-control form-inputtext invalid">
                                    <option value="">Measure Unit</option>
                                    <option value="mm">mm (Millimeters)</option>
                                    <option value="cm">cm (Centimeters)</option>
                                </select>
                            </div>

                            <div class="col-md-3 mt-10">
                                <input type="text" name="label_width" id="label_width" class="form-control form-inputtext invalid mobileregax" placeholder="Label Width">
                            </div>

                            <div class="col-md-3 mt-10">
                                <input type="text" name="label_height" id="label_height" class="form-control form-inputtext invalid mobileregax" placeholder="Label Height">
                            </div>

                            <div class="col-md-3 mt-10">
                                <input type="text" name="label_font_size" id="label_font_size" class="form-control form-inputtext invalid mobileregax" placeholder="Font Size">
                            </div>

                            <br clear="all" />

                            <div class="col-md-3 mt-10">
                                <input type="text" name="label_margin_top" id="label_margin_top" class="form-control form-inputtext invalid mobileregax" placeholder="Margin Top">
                            </div>

                            <div class="col-md-3 mt-10">
                                <input type="text" name="label_margin_right" id="label_margin_right" class="form-control form-inputtext invalid mobileregax" placeholder="Margin Right">
                            </div>

                            <div class="col-md-3 mt-10">
                                <input type="text" name="label_margin_bottom" id="label_margin_bottom" class="form-control form-inputtext invalid mobileregax" placeholder="Margin Bottom">
                            </div>

                            <div class="col-md-3 mt-10">
                                <input type="text" name="label_margin_left" id="label_margin_left" class="form-control form-inputtext invalid mobileregax" placeholder="Margin Left">
                            </div>


                        </div>
                    </div>

                    <div class="col-md-4" style="text-align:left;">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="pa-0 ma-0"><small style="font-size:12px;"><u><i>Use below shortcodes to customise your barcode label.</i></u></small></h5>

                                <br clear="all" />
                                <div class="col-md-12 mb-0 ml-0">Product Name: <b class="barcode_color cursor">[PRODUCT_NAME]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Product Desc: <b class="barcode_color cursor">[PRODUCT_DESC]</b></div>
                                <div class="col-md-12 mb-0 ml-0">SKU Code: <b class="barcode_color cursor">[SKUCODE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Product Code: <b class="barcode_color cursor">[CODE]</b></div>
                                <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
                                <div class="col-md-12 mb-0 ml-0">Product Cost Price: <b class="barcode_color cursor">[COST_PRICE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Product MRP: <b class="barcode_color cursor">[MRP]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Product Offer Price: <b class="barcode_color cursor">[OFFER_PRICE]</b></div>
                                <?php } ?>
                                <div class="col-md-12 mb-0 ml-0">Barcode: <b class="barcode_color cursor">[BARCODE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Batch No: <b class="barcode_color cursor">[BATCH_NO]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Company Name: <b class="barcode_color cursor">[COMPANY]</b></div>

                                {{--<div class="col-md-12 mb-0 ml-0">Brand Name: <b class="barcode_color cursor">[BRAND]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Product Size: <b class="barcode_color cursor">[SIZE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Product Colour: <b class="barcode_color cursor">[COLOUR]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Material: <b class="barcode_color cursor">[MATERIAL]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Category: <b class="barcode_color cursor">[CATEGORY]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Sub Category: <b class="barcode_color cursor">[SUBCATEGORY]</b></div>--}}



                                <?php
                                echo $dynamic_template;
                                ?>


                                {{--<div class="col-md-12 mb-0 ml-0">Secret Code: <b class="barcode_color cursor">[SECRET_CODE]</b></div>--}}
                                <div class="col-md-12 mb-0 ml-0">Supplier Barcode: <b class="barcode_color cursor">[SUPP_BARCODE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Packed Date: <b class="barcode_color cursor">[PACKED_DATE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Best Before: <b class="barcode_color cursor">[BEST BEFORE]</b></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <div class="col-md-6" style="text-align:right">
                    <input type="hidden" class="alertStatus" value="0" />
                    <button type="button" name="SaveBarcodeTemplateBtn" id="SaveBarcodeTemplateBtn" class="btn btn-primary saveBtn"><i class="fa fa-save"></i>Save Template</button>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>
<!-- Create Template Designer-->

<!-- Edit Template Designer-->
<div class="modal fade" id="EditTemplateDesigner" tabindex="-1" role="dialog" aria-labelledby="EditTemplateDesigner" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Template Designer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row" id="TemplateEditorData">

                            <div class="col-md-3">
                                <select name="edit_PrintBarcodeSheets" id="edit_PrintBarcodeSheets" class="form-control form-inputtext">
                                    <option value="">Sheet</option>
                                    @foreach($barcode_sheet as $i=> $value)
                                        <option value="<?php echo $value['barcode_sheet_id']?>"><?php echo $value['label_name']?>  (<?php echo $value['label_tagline']?>)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <select name="edit_PrintBarcodeType" id="edit_PrintBarcodeType" class="form-control form-inputtext">
                                    <option value="">Barocode Type</option>
                                    <option value="C128">C128</option>
                                    <option value="C128A">C128A</option>
                                    <option value="C128B">C128B</option>
                                    <option value="C128C">C128C</option>
                                    <option value="EAN13">EAN13</option>
                                    <!-- <option value="CODE11">CODE11</option> -->
                                    <option value="C39">C39</option>
                                    <!-- <option value="C39+">C39+</option>
                                    <option value="C39E">C39E</option>
                                    <option value="C39E+">C39E+</option>
                                    <option value="C93">C93</option>
                                    <option value="S25">S25</option>
                                    <option value="S25+">S25+</option>
                                    <option value="I25">I25</option>
                                    <option value="MSI+">MSI+</option>
                                    <option value="POSTNET">POSTNET</option> -->
                                    <option value="QRCODE">QRCODE</option>
                                    <!-- <option value="PDF417">PDF417</option>
                                    <option value="DATAMATRIX">DATAMATRIX</option> -->
                                </select>
                            </div>

                            <div class="col-md-5">
                                <input type="text" name="edit_template_name" id="edit_template_name" class="form-control form-inputtext" placeholder="Template Name">
                            </div>

                            <br clear="all" />

                            <div class="col-md-12">
                                <textarea name="edit_template_data" id="edit_template_data" class="form-control form-inputtext invalid"></textarea>
                            </div>

                            <br clear="all" />

                            <div class="col-md-3 mt-10">
                                <input type="text" name="edit_label_width" id="edit_label_width" class="form-control form-inputtext" placeholder="Label Width">
                            </div>

                            <div class="col-md-3 mt-10">
                                <input type="text" name="edit_label_height" id="edit_label_height" class="form-control form-inputtext" placeholder="Label Height">
                            </div>

                            <div class="col-md-3 mt-10">
                                <select name="edit_label_size_type" id="edit_label_size_type" class="form-control form-inputtext">
                                    <option value="">Size Type</option>
                                    <option value="mm">mm (Millimeters)</option>
                                    <option value="cm">cm (Centimeters)</option>
                                </select>
                            </div>

                            <div class="col-md-3 mt-10">
                                <input type="text" name="edit_label_font_size" id="edit_label_font_size" class="form-control form-inputtext" placeholder="Font Size">
                            </div>

                            <br clear="all" />



                            <div class="col-md-3 mt-10">
                                <input type="text" name="edit_label_margin_top" id="edit_label_margin_top" class="form-control form-inputtext" placeholder="Margin Top">
                            </div>

                            <div class="col-md-3 mt-10">
                                <input type="text" name="edit_label_margin_right" id="edit_label_margin_right" class="form-control form-inputtext" placeholder="Margin Right">
                            </div>

                            <div class="col-md-3 mt-10">
                                <input type="text" name="edit_label_margin_bottom" id="edit_label_margin_bottom" class="form-control form-inputtext" placeholder="Margin Bottom">
                            </div>

                            <div class="col-md-3 mt-10">
                                <input type="text" name="edit_label_margin_left" id="edit_label_margin_left" class="form-control form-inputtext" placeholder="Margin Left">
                            </div>


                        </div>
                    </div>

                    <div class="col-md-4" style="text-align:left;">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 style="padding:0; margin:0;"><small style="font-size:12px;"><u><i>Use below shortcodes to customise your barcode label.</i></u></small></h5>

                                <br clear="all" />
                                <div class="col-md-12 mb-0 ml-0">Product Name: <b class="barcode_color cursor">[PRODUCT_NAME]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Product Desc: <b class="barcode_color cursor">[PRODUCT_DESC]</b></div>
                                <div class="col-md-12 mb-0 ml-0">SKU Code: <b class="barcode_color cursor">[SKUCODE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Product Code: <b class="barcode_color cursor">[CODE]</b></div>
                                <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
                                <div class="col-md-12 mb-0 ml-0">Product Cost Price: <b class="barcode_color cursor">[COST_PRICE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Product MRP: <b class="barcode_color cursor">[MRP]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Product Offer Price: <b class="barcode_color cursor">[OFFER_PRICE]</b></div>
                                <?php } ?>
                                <div class="col-md-12 mb-0 ml-0">Barcode: <b class="barcode_color cursor">[BARCODE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Batch No: <b class="barcode_color cursor">[BATCH_NO]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Company Name: <b class="barcode_color cursor">[COMPANY]</b></div>
                                {{--<div class="col-md-12 mb-0 ml-0">Product Size: <b class="barcode_color cursor">[SIZE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Product Colour: <b class="barcode_color cursor">[COLOUR]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Brand Name: <b class="barcode_color cursor">[BRAND]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Material: <b class="barcode_color cursor">[MATERIAL]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Category: <b class="barcode_color cursor">[CATEGORY]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Sub Category: <b class="barcode_color cursor">[SUBCATEGORY]</b></div>--}}
                                <?php
                                echo $dynamic_template;
                                ?>
                                {{--<div class="col-md-12 mb-0 ml-0">Secret Code: <b class="barcode_color cursor">[SECRET_CODE]</b></div>--}}
                                <div class="col-md-12 mb-0 ml-0">Supplier Barcode: <b class="barcode_color cursor">[SUPP_BARCODE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Packed Date: <b class="barcode_color cursor">[PACKED_DATE]</b></div>
                                <div class="col-md-12 mb-0 ml-0">Best Before: <b class="barcode_color cursor">[BEST BEFORE]</b></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">

                <div class="col-md-6" style="text-align:right">
                    <input type="hidden" name="edit_value_id" id="edit_value_id" />
                    <button type="button" name="edit_SaveBarcodeTemplateBtn" id="edit_SaveBarcodeTemplateBtn" class="btn btn-primary">Update Template</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Create Template Designer-->

<span class="col-xl-12">
<section class="hk-sec-wrapper">
<h5 class="hk-sec-title mb-0">Search Result <span class="totalSearchCount"></span>




    <?php
    if($role_permissions['permission_print']==1)
    {
    ?>
        <button type="button" id="PrintBarcodes" name="PrintBarcodes" class="btn btn-primary saveprintBtn mt-5 pull-right PrintBarcodes" style="display:none;"><i class="fa fa-print"></i>Print Barcode</button>
    <?php
    }
    ?>


    <small style="float:right; padding: 14px 7px" class="badge badge-soft-danger mr-10 mb-10 ">No. of Sheets required: <b><span id="searchBarcodeSheets">0</span></b></small>
<small style="float:right; padding: 14px 7px" class="badge badge-soft-success mr-10 mb-10">Total Print Qty: <b><span id="barcodeTotalQty">0</span></b><input type="hidden" id="barcodeTotalQty_text" name="barcodeTotalQty_text" /></small>
<span class="input-group" >

     <!-- <span class="input-group-prepend">
        <input type="text" class="form-control form-inputtext" id="manually_mfg_date" name="manually_mfg_date" placeholder="Choose Date"  style="color:#000 !important;" tabindex="-1">
    </span>
    <span class="input-group-prepend">
        <input type="text" class="form-control form-inputtext number" id="best_before" name="best_before" placeholder="Enter Days">
    </span>
    <span class="input-group-prepend">
        <input type="text" class="form-control form-inputtext" disabled="disabled" id="manually_exp_date" name="manually_exp_date" placeholder="Choose Date"  style="color:#000 !important;" tabindex="-1">
    </span> -->

<!-- <small class="blank-label-input mr-10">  -->
    <span class="input-group-prepend"><label class="input-group-text searchicon" style="height: 47px;"><i class="fa fa-search"></i></label></span>
        <input type="text" name="printing_productsearch" id="printing_productsearch" class="form-control form-inputtext typeahead mr-10" style="width: 20%;" placeholder="Product Name/Barcode"/>
<!-- </small> -->

<!-- <small  class="blank-label-input mr-10"> -->
        <input type="hidden" name="qty_search" id="qty_search" value="">
        <input type="text" class="dark-placeholder form-control form-inputtext typeahead onlyinteger mr-10" name="blank_label" placeholder="No. Of Blank Labels" id="blank_label" value="" maxlength="2"  >
        <input type="text" class="form-control form-inputtext typeahead mr-10" id="manually_mfg_date" name="manually_mfg_date" placeholder="Choose Date"  style="color:#000 !important;" tabindex="-1">
        <input type="text" class="form-control form-inputtext typeahead number mr-10" id="best_before" name="best_before" placeholder="Enter Days">
        <input type="text" class="form-control form-inputtext typeahead mr-10" disabled="disabled" id="manually_exp_date" name="manually_exp_date" placeholder="Choose Date"  style="color:#000 !important;" tabindex="-1">

<!-- </small> -->


</div>

</h5>


<div class="row pa-0 ma-0">
    <div class="col-sm pa-0">
        <div class="table-wrap">
            <div class="table-responsive">
                <input type="hidden" name="search_fileter_with" id="search_fileter_with" value="">
                <table class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>
                    <thead class="">
                        <tr class="blue_Head">
                            <th>Product Name</th>
                            <th >Barcode</th>
                            <th>Supp. Barcode</th>
                            <th>Pcode</th>
                            <th>SKU</th>
                            <th>Mfg Date</th>
                            <th>Expiry Date</th>
                           {{-- <th>Category</th>
                            <th>Brand</th>
                            <th>Color</th>
                            <th>Size</th>--}}
                            <?php echo $dynamic_table_col?>
                            <th>Qty</th>
                            <th>Batch No.</th>
                            <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
                            <th class="rightAlign">Cost Price</th>
                            <th class="rightAlign">MRP</th>
                            <?php } ?>
                            <th>Print Qty<input type="checkbox"  name="transfer_qty"  id="transfer_qty"  class="form-control" ></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="searchResult">
                        @include('barcodeprinting::barcodeprinting/view_printing_data')
                    </tbody>
                </table>

            </div>
            <?php
                if($role_permissions['permission_print']==1)
                {
                ?>
                    <button type="button" id="PrintBarcodes" name="PrintBarcodes" class="btn btn-primary saveprintBtn mt-10 mb-10 pull-right PrintBarcodes" style="display:none;"><i class="fa fa-print"></i>Print Barcode</button>
                <?php
                }
                ?>
        </div>
    </div>
</div>
</section>
</div>
</div>


<link rel="stylesheet" href="{{URL::to('/')}}/public/build/css/intlTelInput.css">


    </div>



    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
    <script src="{{URL::to('/')}}/public/dist/js/moment.min.js"></script>
    <script src="{{URL::to('/')}}/public/dist/js/daterangepicker.js"></script>
    <script type="text/javascript">
      $('.daterange').daterangepicker({


                autoUpdateInput: false,

                },function(start_date, end_date) {


        $('.daterange').val(start_date.format('DD-MM-YYYY')+' - '+end_date.format('DD-MM-YYYY'));

                     var inoutdate         =     $("#fromtodate").val();


                    var totalnights       =     inoutdate.split(' - ');
                    $("#from_date").val(totalnights[0]);
                    $("#to_date").val(totalnights[1]);
        });
    </script>
    <script type="text/javascript">
    $(document).ready(function(e){
        $('#cke_template_data').addClass('invalid');
        localStorage.removeItem('barcode-printing-record');
    });

    </script>

        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/public/build/js/intlTelInput.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>

        <script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>


        <script type="text/javascript">
            CKEDITOR.replace('edit_template_data', {
            height: ['150px']
        });
            CKEDITOR.replace('template_data', {
            height: ['150px']
        });
            CKEDITOR.config.allowedContent = true;

            var show_dynamic_feature = '<?php echo $show_dynamic_feature?>';
            var footer_cnt = '<?php echo $footer_cnt?>';
        </script>

        <script src="{{URL::to('/')}}/public/modulejs/BarcodePrinting/barcode-printing.js"></script>
@endsection

