<?php
/**
 *
 * User: Maya
 * Date: 20/07/19
 */
?>
<table id="view_inward_table_data" class="table tablesaw table-bordered table-hover mb-0" data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>
    <thead>
        <tr class="blue_Head">
            <th></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Company Name<span id="debit_date_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Full Name<span id="debit_no_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Company Address<span id="debit_no_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Contact No<span id="debit_no_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">GSTIN<span id="debit_no_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Email Id<span id="debit_no_icon"></span></th>
        </tr>
    </thead>
    <tbody>
        @foreach($view_store AS $key=>$value)
            <?php if($key % 2 == 0)
            {
                $tblclass = 'even';
            }
            else
            {
                $tblclass = 'odd';
            }
            ?>
            <tr id="viewstore_{{$value->company_relationship_trees_id}}" class="<?php echo $tblclass ?>" >
                <td class="leftAlign">
                    <i class="fa fa-eye" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;font-weight:bold;" id="viewstore_{{$value->company_relationship_trees_id}}" onclick="return View_Store_Detail(this);" title="View Store Deatil"></i>
                    <a id="edit_store_data" title="Edit" onclick="return edit_store_profile('{{encrypt($value->company_relationship_trees_id)}}','{{encrypt($value->wherehouse_id)}}','{{encrypt($value->store_id)}}');">
                    <i class="fa fa-edit"></i></a>
                </td>
                <td class="leftAlign">{{$value->company_profile->company_name}}</td>
                <td class="leftAlign">{{$value->company_profile->full_name}}</td>
                <td class="leftAlign">{!!$value->company_profile->company_address!!},{{$value->company_profile->company_pincode}},{{$value->company_profile->state_name->state_name}},{{$value->company_profile->country_name->country_name}}</td>
                <td class="leftAlign">{{$value->company_profile->personal_mobile_no}}</td>
                <td class="leftAlign">{{$value->company_profile->gstin}}</td>
                <td class="leftAlign">{{$value->company_profile->personal_email}}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="7" align="center">
                {!! $view_store->links() !!}
            </td>
        </tr>
    </tbody>
</table>

<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="company_relationship_trees_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="view_store_fetch_data" />

<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>

<script type="text/javascript">
    $(".PagecountResult").html('{{$view_store->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>


