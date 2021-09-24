
<table class="table  table-bordered table-hover  mb-0" data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-no-labels>

    
<thead>
<tr class="blue_Head">
<th class="pa-10 leftAlign" width="14%">Action</th>
<th class="pa-10 leftAlign" width="14%">Return No.</th>
<th width="14%">Return Date</th>
<th width="16%">No. of Products Returned</th>
<th width="14%">Total Qty</th>
<th width="14%">Remarks</th>
<th width="12%">User Name</th>
</tr>
</thead>
<tbody id="view_bill_record">



@foreach($store_return AS $returnkey=>$return_value)

<?php

 if ($returnkey % 2 == 0) {
    $tblclass = 'even';
} else {
    $tblclass = 'odd';
}



?>

<tr id="viewbill_{{$return_value['store_return_id']}}" class="<?php echo $tblclass ?>">

    <td class="leftAlign"><i class="fa fa-eye" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;font-weight:bold;"  id="viewbill_{{$return_value->store_return_id}}" onclick="return viewreturndetail(this);" title="View Return Products Details"></i>

         <?php

            if($role_permissions['permission_edit']==1)
            {
            ?>
                <a id="edit_return" onclick="return edit_storerreturnbill('{{encrypt($return_value->store_return_id)}}');" title="Edit Store Return"><i class="fa fa-edit" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
            <?php
            }

         
            if($role_permissions['permission_delete']==1)
            {
            ?>
            <a id="deletebill_{{$return_value->store_return_id}}" onclick="return delete_storerreturnbill(this);" style="text-decoration:none !important;" target="_blank" title="Delete"><i class="fa fa-trash" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
            <?php
            }
       

    ?>

    </td>
    <td class="leftAlign">{{$return_value['return_no']}}</td>
    <td class="leftAlign">{{$return_value['return_date']}}</td>
    <td class="leftAlign">{{$return_value['totalreturnqty']}}</td>
    <td class="leftAlign">{{round($return_value['total_qty'],2)}}</td>
    <td class="leftAlign">{{$return_value['official_note']}}</td>
    <td class="leftAlign">{{$return_value['user']['employee_firstname']}}</td>
    
</tr>


@endforeach
<tr>
    <td colspan="12" align="center">
        {!! $store_return->links() !!}
    </td>
</tr>

</tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="sales_bill_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_storereturnproduct_detail" />
<script type="text/javascript">
    $(".PagecountResult").html('{{$store_return->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>