<?php
/**
 * Created by PhpStorm.
 * User: retailcore
 * Date: 18/3/19
 * Time: 4:21 PM
 */

$tax_name = "GSTIN";

if($nav_type[0]['tax_type'] == 1)
{
    $tax_name = $nav_type[0]['tax_title'];
}
?>

<table id="customerrecordtable" class="table tablesaw table-bordered table-hover  mb-0"   data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>
        <thead>
        <tr class="blue_Head">
            <th scope="col" class="tablesaw-swipe-cellpersist" >
                <div class="custom-control custom-checkbox checkbox-primary">
                    <input type="checkbox" class="custom-control-input" id="checkallcustomer" name="checkallcustomer" >
                    <label class="custom-control-label" for="checkallcustomer"></label>
                </div>
            </th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1" >Name<span id="customer_name_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2" >Gender<span id="customer_name_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3" >Mobile No.<span id="customer_mobile_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4" >Email<span id="customer_email_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5" >DOB<span id="customer_date_of_birth"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6" ><?php echo $tax_name?><span id="customer_gstin_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7" >Address<span id="customer_address_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8" >Area<span id="customer_area_icon"></span></th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9" >City / Town</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10" >Pin / Zip Code</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11" >State / Region</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12" >Country</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="13" >Source</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="14" >Customer Since</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="15" >Note</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="16" >Referral ID</th>
        </tr>
        </thead>
        <tbody>

@foreach($customer AS $customer_key=>$customer_value)
    <?php if($customer_key % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }
    ?>
    <tr id="{{$customer_value->customer_id}}" class="<?php echo $tblclass ?>">
        <td>
            <?php if($customer_value['delete_option'] == 1 && $role_permissions['permission_delete']==1 && $customer_value['company_id'] == Auth::user()->company_id) { ?>
            <input type="checkbox" name="delete_customer[]" value="{{$customer_value->customer_id }}" id="delete_customer{{$customer_value->customer_id }}">
            <?php } else { ?>

            <button type="button" class="pa-0 ma-0  bold" style="font-size:10px;"  data-trigger="focus" data-placement="top" data-toggle="popover"  title="" data-content="This customer cannot be deleted as this record is associated with other records or transactions." ><i class="fa fa-eye cursor"></i></button>
            <?php } ?>

        <?php
        if($role_permissions['permission_edit']==1 && $customer_value['company_id'] == Auth::user()->company_id)
        {
            ?>
            <a onclick="return editcustomer('{{encrypt($customer_value->customer_id)}}');">
            <i class="fa fa-edit" title="Edit Customer"></i></a>
               <?php
        }
        ?>
