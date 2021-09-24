<?php
/**
 * Created by PhpStorm.
 * User: hemaxi
 * Date: 18/3/19
 * Time: 5:15 PM
 */

$tax_title = 'GST';

if($nav_type[0]['tax_type'] == 1)
{
    $tax_title = $nav_type[0]['tax_title'];

}
?>

<table id="customerrecordtable" class="table tablesaw table-bordered table-hover  mb-0"  data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>
        <thead>
        <tr class="blue_Head">
            <th scope="col" class="tablesaw-swipe-cellpersist" data-tablesaw-sortable-col data-tablesaw-priority="persist">
                <div class="custom-control custom-checkbox checkbox-primary">
                    <input type="checkbox" class="custom-control-input" id="checkall" name="checkall" >
                    <label class="custom-control-label" for="checkall"></label>
                </div>
            </th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Price From &#x20b9;<span id="selling_price_from_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Price To &#x20b9;<span id="selling_price_to_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist"><?php echo $tax_title?> %<span id="percentage_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Note</th>
        </tr>
        </thead>
        <tbody id="gstslabrecord">


@foreach($gst_slabs AS $gstslab_key=>$gstslab_value)
    <?php if($gstslab_key % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }
    ?>
    <tr id="{{$gstslab_value->gst_slabs_master_id}}" class="<?php echo $tblclass ?>">
        <td>
            <?php
            if($role_permissions['permission_delete']==1)
            {
            ?>
                <input type="checkbox" name="delete_gstslabs[]" value="{{$gstslab_value->gst_slabs_master_id }}" id="delete_gstslabs{{$gstslab_value->gst_slabs_master_id }}">
            <?php
            }
            if($role_permissions['permission_edit']==1)
            {
            ?>
                <a onclick="return editgstslabs('{{encrypt($gstslab_value->gst_slabs_master_id)}}');">
                <i class="fa fa-edit" title="Edit GST Slab"></i></a>
            <?php
            }
            ?>
            <?php
            if($role_permissions['permission_delete']==1)
            {
            ?>
                <a id="delete_separate_gstslabs" onclick="return delete_separate_gstslabs(this,'{{$gstslab_value->gst_slabs_master_id}}',event);">
                <i class="fa fa-trash" title="Delete Product"></i></a>
            <?php
            }
            ?>
        </td>
        <td class="rightAlign" >{{($gstslab_value->selling_price_from)}}</td>
        <td class="rightAlign">{{($gstslab_value->selling_price_to)}}</td>
        <td class="rightAlign">{{($gstslab_value->percentage)}}</td>
        <td class="rightAlign">{{$gstslab_value->note}}</td>
    </tr>
@endforeach
<tr>
    <td colspan="5" class="paginateui">
       {!! $gst_slabs->links() !!}
    </td>
</tr>
        </tbody>
    </table>
    <input type="hidden" name="hidden_page" id="hidden_page" value="1" />
    <input type="hidden" name="hidden_column_name" id="hidden_column_name" value="gst_slabs_master_id" />
    <input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="asc" />
    <input type="hidden" name="fetch_data_url" id="fetch_data_url" value="gst_slabs_fetch_data" />
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>


<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script type="text/javascript">
    $(".PagecountResult").html( '{{$gst_slabs->total()}}' );
    $(".PagecountResult").addClass("itemfocus");
</script>
