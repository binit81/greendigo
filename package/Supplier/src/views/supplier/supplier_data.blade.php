<?php
/**
 * Created by PhpStorm.
 * User: retailcore
 * Date: 18/3/19
 * Time: 4:21 PM
 */



?>
    <table  class="table tablesaw table-bordered table-hover  mb-0"  data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>

    <thead>
    <tr class="blue_Head">
        <th scope="col" class="tablesaw-swipe-cellpersist" data-tablesaw-sortable-col data-tablesaw-priority="persist">
            <div class="custom-control custom-checkbox checkbox-primary">
                <input type="checkbox" class="custom-control-input" id="checkallsupplier" name="checkallsupplier" title="Delete Supplier">
                <label class="custom-control-label" for="checkallsupplier"></label>
            </div>
        </th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1" >Company Name<span id="supplier_company_name_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2" >Name<span id="supplier_first_name_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3" >Mobile No.<span id="supplier_company_mobile_no"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">PAN<span id="supplier_pan_no_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Address<span id="supplier_company_address_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6" >Area<span id="supplier_company_area_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7" >City</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Pin / Zip Code</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">State / Region</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">Country</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">Due Days</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Due Date</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="13">Note</th>
    </tr>
    </thead>
    <tbody>

@foreach($supplier AS $supplier_key=>$supplier_value)
    <?php if($supplier_key % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }

    $searchString = ',';
    $contact_no= '';
    if(strpos($supplier_value['supplier_company_dial_code'], $searchString) !== false )
    {
        $contact = explode(',',$supplier_value['supplier_company_dial_code']);
        $contact_mob = explode(',',$supplier_value['supplier_company_mobile_no']);

        foreach ($contact_mob AS $key=>$val)
            {
                if($contact_no == '')
                    {
                        $contact_no = $contact[$key] .' ' .$val;
                    }
                else
                    {
                        $contact_no = $contact_no .','.$contact[$key] .' ' .$val;
                    }
            }
    }
    else
        {
            if($supplier_value['supplier_company_mobile_no'] != ''){
            $contact_no = $supplier_value['supplier_company_dial_code'] .' ' .$supplier_value['supplier_company_mobile_no'];
            }
        }

    $state = '';
    if($supplier_value['state_id'] != '')
    {
            $state = $supplier_value->state_name->state_name;
    }
    ?>
    <tr id="{{$supplier_value->supplier_company_info_id}}" class="<?php echo $tblclass ?>">
        <td>

            <?php if($supplier_value->delete_option == 1 && $role_permissions['permission_delete'] == 1 && $supplier_value['company_id'] == Auth::user()->company_id) { ?>
            <input type="checkbox" title="Check for delete supplier" name="delete_supplier[]" value="{{$supplier_value->supplier_company_info_id }}" id="delete_supplier{{$supplier_value->supplier_company_info_id }}">
            <a id="delete_separate_supplier" onclick="return delete_separate_supplier(this,'{{$supplier_value->supplier_company_info_id}}',event);">
                <i class="fa fa-trash" title="Delete supplier"></i></a>
            <?php }
            else { ?>


                <button type="button" class="pa-0 ma-0  bold" style="font-size:10px;"  data-trigger="focus" data-placement="top" data-toggle="popover"  title="" data-content="This supplier cannot be deleted as this record is associated with other records or transactions." class="fa fa-info" ><i class="fa fa-eye cursor"></i></button>
            <?php
            }
            if($role_permissions['permission_edit']==1 && $supplier_value['company_id'] == Auth::user()->company_id)
            {
            ?>
                <a  onclick="return editsupplier('{{encrypt($supplier_value->supplier_company_info_id)}}');">
                <i class="fa fa-edit" title="Edit Inward Stock"></i></a>
            <?php
            }
            ?>

	     <?php if($supplier_value->delete_option==0) {?>
            <a title="Dependent Record" class="dependent_record" onclick="return dependent_record(this)"  data-id="{{encrypt($supplier_value->supplier_company_info_id)}}" data-url="supplier_dependency">
            <i class="fa fa-link" aria-hidden="true"></i>
		 <?php }?>

        </td>
        <td style="text-align: left;" >{{$supplier_value->supplier_company_name}}</td>
        <td style="text-align: left;"  >{{$supplier_value->supplier_first_name}}&nbsp;{{$supplier_value->supplier_last_name}}</td>
        <td style="text-align: left;"><?php echo $contact_no ?></td>
        <td style="text-align: left;">{{$supplier_value->supplier_pan_no}}</td>
        <td style="text-align: right;">{{$supplier_value->supplier_company_address}}</td>
        <td style="text-align: left;">{{$supplier_value->supplier_company_area}}</td>
        <td style="text-align: left;">{{$supplier_value->supplier_company_city}}</td>
        <td style="text-align: left;">{{$supplier_value->supplier_company_zipcode}}</td>
        <td style="text-align: left;"><?php echo $state ?></td>
        <td style="text-align: left;">{{$supplier_value->country_name->country_name}}</td>
        <td style="text-align: right;">{{$supplier_value->supplier_payment_due_days}}</td>
        <td style="text-align: right;">{{$supplier_value->supplier_payment_due_date}}</td>
        <td style="text-align: left;">{{$supplier_value->note}}</td>
    </tr>
@endforeach

<tr>
    <td colspan="14" class="paginateui">
        {!! $supplier->links() !!}
    </td>
</tr>
    </tbody>
    </table>

<input type="hidden" name="hidden_page" id="hidden_page" value="1"/>
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="supplier_company_info_id"/>
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC"/>
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="supplier_fetch_data"/>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script>
    $(".PagecountResult").html('{{$supplier->total()}}');
    $(".PagecountResult").addClass("itemfocus");

</script>
