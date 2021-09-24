<?php
/**
 * Created by PhpStorm.
 * User: Hemaxi
 * Date: 26/4/19
 * Time: 17:37 AM
 */

$pending_amt = $outstanding_detail - $amount_payable;
$total_paid_amt = 0;


?>
<table class="table tablesaw view-bill-screen table-hover pb-30 dtr-inline">
                                        <thead>
                                        <tr>
                                            <th scope="col" class="billsorting centerAlign" data-tablesaw-priority="persist">View</th>
                                            <th scope="col" class="billsorting" data-sorting_type="asc" data-column_name="supplier_company_name"  data-tablesaw-sortable-col data-tablesaw-priority="1">Supplier Company<span id="supplier_company_name_icon"></span></th>
                                            <th scope="col" class="billsorting" data-sorting_type="asc" data-column_name="supplier_first_name" data-tablesaw-sortable-col data-tablesaw-priority="2">Supplier Name<span id="supplier_first_name_icon"></span></th>
                                            <th scope="col" class="billsorting centerAlign" data-sorting_type="asc" data-column_name="supplier_company_mobile_no" data-tablesaw-sortable-col data-tablesaw-priority="3">Mobile No.<span id="supplier_company_mobile_no  _icon"></span></th>
                                            <th scope="col" class="billsorting rightAlign" data-sorting_type="asc" data-column_name=""data-tablesaw-sortable-col data-tablesaw-priority="4">Outstanding Amount</th>
                                            <th scope="col" class="billsorting rightAlign" data-sorting_type="asc" data-column_name=""data-tablesaw-sortable-col data-tablesaw-priority="5">Paid Amount</th>
                                            <th scope="col" class="billsorting rightAlign" data-sorting_type="asc" data-column_name="" data-tablesaw-sortable-col data-tablesaw-priority="6">Amount Payable</th>
                                            <th scope="col" class="billsorting centerAlign" data-sorting_type="asc" data-column_name="productwise_discounttotal" data-tablesaw-sortable-col data-tablesaw-priority="7">Action<span id="discount_amount_icon"></span></th>
                                        </tr>
                                        </thead>
                                        <tbody id="view_bill_record">
@if(isset($outstanding_payment) && $outstanding_payment != '')