<?php  if($customer_value['delete_option'] == 0) { ?>
            <a title="Dependent Record" class="dependent_record" onclick="return dependent_record(this)"  data-id="{{encrypt($customer_value->customer_id)}}" data-url="customer_dependency">
            <i class="fa fa-link" aria-hidden="true"></i>
 <?php } ?>

        <?php if($customer_value['delete_option'] == 1 && $role_permissions['permission_delete']==1 && $customer_value['company_id'] == Auth::user()->company_id) { ?>
        <a id="delete_separate_customer" onclick="return delete_separate_customer(this,'{{$customer_value->customer_id }}',event);">
                <i class="fa fa-trash" title="Delete Sorce"></i></a>
        <?php
        }
        ?>

        </td>
        <td style="text-align: left;">{{$customer_value->customer_name}}</td>

        <?php
        $customer_gender = '';
        if(isset($customer_value['customer_gender']) && $customer_value['customer_gender'] != '' && $customer_value['customer_gender'] != 0)
        {

            if($customer_value['customer_gender'] == 1)
                {
                    $customer_gender = "Male";
                }
            elseif($customer_value['customer_gender'] == 2)
                {
                    $customer_gender = "Female";
                }
            else
                {
                    $customer_gender = "Transgender";
                }
        }?>

        <td style="text-align: left;"><?php echo $customer_gender?></td>
        <td style="text-align: right;">
            <?php if($customer_value['customer_mobile'] != ''){?>
            {{$customer_value->customer_mobile_dial_code}} {{$customer_value->customer_mobile}}
            <?php }  ?>

        </td>
        <td style="text-align: left;">{{$customer_value->customer_email}}</td>

        <?php
        if($customer_value->customer_date_of_birth != '')
            {
                $date =$customer_value->customer_date_of_birth;
                $dob = date('d-m-Y',strtotime($date));
            }
        else
            {
                $dob = '';
            }

        ?>
        <td style="text-align: right;"><?php echo $dob ?></td>
        <?php
        if(isset($customer_value) && isset($customer_value['customer_address_detail'])
            && isset($customer_value['customer_address_detail']['customer_gstin']))
        {
            $gstin = $customer_value['customer_address_detail']['customer_gstin']; } else {
            $gstin = ''; }
        ?>
        <td style="text-align: right;">{{$gstin}}</td>
        <?php
        if(isset($customer_value) && isset($customer_value['customer_address_detail'])
            && isset($customer_value['customer_address_detail']['customer_address']))
        {
            $customeraddress = $customer_value['customer_address_detail']['customer_address']; } else {
            $customeraddress = ''; }
        ?>

        <td style="text-align: left;">{{$customeraddress}}</td>
        <?php
        if(isset($customer_value) && isset($customer_value['customer_address_detail'])
            && isset($customer_value['customer_address_detail']['customer_area']))
        {
            $customer_area = $customer_value['customer_address_detail']['customer_area']; } else {
            $customer_area = ''; } ?>
        <td style="text-align: left;">{{$customer_area}}</td>
        <?php if(isset($customer_value) && isset($customer_value['customer_address_detail'])
            && isset($customer_value['customer_address_detail']['customer_city']))
        {
            $customer_city = $customer_value['customer_address_detail']['customer_city']; } else {
            $customer_city = ''; }
        ?>
        <td style="text-align: left;">{{$customer_city}}</td>

        <?php if(isset($customer_value) && isset($customer_value['customer_address_detail'])
            && isset($customer_value['customer_address_detail']['customer_pincode']))
        {
            $customer_pincode = $customer_value['customer_address_detail']['customer_pincode']; } else {
            $customer_pincode = ''; } ?>
        <td style="text-align: right;">{{$customer_pincode}}</td>


        <?php if(isset($customer_value) && isset($customer_value['customer_address_detail'])
            && isset($customer_value['customer_address_detail']['state_id']))
        {
            $state_name_val = $customer_value['customer_address_detail']['state_name']['state_name']; } else {
            $state_name_val = ''; } ?>
        <td style="text-align: left;">{{$state_name_val}}</td>

        <?php if(isset($customer_value) && isset($customer_value['customer_address_detail'])
            && isset($customer_value['customer_address_detail']['country_id']))
        {
            $country_name_val = $customer_value['customer_address_detail']['country_name']['country_name']; } else {
            $country_name_val = ''; } ?>
        <td style="text-align: left;">{{$country_name_val}}</td>

        <?php if($customer_value['customer_source_id'] != null){ ?>
        <td style="text-align: left;">{{$customer_value->customer_source->source_name}}</td>
        <?php } else { ?>
          <td></td>
        <?php } ?>

            <td style="text-align: right;">
                <?php $date = explode(' ',$customer_value->created_at)[0];

                echo  date('d-m-Y', strtotime($date));

                ?>
            </td>


        <?php if(isset($customer_value) && isset($customer_value['note']) && $customer_value['note'] != '')
        {
            $customer_note = $customer_value['note']; } else {
            $customer_note = ''; } ?>
        <td style="text-align: left;">{{$customer_note}}</td>
        <td style="text-align: left;">{{$customer_value['referral_id']}}</td>
    </tr>
@endforeach

<tr>
    <td  colspan="17" align="center">
        {!! $customer->links() !!}
    </td>
</tr>
        </tbody>
</table>

    <input type="hidden" name="hidden_page" id="hidden_page" value="1" />
    <input type="hidden" name="hidden_column_name" id="hidden_column_name" value="customer_id" />
    <input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
    <input type="hidden" name="fetch_data_url" id="fetch_data_url" value="customer_fetch_data" />


<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>

<script type="text/javascript">
    $(".PagecountResult").html('{{$customer->total()}}');
    $(".PagecountResult").addClass("itemfocus");

</script>




