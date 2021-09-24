<?php
/**
 * Created by PhpStorm.
 * User: retailcore
 * Date: 18/3/19
 * Time: 5:15 PM
 */
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
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Range From(Days)<span id="range_from"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Range To(Days)<span id="range_from"></span></th>
        </tr>
        </thead>
        <tbody id="gstslabrecord">


 @foreach($productage_range AS $rangekey=>$rangevalue)
    <?php if($rangekey % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }
    ?>
    <tr id="{{$rangevalue->productage_range_id}}" class="<?php echo $tblclass ?>">
        <td class="leftAlign">
            <?php
            if($role_permissions['permission_delete']==1)
            {
            ?>
                <input type="checkbox" name="delete_productage_range[]" value="{{$rangevalue->productage_range_id }}" id="delete_productage_range{{$rangevalue->productage_range_id }}">
            <?php
            }
            if($role_permissions['permission_edit']==1)
            {
            ?>
                <a onclick="return editproductage_range('{{encrypt($rangevalue->productage_range_id)}}');">
                <i class="fa fa-edit" title="Edit GST Slab"></i></a>
            <?php
            }
            ?>
        </td>
        <td class="rightAlign" >{{($rangevalue->range_from)}}</td>
        <td class="rightAlign">{{($rangevalue->range_to)}}</td>
        </tr>
@endforeach
<tr>
    <td colspan="5" class="paginateui">
       {!! $productage_range->links() !!}
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
    $(".PagecountResult").html( '{{$productage_range->total()}}' );
    $(".PagecountResult").addClass("itemfocus");
</script>
