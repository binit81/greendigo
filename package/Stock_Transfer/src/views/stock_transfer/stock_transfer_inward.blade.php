@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')


    <div class="container">


        <span class="commonbreadcrumbtn badge badge-danger badge-pill"
              id="searchCollapse"><i class="glyphicon glyphicon-search"></i>&nbsp;Search</span>



        <section class="hk-sec-wrapper mr-15 collapse" id="searchBox">
            <div class="hk-row">
                <div class="col-md-11">
                </div>
            </div>
            <h5 class="hk-sec-title">Stock Transfer Details Filter</h5>

            <div class="row ma-0">
                <div class="col-sm">
                    <div class="row common-search">
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="text" name-attr="from_to_date" maxlength="50" autocomplete="off"
                                       name="filer_from_to" id="filer_from_to" value=""
                                       class="daterange form-control form-inputtext" placeholder="Select Date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <input type="text" name-attr="stock_transfer_no" maxlength="50" autocomplete="off"
                                       name="stock_transfer_no_filter" id="stock_transfer_no_filter" value="" class="form-control form-inputtext"
                                       placeholder="Stock Transfer No.">
                            </div>
                        </div>


                        <div class="col-md-5">
                            <button type="button" class="btn searchBtn search_data" id="search_view_inward"><i
                                    class="fa fa-search"></i>Search
                            </button>
                            <button type="button" name="resetfilter" onclick="reset_stock_transfer_inward();"
                                    class="btn resetbtn" id="resetfilter" data-container="body" data-toggle="popover"
                                    data-placement="bottom" data-content="" data-original-title="" title="">Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>




        <div class="hk-row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="pull-right">
                            <label class="form-label" style="font-size: 20px;color: red"
                                   id="totalinward_record_with_pagination"></label>
                        </div>
                        <div class="table-wrap">
                            <div class="table-responsive" id="viewstockinward">
                                @include('stock_transfer::stock_transfer/stock_transfer_inward_data')
                            </div>
                        </div><!--table-wrap-->
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="viewstockinwardpopup" style="border:1px solid !important;">
            <div class="modal-dialog" style="max-width:90% !important;">
                <form method="post" id="stock_inward_popup_record">
                    <div class="modal-content">
                        <div class="modal-header" style="Padding: 0.50rem 0.25rem 0 0.25rem !important;">
                            <div class="row ma-0">
                                <div class="col-sm">
                                    <div class="row">
                                        @include('commonpopupheader')

                                    </div>
                                </div>
                            </div>

                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>

                        <?php
                        $tax_label = 'GSTIN';


                        if($nav_type[0]['tax_type'] == 1)
                        {
                            $tax_label = $nav_type[0]['tax_title'];
                        }
                        ?>
                        <div class="invoice-to-wrap pb-20">
                            <div class="row">
                                <div class="col-md-7 mb-30" style="width:50% !important;border:0px solid !important;font-size:16px;">
                                    <table style="float:left;">
                                        <tr>

                                        </tr>
                                        <tr>

                                        </tr>
                                    </table>
                                </div>


                                <div class="col-md-5 mb-30" style="width:50% !important;border:0px solid !important;font-size:16px;">
                                    <table style="float:right;">
                                        <tr>
                                            <td class="d-block text-dark font-14 ">Stock Transfer No.</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="text-dark font-14 font-weight-600 text-right"><span class="transfer_no_popup"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="d-block text-dark font-14 ">Stock Transfer Date</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="text-dark font-14 font-weight-600 text-right"><span class="transfer_date_popup"></span></td>
                                        </tr>
                                        <tr></tr>

                                    </table>
                                </div>
                                <div class="col-md-7 mb-30" style="width:50% !important;border:0px solid !important;font-size:16px;">
                                    <tr>
                                        <td>
                                            <h5 class="hk-sec-title">
                                                <small class="badge badge-soft-danger mt-15 mr-10"><b>No.of Items:</b>
                                                    <span class="totcount">0</span>
                                                </small>
                                            </h5>
                                        </td>
                                    </tr>
                                </div>
                            </div>
                        </div>

                        <section class="hk-sec-wrapper">
                            <div class="table-wrap">
                                <div class="table-responsive">
                                    <table  cellpadding="6" border="0" frame="box" id="view_stock_inward_pop" class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>
                                        <thead >
                                        <tr class="blue_Head">
                                            <th class="text-dark font-14 font-weight-600" style="width:4% !important;">Barcode</th>
                                            <th class="text-dark font-14 font-weight-600" style="width:4% !important;">Prod Name</th>
                                            <th class="text-dark font-14 font-weight-600" style="width:4% !important;">Size / UQC</th>
                                            <th class="text-dark font-14 font-weight-600" style="width:4% !important;">HSN</th>
                                            <th class="text-dark font-14 font-weight-600 garment_case_hide" style="width:4% !important;">Batch No.</th>
                                            <th class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;">Base Price</th>
                                            <th colspan="2" class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;">Disc % & Amt</th>
                                            <th colspan="2" class="text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case" style="width:5% !important;">Scheme % & Amt</th>
                                            <th colspan="2" class="text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case" style="width:5% !important;">Free % & Amt</th>
                                            <th class="text-dark font-14 font-weight-600 garment_case_hide inward_calculation_case" style="width:5% !important;">Cost Rate</th>


                                            <?php if($nav_type[0]['tax_type'] == 1) { ?>
                                            <th class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;"><?php echo $nav_type[0]['tax_title'].' '.$nav_type[0]['currency_title']?></th>
                                            <?php } else { ?>
                                            <th class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;">IGST &#8377;</th>
                                            <th class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;">CGST &#8377;</th>
                                            <th class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;">SGST &#8377;</th>
                                            <?php } ?>
                                            <?php if($nav_type[0]['tax_type'] == 1) { ?>
                                            <th colspan="2" class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;">Profit % & <?php echo $nav_type[0]['currency_title']?></th>
                                            <?php } else { ?>
                                            <th colspan="2" class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;">Profit % & &#8377;</th>
                                            <?php } ?>
                                            <th class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;">Selling Price</th>
                                            <th class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;">Offer Price</th>
                                            <th class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;">MRP</th>
                                            <th class="text-dark font-14 font-weight-600" style="width:4% !important;">Add Qty</th>
                                            <th class="text-dark font-14 font-weight-600 garment_case_hide" style="width:5% !important;">Free Qty</th>
                                            <th class="text-dark font-14 font-weight-600 garment_case_hide" style="width:5% !important;">Mfg Date</th>
                                            <th class="text-dark font-14 font-weight-600 garment_case_hide" style="width:5% !important;">Exp Date</th>
                                            <th class="text-dark font-14 font-weight-600 inward_calculation_case" style="width:5% !important;">Total Cost</th>
                                        </tr>
                                        </thead>
                                        <tbody id="view_stock_inward_record" style="height: 50px;overflow-y: auto">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>


                    </div>
                </form>
            </div>
        </div>

        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>
        <script src="{{URL::to('/')}}/public/modulejs/Stock_Transfer/stock_transfer_inward.js"></script>

@endsection
