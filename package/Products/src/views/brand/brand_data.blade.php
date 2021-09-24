<?php
/**
 * Created by PhpStorm.
 * User: retailcore
 * Date: 18/3/19
 * Time: 4:21 PM
 */
?>

<table id="brandrecordTable" class="table tablesaw table-bordered table-hover  mb-0"   data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>
        <thead>
        <tr class="blue_Head">
            <th scope="col" class="tablesaw-swipe-cellpersist ml-10" >
                <div class="custom-control custom-checkbox checkbox-primary">
                    <input type="checkbox" class="custom-control-input" id="checkallbrand" name="checkallbrand" >
                    <label class="custom-control-label" for="checkallbrand"></label>
                </div>
            </th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1" >Brand Type<span id="brand_name_icon"></span></th>
            <!-- <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Note</th> -->
        </tr>
        </thead>
        <tbody>

@foreach($brand AS $brand_key=>$brand_value)
    <?php if($brand_key % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }
    ?>
    <tr id="{{$brand_value->brand_id}}" class="<?php echo $tblclass ?>">
        <td>
        <?php 
        if($role_permissions['permission_delete']==1)
        {
        ?>
            <input type="checkbox" name="delete_source[]" value="{{$brand_value->brand_id }}" id="delete_source{{$brand_value->brand_id }}">
        <?php
        }
        ?>

        <?php 
        if($role_permissions['permission_edit']==1)
        {
        ?>
            <a onclick="return editsource('{{encrypt($brand_value->brand_id)}}');">
            <i class="fa fa-edit" title="Edit Brand"></i></a>
        <?php
        }
        ?>
        </td>
        <td class="leftAlign">{{$brand_value->brand_type}}</td>
        <!-- <td class="leftAlign">{{$brand_value->note}}</td> -->
    </tr>
@endforeach

<tr>
    <td  colspan="3" align="center">
        {!! $brand->links() !!}
    </td>
</tr>
        </tbody>
</table>

    <input type="hidden" name="hidden_page" id="hidden_page" value="1" />
    <input type="hidden" name="hidden_column_name" id="hidden_column_name" value="brand_id" />
    <input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="asc" />
    <input type="hidden" name="fetch_data_url" id="fetch_data_url" value="brand_fetch_data" />


<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>

<script type="text/javascript">
    $(".PagecountResult").html(' ({{$brand->total()}})');
</script>




