<?php
/**
 *
 * Created by PhpStorm.
 * User: Hemaxi
 * Date: 28/3/19
 * Time: 5:47 PM
 */

$payment_div_val = '';
foreach($payment_methods AS $payment_keys=>$payment_values)
    {
    $supplier_payment = 0;
    if(array_key_exists($payment_values['payment_method_id'],$payment_value))
       {
          $supplier_payment = number_format($payment_value[$payment_values['payment_method_id']],$nav_type[0]['decimal_points']);
       }

 $payment_div_val .= '<td class="centerAlign"><h5><span>'.$supplier_payment.'</span></h5></td>';

        $taxable_amount = 0;
        $overall_gst = 0;
        $overall_cgst = 0;
        $overall_sgst = 0;
        $total_grand_amount = 0;
//$taxable_amount = $inward_stock->sum('cost_rate');
//$overall_gst = $inward_stock->sum('total_cost_igst_amount');
//$overall_cgst = $inward_stock->sum('total_cost_cgst_amount');
//$overall_sgst = $inward_stock->sum('total_cost_sgst_amount');
//$total_grand_amount  = $inward_stock->sum('total_grand_amount');

}
?>
<table id="view_inward_table_data" class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable  data-tablesaw-no-labels>
    <thead>
    <tr class="blue_Head">
        <th  scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Action</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Invoice No.<span id="invoice_no_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Po No.<span id="po_no_icon"></span></th>
        <th scope="col"  data-tablesaw-sortable-col data-tablesaw-priority="3">Inward date <span id="inward_date_icon" class="p" ></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Supplier Name</th>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="5">Total Cost Rate<span id="cost_rate_icon"></span></th>
        <?php if($nav_type[0]['tax_type'] == 1){ ?>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="8">Total Cost <?php echo $nav_type[0]['tax_title'].' '.$nav_type[0]['currency_title']?><span id="total_cost_igst_amount_icon"></span></th>
        <?php } else { ?>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="6">Total Cost CGST &#8377;<span id="total_cost_cgst_amount_icon"></span></th>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="7">Total Cost SGST &#8377;<span id="total_cost_sgst_amount_icon"></span></th>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="8">Total Cost IGST &#8377;<span id="total_cost_igst_amount_icon"></span></th>
        <?php } ?>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">Total Qty<span id="total_qty_icon"></span></th>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="10">Grand Total &#8377;<span id="total_grand_amount_icon"></span></th>
        <?php $priority = 11 ?>
        @foreach($payment_methods AS $payment_methods_key=>$payment_methods_value)
            <?php $priority++; ?>
            <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="<?php echo $priority ?>>">{{$payment_methods_value->payment_method_name}}</th>
        @endforeach
        <?php $priority++; ?>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="<?php echo $priority ?>">Note</th>
    </tr>
    </thead>
    <tbody>
