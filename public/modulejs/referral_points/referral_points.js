$(".percentcls").on('input',function () {
   if($(this).val() != '')
   {
       $(this).parent().next('div').find('.pointscls').val(0);
       $(this).parent().next().next('div').find('.pointamtcls').val(0);
       $(this).parent().next('div').find('.pointamtcls').prop("disabled",true);
   }
});

$(".pointscls").on('input',function ()
{
    if($(this).val() != '')
    {
        $(this).parent().prev('div').find('.percentcls').val(0);

        //
        $(this).parent().next('div').find('.pointamtcls').prop("disabled",false);
    }

});


$("#add_referral_point").click(function () {
    $("#add_referral_point").prop('disabled',true);

        var data_arr = [];
    $("#referral_tbody").find('tr').each(function ()
    {
        if($(this).attr('id') != undefined){
        var datas = {};
        $(this).find('input').each(function ()
        {
            var val = $(this).val();
            var id = $(this).attr('id').split('_').pop();
            var html_id = $(this).attr('id').split('_'+id)[0];

            datas['referral_point_id'] = id;
            datas[html_id] = val;
        });
            data_arr.push(datas);
        }
    });
    var url = 'add_referral_point';
    var type = "POST";
    var dataType = '';
    var data = data_arr;

    callroute(url,type,dataType,data,function (res)
    {
        var dta = JSON.parse(res);
        $("#add_referral_point").prop('disabled',false);
        if(dta['Success'] == "True")
        {
            toastr.success(dta['Message']);
        }
        else {
            toastr.error(dta['Message']);
        }
    })



});
