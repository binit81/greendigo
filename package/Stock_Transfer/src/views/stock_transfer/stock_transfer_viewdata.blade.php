<table id="view_inward_table_data" class="table tablesaw table-bordered table-hover mb-0" data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>
    <thead>
        <tr class="blue_Head">
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Action</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Stock Transfer No</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Stock Transfer Date</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Store Name</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Total Qty.</th>
            <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Total MRP</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Total GST</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Total Selling Price</th>
            <?php } ?>
            <!-- <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Total Offer Price</th> -->
        </tr>
    </thead>
    <tbody>
        @foreach($stock_view AS $key=>$value)
         <?php 
         if($key % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }

    ?>
            <tr id="" class="" onclick="">
                <td id="{{$value->stock_transfer_id}}" class="leftAlign <?php echo $tblclass ?>">
                    <!--view detail -->
                    <a id="popupforview" ondblclick="" onclick="view_products_stock_transfer('{{encrypt($value->stock_transfer_id)}}')">
                        <i class="fa fa-eye" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;font-weight:bold;" title="View Stock Transfer"></i>
                    </a>
                    <!-- <i class="fa fa-eye" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;font-weight:bold;" ></i> -->

                    <!--edit detail -->
                    <?php
                    if($nav_type[0]['company_id'] == $value['company_id']) { ?>
                   <!--  <a id="edit_stock_transfer_data" title="Edit" onclick="return Edit_Stock_Transfer('{{encrypt($value->stock_transfer_id)}}');">
                    <i class="fa fa-edit"></i></a> -->
                    <?php } ?>

                    <!--delete detail -->
                    <?php
                    if($nav_type[0]['company_id'] == $value['company_id']) { ?>
                    <a id="deletebill_" onclick="return deletebill(this);" style="text-decoration:none !important;" target="_blank" title="Delete"><i class="fa fa-trash" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
                    <?php } ?>
                <!--delete detail -->
                    <?php
                    if($nav_type[0]['company_id'] == $value['store_id']) { ?>
                    <span>Take Inward</span>
                    <?php } ?>
                </td>
                <td class="leftAlign">{{$value->stock_transfer_no}}</td>
                <td class="leftAlign">{{$value->stock_transfer_date}}</td>
                <td class="leftAlign">{{$value->store_name->full_name}}</td>
                <td class="leftAlign">{{$value->total_qty}}</td>
                <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
                <td class="leftAlign">{{$value->total_mrp}}</td>
                <td class="leftAlign">{{$value->total_gst}}</td>
                <td class="leftAlign">{{$value->total_sellprice}}</td>
                <?php } ?>
            </tr>
        @endforeach
        <tr>
            <td colspan="8" class="paginateui">
                {!! $stock_view->links() !!}
            </td>
        </tr>
    </tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1"/>
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="stock_transfer_id"/>
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="desc"/>
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="stock_transfer_fetch_data"/>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script>
    $(".PagecountResult").html('{{$stock_view->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>
