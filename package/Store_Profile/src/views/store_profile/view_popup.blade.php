<div class="container">                                                      
  <div class="table-responsive"> 
    <table  class="table tablesaw table-bordered table-hover table-striped mb-0" width="100%" cellpadding="6" border="0" frame="box" style="border:1px solid #C0C0C0 !important;">
        <thead>
        <tr class="blue_Head">
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Company Name</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Full Name</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3" >Company Address</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Personal Mobile No.</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Company Mobile No.</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">GSTIN</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Personal Email Id</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Company Email Id</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">Website</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">Facebook</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Instagram</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="13">Pinterest</th>
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
                <tr id="" class="<?php echo $tblclass ?>">
                    <td class="leftAlign">{{$value->company_profile->company_name}}</td>
                    <td class="leftAlign">{{$value->company_profile->full_name}}</td>
                    <td class="leftAlign">{!!$value->company_profile->company_address!!},{{$value->company_profile->company_pincode}},{{$value->company_profile->state_name->state_name}},{{$value->company_profile->country_name->country_name}}</td>
                    <td class="leftAlign">{{$value->company_profile->personal_mobile_no}}</td>
                    <td class="leftAlign">{{$value->company_profile->company_mobile}}</td>
                    <td class="rightAlign">{{$value->company_profile->gstin}}</td>
                    <td class="rightAlign">{{$value->company_profile->personal_email}}</td>
                    <td class="rightAlign">{{$value->company_profile->company_email}}</td>
                    <td class="rightAlign">{{$value->company_profile->website}}</td>
                    <td class="rightAlign">{{$value->company_profile->facebook}}</td>
                    <td class="rightAlign">{{$value->company_profile->instagram}}</td>
                    <td class="rightAlign">{{$value->company_profile->pinterest}}</td>
                </tr>
            @endforeach
        </tbody>
     </table>
    </div>
</div>
<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script src="{{URL::to('/')}}/public/template/bootstrap/dist/js/bootstrap.min.js"></script>