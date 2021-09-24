@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')

<style type="text/css">
.modal-dialog {
    max-width: 90% !important;
}
</style>
    <link rel="stylesheet"
          href="{{URL::to('/')}}/public/bower_components/bootstrap-datepicker/css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="{{URL::to('/')}}/public/build/css/intlTelInput.css">
    <link rel="stylesheet" href="{{URL::to('/')}}/public/bower_components/sweetalert/css/sweetalert.css">
    <div class="container">
          <span class="commonbreadcrumbtn badge badge-pill downloadBtn"  id="download_supplier_tmpate">
            <i class="ion ion-md-download"></i>&nbsp;Download Supplier Template</span>
        <?php
        if($role_permissions['permission_upload']==1) { ?>
        <span class="commonbreadcrumbtn badge exportBtn badge-pill uploadBtn mr-0"  id="upload_supplier_tmpate">&nbsp<i class="fa fa-upload"></i>&nbsp;
        Upload Supplier</span>
        <?php } ?>
 <?php
      if($role_permissions['permission_add']==1)
      {
      ?>
      <span class="commonbreadcrumbtn badge badge-primary badge-pill mr-10" id="supplierpopup"><i class="glyphicon glyphicon-plus"></i>&nbsp;Add New Supplier </span>
 <?php
      }
      ?>
      <span class="commonbreadcrumbtn badge badge-danger badge-pill"  id="searchCollapse">
        <i class="glyphicon glyphicon-search"></i>&nbsp;Search</span>




  {{--  <div class="hk-row">
        <div class="col-md-12">
            <div class="">
                <div class="">
                    <ul class="nav nav-tabs" data-tabs="tabs">
                        <li class="active"><a data-toggle="tab" href="#supplier_company_info">Supplier Company Info</a>

                        </li>
                        <li><a data-toggle="tab" href="#supplier_bank">Supplier Bank</a>

                        </li>
                        <li><a data-toggle="tab" href="#supplier_gst">Supplier GST</a>

                        </li>
                        <li><a data-toggle="tab" href="#supplier_contact_detail">Supplier Contact Detail</a>

                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>--}}
    <div class="modal fade"  id="addsupplierpopup" data-backdrop="static">
        <div class="modal-dialog modal-lg" >
            <div class="modal-content" >
                <div class="modal-header">
                    <h4 class="modal-title">Add Supplier</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
    <div class="modal-body" >
    <div class="col-xl-12">
        <div class="hk-row">
            <form name="supplier_form" id="supplier_form">
                <div class="col-md-12">
                    <div class="tab-content">

                            <input type="hidden" name="supplier_company_info_id" id="supplier_company_info_id" value="">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body greybg">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Company Name</label>
                                                    <input type="text" maxlength="50" autocomplete="off"
                                                           name="supplier_company_name" id="supplier_company_name"
                                                           value=""
                                                           class="form-control form-inputtext invalid" placeholder="" autofocus>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">PAN</label>
                                                    <input type="text" maxlength="10" name="supplier_pan_no"
                                                           id="supplier_pan_no" value=""
                                                           class="form-control form-inputtext" placeholder="">
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">First Name</label>
                                                    <input type="text" autocomplete="off" maxlength="50"
                                                           name="supplier_first_name" id="supplier_first_name" value=""
                                                           class="form-control form-inputtext invalid" placeholder="">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Last Name / Surname</label>
                                                    <input type="text" maxlength="50" name="supplier_last_name"
                                                           id="supplier_last_name" value=""
                                                           class="form-control form-inputtext invalid"
                                                           placeholder="">
                                                </div>

                                                <div class="col-md-12">
                                                    <label class="form-label">Note</label>
                                                    <textarea class="form-control" name="supplier_note" id="supplier_note"></textarea>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Due Days</label>
                                                    <input type="text" class="form-control form-inputtext number" id="supplier_due_days" name="supplier_due_days" placeholder="Enter Days">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Due Date</label>
                                                    <input type="text" class="form-control form-inputtext" id="supplier_due_date" name="supplier_due_date" placeholder="Choose Date"  style="color:#000 !important;" tabindex="-1">
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="card">
                                        <div class="card-body greybg">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Shop no.,Building,Street etc.</label>
                                                    <input type="text" maxlength="255" name="supplier_company_address"
                                                           id="supplier_company_address" value=""
                                                           class="form-control form-inputtext" placeholder="">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Area</label>
                                                    <input type="text" maxlength="100" name="supplier_company_area"
                                                           id="supplier_company_area" value=""
                                                           class="form-control form-inputtext" placeholder="">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Pin / Zip Code</label>
                                                    <input type="text" maxlength="15" name="supplier_company_zipcode"
                                                           id="supplier_company_zipcode" value=""
                                                           class="form-control form-inputtext" placeholder="">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">City / Town</label>
                                                    <input type="text" maxlength="100" name="supplier_company_city"
                                                           id="supplier_company_city" value=""
                                                           class="form-control form-inputtext" placeholder="">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">State</label>
                                                    <select name="state_id" id="state_id"
                                                            class="form-control form-inputtext">
                                                        <option value="">Select State</option>
                                                        @foreach($state AS $statekey=>$statevalue)
                                                            <option value="{{$statevalue->state_id}}">{{$statevalue->state_name}}</option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Country</label>
                                                    <select name="country_id" id="country_id"
                                                            class="form-control form-inputtext">
                                                        @foreach($country AS $countrykey=>$countryvalue)
                                                            <option
                                                                <?php if ($countryvalue['country_id'] == $nav_type[0]['country_id']) echo "selected"  ?> value="{{$countryvalue->country_id}}">{{$countryvalue->country_name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body greybg">
                                            <a id="companymobileplus" data-id="2"><i class="fa fa-plus"></i></a>
                                            <div id="repeat_companymobile">
                                                <div class="col-md-12" id="mobile_1">
                                                    <label class="form-label">Phone No.</label>
                                                    <input style="width: 100%;" type="tel" autocomplete="off"
                                                           name="supplier_company_mobile_no"
                                                           id="supplier_company_mobile_no" value="" maxlength="15"
                                                           class="form-control form-inputtext mobileregax"
                                                           placeholder="">
                                                    <input type="hidden" name="supplier_company_dial_code"
                                                           id="supplier_company_dial_code" value="">
                                                </div>
                                                <div class="col-md-12" id="mobile_2">
                                                    <label class="form-label">Phone No.</label>
                                                    <input style="width: 100%;" type="tel" autocomplete="off"
                                                           name="supplier_company_mobile_no"
                                                           id="supplier_company_mobile_no" value="" maxlength="15"
                                                           class="form-control form-inputtext mobileregax"
                                                           placeholder="">
                                                    <input type="hidden" name="supplier_company_dial_code"
                                                           id="supplier_company_dial_code" value="">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                        <div class="card">
                                        <div class="card-body greybg">
                                          <div class="row">
                                            <div class="col-md-12">
                                                    <label class="form-label">Note</label>
                                                    <textarea class="form-control" name="supplier_note" id="supplier_note"></textarea style="width: 100%;">
                                                </div>

                                              {{--  <div class="col-md-6">
                                                    <label class="form-label">Due  Days</label>
                                                    <input type="text" class="form-control form-inputtext number" id="supplier_due_days" name="supplier_due_days" placeholder="Enter Days"  style="width: 100%;">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Due  Date</label>
                                                    <input type="text" class="form-control form-inputtext" id="supplier_due_date" name="supplier_due_date" placeholder="Choose Date"  style="color:#000 !important;width: 100%;" tabindex="-1">
                                                </div>--}}

                                          </div>
                                        </div>
                                      </div>


                                </div>
                            </div>

                        <div class="row">
                                <div class="span10 offset1">
                                    <div class="well">
                                        <div class="col-md-12">

                                            <div class="card-body greybg">
                                                <h5 class="hk-sec-title">Supplier Banks<a id="bankplus" data-id="1"><i
                                                                class="fa fa-plus"></i></a></h5>
                                                <div id="repeat_bank">
                                                    <div class="row" id="new_bank_1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Bank Name</label>
                                                            <input type="text" maxlength="100" name="supplier_bank_name"
                                                                   id="supplier_bank_name" value=""
                                                                   class="form-control form-inputtext"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Bank Account Name</label>
                                                            <input type="text" maxlength="100"
                                                                   name="supplier_bank_account_name"
                                                                   id="supplier_bank_account_name" value=""
                                                                   class="form-control form-inputtext"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Bank Account No.</label>
                                                            <input type="text" maxlength="20"
                                                                   name="supplier_bank_account_no"
                                                                   id="supplier_bank_account_no" value=""
                                                                   class="form-control form-inputtext number"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Bank IFSC Code</label>
                                                            <input type="text" maxlength="11" name="supplier_bank_ifsc_code"
                                                                   id="supplier_bank_ifsc_code" value=""
                                                                   class="form-control form-inputtext" placeholder="">
                                                        </div>
                                                        <input type="hidden" name="supplier_bank_id" id="supplier_bank_id" value="">

                                                    </div>
                                                </div>
                                            </div>


                                        </div>

                                    </div>
                                </div>
                            </div>

                        <div class="row">
                                <div class="span10 offset1">
                                    <div class="hk-row">
                                        <div class="col-md-12">
                                            <?php
                                            $tax_name = "GSTIN";
                                            $tax_nm = "GST";
                                            if($nav_type[0]['tax_type'] == 1)
                                            {
                                                $tax_name = $nav_type[0]['tax_title'];
                                                $tax_nm = $nav_type[0]['tax_title'];
                                            }
                                            ?>
                                            <div class="card-body greybg">
                                                <h5 class="hk-sec-title">Supplier <?php echo $tax_nm?><a id="gstplus" data-id="1"><i
                                                                class="fa fa-plus"></i></a></h5>
                                                <div id="repleat_gst">
                                                    <div class="row" id="new_gst_1">
                                                        <div class="col-md-1">
                                                            <label class="form-label">Treatment</label>
                                                            <select id="supplier_treatment_id"
                                                                    name="supplier_treatment_id"
                                                                    class="form-control form-inputtext" onchange="showhide_gst(this);">
                                                                @foreach($supplier_treatments AS $treatment_key=>$treatment_value)
                                                                    <option value="{{$treatment_value->supplier_treatment_id}}">{{$treatment_value->supplier_treatment_name}}</option>
                                                                @endforeach
                                                            </select>

                                                        </div>
                                                        <div class="col-md-2" id="supplier_gst">
                                                            <label class="form-label"><?php echo $tax_name?></label>
                                                          
                                                            <input type="text" onkeyup="getstate(this);" maxlength="15"
                                                                   name="supplier_gstin" id="supplier_gstin" value=""
                                                                   class="form-control form-inputtext invalid"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <label class="form-label">State</label>
                                                            <select id="state_id" name="state_id"
                                                                    class="form-control form-inputtext invalid">
                                                                <option value="">Select State</option>
                                                                @foreach($state AS $state_key=>$state_value)
                                                                    <option value="{{$state_value->state_id}}">{{$state_value->state_name}}</option>
                                                                @endforeach
                                                            </select>

                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Address</label>
                                                            <input type="text" maxlength="20" name="supplier_address"
                                                                   id="supplier_address" value=""
                                                                   class="form-control form-inputtext"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Area</label>
                                                            <input type="text" maxlength="20" name="supplier_area"
                                                                   id="supplier_area"
                                                                   value="" class="form-control form-inputtext"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <label class="form-label">Zipcode</label>
                                                            <input type="text" maxlength="20"
                                                                   name="supplier_gst_zipcode"
                                                                   id="supplier_gst_zipcode" value=""
                                                                   class="form-control form-inputtext" placeholder="">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">City</label>
                                                            <input type="text" maxlength="20" name="supplier_gst_city"
                                                                   id="supplier_gst_city" value=""
                                                                   class="form-control form-inputtext"
                                                                   placeholder="">
                                                        </div>
                                                        <input type="hidden" name="supplier_gst_id" id="supplier_gst_id"
                                                               value="">
                                                    </div>

                                                </div>
                                            </div>


                                        </div>

                                    </div>


                                </div>
                            </div>

                        <div class="row">
                                <div class="span10 offset1">
                                    <div class="hk-row">
                                        <div class="col-md-12">

                                            <div class="card-body greybg">
                                                <h5 class="hk-sec-title">Supplier Contact Details<a id="contactplus"
                                                                                                    data-id="1"><i
                                                                class="fa fa-plus"></i></a></h5>
                                                <div id="repeat_contact">
                                                    <div class="row" id="new_contact_1">
                                                        <div class="col-md-0">
                                                            <label class="form-label"></label>
                                                            <select id="salutation_id" name="salutation_id"
                                                                    class="form-control form-inputtext">
                                                                @foreach($salutation AS $salutation_key=>$salutation_value)
                                                                    <option value="{{$salutation_value->salutation_id}}">{{$salutation_value->salutation_prefix}}</option>
                                                                @endforeach
                                                            </select>

                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">First Name</label>
                                                            <input type="text" maxlength="100"
                                                                   name="supplier_contact_firstname"
                                                                   id="supplier_contact_firstname" value=""
                                                                   class="form-control form-inputtext"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <label class="form-label">Last Name / Surname</label>
                                                            <input type="text" maxlength="100"
                                                                   name="supplier_contact_lastname"
                                                                   id="supplier_contact_lastname" value=""
                                                                   class="form-control form-inputtext" placeholder="">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <label class="form-label">Designation</label>
                                                            <input type="text" maxlength="100"
                                                                   name="supplier_contact_designation"
                                                                   id="supplier_contact_designation" value=""
                                                                   class="form-control form-inputtext" placeholder="">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Email Id</label>
                                                            <input type="text" maxlength="100"
                                                                   name="supplier_contact_email_id"
                                                                   id="supplier_contact_email_id" value=""
                                                                   class="form-control form-inputtext"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <label class="form-label">Date of Birth</label>
                                                            <input type="text" maxlength="100"
                                                                   name="supplier_date_of_birth"
                                                                   id="supplier_date_of_birth" value=""
                                                                   class="form-control form-inputtext" placeholder="">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Mobile No.</label>
                                                            <input style="width: 100%;" type="tel" autocomplete="off"
                                                                   name="supplier_contact_mobile_no"
                                                                   id="supplier_contact_mobile_no"
                                                                   value="" maxlength="15"
                                                                   class="form-control form-inputtext mobileregax"
                                                                   placeholder="">
                                                            <input type="hidden" name="supplier_contact_dial_code"
                                                                   id="supplier_contact_dial_code" value="">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Whatsapp No.</label>
                                                            <input style="width: 100%;" type="tel" autocomplete="off"
                                                                   name="supplier_whatsapp_no" id="supplier_whatsapp_no"
                                                                   value=""
                                                                   maxlength="15"
                                                                   class="form-control form-inputtext mobileregax"
                                                                   placeholder="">
                                                            <input type="hidden" name="supplier_whatsapp_dial_code"
                                                                   id="supplier_whatsapp_dial_code" value="">
                                                        </div>
                                                        <input type="hidden" name="supplier_contact_details_id"
                                                               id="supplier_contact_details_id" value="">

                                                    </div>

                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12">

                                                        <div class="pull-right">

                                                            <input type="hidden" class="alertStatus" value="0" />

                                                            <button type="button" name="addsupplier"
                                                                    class="btn btn-info addbutton"
                                                                    id="addsupplier" data-container="body"
                                                                    data-toggle="popover"
                                                                    data-placement="bottom" data-content="">Add Supplier
                                                            </button>

                                                            <button type="button" name="resetsupplier"
                                                                    onclick="resetsupplierdata();"
                                                                    class="btn btn-light resetbtn" id="resetsupplier"
                                                                    data-container="body"
                                                                    data-toggle="popover" data-placement="bottom"
                                                                    data-content="">Reset
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                    </div>
                </div>
            </form>
          </div>
        </div>
    </div>
            </div>
        </div>
    </div>

<!-- Vrunda -->
<div class="col-xl-12 collapse" id="searchbox">
        <div class="hk-row">
    <div class="card pa-20">
        <div class="" id="filterarea_block">
            <div class="hk-row common-search">
                <div class="col-md-3 pb-10">
                    <div class="form-group">
                        <input type="text" name-attr="company_name" name="filter_company_name"  placeholder="Company Name" id="filter_company_name" class="form-control form-inputtext" />
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <input type="text" name-attr="supplier_gstin" name="filter_gst_in"  placeholder="Supplier GSTIN" id="filter_gst_in" class="form-control form-inputtext" />
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <input type="text" name-attr="supplier_name" name="filter_supplier_name"  placeholder="Supplier Name" id="filter_supplier_name" class="form-control form-inputtext" />
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <input type="text" name-attr="supplier_mobile" name="filter_mobile_no"  placeholder="Supplier Mobile No." id="filter_mobile_no" class="form-control form-inputtext" />
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <input type="text" name-attr="supplier_city" name="filter_supplier_city"  placeholder="City / Town" id="filter_supplier_city" class="form-control form-inputtext" />
                    </div>
                </div>

                <div class="col-md-3 ">
                    <select name-attr="state_id" name="filter_state_id" id="filter_state_id" class="form-control form-inputtext">
                        <option value="0">Select State</option>
                        @foreach($state AS $statekey=>$statevalue)
                            <option value="{{$statevalue->state_id}}">{{$statevalue->state_name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name-attr="country_id" name="filter_country_id" id="filter_country_id" class="form-control form-inputtext">
                        <option value="0">Select Country</option>
                        @foreach($country AS $countrykey=>$countryvalue)
                            <option  value="{{$countryvalue->country_id}}">{{$countryvalue->country_name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="button" class="btn btn-info searchBtn search_data" id="search_supplier"><i class="fa fa-search"></i>Search</button>
                    <button type="button" name="resetfilter" onclick="resetsupplierfilterdata();" class="btn btn-info resetbtn" id="resetfilter" data-container="body" data-toggle="popover" data-placement="bottom" data-content="" data-original-title="" title="">Reset</button>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
<!-- vrunda -->
        <div class="col-xl-12">

        <div class="hk-row">
            <div class="card">
                <div class="card-body">
                   {{-- <h5 class="hk-sec-title">Supplier List</h5>--}}
                    <div class="hk-row">
                        <div class="col-md-9">
                          <?php
                          if($role_permissions['permission_delete']==1)
                          {
                          ?>
                            <a id="deletesupplier" name="deletesupplier">
                            <i class="fa fa-trash" style="font-size: 20px;color: red;margin-left: 20px;"></i></a>
                          <?php
                          }
                          ?>
                        </div>
{{--                        <div class="col-md-3">--}}
{{--                            <div class="form-group"><input type="text" name="serach" id="serach" class="form-control form-inputtext"/></div>--}}
{{--                        </div>--}}

                    </div>

                    <div class="table-wrap">
                        <div class="table-responsive" id="supplierrecord">
                            @include('supplier::supplier/supplier_data')
                        </div>
                    </div><!--table-wrap-->
                </div>
            </div>
        </div>
    </div>

        <div class="modal fade" id="upload_supplier_popup" style="border:1px solid !important;">
            <div class="modal-dialog" style="max-width:30% !important;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Supplier(Excel File)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <span class="badge badge-pill downloadBtn mt-10" style="width:54%;cursor:pointer;color:#ffffff;margin-left: 178px" id="download_supplier_tmpate">
                        <i class="ion ion-md-download"></i>&nbsp;Download Supplier Template</span>
                    <br>
                    <div class="row">
                        <div class="col-sm">
                            <div class="row">
                                <div class="card">
                                    <div class="card-body">

                                        <input type="file" class="" id="supplierfileUpload"  accept=".xlsx, .xls" />
                                        <button type="button"  class="btn btn-info btn-block mt-10 uploadBtn" name="uploadsupplier" id="uploadsupplier" >
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
    <script type="text/javascript" src="{{URL::to('/')}}/public/build/js/intlTelInput.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/datepicker.js"></script>
    <script src="{{URL::to('/')}}/public/bower_components/sweetalert/js/sweetalert.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/modulejs/supplier/supplier.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/dist/js/xlsx.full.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/modulejs/common.js"></script>

    <script>
        $(document).ready(function () {

            selectdiacode();
        });
        $('#searchCollapse').click(function(e){
            $('#searchbox').slideToggle();
        })
        var state ='<?php echo $state ?>';

        var supplier_treatments = '<?php echo $supplier_treatments ?>';

        var salutation = '<?php echo $salutation ?>';

            function selectdiacode() {
                $("#supplier_form").find('.mobileregax').each(function () {
                    var id = $(this).attr('id');

                    if (id != 'supplier_company_mobile_no') {

                        var input = document.querySelector('#' + id);

                        window.intlTelInput(input, {
                            initialCountry: mobile_dial_code,
                            separateDialCode: true,
                            autoPlaceholder: false,

                            utilsScript: "{{URL::to('/')}}/public/build/js/utils.js",
                        });
                    }
                });
                var dataid = $("#companymobileplus").data('id');

                if (dataid > 1) {
                    var i = 1;


                    for (i = 1; i <= dataid; i++) {

                        var input = document.querySelector('#mobile_' + i + ' #supplier_company_mobile_no');

                        window.intlTelInput(input, {
                            initialCountry: mobile_dial_code,
                            separateDialCode: true,
                            autoPlaceholder: false,

                            utilsScript: "{{URL::to('/')}}/public/build/js/utils.js",
                        });


                    }
                }
            }

    </script>

@endsection
