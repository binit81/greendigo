$("#add_loyaltysetup").click(function ()
{
   if(validate_loyaltyform('loyalty_setupform'))
   {
       $("#add_loyaltysetup").prop('disabled',true);
       var type = "POST";
       var dataType = "";
       var data = {
           "formdata": $("#loyalty_setupform").serialize(),
       };
       var url = "add_loyalty_setup";

       callroute(url,type,dataType,data,function (res)
       {
           $("#add_loyaltysetup").prop('disabled',false);

           var dta = JSON.parse(res,true);

           if(dta['Success'] == "True")
           {
                toastr.success(dta['Message']);
                return false;
           }else{
               toastr.error(dta['Message']);
               return false;
           }
       })
   }
   else{
       return false;
   }
});

function validate_loyaltyform(frmid)
{
    var error = 0;

    if($("#schedule_date").val() == '')
    {
        error = 1;
        toastr.error("Schedule Date Can Not Be Empty");
    }
    if (!$("#infinity").is(':checked')) {
        if($("#expiry_date").val() == '')
        {
            error = 1;
            toastr.error("Expiry Date Can Not Be Empty");
        }
    }





    if($("#purchase_amount").val() == '')
    {
        error = 1;
        toastr.error("Purchase Amount Can Not Be Empty");
    }

    if($("#points").val() == '')
    {
        error = 1;
        toastr.error("Points Can Not Be Empty");
    }
    if($("#points_amount").val() == '')
    {
        error = 1;
        toastr.error("Points Amount Can Not Be Empty");
    }

    if($("#redeem_point").val() == '')
    {
        error = 1;
        toastr.error("Redeem Points Limit Can Not Be Empty");
    }

    if(error == 1)
    {
        return false;
    }else{
        return true;
    }
}

$("#resetloyalty_setup").click(function () {

    $("#loyalty_setupform").find('input','select','hidden').val('');
});

$(function () {
    jQuery.noConflict();
    $("[id*=schedule_date]").datepicker({
        minDate: new Date(),
        format: "dd-mm-yyyy",
        onSelect: function (selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate() + 1);
            $("[id*=expiry_date]").datepicker("option", "minDate", dt);
        }
    });
    $("[id*=expiry_date]").datepicker({
        onSelect: function (selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate() - 1);
            $("[id*=schedule_date]").datepicker("option", "maxDate", dt);
        }
    });
});

$("#infinity").click(function () {
    if($("#infinity").prop('checked'))
    {
        $("#expiry_date").removeClass('invalid');
        $("#expiry_date").attr('disabled',true);
    }
    else {
        $("#expiry_date").addClass('invalid');
        $("#expiry_date").attr('disabled',false);
    }
});

