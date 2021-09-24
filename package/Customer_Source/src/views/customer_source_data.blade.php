<?php
/**
 * Created by PhpStorm.
 * User: retailcore
 * Date: 18/3/19
 * Time: 4:21 PM
 */
?>

<table id="sourcetablerecord" class="table tablesaw table-bordered table-hover  mb-0"   data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>
        <thead>
        <tr class="blue_Head">
            <th scope="col" class="tablesaw-swipe-cellpersist ml-10" >
                <div class="custom-control custom-checkbox checkbox-primary">
                    <input type="checkbox" class="custom-control-input" id="checkallcustomer_source" name="checkallcustomer_source" >
                    <label class="custom-control-label" for="checkallcustomer_source"></label>
                </div>
            </th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1" >Source  Name<span id="customer_source_name_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Note</th>
        </tr>
        </thead>
        <tbody>

@foreach($customer_source AS $customer_source_key=>$customer_source_value)
    <?php if($customer_source_key % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }
    ?>
    <tr id="{{$customer_source_value->customer_source_id}}" class="<?php echo $tblclass ?>">
        <td>
        <?php
        if($customer_source_value['delete_option'] == 1 && $role_permissions['permission_delete']==1 && $customer_source_value['company_id'] == Auth::user()->company_id)
        {
        ?>
            <input type="checkbox" name="delete_source[]" value="{{$customer_source_value->customer_source_id }}" id="delete_source{{$customer_source_value->customer_source_id }}">
        <?php
        }
        else
            {
        ?>
            <button type="button" class="pa-0 ma-0  bold" style="font-size:10px;"  data-trigger="focus" data-placement="top" data-toggle="popover"  title="" data-content="This customer source cannot be deleted as this record is associated with other records or transactions." ><i class="fa fa-eye cursor"></i></button>
            <?php } ?>

        <?php
        if($role_permissions['permission_edit']==1 && $customer_source_value['company_id'] == Auth::user()->company_id)
        {
        ?>
            <a onclick="return editsource('{{encrypt($customer_source_value->customer_source_id)}}');">
            <i class="fa fa-edit" title="Edit Customer Source"></i></a>
        <?php
        }
        ?>
        <?php
        if($customer_source_value['delete_option'] == 1 && $role_permissions['permission_delete']==1 && $customer_source_value['company_id'] == Auth::user()->company_id)
        {
        ?>
            <a id="delete_separate_source" onclick="return delete_separate_source(this,'{{$customer_source_value->customer_source_id}}',event);">
                <i class="fa fa-trash" title="Delete Sorce"></i></a>
        <?php
        }
        ?>
        </td>
        <td class="leftAlign">{{$customer_source_value->source_name}}</td>
        <td class="leftAlign">{{$customer_source_value->note}}</td>
    </tr>
@endforeach

<tr>
    <td  colspan="3" align="center">
        {!! $customer_source->links() !!}
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

<script type="text/javascript">
    $(".PagecountResult").html('{{$customer_source->total()}}');
    $(".PagecountResult").addClass("itemfocus");

</script>




