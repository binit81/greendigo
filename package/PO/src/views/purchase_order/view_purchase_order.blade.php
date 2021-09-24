@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
    <link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/sweetalert/css/sweetalert.css">

    <script>
        var id = '';
    </script>

    <div class="container">

 <?php
    if($role_permissions['permission_add']==1)
    {
    ?>

     <a href="{{URL::to('issue_po')}}"><span class="commonbreadcrumbtn badge badge-primary badge-pill mr-80"  id="addnewcollapse"><i class="glyphicon glyphicon-plus"></i>&nbsp;Make PO</span></a>
 <?php
    }
    ?>
     <span class="commonbreadcrumbtn badge badge-danger badge-pill" id="searchCollapse"><i class="glyphicon glyphicon-search"></i>&nbsp;Search</span>
    <section class="hk-sec-wrapper collapse" id="searchbox" style="padding: 0.8rem 1.5rem 0 1.5rem;">
        <div class="row ma-0">
            <div class="col-sm">
                <div class="row common-search">
                    <div class="col-md-3 pb-10">
                        <div class="form-group">
                            <input type="text" name-attr="from_to_date"  maxlength="50" autocomplete="off" name="filer_from_to" id="filer_from_to" value="" class="daterange form-control form-inputtext" placeholder="Select PO Date">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <input type="text" name-attr="po_no"  maxlength="50" autocomplete="off" name="po_no" id="po_no" value="" class="form-control form-inputtext" placeholder="PO No.">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="text"  maxlength="50" autocomplete="off" name="supplier_name" id="supplier_name" value="" class="form-control form-inputtext" placeholder="Supplier Name">
                            <input type="hidden" name-attr="supplier_name" name="supplier_id" id="supplier_id" value="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-info searchBtn search_data" id="search_view_po"><i class="fa fa-search"></i>Search</button>
                        <button type="button" name="resetfilter" onclick="resetpofilterdata();" class="btn btn-info resetbtn" id="resetfilter" data-container="body" data-toggle="popover" data-placement="bottom" data-content="" data-original-title="" title="">Reset</button>
                        {{--<button type="button" class="btn btn-info exportBtn" id="po_record_export" style="float:right;"><i class="ion ion-md-download"></i>&nbsp;Export To Excel</button>--}}
                    </div>
                </div>
            </div>
        </div>

    </section>

        <div class="hk-row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">

                        <div class="table-wrap">
                            <div class="table-responsive" id="porecord">
                                @include('PO::purchase_order/view_purchase_order_data')
                            </div>
                        </div><!--table-wrap-->
                    </div>
                </div>
            </div>
        </div>


    <div class="modal fade" id="viewpopopup">
        <div class="modal-dialog modal-lg" style="max-width:90% !important;">
            <div class="modal-content" >
                <form method="post" id="po_popup_record">
                    <input type="hidden" name="po_batch" id="po_batch" value="" >
                    <div class="modal-header" >
                        <div class="row ma-0">
                            <div class="col-sm">
                                <div class="row">

                                        <!--  <div class="form-group">
                                         <button class="btn btn-primary"  style="color:#fff !important;cursor:pointer;" type="button" data-id="" id="previousorder">Previous</button>
                                           <button class="btn btn-primary" style="color:#fff !important;cursor:pointer;" type="button" data-id=""  id="nextorder">Next</button>
                                        </div> -->
                                            @include('commonpopupheader')

                                   <!--  <div class="col-md-4">
                                          <center><h5 class="modal-title">PO Details : <span class="invoiceno"></span></h5></center>
                                    </div> -->
                                    <div class="col-md-4">
                                         <div class="form-group"  style="float:right;">
                                         <span style="width:250px;float:right;border:1xpx solid;">Action :
                                            <span>
                                                <a class="" id="edit_po_popup" title="Edit"><i class="fa fa-edit" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
                                                <a id="print_detail_po" href="{{URL::to('print_po')}}?id=param&print_type={{encrypt('2')}}" style="text-decoration:none !important;" target="_blank" title="Print">
                                                <i class="fa fa-print" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
                                                <a class="show_takeinward" id="take_inward_data" title="Take Inward"><button class="btn btn-success btn-sm">Take Inward</button></a>
                                            </span>
                                        </span>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                   </div>
                <Div class="row">
                    <div class="col-sm-12">
                     <!-- <div class="col-md-12 mb-30" > -->
                             <!-- <table style="float:left;">
                                <tr>
                                    <td> -->
                                     <h5 class="hk-sec-title">
                                      <small class="badge badge-soft-danger mt-15 mr-10"><b>No.of Items:</b>
                                      <span class="totcount">0</span>
                                    </small>
                                  </h5>
                               <!--  </td>
                              </tr>
                             </table> -->
                       <!--  </div> -->


                    <!-- <div class="modal-header">
                        <span class="d-block display-4" style="margin-left: 1520px">
                            <a id="print_detail_po" href="{{URL::to('print_po')}}?id=param&print_type={{encrypt('2')}}" style="text-decoration:none !important;" target="_blank" title="Print"><i class="fa fa-print" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
                        </span>
                        <span class="show_takeinward" style="margin-left: 20px;display: none;">
                            <button><a id="take_inward_data" title="Take Inward" >Take Inward</a></button>
                        </span>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div> -->

                    <!-- <div class="col-md-2">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-5">
                                        <div>
                                            <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Qty</span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="d-block display-4 text-dark mb-5"><span id="po_total_qty"></span> </span>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                         <section class="hk-sec-wrapper">
                                    <div class="table-wrap">
                                        <div class="table-responsive">
                                            {{--<table class="table tablesaw table-bordered table-hover table-striped mb-0 view-bill-screen  pb-30  dtr-inline tablesaw-sortable data-tablesaw-no-labels"
                                                   width="100%" cellpadding="6" border="0" frame="box" style="border:1px solid #C0C0C0 !important;"
                                                   data-tablesaw-no-labels role="grid" aria-describedby="datable_1_info" id="viewinward">--}}

                                                <table  class="table tablesaw table-bordered table-hover table-striped mb-0"
                                                        data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch
                                                        cellpadding="6" border="0" frame="box" id="viewinward">
                                                <thead>
                                                <?php
                                                $tax_lable = "GST";
                                                $show_dynamic_feature = '';
                                                if($nav_type[0]['tax_type']== 1)
                                                {
                                                    $tax_lable = $nav_type[0]['tax_title'];
                                                }
                                                ?>
                                                <tr class="blue_Head">
                                                    <th class="text-dark font-14 font-weight-600" >Barcode</th>
                                                    <th class="text-right text-dark font-14 font-weight-600" >Prod Name</th>

                                                    <th class="text-right text-dark font-14 font-weight-600 po_with_batch_no"><?php echo $nav_type[0]['unique_barcode_name']?></th>
                                                    <?php
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

                                                    <th class="text-dark font-14 font-weight-600" ><?php echo $feature_value['product_features_name']?></th>
                                                    <?php } ?>
                                                    <?php

                                                    }
                                                    }
                                                    }
                                                    ?>
                                                    <th class="text-dark font-14 font-weight-600" >UQC</th>
                                                    <th class="text-dark font-14 font-weight-600 with_calculation" >Cost Rate</th>
                                                    <th colspan="2" class="text-dark font-14 font-weight-600 with_calculation" ><?php echo $tax_lable?> % & Amt</th>
                                                    <th class="text-dark font-14 font-weight-600" >Qty</th>
                                                    <th class="text-dark font-14 font-weight-600 with_calculation" >Total Cost Without <?php echo $tax_lable?></th>
                                                    <th class="text-dark font-14 font-weight-600 with_calculation" >Total <?php echo $tax_lable?></th>
                                                    <th class="text-dark font-14 font-weight-600 with_calculation" >Total Cost With <?php echo $tax_lable?></th>
                                                    <th class="text-dark font-14 font-weight-600" >Received Qty</th>
                                                    <th class="text-dark font-14 font-weight-600" >Pending Qty</th>
                                                    <th class="text-dark font-14 font-weight-600 po_with_batch_no" >Mfg Date</th>
                                                    <th class="text-dark font-14 font-weight-600 po_with_batch_no" >Exp Date</th>
                                                    <th class="text-dark font-14 font-weight-600" >Batch No</th>
                                                    </tr>
                                                </thead>

                                                <tbody id="view_po_record">

                                                </tbody>

                                                <tfoot style="border-bottom:1px solid #C0C0C0 !important;border-top:1px solid #C0C0C0 !important;">
                                                <tr>
                                                    <?php
                                                    $po_btch =  "<script>
                                                                            $('#po_batch').val();

                                                                        </script>";



                                                    $colspan = '7';

                                                    if($po_btch == 1 && $nav_type[0]['po_calculation'] == 2)
                                                    {
                                                        $colspan = '4';
                                                    }


                                                    if($po_btch == 0 && $nav_type[0]['po_calculation'] == 2)
                                                    {
                                                        $colspan = '4';
                                                    }
                                                    if($show_dynamic_feature != '')
                                                    {
                                                        $feature = explode(',',$show_dynamic_feature);

                                                        foreach($feature AS $fea_key=>$fea_val)
                                                        {
                                                            $colspan++;
                                                        }
                                                        }
                                                    //with_calculation
                                                    ?>

                                                    <th colspan="<?php echo $colspan ?>" class="text-right text-dark font-14 font-weight-600">Total</th>
                                                    <th class="text-right text-dark font-14 font-weight-600"><span id="po_total_qty"></th>
                                                    <th class="text-right text-dark font-14 font-weight-600 with_calculation"></th>
                                                    <th class="text-right text-dark font-14 font-weight-600 with_calculation"></th>
                                                    <th class="text-right text-dark font-14 font-weight-600 with_calculation"></th>
                                                    <th class="text-right text-dark font-14 font-weight-600"></th>
                                                    <th class="text-right text-dark font-14 font-weight-600"></th>
                                                    <th class="text-right text-dark font-14 font-weight-600 po_with_batch_no"></th>
                                                    <th class="text-right text-dark font-14 font-weight-600 po_with_batch_no"></th>
                                                    <th class="text-right text-dark font-14 font-weight-600"></th>
                                                </tr>
                                              </tfoot>
                                            </table>
                                        </div>
                                    </div>
                        </section>
</div> <!-- end col -->
</div><!-- end row -->


                </form>
            </div>
        </div>
    </div>


    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
     <script src="{{URL::to('/')}}/public/bower_components/sweetalert/js/sweetalert.min.js"></script>

    <script src="{{URL::to('/')}}/public/modulejs/purchase_order/view_po_detail.js"></script>

    <script type="text/javascript">
    $(document).ready(function(e){
        $('#searchCollapse').click(function(e){
            $('#searchbox').slideToggle();
        })
    })

    var show_dynamic_feature = '<?php echo $show_dynamic_feature?>';
    </script>

@endsection
