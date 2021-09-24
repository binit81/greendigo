@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')
<link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/sweetalert/css/sweetalert.css">
    <div class="container">
        <?php
        if($role_permissions['permission_add']==1)
        {
        ?>
            <a href="{{URL::to('debit_note')}}"><span class="commonbreadcrumbtn badge badge-primary badge-pill mr-80"  ><i class="glyphicon glyphicon-plus"></i>&nbsp;Make Debit Note</span></a>
        <?php
        }
        ?>
        <span class="commonbreadcrumbtn badge badge-danger badge-pill" id="searchCollapse"><i class="glyphicon glyphicon-search"></i>&nbsp;Search</span>

    <section class="hk-sec-wrapper mr-20 collapse" id="searchbox" style="padding: 0.8rem 1.5rem 0 1.5rem;">

           <div class="row ma-0">
            <div class="col-sm">
                <div class="row">
                    <div class="col-md-3 pb-10">
                        <div class="form-group">
                            <input type="text"  maxlength="50" autocomplete="off" name="debit_no" id="debit_no" value="" class="form-control form-inputtext" placeholder="Debit No.">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="text"  maxlength="50" autocomplete="off" name="supplier_name" id="supplier_name" value="" class="form-control form-inputtext" placeholder="Supplier Name">
                            <input type="hidden" name="supplier_gst_id" id="supplier_gst_id" value="">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <button type="button" class="btn btn-info searchBtn" id="search_view_debit"><i class="fa fa-search"></i>Search</button>
                        <button type="button" name="resetfilter" onclick="resetdebitfilterdata();" class="btn btn-info resetbtn" id="resetfilter" data-container="body" data-toggle="popover" data-placement="bottom" data-content="" data-original-title="" title="">Reset</button>
                        {{--<button type="button" class="btn btn-info exportBtn" id="po_record_export" style="float:right;">Export To Excel</button>--}}
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
                            <div class="table-responsive" id="debitnoterecord">
                            @include('debit_note::debit_note/view_debit_note_data')
                            </div>
                        </div><!--table-wrap-->
                    </div>
                </div>
            </div>
        </div>


      <div class="modal fade" id="viewdebitpopup">
        <div class="modal-dialog modal-lg" style="max-width:90% !important;">
            <div class="modal-content" >
                <form method="post" id="debit_popup_record">
                    <div class="modal-header" style="Padding: 0.50rem 0.25rem 0 0.25rem !important;">
                        <div class="row ma-0">
                            <div class="col-sm">
                                <div class="row">
                                    <!--  <div class="form-group">
                                         <button class="btn btn-primary" id="previousdebit" style="color:#fff !important;cursor:pointer;" type="button" data-id="">Previous</button>
                                           <button class="btn btn-primary" id="nextdebit" style="color:#fff !important;cursor:pointer;" type="button" data-id="">Next</button>
                                        </div> -->
                                          @include('commonpopupheader')

                                      <!-- <div class="col-md-4">
                                          <center><h5 class="modal-title">Debit Details : <span class="invoiceno"></span></h5></center>
                                    </div> -->
                                    <div class="col-md-4">
                                        <div class="form-group"  style="float:right;">
                                        <span style="width:150px;float:right;border:1xpx solid;">Action :
                                            <a title="Edit" id="debit_note_edit_popup">
                                                <i class="fa fa-edit" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i>
                                            </a>

                                            {{--<a id="delete_debit_note_popup" style="text-decoration:none !important;" target="_blank" title="Delete">
                                            <i class="fa fa-trash" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>--}}

                                            <a id="print_detail_debit" href="{{URL::to('print_debit_note')}}?id=param" style="text-decoration:none !important;" target="_blank" title="Print"><i class="fa fa-print" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
                                        </span>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                    </div>



                    <div class="row">
                        <div class="col-md-12 mb-30" style="width:50% !important;border:0px solid !important;font-size:16px;">
                          <table style="float:right;">
                              <tr>
                                  <td class="d-block text-dark font-14">Debit No.</td>
                                  <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                  <td class="text-dark font-14 font-weight-600 text-right"><span class="popup_debit_no"></span></td>
                              </tr>
                              <tr>
                                  <td class="d-block text-dark font-14">Debit Date</td>
                                  <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                  <td class="text-dark font-14 font-weight-600 text-right"><span class="popup_debit_date"></span></td>
                              </tr>
                              <tr>

                              </tr>
                          </table>
                        </div>
                    </div>
                     <div class="col-md-12 mb-30" >
                             <table style="float:left;">
                                <tr>
                                    <td>
                                     <h5 class="hk-sec-title">
                                      <small class="badge badge-soft-danger mt-15 mr-10"><b>No.of Items:</b>
                                      <span class="totcount">0</span>
                                    </small>
                                  </h5>
                                </td>
                              </tr>
                             </table>
                        </div>
                        <br>
                    <!-- <div class="col-md-2">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-5">
                                    <div>
                                        <span class="d-block font-15 text-dark font-weight-500 greencolor">Total Qty</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="d-block display-4 text-dark mb-5"><span id="debit_total_qty"></span> </span>
                                </div>
                            </div>
                        </div>
                    </div> -->


                    <section class="hk-sec-wrapper">

                 <div class="table-wrap">
                                <div class="table-responsive">
                                    <?php
                                    $tax_lable = "GST";
                                    $show_debit_dynamic_feature = '';
                                    $fea_cnt = 0;
                                    if($nav_type[0]['tax_type']== 1)
                                    {
                                        $tax_lable = $nav_type[0]['tax_title'];
                                    }
                                    ?>

                                         <table  class="table tablesaw table-bordered table-hover table-striped mb-0"
                                                 data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch
                                                 cellpadding="6" border="0" frame="box" id="viewinward" >
                                        <thead>

                                        <tr class="blue_Head">
                                            <th style="width: 3%;">Barcode</th>
                                            <th style="width: 3%;">Prod Name</th>

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
                                                $fea_cnt++;
                                                if($show_debit_dynamic_feature == '')
                                                {
                                                    $show_debit_dynamic_feature =$feature_value['html_id'];
                                                }
                                                else
                                                {
                                                    $show_debit_dynamic_feature = $show_debit_dynamic_feature.','.$feature_value['html_id'];
                                                }
                                            ?>

                                            <th class="text-dark font-14 font-weight-600" style="width:4% !important;"><?php echo $feature_value['product_features_name']?></th>
                                            <?php } ?>
                                            <?php

                                            }
                                            }
                                            }
                                            ?>

                                            <th class="with_calculation_debit" style="width: 3%;">Cost Rate</th>
                                            <th class="with_calculation_debit" colspan="2" style="width: 3%;"><?php echo $tax_lable?> % & Amt</th>
                                            <th style="width: 3%;">qty</th>
                                            <th class="with_calculation_debit" style="width: 3%;">total cost without <?php echo $tax_lable?></th>
                                            <th class="with_calculation_debit" style="width: 3%;">total <?php echo $tax_lable?></th>
                                            <th class="with_calculation_debit" style="width: 3%;">total cost with <?php echo $tax_lable?></th>
                                            <th style="width: 3%;">Remarks</th>
                                        </tr>
                                        </thead>
                                        <tbody id="view_debit_record">

                                        </tbody>
                                        <tfoot style="border-bottom:1px solid #C0C0C0 !important;border-top:1px solid #C0C0C0 !important;">
                                            <tr>
                                                <?php
                                                $colspan = 5;
                                                if($nav_type[0]['inward_calculation'] == 3)
                                                    {
                                                        $colspan = 2;
                                                    }
                                                ?>
                                                <th colspan="<?php echo $colspan+$fea_cnt ?>" class="text-right text-dark font-14 font-weight-600">Total</th>
                                                <th class="text-right text-dark font-14 font-weight-600"><span id="debit_total_qty"></th>
                                                <th class="text-right text-dark font-14 font-weight-600 with_calculation_debit"></th>
                                                <th class="text-right text-dark font-14 font-weight-600 with_calculation_debit"></th>
                                                <th  class="text-right text-dark font-14 font-weight-600 with_calculation_debit"></th>
                                                <th class="text-right text-dark font-14 font-weight-600"></th>
                                            </tr>
                                      </tfoot>
                                    </table>
                                </div>
                            </div>





                </form>
            </div>
        </div>
    </div>


    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>

    <script src="{{URL::to('/')}}/public/dist/js/moment.min.js"></script>
    <script src="{{URL::to('/')}}/public/dist/js/daterangepicker.js"></script>
    <script src="{{URL::to('/')}}/public/bower_components/sweetalert/js/sweetalert.min.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/common.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/debit_note/view_debit_note.js"></script>


        <script type="text/javascript">
            $(document).ready(function(e){
                $('#searchCollapse').click(function(e){
                    $('#searchbox').slideToggle();
                })

                $('#addnewcollapse').click(function(e){
                    $('#addnewbox').slideToggle();
                })
            })


            var show_debit_dynamic_feature = '<?php echo $show_debit_dynamic_feature?>';

    </script>


@endsection
