@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')

    <html>
    <head>
        <title></title>
        <link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/bootstrap-datepicker/css/bootstrap-datepicker.css">
        <link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/sweetalert/css/sweetalert.css">
    </head>
    <body>

    <form name="issue_po" id="issue_po" method="post" enctype="multipart/form-data">
        <?php
        $po_terms_and_condition = (isset($po_terms_condition) && $po_terms_condition != '' ? $po_terms_condition : '');

        ?>

        <input type="hidden" name="purchase_order_id" id="purchase_order_id" value="">
        <input type="hidden" name="po_with_unique_barcode" id="po_with_unique_barcode" value="">
        <div class="container">

            <a href="{{URL::to('view_issue_po')}}">
                <span class="commonbreadcrumbtn badge viewBtn badge-pill"
                       id="searchCollapse"><i
                            class="ion ion-md-apps"></i>&nbsp;View PO</span></a>

            <?php
                if($role_permissions['permission_export']==1)
                {
                ?>
                <span class="commonbreadcrumbtn badge badge-pill downloadBtn mr-0"  id="downloadpotmpate"><i class="ion ion-md-download"></i>&nbsp;Download PO Template</span></a>
                <?php
                }
                ?>

            <?php
            if($role_permissions['permission_upload']==1) { ?>
            <span class="commonbreadcrumbtn badge exportBtn badge-pill uploadBtn mr-0"  id="upload_po_tmpate">&nbsp<i class="fa fa-upload"></i>&nbsp;
        Upload PO</span>
            <?php } ?>

            <div class="col-xl-12">
                <div class="hk-row">
                    <div class="col-sm-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label">Supplier</label>
                                        <input class="form-control form-inputtext invalid" value="" maxlength=""
                                               type="text" name="supplier_name" id="supplier_name" placeholder=" " autofocus>
                                        <input type="hidden" name="gst_id" id="gst_id" value="">
                                        <input type="hidden" name="state_id" id="state_id" value="">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                       {{-- <label class="form-label">PO No.</label>--}}
                                        <input class="form-control form-inputtext invalid" value="<?php echo $po_no;?>"
                                               style="color:black;font-size: 20px" autocomplete="off" type="text"
                                               name="po_no" id="po_no" placeholder=" " readonly="readonly">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">PO Date</label>
                                        <input class="form-control form-inputtext invalid" value="" autocomplete="off" type="text" name="po_date" id="po_date" placeholder=" ">
                                    </div>


                                    <div class="col-md-12">
                                        <label class="form-label">Note</label>
                                        <textarea name="po_note" id="po_note" class="form-control"></textarea>
                                    </div>

                                </div>


                            </div>
                        </div>
                    </div>


                    <div class="col-md-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label">Delivery Date</label>
                                        <input class="form-control form-inputtext invalid" value="" autocomplete="off"
                                               type="text" name="delivery_date" id="delivery_date" placeholder=" ">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Delivery To</label>
                                        <input class="form-control form-inputtext invalid" value="" autocomplete="off"
                                               type="text" name="delivery_to" id="delivery_to" placeholder=" ">
                                    </div>

                                    <?php
                                    $company_address = isset($nav_type[0]['company_address']) ? strip_tags(trim($nav_type[0]['company_address'])) : '';
                                    $company_area = isset($nav_type[0]['company_area']) ? $nav_type[0]['company_area'] : '';
                                    $company_zip = isset($nav_type[0]['company_pincode']) ? $nav_type[0]['company_pincode'] : '';
                                    $company_city = isset($nav_type[0]['company_city']) ? $nav_type[0]['company_city'] : '';
                                    $address = $company_address.' '.$company_area.' '.$company_zip.' '.$company_city;

                                    ?>

                                    <div class="col-md-12">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control form-inputtext invalid" name="address"
                                                  id="address" rows="3"><?php echo $address ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label">Terms & Condition(will display on PO)</label>
                                        <textarea class="form-control form-inputtext" value="" name="terms_condition"
                                                  id="terms_condition" placeholder=" "
                                                  rows="3"><?php echo $po_terms_and_condition?></textarea>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $tax_name = "GST";
                    if($nav_type[0]['tax_type'] == 1)
                    {
                        $tax_name = $nav_type[0]['tax_title'];
                    }
                    ?>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Total Qty</label>
                                        <input class="form-control form-inputtext invalid"
                                               style="color:black;font-size: 20px" value="0" autocomplete="off"
                                               type="text" name="total_qty" id="total_qty" placeholder=" "
                                               readonly="readonly">
                                    </div>
                                    <div class="col-md-6 with_calculation">
                                        <label class="form-label">Total Cost Rate</label>
                                        <input class="form-control form-inputtext invalid"
                                               style="color:black;font-size: 20px" value="0" autocomplete="off"
                                               type="text" name="total_cost_rate" id="total_cost_rate" placeholder=" "
                                               readonly="readonly">
                                    </div>
                                    <div class="col-md-6 with_calculation">
                                        <label class="form-label">Total <?php echo $tax_name?></label>
                                        <input class="form-control form-inputtext invalid"
                                               style="color:black;font-size: 20px" value="0" autocomplete="off"
                                               type="text" name="total_gst" id="total_gst" placeholder=" "
                                               readonly="readonly">
                                    </div>
                                    <div class="col-md-6 with_calculation">
                                        <label class="form-label">Total Cost Price</label>
                                        <input class="form-control form-inputtext invalid"
                                               style="color:black;font-size: 20px" value="0" autocomplete="off"
                                               type="text" name="total_cost_price" id="total_cost_price" placeholder=" "
                                               readonly="readonly">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                {{--<div class="col-md-12">

                                    <button type="button" class="btn btn-success saveBtn btn-block" name="addpoprint" id="addpoprint">
                                        <i class="fa fa-save"></i>Save & Print</button>


                                    <div class="col-md-2 mt-2">
                                    <a id="print_save_po" href="{{URL::to('print_po')}}?id=param&print_type={{encrypt('1')}}" style="text-decoration:none !important;" target="_blank" title="Print">
                                    </a>
                                    </div>


                                    <button type="button" class="btn btn-info savenewBtn btn-block" name="addpo" id="addpo">
                                    <i class="fa fa-save"></i>Save & New</button>
                                </div>
--}}

                                <div class="row pa-0 ma-0">

                                    <div class="col-sm-6 pa-0 pr-5 po_with_batch_no" style="display:none">

                                        <button type="button" class="btn btn-success btn-block btn-space " name="generate_unique_barcode" id="generate_unique_barcode" style="white-space: normal;">
                                            Generate <?php echo $nav_type[0]['unique_barcode_name']?></button>

                                    </div>

                                    <div class="col-sm-6 pa-0 pr-5">
                                        <?php
                                        if($role_permissions['permission_print']==1)
                                        {
                                        ?>
                                            <button type="button" class="btn btn-success saveBtn btn-block btn-space" name="addpoprint" id="addpoprint">
                                            <i class="fa fa-print"></i>Save & Print</button>
                                            <a id="print_save_po" href="{{URL::to('print_po')}}?id=param&print_type={{encrypt('1')}}" style="text-decoration:none !important;" target="_blank" title="Print"></a>
                                        <?php
                                        }
                                        ?>
                                    </div>

                                    <div class="col-sm-6 pa-0 pl-5">
                                        <?php
                                        if($role_permissions['permission_add']==1)
                                        {
                                        ?>
                                            <button type="button" class="btn btn-info savenewBtn btn-block btn-space" name="addpo" id="addpo">
                                            <i class="fa fa-save"></i>Save & New</button>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>


    <div class="col-xl-12">
        <div class="hk-row col-xl-12">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-wrap">
                            <div class="table-responsive">
                                <div class="hk-row">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-prepend"><label class="input-group-text" style="height: 40px;padding-top: 13px"><i class="fa fa-search"></i></label></span>
                                            <input class="form-control form-inputtext" value="" maxlength="" type="text" name="productsearch" id="productsearch" placeholder="Barcode/Product Code/Product Name">
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                    </div>
                                    <div class="col-md-1 rightAlign">
                                        <h5 class="hk-sec-title">
                                            <small class="badge badge-soft-danger mt-15 mr-10"><b>No. of Items:</b>
                                                <span class="pototalitems">0</span>
                                            </small>
                                        </h5>
                                    </div>
                                </div>




                            <table class="table tablesaw view-bill-screen table-hover pb-30 dataTable dtr-inline tablesaw-sortable data-tablesaw-no-labels table-bordered "
                                    data-tablesaw-sortable-switch data-tablesaw-no-labels
                                   role="grid" aria-describedby="datable_1_info"
                                   id="inwardtable"  >
                                <thead>
                                <tr class="blue_Head">

                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Barcode</th>
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2" >Prod Name</th>
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">HSN</th>

                                    <th scope="col" class="po_with_batch_no" data-tablesaw-sortable-col data-tablesaw-priority="4"><?php echo $nav_type[0]['unique_barcode_name']?></th>


                                    <?php
                                    $show_dynamic_feature = '';
                                    if (isset($product_features) && $product_features != '' && !empty($product_features))
                                    {

                                    foreach ($product_features AS $feature_key => $feature_value)
                                    {

                                    if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
                                    {
                                    $search =$urlData['breadcrumb'][0]['nav_url'];

                                    if (strstr($feature_value['show_feature_url'],$search) )
                                    {
                                    if($show_dynamic_feature == '')
                                    {
                                        $show_dynamic_feature =$feature_value['html_id'];
                                    }
                                    else
                                    {
                                        $show_dynamic_feature = $show_dynamic_feature.','.$feature_value['html_id'];
                                    }
                                    ?>

                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist"><?php echo $feature_value['product_features_name']?></th>
                                    <?php } ?>
                                    <?php
                                    }
                                    }
                                    }
                                    ?>

                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">UQC</th>
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">In Stock</th>
                                    <th class="with_calculation" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Cost Rate</th>
                                    <?php if($nav_type[0]['tax_type'] == 1) {?>
                                    <th class="with_calculation" colspan="2" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Cost <?php echo $nav_type[0]['tax_title']?> % & Amt</th>
                                    <?php } else { ?>
                                    <th class="with_calculation" colspan="2" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Cost GST % & Amt</th>
                                    <?php } ?>
                                    <th  scope="col" data-tablesaw-sortable-col scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">Qty</th>
                                    <th class="with_calculation" scope="col" data-tablesaw-sortable-col scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">Total Cost Rate</th>
                                    <?php if($nav_type[0]['tax_type'] == 1) {?>
                                    <th class="with_calculation">Total <?php echo $nav_type[0]['tax_title']?></th>
                                    <?php } else { ?>
                                    <th class="with_calculation" scope="col" data-tablesaw-sortable-col scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">Total GST</th>
                                    <?php } ?>
                                    <th class="with_calculation" scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-col data-tablesaw-priority="12" >Total Cost Price</th>

                                    <th scope="col" class="po_with_batch_no" data-tablesaw-sortable-col data-tablesaw-sortable-col data-tablesaw-priority="13" >Mfg Date</th>
                                    <th scope="col" class="po_with_batch_no" data-tablesaw-sortable-col data-tablesaw-sortable-col data-tablesaw-priority="14" >Exp Date</th>

                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-col data-tablesaw-priority="15" >Remarks</th>
                                    <th scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-col data-tablesaw-priority="16"><i class="fa fa-remove"></i></th>
                                </tr>
                                </thead>
                                <tbody id="po_product_detail_record">
                                </tbody>
                            </table>
                              </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>


    <div class="modal fade" id="upload_po_popup" style="border:1px solid !important;">
        <div class="modal-dialog" style="max-width:30% !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload PO(Excel File)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <span class="badge badge-pill downloadBtn mt-10" style="width:54%;cursor:pointer;color:#ffffff;margin-left: 178px" id="downloadpotmpate">
                        <i class="ion ion-md-download"></i>&nbsp;Download PO Template</span>
                <br>
                <div class="row">
                    <div class="col-sm">
                        <div class="row">
                            <div class="card">
                                <div class="card-body">

                                    <input type="file" class="" id="pofileUpload"  accept=".xlsx, .xls" />
                                    <button type="button"  class="btn btn-info btn-block mt-10 uploadBtn" name="uploadpo" id="uploadpo" >
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
    <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>
    <script src="{{URL::to('/')}}/public/dist/js/bootstrap-typeahead.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/xlsx.full.min.js"></script>

    <script src="{{URL::to('/')}}/public/bower_components/sweetalert/js/sweetalert.min.js"></script>

    <script type="text/javascript" src="{{URL::to('/')}}/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/purchase_order/issue_po.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/purchase_order/po_import.js"></script>
    <?php
    if($nav_type[0]['po_calculation'] == 1)
    {
    ?>
    <script src="{{URL::to('/')}}/public/modulejs/purchase_order/issue_po_calculation.js"></script>
    <?php }
    if($nav_type[0]['po_calculation'] == 2) {
    ?>
    <script src="{{URL::to('/')}}/public/modulejs/purchase_order/po_without_calculation.js"></script>
    <?php } ?>

    <script>
        CKEDITOR.replace('terms_condition', {
            height: ['100px']
        });

        var batch_no = "<?php echo $unique_batch_no ?>";
        var po_show_dynamic_feature = "<?php echo $show_dynamic_feature ?>";


        $("#po_with_unique_barcode").val("<?php echo $po_unique_barcode ?>");



    </script>

    </body>
    </html>
@endsection






