@foreach($outstanding_payment AS $key=>$value)
    <?php


    if($key % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }

   $company_id = $value[0]['inward_stock']['company_id'];
    $gst_id = $value[0]['inward_stock']['supplier_gst_id'];
    $company_name =  $value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'];
    $first_name =  $value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_first_name'];
    $last_name = $value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_last_name'];

    $mobile_no = '';

    if($value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_mobile_no'] != '')
        {
            $searchString = ',';

            if( strpos($value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_mobile_no'],$searchString) !== false )
            {
                $mobile = explode(',',$value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_mobile_no']);
                $dial_code = explode(',',$value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_dial_code']);


                foreach ($mobile AS $mobile_key=>$mobile_value)
                    {
                        if($mobile_no == '')
                            {
                                $mobile_no = $dial_code[$mobile_key] . ' ' .$mobile_value;
                            }
                        else
                            {
                                $mobile_no =  $mobile_no.','.$dial_code[$mobile_key] . '' .$mobile_value;
                            }

                    }

            }
            else
                {
                    $mobile_no = $value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_dial_code'] .' '.$value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_mobile_no'];
                }
        }







    $total_outstanding_amt = 0;
    $total_paid_amt = 0;

    /*if(isset($value['supplier_payment_details']) && $value['supplier_payment_details'] != '')
    {
          foreach($value['supplier_payment_details'] AS $payment_key=>$payment_value)
          {*/
             if($value[0]['outstanding_payment'] != '' && $value[0]['outstanding_payment'] != NULL)
             {
                $search_string = ',';
                if(strpos($value[0]['outstanding_payment'],$search_string) !== false)
                    {

                        $outstanding_amount = explode(',',$value[0]['outstanding_payment']);

                        $amount = explode(',',$value[0]['amount']);


                        foreach($amount AS $key=>$value)
                        {
                            $total_outstanding_amt += $value;
                             $total_paid_amt += ($value - $outstanding_amount[$key]);
                        }
                    }
                else
                    {
                        $total_outstanding_amt += $value[0]['amount'];
                         $total_paid_amt += ($value[0]['amount'] - $value[0]['outstanding_payment']);

                    }

             }
          /*}
     }*/

    $amount_to_pay = 0;
    $amount_to_pay = ($total_outstanding_amt - $total_paid_amt);
    $encrypt_gstid = encrypt($gst_id);

    if(strpos($encrypt_gstid,'=') !== false)
        {
          $encrypt_gstid =   str_replace('=','',$encrypt_gstid);
        }
    ?>

    <tr id="<?php echo $encrypt_gstid?>" style="background:#EEEEEE !important;margin:0 0 10px 0 !important;border-bottom: white 15px solid;" class="<?php echo $tblclass ?>">
        <td>
            <a>
            <span id="down_<?php echo $encrypt_gstid ?>" onclick="return showdetails_supplier(this);" style="font-weight:bold;font-size:14px;color:#28a745 !important;">
                <i class="fa fa-arrow-down"></i>Show</span>
                <span id="up_<?php echo $encrypt_gstid ?>" onclick='return hidedetails_supplier(this);' style="font-weight:bold;font-size:14px;color:#f00 !important;display:none;">
                    <i class="fa fa-arrow-up"></i>Hide</span>
            </a>
        </td>

        <!-- <td><a id="view_outstanding" href="{{URL::to('list_outstanding_payment')}}?supplier_gst_id='<?php echo encrypt($gst_id)?>'"  style="text-decoration:none !important;" >
                <i class="fa fa-eye" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;font-weight:bold;">View Detail</i>
            </a></td> -->
        <td class="leftAlign bold"><?php echo $company_name?></td>
        <td style="text-align: left;width: 10%;"><?php echo $first_name?>
            <?php echo $last_name?>  </td>
        <td><?php echo $mobile_no?></td>
        <td class="rightAlign"><?php echo $total_outstanding_amt ?></td>
        <td class="rightAlign"><?php echo $total_paid_amt ?></td>
        <td class="rightAlign" style="color: #FF8C00"><?php echo $amount_to_pay?></td>
        <td  class="centerAlign">
            @if($company_id == Auth::user()->company_id)
            <a href="{{URL::to('list_outstanding_payment')}}?supplier_gst_id='<?php echo encrypt($gst_id)?>'"  style="text-decoration:none !important;" target="_blank">
                <button type="button" id="view_outstanding" class="btn btn-primary" style="color:#fff;padding: .15rem .35rem;line-height:1.3 !important;font-size:13px !important;">
                <i class="fa fa-eye" aria-hidden="true" style="margin:0 !important">View Detail</i>
                </button>
           </a>
            @endif
        </td>
      

    </tr>
<!-- vrunda -->
    <div id="showsupplierd_<?php echo $encrypt_gstid ?>" style="display:none">

        <tr id="showdetail<?php echo $encrypt_gstid ?>"></tr>
    </div>
<!-- vrunda -->
    
@endforeach
@endif
<tr>
    <td colspan="7" class="paginateui">
        {{--{!! $outstanding_payment->links() !!}--}}
    </td>
</tr>
</tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="supplier_gst_id" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="supplier_company_name" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_supplierpaymentdetail" />                                    
<?php
$totalsupplier = count($outstanding_payment);
?>
<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script type="text/javascript">
$('.totinvoices').html({{round($total_invoice)}});
$('.outstanding_detail').html({{round($outstanding_detail)}});
$('.pending_amt').html({{round($pending_amt)}});
$('.amount_payable').html({{round($amount_payable)}});
$(".PagecountResult").html('{{$totalsupplier}}');
$(".PagecountResult").addClass("itemfocus");

</script>

