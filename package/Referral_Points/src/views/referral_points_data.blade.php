<?php
/**
 * Created by PhpStorm.
 * User: retailcore
 * Date: 18/3/19
 * Time: 4:21 PM
 */
?>

<table id="flat_points_record" class="table tablesaw table-bordered table-hover  mb-0"   data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>
        <thead>
        <tr class="blue_Head">
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1" >Type Of Purchase</th>
            <th scope="col" class="text-center" data-tablesaw-sortable-col data-tablesaw-priority="4">Reffering Customer</th>
            <th scope="col" class="text-center" data-tablesaw-sortable-col data-tablesaw-priority="5">New Customer</th>
            <th scope="col" class="text-center" data-tablesaw-sortable-col data-tablesaw-priority="5">Points Amount</th>
        </tr>
        </thead>
        <tbody id="referral_tbody">
        @foreach($referral_points AS $referral_points_key =>$referral_points_value)
        <tr id="{{$referral_points_value['referral_point_id']}}">
            <td>{{$referral_points_value['type_of_purchase']}}</td>
            <td>
                <div class="row">
                    <div class="col-md-3 mt-30">
                        {{--<input type="checkbox" class="form-control" name="reffering_customer_{{$referral_points_value['referral_point_id']}}" id="reffering_customer_{{$referral_points_value['referral_point_id']}}">--}}
                    </div>
                <div class="col-md-3">
                    <label class="form-label">Percent %</label>
                    <input type="text" value="{{$referral_points_value['reffering_percent']}}" class="form-control form-inputtext number percentcls" name="reffering_percent_{{$referral_points_value['referral_point_id']}}" id="reffering_percent_{{$referral_points_value['referral_point_id']}}" >
                </div>
                <div class="col-md-3">
                    <label class="form-label">Points</label>
                    <input type="text" value="{{$referral_points_value['reffering_points']}}" class="form-control form-inputtext number pointscls"  name="reffering_points_{{$referral_points_value['referral_point_id']}}" id="reffering_points_{{$referral_points_value['referral_point_id']}}">
                </div>
                  {{--  <div class="col-md-3" >
                        <label class="form-label">Points Amount</label>
                        <input type="text" disabled value="{{$referral_points_value['reffering_points_amount']}}" class="form-control number form-inputtext pointamtcls"  name="reffering_points_amount_{{$referral_points_value['referral_point_id']}}" id="reffering_points_amount_{{$referral_points_value['referral_point_id']}}">
                    </div>--}}
                </div>
            </td>
            <td>
                <div class="row">
                    <div class="col-md-3 mt-30">
                        {{--<input type="checkbox" class="form-control" name="new_customer_{{$referral_points_value['referral_point_id']}}" id="new_customer_{{$referral_points_value['referral_point_id']}}">--}}
                    </div>
                <div class="col-md-3">
                    <label class="form-label">Percent  %</label>
                <input type="text" value="{{$referral_points_value['new_customer_percent']}}" class="form-control form-inputtext number percentcls"  name="new_customer_percent_{{$referral_points_value['referral_point_id']}}" id="new_customer_percent_{{$referral_points_value['referral_point_id']}}">
                </div>
                    <div class="col-md-3">
                        <label class="form-label">Points</label>
                        <input type="text" value="{{$referral_points_value['new_customer_points']}}" class="form-control form-inputtext number pointscls"  name="new_customer_points_{{$referral_points_value['referral_point_id']}}" id="new_customer_points_{{$referral_points_value['referral_point_id']}}">
                    </div>
                   {{-- <div class="col-md-3">
                        <label class="form-label">Points Amount</label>
                        <input type="text" disabled value="{{$referral_points_value['new_customer_points_amount']}}" class="form-control form-inputtext number pointamtcls"  name="new_customer_points_amount_{{$referral_points_value['referral_point_id']}}" id="new_customer_points_amount_{{$referral_points_value['referral_point_id']}}">
                    </div>--}}
                </div>
            </td>
            <?php if($referral_points_key == 0) { ?>
            <td id="point_amount_td">
                <label class="form-label">Points Amount</label>
                <input type="text" disabled value="{{$referral_points_value['points_amount']}}" class="form-control form-inputtext number pointamtcls"  name="points_amount" id="points_amount">
            </td>
            <?php } ?>
        </tr>
        @endforeach


<tr>
    <td  colspan="6" align="center">

    </td>
</tr>
   </tbody>
</table>

    <input type="hidden" name="hidden_page" id="hidden_page" value="1" />
    <input type="hidden" name="hidden_column_name" id="hidden_column_name" value="customer_source_id" />
    <input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="asc" />
    <input type="hidden" name="fetch_data_url" id="fetch_data_url" value="customer_source_fetch_data" />


<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script>
    $("#point_amount_td").attr('rowspan','2');
</script>