@foreach($inward_stock AS $inward_key=>$inward_value)
    <?php if($inward_key % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }

    ?>
    <tr id="{{$inward_value->inward_stock_id}}" class="<?php echo $tblclass ?>">
        <td class="leftAlign" >
            <a id="popupforview" ondblclick="return false;" onclick="viewinward('{{encrypt($inward_value->inward_stock_id)}}');">
                <i class="fa fa-eye" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;font-weight:bold;" title="View Inward Stock" ></i>
            </a>
            <?php
                if($nav_type[0]['inward_type'] == $inward_value['inward_type'] && $role_permissions['permission_edit'] == 1 && $inward_value['stock_inward_type'] == 0)
                { ?>
                    <a id="edit_inword_stock" onclick="return edit_inwardstock('{{encrypt($inward_value->inward_stock_id)}}','{{$inward_value->inward_type}}');">
                    <i class="fa fa-edit" title="Edit Inward Stock"></i></a>

            <?php if($inward_value->total_qty == $inward_value->totalpendingqty) {?>
            <a id="delete_inword_stock" onclick="return delete_inwardstock('{{encrypt($inward_value->inward_stock_id)}}');">
                <i class="fa fa-trash" title="Delete Inward Stock"></i>
            </a>
            <?php } else { ?>
            <a href="#" data-toggle="tooltip" title="Stock quantity of this inward is used, so it cannot be deleted!"><i class="fa fa-info"></i></a>
            <?php  } ?>

            <?php } else { ?>
                <i class="fa fa-info" title="Change your inward type for edit or delete this inward!"></i>
            <?php } ?>
        </td>
        <td class="leftAlign">{{$inward_value->invoice_no}}</td>
        <td class="leftAlign">{{$inward_value->po_no}}</td>
        <td class="leftAlign">{{$inward_value->inward_date}}</td>
        <td class="leftAlign">
            <?php if($inward_value['supplier_gst_id'] != NULL) { ?>
            {{$inward_value->supplier_gstdetail->supplier_company_info->supplier_first_name}}
            {{$inward_value->supplier_gstdetail->supplier_company_info->supplier_last_name}}
            <?php } ?>
        </td>
        <?php
        $taxable_amount +=  round($inward_value->cost_rate);
        ?>
        <td class="rightAlign inward_calculation_case">{{number_format($inward_value->cost_rate,$nav_type[0]['decimal_points'])}}</td>

        <?php if($nav_type[0]['tax_type']==1) {
        $overall_gst +=  round($inward_value->total_cost_igst_amount);
            ?>
        <td class="rightAlign inward_calculation_case">{{number_format($inward_value->total_cost_igst_amount,$nav_type[0]['decimal_points'])}}</td>
        <?php } else {
        $overall_gst +=  round($inward_value->total_cost_igst_amount);
        $overall_cgst +=  round($inward_value->total_cost_cgst_amount);
        $overall_sgst +=  round($inward_value->total_cost_sgst_amount);
            ?>
        <td class="rightAlign inward_calculation_case">{{number_format($inward_value->total_cost_cgst_amount,$nav_type[0]['decimal_points'])}}</td>
        <td class="rightAlign inward_calculation_case">{{number_format($inward_value->total_cost_sgst_amount,$nav_type[0]['decimal_points'])}}</td>
        <td class="rightAlign inward_calculation_case">{{number_format($inward_value->total_cost_igst_amount,$nav_type[0]['decimal_points'])}}</td>
        <?php } ?>

        <td class="rightAlign">{{$inward_value->total_qty}}</td>
        <?php
        $total_grand_amount += round($inward_value->total_grand_amount);
        ?>
        <td class="rightAlign inward_calculation_case">{{number_format($inward_value->total_grand_amount,$nav_type[0]['decimal_points'])}}</td>

        @foreach($payment_methods AS $payment_methods_key=>$payment_methods_value)
            <?php  $payment_value  = 0; ?>
            @foreach($inward_value->inward_supplier_payment AS $key=>$value)
            <?php
            if($value['payment_method_id'] == $payment_methods_value['payment_method_id'])
            {
                $payment_value = $value['amount'];
            }
            ?>
            @endforeach

            <td class="rightAlign inward_calculation_case" id=""><?php echo $payment_value?></td>


        @endforeach
        <td class="leftAlign">{{$inward_value->note}}</td>
    </tr>
@endforeach
<tr>
    <td colspan="<?php echo $priority?>" class="paginateui">
        {!! $inward_stock->links() !!}
    </td>
</tr>
    </tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1"/>
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="inward_stock_id"/>
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="desc"/>
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="inward_fetch_data"/>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script>
    $("#payment_show_val").html('');

    $(".PagecountResult").html('{{$inward_stock->total()}}');


    $(".totalinvoice").html('{{$inward_stock->total()}}');
    $(".PagecountResult").addClass("itemfocus");

    var in_calculation = '<?php echo $nav_type[0]['inward_calculation']?>';

    if(in_calculation == 3)
    {
        $(".inward_calculation_case").hide();
    }
    else
    {
        $(".inward_calculation_case").show();
    }
    <?php if($max_date != ''){?>
    $('.viewinwardfromdate').html("{{$max_date}}");
    $('.viewinwardtodate').html("{{$min_date}}");
    <?php } ?>

    $("#payment_show_val").append('<?php echo $payment_div_val?>');



    $(".taxabletariff").html('<?php echo number_format($taxable_amount,$nav_type[0]['decimal_points']) ?>');
    $(".overalligst").html('<?php echo number_format($overall_gst,$nav_type[0]['decimal_points']) ?>');
    $(".overallcgst").html('<?php echo number_format($overall_cgst,$nav_type[0]['decimal_points']) ?>');
    $(".overallsgst").html('<?php echo number_format($overall_cgst,$nav_type[0]['decimal_points']) ?>');
    $(".overalligst").html('<?php echo number_format($overall_gst,$nav_type[0]['decimal_points']) ?>');
    $(".overallgrand").html('<?php echo number_format($total_grand_amount,$nav_type[0]['decimal_points']) ?>');


</script>


