<?php
/**
 *
 * Created by PhpStorm.
 * User: Hemaxi
 * Date: 07/05/19
 * Time: 11:18 PM
 */


?>

    <table id="view_inward_table_data" class="table tablesaw table-bordered table-hover table-striped mb-0"   data-tablesaw-sortable data-tablesaw-no-labels>
        <thead>
    <tr class="blue_Head">

        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1" >Action</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">PO No.<span id="po_no_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">PO Date<span id="po_date_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Supplier Company Name<span id=""></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Supplier Name<span id="supplier_gst_id_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Delivery Date<span id="delivery_date_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Delivery To<span id="delivery_to_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Qty Required<span id="total_qty"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">Qty Received</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">Qty Pending</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">Take Inward</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Note</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="13">Print</th>
    </tr>
    </thead>
    <tbody>

@foreach($purchase_order AS $key=>$value)
    <?php if($key % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }
    ?>
    <tr id="{{$value->purchase_order_id}}" class="<?php echo $tblclass ?>">

        <td class="leftAlign">
            <a title="View PO" ondblclick="view_po_detail('{{encrypt($value->purchase_order_id)}}');">
            <i class="fa fa-eye" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;font-weight:bold;"  ></i>
            </a>

        @if($value['received_qty'] > 0)
            <a title="No Edit">---</a>
            <a title="No Delete">---</a>
        @else
            <?php
            if($role_permissions['permission_edit']==1)
            {
            ?>
                <a id="edit_purchase_order" title="Edit" onclick="return edit_po('{{encrypt($value->purchase_order_id)}}','1');"><i class="fa fa-edit"></i></a>
            <?php
            }
                if($role_permissions['permission_delete']==1)
                {
            ?>
                <a id="deletepo" onclick="delete_po_data('{{encrypt($value->purchase_order_id)}}');">
                    <i class="fa fa-trash" title="Delete PO"></i>
                </a>
            <?php
            }
            ?>
        @endif
        </td>
        <td class="leftAlign">{{$value->po_no}}</td>
        <td class="leftAlign">{{$value->po_date}}</td>
        <td class="leftAlign">{{$value->supplier_gstdetail->supplier_company_info->supplier_first_name}} {{$value->supplier_gstdetail->supplier_company_info->supplier_company_name}}</td>
        <td class="leftAlign">{{$value->supplier_gstdetail->supplier_company_info->supplier_first_name}} {{$value->supplier_gstdetail->supplier_company_info->supplier_last_name}}</td>
        <td class="leftAlign">{{$value->delivery_date}}</td>
        <td class="leftAlign">{{$value->delivery_to}}</td>
        <td class="rightAlign">{{$value->total_qty}}</td>
        <td class="rightAlign">{{$value->received_qty}}</td>
        <td class="rightAlign">{{$value->pending_qty}}</td>


        @if($value['pending_qty'] != 0)
        <td>
            <!-- <a id="take_inward_data" title="Take Inward" onclick="return edit_po('{{encrypt($value->purchase_order_id)}}','2');"><span class=""><i class="fa fa-plus"></i>&nbsp;Take Inward</span></a> -->
            <!-- <button class="takeinwardbtn badge badge-primary badge-pill"> -->
            <?php
            if($role_permissions['permission_add']==1)
            {
            ?>
                <a class="takeinwardbtn badge badge-primary badge-pill" id="take_inward_data" title="Take Inward" onclick="return edit_po('{{encrypt($value->purchase_order_id)}}','2');"><i class="glyphicon glyphicon-plus"></i>&nbsp;Take Inward</a>
            <?php
            }
            ?>
            <!-- </button> -->
        </td>
            @else
            <td>
                <a title="No Inward">---</a>
            </td>
        @endif

        <td>{{$value->note}}</td>

        <td>
            <?php
            if($role_permissions['permission_print']==1)
            {
            ?>
                <a href="{{URL::to('print_po')}}?id={{encrypt($value->purchase_order_id)}}&print_type={{encrypt('1')}}" style="text-decoration:none !important;" target="_blank" title="Print">
                <i class="fa fa-print" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i>
            <?php
            }
            ?>
            </a>
        </td>
    </tr>
@endforeach

<tr>
    <td colspan="13" class="paginateui">
        {!! $purchase_order->links() !!}
    </td>
</tr>
    </tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1"/>
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="purchase_order_id"/>
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="desc"/>
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="purchase_order_fetch_data"/>


<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script type="text/javascript">
    $(".PagecountResult").html('{{$purchase_order->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>
