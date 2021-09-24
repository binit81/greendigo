<?php
/**
 * Created by PhpStorm.
 * User: Hemaxi
 * Date: 24/10/19
 * Time: 17:06 PM
 */
?>
@include('pagetitle')
@extends('master')
@section('main-hk-pg-wrapper')


    <div class="col-xl-12">
        <div class="hk-row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <form name="loyalty_setupform" id="loyalty_setupform">
                            <input type="hidden" name="loyalty_setup_id" id="loyalty_setup_id" value="<?php echo (isset($loyalty_setup_data)) ? $loyalty_setup_data['loyalty_setup_id'] : '' ?>">
                            <div class="col-sm">
                                <div class="row">

                                    <div class="col-md-2">
                                        <label class="form-label">Schedule Date</label>
                                        <input type="text"  name="schedule_date" id="schedule_date" value="<?php echo (isset($loyalty_setup_data)) ? $loyalty_setup_data['schedule_date'] : '' ?>" autocomplete="off" class="form-control form-inputtext invalid" placeholder="">
                                    </div>

                                    <?php
                                    $chk = '';
                                    $dis = '';
                                    $cls = 'invalid';
                                    if(isset($loyalty_setup_data) && $loyalty_setup_data['expiry_date'] == '')
                                        {
                                            $chk = "checked";
                                            $cls = '';
                                            $dis = 'disabled';
                                        }
                                    ?>

                                    <div class="col-md-2">
                                        <label class="form-label">Infinity</label>
                                        <input type="checkbox" <?php echo $chk ?> name="infinity" id="infinity" value="">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="text"  name="expiry_date" id="expiry_date" value="<?php echo (isset($loyalty_setup_data)) ? $loyalty_setup_data['expiry_date'] : '' ?>" autocomplete="off" <?php echo $dis ?> class="form-control form-inputtext <?php echo $cls ?>" placeholder="">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Purchase Amount</label>
                                        <input type="text" name="purchase_amount" id="purchase_amount" value="<?php echo (isset($loyalty_setup_data)) ? $loyalty_setup_data['purchase_amount'] : '' ?>" class="form-control form-inputtext number invalid" placeholder="">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Points</label>
                                        <input type="text" name="points" id="points" value="<?php echo (isset($loyalty_setup_data)) ? $loyalty_setup_data['points'] : '' ?>" class="form-control form-inputtext number invalid" placeholder="">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Points Amount</label>
                                        <input type="text" name="points_amount" id="points_amount" value="<?php echo (isset($loyalty_setup_data)) ? $loyalty_setup_data['points_amount'] : '' ?>" class="form-control form-inputtext number invalid" placeholder="">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Redeem Point Limit</label>
                                        <input type="text" name="redeem_point" id="redeem_point" value="<?php echo (isset($loyalty_setup_data)) ? $loyalty_setup_data['redeem_point'] : '' ?>" class="form-control form-inputtext number invalid" placeholder="">
                                    </div>

                                    <div class="col-md-4"></div>
                                    <div class="col-md-4"></div>


                                    <div class="col-md-4 pt-25 rightAlign">
                                        <?php
                                        if($role_permissions['permission_add'] == 1)
                                        {
                                        ?>
                                        <button type="button" name="add_loyaltysetup" class="btn btn-info addbutton saveBtn" id="add_loyaltysetup" data-container="body" data-toggle="popover" data-placement="bottom" data-content="">
                                            <i class="fa fa-save"></i>Add Loyalty Setup
                                        </button>
                                        <?php
                                        }
                                        ?>
                                        <button type="button" name="resetloyalty_setup" class="btn btn-info resetbtn" id="resetloyalty_setup" data-container="body" data-toggle="popover" data-placement="bottom" data-content="">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/bootstrap/js/bootstrap.min.js"></script>

    <script src="{{URL::to('/')}}/public/modulejs/Loyaltysetup/loyaltysetup.js"></script>

@endsection
