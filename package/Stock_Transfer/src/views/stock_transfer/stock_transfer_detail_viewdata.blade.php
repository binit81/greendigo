<table id="view_inward_table_data" class="table tablesaw table-bordered table-hover mb-0" data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>
    <thead>
        <tr class="blue_Head">
            <!-- <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Action</th> -->
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Batch No</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Stock Transfer No</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Barcode</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Product Name</th>
            <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Cost Rate</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Total cost Rate With Qty</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Sell Price</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Selling GST(%)</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Selling GST Amt</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">Offer Price</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">Total Offer Price</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">Product MRP</th>
            <?php } ?>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Product Qty</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="13">Pending Inward Qty</th>

        </tr>
    </thead>
    <tbody>
         @foreach($stock_transferdetail_view AS $key=>$value)
            <?php
             $barcode = '';
             if($value['product_data']['supplier_barcode'] != '')
             {
             $barcode =  $value['product_data']['supplier_barcode'];
             }
             else
             {
             $barcode = $value['product_data']['product_system_barcode'];
             }
             ?>
            <tr id="" class="" onclick="">
                <td class="leftAlign">{{$value->batch_no}}</td>
                <td class="leftAlign">{{$value->stock_transfer_no->stock_transfer_no}}</td>
                <td class="leftAlign"><?php echo $barcode ?></td>
                <td class="leftAlign">{{$value->product_data->product_name}}</td>
                <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
                <td class="leftAlign">{{$value->cost_rate}}</td>
                <td class="leftAlign">{{$value->total_cost_rate_with_qty}}</td>
                <td class="leftAlign">{{$value->sell_price}}</td>
                <td class="leftAlign">{{$value->selling_gst_percent}}</td>
                <td class="leftAlign">{{$value->selling_gst_amount}}</td>
                <td class="leftAlign">{{$value->offer_price}}</td>
                <td class="leftAlign">{{$value->total_offer_price}}</td>
                <td class="leftAlign">{{$value->product_mrp}}</td>
                <?php } ?>
                <td class="leftAlign">{{$value->product_qty}}</td>
                <td class="leftAlign">{{$value->pending_rcv_qty}}</td>
            </tr>
        @endforeach
         <tr>
             <td colspan="14" class="paginateui">
                 {!! $stock_transferdetail_view->links() !!}
             </td>
         </tr>
    </tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1"/>
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="stock_transfers_detail_id"/>
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="desc"/>
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="stock_transfer_detail_fetch_data"/>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script>
    $(".PagecountResult").html('{{$stock_transferdetail_view->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>
