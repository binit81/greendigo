<?php
/**
 *
 * Created by PhpStorm.
 * User: Hemaxi
 * Date: 28/3/19
 * Time: 5:47 PM
 */
$currency_title = '₹';
if ($nav_type[0]['tax_type'] == 1) {
    $currency_title = $nav_type[0]['currency_title'];
}
?>

    <table id="view_inward_table_data" class="table tablesaw table-bordered table-hover table-striped mb-0"
           data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>

        <thead>
        <tr class="blue_Head">
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Action</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Company Name</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Stock Transfer No.<span
                    id="stock_transfer_no_icon"></span></th>
           {{-- <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Stock Transfer Date<span
                    id="stock_transfer_date_icon"></span></th>--}}

            <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Total MRP <?php echo $currency_title ?><span id="total_mrp_icon"></span></th>
            <?php if ($nav_type[0]['tax_type'] == 1) { ?>
                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Total <?php echo $nav_type[0]['tax_title'] . ' ' . $nav_type[0]['currency_title'] ?><span id="total_cost_igst_amount_icon"></span></th>
            <?php } else { ?>
                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Total GST <?php echo $currency_title ?><span id="total_gst_icon"></span></th>
            <?php } ?>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">Total Selling Price <?php echo $currency_title ?><span id="total_sellprice_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">Total Offer Price <?php echo $currency_title ?><span id="total_offerprice_amount_icon"></span></th>
            <?php } ?>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">Total Qty<span
                    id="total_qty_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">Pending Inward Qty<span
                    id="total_qty_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Take Inward<span
                    id="total_qty_icon"></span></th>
        </tr>
        </thead>
        <tbody>
        @foreach($stock_transfer_inward_data AS $stock_inward_key=>$stock_inward_value)
        <?php
        $tblclass = 'even';
        if ($stock_inward_key % 2 == 0)
        {
            $tblclass = 'even';
        } else {
            $tblclass = 'odd';
        }
        ?>
        <tr id="{{$stock_inward_value->stock_transfer_id}}" class="<?php echo $tblclass ?>">
            <td class="leftAlign">
                <a id="stock_inward_popup" ondblclick="return false;"
                   onclick="view_stock_transfer('{{encrypt($stock_inward_value->stock_transfer_id)}}');">
                    <i class="fa fa-eye" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;font-weight:bold;"
                       title="View Inward Stock"></i>
                </a>
            </td>
            <td class="leftAlign">{{$stock_inward_value->warehouse->company_name}}</td>
            <td class="leftAlign">{{$stock_inward_value->stock_transfer_no}}</td>
            <td class="leftAlign">{{$stock_inward_value->stock_transfer_date}}</td>
            <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
            <td class="rightAlign">{{number_format($stock_inward_value->total_mrp,$nav_type[0]['decimal_points'])}}</td>
            <td class="rightAlign">{{number_format($stock_inward_value->total_gst,$nav_type[0]['decimal_points'])}}</td>
            <td class="rightAlign">{{number_format($stock_inward_value->total_sellprice,$nav_type[0]['decimal_points'])}}</td>
            <td class="rightAlign">{{number_format($stock_inward_value->total_offerprice,$nav_type[0]['decimal_points'])}}</td>
            <?php } ?>
            <td class="rightAlign">{{$stock_inward_value->total_qty}}</td>
            <td class="rightAlign">{{$stock_inward_value->pending_qty}}</td>
            <td>
                <!--Take Inward -->
                <?php

                //if($nav_type[0]['company_profile_id'] == $stock_inward_value['store_id']) {
                if($stock_inward_value['pending_qty'] != 0) { ?>
                <a class="takeinwardbtn badge badge-primary badge-pill" id="take_stock_inward_data" title="Take Inward" onclick="return take_transfer_inward('{{encrypt($stock_inward_value->stock_transfer_id)}}');"><i class="glyphicon glyphicon-plus"></i>&nbsp;Take Inward</a>

                <?php }
                 else { ?>
                ---
                <?php } ?>
            </td>
        </tr>
        @endforeach
        <tr>
            <td colspan="11" class="paginateui">
                {!! $stock_transfer_inward_data->links() !!}
            </td>
        </tr>
        </tbody>
    </table>
    <input type="hidden" name="hidden_page" id="hidden_page" value="1"/>
    <input type="hidden" name="hidden_column_name" id="hidden_column_name" value="stock_transfer_id"/>
    <input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="desc"/>
    <input type="hidden" name="fetch_data_url" id="fetch_data_url" value="stock_transfer_inward_fetch_data"/>

    <script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
    <script>
        $(".PagecountResult").html('{{$stock_transfer_inward_data->total()}}');
        $(".PagecountResult").addClass("itemfocus");
    </script>
<?php
