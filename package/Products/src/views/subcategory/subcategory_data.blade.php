<?php
/**
 * Created by PhpStorm.
 * User: retailcore
 * Date: 18/3/19
 * Time: 4:21 PM
 */
?>

<table id="customerrecordtable" class="table tablesaw table-bordered table-hover  mb-0"   data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>
        <thead>
        <tr class="blue_Head">
            <th scope="col" class="tablesaw-swipe-cellpersist ml-10" >
                <div class="custom-control custom-checkbox checkbox-primary">
                    <input type="checkbox" class="custom-control-input" id="checkallcustomer_source" name="checkallcustomer_source" >
                    <label class="custom-control-label" for="checkallcustomer_source"></label>
                </div>
            </th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1" >Category  Name<span id="customer_source_name_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1" >Sub Category  Name<span id="customer_source_name_icon"></span></th>
             <!-- <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Note</th> -->
        </tr>
        </thead>
        <tbody>

@foreach($subcategory AS $subcategory_key=>$subcategory_value)
    <?php if($subcategory_key % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }
    ?>
    <tr id="{{$subcategory_value->subcategory_id}}" class="<?php echo $tblclass ?>">
        <td>
        <?php 
        if($role_permissions['permission_delete']==1)
        {
        ?>
            <input type="checkbox" name="delete_source[]" value="{{$subcategory_value->customer_source_id }}" id="delete_source{{$subcategory_value->customer_source_id }}">
        <?php
        }
        ?>

        <?php 
        if($role_permissions['permission_edit']==1)
        {
        ?>
            <a onclick="return editsource('{{encrypt($subcategory_value->customer_source_id)}}');">
            <i class="fa fa-edit" title="Edit Customer Source"></i></a>
        <?php
        }
        ?>
        </td>
        <td class="leftAlign">{{$subcategory_value->subcategory_name}}</td>
        <td class="leftAlign">{{$subcategory_value->category->category_name}}</td>
        <!-- <td class="leftAlign">{{$subcategory_value->note}}</td> -->
    </tr>
@endforeach

<tr>
    <td  colspan="3" align="center">
        {!! $subcategory->links() !!}
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
    $(".PagecountResult").html(' ({{$subcategory->total()}})');
</script>




