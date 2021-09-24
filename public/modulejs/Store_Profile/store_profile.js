function edit_store_profile(store_id,company_id,company_profile_id)
{
    var  url = "edit_store";
    var type = "POST";
    var dataType = "";
    var data = {
        'company_relationship_tree_id' : store_id,
        'company_id' : company_id,
        'company_profile_id' : company_profile_id
    };

    callroute(url, type, dataType, data,function (data) {
       var dta = JSON.parse(data);
        if (dta['Success'] == "True")
        {
            var url = '';
            if(dta['url'] != '' && dta['url'] != 'undefined')
            {
                url = dta['url'];
            }
            localStorage.setItem('company_profile', JSON.stringify(dta['Data'][0]));
            window.location.href = url;
        }
    });
}

// view popup
function View_Store_Detail(obj)
{
    var id = $(obj).attr('id');
    var company_relationship_trees_id = $(obj).attr('id').split('viewstore_')[1];
    var data = {
        'company_relationship_trees_id':company_relationship_trees_id
    };
    var url = 'view_store_popup';
    var type = "POST";
    var dataType = "";

    callroute(url, type, dataType, data,function (data)
    {
        $('#store_values').html('');
        $('#store_values').html(data);
        $("#viewstorepopup").modal('show');
    });
}
