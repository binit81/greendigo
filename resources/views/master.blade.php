    <!DOCTYPE html>
<!--
Template Name: Mintos - Responsive Bootstrap 4 Admin Dashboard Template
Author: Hencework
Contact: https://hencework.ticksy.com/

License: You must have a valid license purchased only from templatemonster to legally use the template for your project.
-->
<html lang="en">


<!-- Mirrored from hencework.com/theme/mintos/dashboard1.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 05 Mar 2019 07:02:35 GMT -->
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Retailcore</title>
    <meta name="description" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.png">

    <link rel="icon" type="image/png" href="favicon.png" />

    <!-- vector map CSS -->
    <link href="{{URL::to('/')}}/public/template/vectormap/jquery-jvectormap-2.0.3.css" rel="stylesheet" type="text/css" />

    <!-- Toggles CSS -->
    <link href="{{URL::to('/')}}/public/template/jquery-toggles/css/toggles.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/template/jquery-toggles/css/themes/toggles-light.css" rel="stylesheet" type="text/css">

    <!-- Toastr CSS -->
    <link href="{{URL::to('/')}}/public/template/jquery-toast-plugin/dist/jquery.toast.min.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/template/ion-rangeslider/css/ion.rangeSlider.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/template/ion-rangeslider/css/ion.rangeSlider.skinHTML5.css" rel="stylesheet" type="text/css">

    <!-- select2 CSS -->
    <link href="{{URL::to('/')}}/public/template/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />



    <!-- Toastr CSS -->
    <link href="{{URL::to('/')}}/public/template/jquery-toast-plugin/dist/jquery.toast.min.css" rel="stylesheet" type="text/css">

    <!-- Custom CSS -->
    <link href="{{URL::to('/')}}/public/dist/css/style.css" rel="stylesheet" type="text/css">


    <!-- Pickr CSS -->
    <link href="{{URL::to('/')}}/public/template/pickr-widget/dist/pickr.min.css" rel="stylesheet" type="text/css" />

    <!-- Daterangepicker CSS -->
    <link href="{{URL::to('/')}}/public/dist/css/daterangepicker.css" rel="stylesheet" type="text/css" />
    <link href="{{URL::to('/')}}/public/template/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
    <link href="{{URL::to('/')}}/public/template/datatables.net-dt/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="{{URL::to('/')}}/public/template/datatables.net-responsive-dt/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="{{URL::to('/')}}/public/template/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css" />

    <link href="{{URL::to('/')}}/vendor/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css" />

    <link href="{{URL::to('/')}}/public/template/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{URL::to('/')}}/public/template/jquery-steps/demo/css/jquery.steps.css">
    <link href="{{URL::to('/')}}/public/template/dropzone/dist/dropzone.css" rel="stylesheet" type="text/css"/>
    <link href="{{URL::to('/')}}/public/template/dropify/dist/css/dropify.min.css" rel="stylesheet" type="text/css"/>

    <!-- Custom CSS -->
    <link href="{{URL::to('/')}}/public/template/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
   {{-- <link href="https://fonts.googleapis.com/icon?family=Material+Icons|Nunito:300,400,400i,600,700" rel="stylesheet" type="text/css">--}}

    {{--<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" >--}}

        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">


    <link href="{{URL::to('/')}}/public/template/owl.carousel/dist/assets/owl.carousel.min.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/template/owl.carousel/dist/assets/owl.theme.default.min.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/dist/css/style.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/dist/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/dist/css/ionicons.min.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/dist/css/dripicons.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/dist/css/glyphicons.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/dist/css/themify-icons.css" rel="stylesheet" type="text/css">
    <link href="{{URL::to('/')}}/public/dist/css/animate.css" rel="stylesheet" type="text/css">
     <link href="{{URL::to('/')}}/public/dist/css/simple-line-icons.css" rel="stylesheet" type="text/css">
      <link href="{{URL::to('/')}}/public/dist/css/linea-icon.css" rel="stylesheet" type="text/css">
      <link href="{{URL::to('/')}}/public/dist/css/pe-icon-7-stroke.css" rel="stylesheet" type="text/css">
      {{--<link href="{{URL::to('/')}}/public/dist/css/material-design-iconic-font.min.css" rel="stylesheet" type="text/css">--}}
      <link href="{{URL::to('/')}}/public/css/commoncss.css" rel="stylesheet" type="text/css">
      <link href="{{URL::to('/')}}/public/bower_components/toastr/toastr.css" rel="stylesheet" type="text/css">




</head>

<body>

    <?php

    if($software['software_installation']==1)
    {
        if($software['installation_remaining_days']>=$software['installation_days'])
        {
            // echo 'running';
        }
        elseif($software['installation_remaining_days']<$software['installation_days'] && $software['installation_remaining_days']>0)
        {
            // echo 'alert';
            ?>
            <script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>
            <script type="text/javascript">
            $(document).ready(function(e){
                var installation_to     =   "<?php echo date('d M, Y',strtotime($software['installation_to']))?>";
                var installation_days     =   "<?php echo $software['installation_remaining_days']?>";
                 jQuery.toast({
                    heading: '<b>AMC Expiring Alert!</b>',
                    text: '<p>Software AMC will expire on: <b>'+installation_to+'</b> with in <b>'+installation_days+'</b> days<br><br><b>Contact Sales team for further assistance</b></p>',
                    position: 'top-right',
                    loaderBg:'#7a5449',
                    class: 'jq-toast-danger',
                    hideAfter: 11155500,
                    stack: 6,
                    showHideTransition: 'fade'
                });
            })
            </script>
            <?php
        }
        elseif($software['installation_remaining_days']==0 || $software['installation_remaining_days']<0)
        {
            // echo 'shutdown';
            ?>
            <script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>
            <script type="text/javascript">
            $(document).ready(function(e){
                var installation_to     =   "<?php echo date('d M, Y',strtotime($software['installation_to']))?>";
                var installation_days     =   "<?php echo $software['installation_remaining_days']?>";
                 jQuery.toast({
                    heading: '<b>AMC Expired Alert!</b>',
                    text: '<p>Software AMC is expired on: <b>'+installation_to+'</b><br><b>Contact Sales team for further assistance</b></p>',
                    position: 'top-right',
                    loaderBg:'#7a5449',
                    class: 'jq-toast-danger',
                    hideAfter: 11155500,
                    stack: 6,
                    showHideTransition: 'fade'
                });
            })
            </script>
            <?php
        }
    }

    ///////////////////////////////////
    ///////////////////////////////////
   if($software['software_installation']==1)
    {
        if($software['installation_remaining_days']>=$software['installation_days'])
        {
            // echo 'running';
        }
        elseif($software['installation_remaining_days']<$software['installation_days'] && $software['installation_remaining_days']>0)
        {
            // echo 'alert';
            ?>
            <script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>
            <script type="text/javascript">
            $(document).ready(function(e){
                var installation_to     =   "<?php echo date('d M, Y',strtotime($software['installation_to']))?>";
                var installation_days     =   "<?php echo $software['installation_remaining_days']?>";
                 jQuery.toast({
                    heading: '<b>Software AMC Expiry in '+installation_days+' days</b>',
                    text: '<p><b>Renewal date : '+installation_to+'</b><br>With AMC you will get support on issues, training, and updates.<br><br>To renew call <b>83697 23300</b> or email to <a href="mailto:sales@retailcore.in" style="color:#333333;border-bottom:0;">sales@retailcore.in</a></p>',
                    position: 'top-right',
                    loaderBg:'#7a5449',
                    class: 'jq-toast-danger',
                    hideAfter: 11155500,
                    stack: 6,
                    showHideTransition: 'fade'
                });
            })
            </script>
            <?php
        }
        elseif($software['installation_remaining_days']==0 || $software['installation_remaining_days']<0)
        {
            // echo 'shutdown';
            ?>
            <script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>
            <script type="text/javascript">
            $(document).ready(function(e){
                var installation_to     =   "<?php echo date('d M, Y',strtotime($software['installation_to']))?>";
                var installation_days     =   "<?php echo $software['installation_remaining_days']?>";
                 jQuery.toast({
                    heading: '<b>Technical Support Renewal</b>',
                    text: '<p><b>Renewal date : '+installation_to+'</b><br><b>Please note :</b> If not renewed then your software license will not be eligible for support and updates.<br><br>To renew call <b>83697 23300</b> or email to <a href="mailto:sales@retailcore.in" style="color:#333333;border-bottom:0;">sales@retailcore.in</a></p>',
                    position: 'top-right',
                    loaderBg:'#FFFFC3',
                    class: 'jq-toast-danger',
                    hideAfter: 11155500,
                    stack: 6,
                    showHideTransition: 'fade'
                });
            })
            </script>
            <?php
        }
    }

    ///////////////////////////////////
    ///////////////////////////////////
    // print_r($software); exit;
    if($software['software_license']==1)
    {
        if($software['license_remaining_days']>=$software['license_days'])
        {
            // echo 'running';
        }
        elseif($software['license_remaining_days']<$software['license_days'] && $software['license_remaining_days']>0)
        {
            // echo 'alert';
            ?>
            <script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>
            <script type="text/javascript">
            $(document).ready(function(e){
                var license_to     =   "<?php echo date('d M, Y',strtotime($software['license_to']))?>";
                var installation_days     =   "<?php echo $software['installation_remaining_days']?>";
                 jQuery.toast({
                    heading: '<b>Cloud Server Renewal</b>',
                    text: '<p><b>Renewal date : '+license_to+'</b><br>Please note : If not renewed then your software will <b>stop working</b><br><br>To renew call <b>83697 23300</b> or email to <a href="mailto:sales@retailcore.in" style="color:#333333;border-bottom:0;">sales@retailcore.in</a></p>',
                    position: 'top-right',
                    loaderBg:'#7a5449',
                    class: 'jq-toast-danger',
                    hideAfter: 11155500,
                    stack: 6,
                    showHideTransition: 'fade'
                });
            })
            </script>
            <?php
        }
        elseif($software['license_remaining_days']==0 || $software['license_remaining_days']<0)
        {
            // echo 'shutdown';
            ?>
            <script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>

            <script>
            $(document).ready(function(e){
                $('#showLicenseBox').modal('show');
            });
            </script>

            <style type="text/css">
            .modal-backdrop.show{
                opacity: 1  !important;
            }
            </style>
            <div class="modal fade mt-100" id="showLicenseBox" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="showResume" aria-hidden="true">
            <form method="post" name="license_key" id="license_key">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <h5 class="modal-title center" style="width:100%">
                            <center>Software is Suspended!</center>
                        </h5>


                    </div>
                    <div class="modal-body">

                        <table width="100%" border="0" cellpadding="6" cellspacing="2">
                          <tr>
                            <td colspan="3" align="center"><small><b>Please call 83697 23300 or email to sales@retailcore.in</b></small></td>
                          </tr>
                        </table>
                    </div>

                </div>

            </div>
            </form>
            </div>
            <?php
        }
    }


    if(sizeof($company_code)!=0)
    {
        if($company_code[0]['company_code']=='')
        {
            ?>
            <script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>

            <script>
            $(document).ready(function(e){
                $('#showLicenseBox').modal('show');
            });
            </script>

            <style type="text/css">
            .modal-backdrop.show{
                opacity: 1  !important;
            }
            </style>

            <div class="modal fade mt-100" id="showLicenseBox" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="showResume" aria-hidden="true">
            <form method="post" name="license_key" id="license_key">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <h5 class="modal-title center" style="width:100%">
                            <center>Please enter your purchased licensed key to activate the software.</center>
                        </h5>


                    </div>
                    <div class="modal-body">

                        <table width="100%" border="0" cellpadding="6" cellspacing="2">
                          <tr>
                            <td align="right" width="30%">Software License Key</td>
                            <td align="left" width="40%"><input type="text" name="apiKey" id="apiKey" class="form-control mt-10 form-inputtext invalid" /></td>
                            <td align="left" width="30%"><button type="submit" name="licenseBtn" id="licenseBtn" class="btn btn-primary savebtn">Activate Software</button></td>
                          </tr>
                          <tr>
                            <td colspan="3" align="center"><small><b>Note:</b> if you don't have the license key, please contact support.</small></td>
                          </tr>
                        </table>
                    </div>

                </div>

            </div>
            </form>
            </div>
            <?php
        }
    }


    if($role_permissions['userType']==0)
    {

    }
    else
    {
        if($role_permissions['permission_view']==0)
        {
            ?><script>window.location='404'</script><?php
        }
    }

    ?>

    @include('home_navigation')

    @include('layouts.footer-scripts')
    {{--FOR SOFTWARE CONFIGURATION--}}

    <div class="modal fade" id="software_configuration_popup" style="border:1px solid !important;">
        <div class="modal-dialog" style="max-width:70% !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Software Configuration</h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">x</span>
                    </button>
                </div>

                <div class="verify_form">
                    <form name="software_verification_form" id="software_verification_form" onsubmit="return false;">
                        <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="text" autocomplete="off" maxlength="15" name="configuration_password" id="configuration_password" value="" autofocus class="form-control form-inputtext invalid" placeholder="">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"></label>
                                <button type="button" name="verify_password" class="btn btn-success saveBtn btn-block" id="verify_password" data-container="body" data-toggle="popover" data-placement="bottom">Submit</button>
                        </div>
                        </div>
                    </form>
                </div>



                <form style="display: none;" name="software_configuration_form" id="software_configuration_form">

                    <?php
                    $configuration = '';
                    $checked = 'checked';

                    if(sizeof($nav_type)!=0)
                    {
                        $configuration = $nav_type[0];
                        $checked = '';
                    }
                    ?>
                    <input type="hidden" name="company_profile_id" id="company_profile_id" value="<?php echo isset($configuration)&&$configuration != ''?$configuration['company_profile_id'] : '' ?>">
                    <div class="row">
                    <div class="col-sm">
                        <div class="row">

                            <div class="row ma-0 pa-0">
                                <div class="col-md-8">
                                    <div class="card">
                                <div class="card-body">
                                    <h5 class="card-text">Tax Type</h5>
                                    <div class="row ma-0 pa-0">
                                        <div class="col-md-4 ma-0 pa-0">
                                            <input type="radio" class="form-control" id="international_vat" name="tax_type" value="1" style="width:15% !important;height: calc(1.30rem) !important;"
                                            <?php if(isset($configuration)&&$configuration!= ''&&$configuration['tax_type'] == 1) echo "checked" ?>><b style="font-size:14px !important;">International Vat</b>
                                        </div>
                                        <div class="col-md-8 ma-0 pa-0">
                                            <input type="radio" class="form-control" id="indian_gst" <?php echo $checked?> name="tax_type" <?php if(isset($configuration)&&$configuration!= ''&&$configuration['tax_type'] == 2)echo "checked" ?> value="2" style="width:15% !important;height: calc(1.30rem) !important;" >
                                            <span style="margin:2px 0 0 0;"><b style="font-size:14px !important;">Indian GST</b></span>
                                        </div>
                                    </div>
                                    <br>
                                    <?php $showhide = "none";
                                    if(isset($configuration)&&$configuration!=''&& $configuration['tax_type']=='1')
                                    {
                                        $showhide = "show";
                                    }

                                    ?>
                                    <div class="row ma-0 pa-0">
                                        <div class="col-md-4 ma-0 vatdetails" style="display:<?php echo $showhide?>">
                                            <b style="font-size:14px !important;">Tax Title</b>
                                            <input type="text" name="tax_title" id="tax_title" class="form-control form-inputtext" value="<?php echo isset($configuration)&&$configuration != ''?$configuration['tax_title'] : '' ?>">
                                        </div>
                                        <div class="col-md-4 ma-0 vatdetails" style="display:<?php echo $showhide?>">
                                            <b style="font-size:14px !important;">Currency Title</b>
                                            <input type="text" name="currency_title" id="currency_title" class="form-control form-inputtext" value="<?php echo isset($configuration)&&$configuration != ''?$configuration['currency_title'] : '' ?>">
                                        </div>
                                        <div class="col-md-4 ma-0 decimaldetails">
                                            <b style="font-size:14px !important;">Decimal Points</b>
                                            <input type="text" name="decimal_points" maxlength="1" id="decimal_points" class="form-control form-inputtext" onkeydown="return validateNumber(event);" value="<?php echo isset($configuration)&&$configuration != ''?$configuration['decimal_points'] : '' ?>">
                                        </div>
                                    </div>


                                </div>
                            </div>
                                </div>
                                <div class="col-md-4 ma-0 pa-0">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-text">Invoice/PO/Receipt Type<i class="fa fa-info-circle" title="Info to be shown of bottom of your bill"></i></h5>
                                            <div class="row ma-0">
                                                <div class="col-md-12 ma-5">
                                                     <input type="radio" class="form-control" maxlength="20" name="series_type" id="regular_series" <?php echo $checked?>  value="1" <?php echo (isset($configuration)&&$configuration != ''&&$configuration['series_type']==1)? "checked" : ''?> style="width:15% !important;height: calc(1.30rem) !important;"><b style="font-size:14px !important;"> Regular </b>
                                                </div>
                                                <div class="col-md-12 ma-5">
                                                     <input type="radio" class="form-control" maxlength="20" name="series_type"
                                                               id="monthwise_series" value="2" <?php echo (isset($configuration)&&$configuration != ''&&$configuration['series_type']==2)? "checked" : ''?>style="width:15% !important;height: calc(1.30rem) !important;"><b style="font-size:14px !important;"> Month Wise </b>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-text">Billing Type<i class="fa fa-info-circle" title="Info to be shown of bottom of your bill"></i></h5>
                                            <div class="row ma-0">
                                                <div class="col-md-4 ma-0 pa-0"><input type="radio" class="form-control" id="billtype_without_gst" name="billtype" <?php echo $checked?>
                                                    <?php echo isset($configuration)&&$configuration != ''&&$configuration['billtype']==1?'checked':'';?>
                                                    value="1" style="width:15% !important;height: calc(1.30rem) !important;"><b style="font-size:14px !important;">Without GST Range</b></div>
                                                <div class="col-md-4 ma-0 pa-0">
                                                    <input type="radio" class="form-control" id="billtype_with_gst" name="billtype" value="2" style="width:15% !important;height: calc(1.30rem) !important;"
                                                    <?php echo isset($configuration)&&$configuration != ''&&$configuration['billtype']==2?'checked':''; ?>><span style="margin:2px 0 0 0;"><b style="font-size:14px !important;">With GST Range</b></span>
                                                </div>
                                                <div class="col-md-4 ma-0 pa-0">
                                                    <input type="radio" class="form-control" id="billtype_batch_no" name="billtype" value="3" style="width:15% !important;height: calc(1.30rem) !important;"
                                                    <?php echo isset($configuration)&&$configuration != ''&&$configuration['billtype']==3?'checked':''; ?>><span style="margin:2px 0 0 0;"><b style="font-size:14px !important;">Batch No. Wise</b></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>





                            <div class="row">
                                <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-text">Billing Print Type<i class="fa fa-info-circle" title="Info to be shown of bottom of your bill"></i></h5>
                                    <div class="row ma-0">
                                        <div class="col-md-6 ma-0 pa-0">
                                            <input type="radio" class="form-control" id="a5_print" name="billprint_type" <?php echo $checked?>  value="1" <?php echo (isset($configuration)&&$configuration != ''&&$configuration['billprint_type']==1)? "checked" : ''?> style="width:15% !important;height: calc(1.30rem) !important;"><b style="font-size:14px !important;">A4/A5 Print</b></div>
                                        <div class="col-md-6 ma-0 pa-0">
                                            <input type="radio" class="form-control" id="thermal_print" name="billprint_type" value="2" <?php echo (isset($configuration)&&$configuration != ''&&$configuration['billprint_type']==2)? "checked" : ''?> style="width:15% !important;height: calc(1.30rem) !important;" ><span style="margin:2px 0 0 0;"><b style="font-size:14px !important;">Thermal Print</b></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                </div>
                                <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-text">Navigation Type<i class="fa fa-info-circle" title="manage your navigation location"></i></h5>
                                    <div class="row ma-0">
                                        <div class="col-md-6">
                                            <input type="radio" class="form-control" <?php echo $checked?>  name="navigation_type" value="1" style="width:15% !important;height: calc(1.30rem) !important;" <?php echo (isset($configuration)&&$configuration != ''&&$configuration['navigation_type']==1)? "checked" : ''?>><b style="font-size:14px !important;">Horizontal(Top)</b>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="radio" class="form-control" id="navigation_type" name="navigation_type" value="2" style="width:15% !important;height: calc(1.30rem) !important;" <?php echo isset($configuration)&&$configuration != ''&&$configuration['navigation_type']==2?'checked':''; ?>><span style="margin:2px 0 0 0;"><b style="font-size:14px !important;">Vertical(Left)</b></span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                                </div>
                                <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-text">Inward Type</h5>
                                    <div class="row ma-0">
                                        <div class="col-md-6">
                                            <input type="radio" class="form-control"   <?php echo $checked?> id="fmcg_inward" name="inward_type" value="1" <?php  if(isset($configuration)&&$configuration!= ''&&$configuration['inward_type'] == '1') echo "checked"?>
                                                   style="width:15% !important;height: calc(1.30rem) !important;">
                                            <b style="font-size:14px !important;">FMCG</b>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="radio" class="form-control" id="garment_inward" name="inward_type" <?php  if(isset($configuration)&&$configuration!= ''&&$configuration['inward_type'] == '2') echo "checked" ?> value="2" style="width:15% !important;height: calc(1.30rem) !important;" >
                                            <span style="margin:2px 0 0 0;"><b style="font-size:14px !important;">Garment</b></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                </div>
                                <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-text">Inward Calculation</h5>
                                    <div class="row ma-0">
                                        <div class="col-md-6">
                                            <input type="radio" <?php echo $checked?> class="form-control" id="without_roundoff" name="inward_calculation" value="1" style="width:15% !important;height: calc(1.30rem) !important;"
                                            <?php if(isset($configuration)&&$configuration!= ''&&$configuration['inward_calculation'] == '1') echo "checked"?>>
                                            <b style="font-size:14px !important;">Without Roundoff</b>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="radio" class="form-control" id="with_roundoff" name="inward_calculation" <?php if(isset($configuration)&&$configuration!= ''&&$configuration['inward_calculation'] == '2')echo "checked" ?> value="2" style="width:15% !important;height: calc(1.30rem) !important;" >
                                            <span style="margin:2px 0 0 0;"><b style="font-size:14px !important;">With Roundoff</b></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="row pa-0 ma-0">
                        <div class="col-sm-3 pa-10 pr-5">
                        <button type="button" name="add_software_configuration" class="btn btn-success saveBtn btn-block"
                                id="add_software_configuration" data-container="body" data-toggle="popover" data-placement="bottom">Update Information
                        </button>
                        </div>
                    </div>
            </div>
            </form>
        </div>
    </div>
    </div>

    {{--end of software configuration popup--}}





    {{--FOR DEPENDENT POPUP--}}
        <div class="modal fade" id="dependency_popup" style="border:1px solid !important;">
            <div class="modal-dialog" style="max-width:70% !important;">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                        <h5 class="modal-title">Dependent Record</h5>
                            </div>
                            <div class="col-md-4"></div>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>


                        <form name="dependent_form" id="dependent_form" onsubmit="return false;">


                            <table class="table tablesaw table-bordered table-hover table-striped mb-0" width="100%" cellpadding="6" border="0" frame="box" style="border:1px solid #C0C0C0 !important;">

                                        <thead>
                                        <tr class="blue_Head">
                                                <th class="text-dark font-14 font-weight-600" style="width:3% !important;">Sr No.</th>
                                                <th class="text-dark font-14 font-weight-600" style="width:3% !important;">Module Name</th>
                                                <th class="text-dark font-14 font-weight-600" style="width:3% !important;">Details</th>
                                                {{--<th class="text-dark font-14 font-weight-600" style="width:3% !important;color:#fff !important;">Created DateTime<br/>Updated Datetime</th>--}}
                                        </tr>
                                            </thead>
                                            <tbody id="view_dependent_record" style="height: 50px;overflow-y: auto">

                                            </tbody>
                                        </table>


                        </form>
                </div>
            </div>
        </div>

    {{--end of dependent popup--}}
    
    <style type="text/css">
.iagreebox{
    background: #ffffff;
    text-align: center;
    padding: 10px 0;
    width: 100%;
    position: fixed;
    bottom: 0;
    font-size: 13px;
}
.iagreePopup{
    width: 80%;
    margin: 100px auto;
    left: 10%;
    background: #ffffff;
    padding: 20px;
    position: fixed;
    top: 0;
    height: 80%;
    box-shadow: #999999 0px 0px 3px;
    overflow: auto;
    z-index: 1000000;
}
.closeBtn{
    width: 80%;
    margin: 100px auto;
    left: 10%;
    background: #ffffff;
    padding: 20px;
    position: fixed;
    top: 0;
    text-align: right;
    overflow: auto;
    z-index: 1000000;
    cursor: pointer;
}
</style>
<div class="iagreebox">By using RetailCore Software you agree to our <a id="iagreeClick" style="color:#0011E9; cursor:pointer; text-decoration:underline;">software service level agreement (SLA)</a></div>


<?php
    $iagree     =   DEFAULT_COMPANY_URL.'rc_terms_and_conditions.html';
?>
<div class="iagreePopup" style="display:none;">
    
    <br clear="all" /><br clear="all" />
    <?php  @include($iagree) ?>
</div>
<div class="closeBtn" style="display:none;"><b>Close [x]</b></div>

<!--<script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>-->
<script type="text/javascript">
$(document).ready(function(e){
    $('#iagreeClick').click(function(e){
        $('.iagreePopup').show();
        $('.closeBtn').show();
    })
    $('.closeBtn').click(function(e){
        $('.iagreePopup').hide();
        $('.closeBtn').hide();
    })
})
</script>
</body>


<!-- Mirrored from hencework.com/theme/mintos/dashboard1.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 05 Mar 2019 07:03:17 GMT -->
</html>
    <script>
        <?php if(sizeof($nav_type)!=0){?>

        var bill_type           = '<?php echo $nav_type[0]['billtype']?>';
        var tax_type            = '<?php echo $nav_type[0]['tax_type']?>';
        var tax_title           = '<?php echo $nav_type[0]['tax_title']?>';
        var currency_title      = '<?php echo $nav_type[0]['currency_title']?>';
        var decimal_points      = '<?php echo $nav_type[0]['decimal_points']?>';
        var billprint_type      = '<?php echo $nav_type[0]['billprint_type']?>';
        var product_image_url   = '<?php echo PRODUCT_IMAGE_URL?>';
        var employee_image_url  = '<?php echo EMPLOYEE_IMAGE_URL?>';
        var DAMAGE_USED_PRODUCT_IMAGE  = '<?php echo DAMAGE_USED_PRODUCT_IMAGE?>';
        var company_dial_code   = '<?php echo $nav_type[0]['company_mobile_dial_code']?>';
        var mobile_dial_code    =  company_dial_code.split(',')[1];
        var po_with_unique_barcode   = '<?php echo $nav_type[0]['po_with_unique_barcode']?>';
        var po_calculation   = '<?php echo $nav_type[0]['po_calculation']?>';
        var inward_calculation = '<?php echo $nav_type[0]['inward_calculation']?>';
        var mrp_modification_type = '<?php echo $nav_type[0]['mrp_modification_type']?>';
        var bill_calculation = '<?php echo $nav_type[0]['bill_calculation']?>';
        var product_calculation = '<?php echo $nav_type[0]['product_calculation']?>';
        var inward_type = '<?php echo $nav_type[0]['inward_type']?>';
        var bill_excel_column_check = '<?php echo $nav_type[0]['bill_excel_column_check']?>';
        var po_with_unique_barcode = '<?php echo $nav_type[0]['po_with_unique_barcode']?>';
        var product_item_type = '<?php echo $nav_type[0]['product_item_type']?>';

        <?php } ?>
    </script>



