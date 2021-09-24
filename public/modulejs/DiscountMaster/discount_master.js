$("#addbuyx").click(function()
{
//
    $(this).prop('disabled', true);
    count++;
   // $("#image1").clone().attr('id', 'product_image_'+count).insertAfter("#product_image_1");
    $('#addmoreimg').before('<div class="col-md-2 block_'+count+'" class="previews"><label class="form-label">Product Image Caption</label><input type="text" name="imageCaption[]" id="imageCaption_'+count+'" placeholder="" /><button type="button" class="btn btn-danger mt-10" onclick="removefun('+count+')"><i class="fa fa-minus"></i></button></div><div class="col-md-2 block_'+count+'">'+
        '<div class="form-group">' +
        '<label class="form-label">Product Image</label><input onchange="previewandvalidation(this);" accept=".png, .jpg, .jpeg" type="file" name="product_image[]" id="product_image_'+count+'" data-counter="'+count+'" class="form-control form-inputtext productimage">' +
        '<div style="display: none" id="preview_'+count+'" class="previews">' +
        '<a  onclick="removeimgsrc('+count+');" class="displayright"><i class="fa fa-remove" style="font-size: 20px;"></i></a>' +
        '<img src="" id="product_preview_'+count+'" width="" height="150px"></div></div></div>');

    $(this).prop('disabled', false);

});
$("#start_date").datepicker({

    format:'dd-mm-yyyy',
    startDate: '+1d',
    autoclose: true,
    todayHighlight:false,
}).on('keypress paste', function (e) {
    e.preventDefault();
    return false;
});
$("#end_date").datepicker({
    format:'dd-mm-yyyy',
    startDate: '+1d',
    autoclose: true,
    todayHighlight:false,
}).on('keypress paste', function (e) {
    e.preventDefault();
    return false;
});
$("#start_date").change(function(e){
     
     var startdate  =   $('#start_date').val();
     $('.startdate').html(startdate);

});

$("#end_date").change(function(e){
     
        var from            =   $("#start_date").val();
        var to              =   $("#end_date").val();
        var fromdate        =   from.split('-');
        var todate          =   to.split('-');
        var newstartdate    =   new Date(fromdate[2]+'-'+fromdate[1]+'-'+fromdate[0]);
        var newtodate       =   new Date(todate[2]+'-'+todate[1]+'-'+todate[0]);
         

      

        if(newstartdate > newtodate){
               error = 1;
               toastr.error("End Date cannot be greater than Start Date range !"); 
        }
        else
        {
          var enddate  =   $('#end_date').val();
          $('.enddate').html(enddate);
        }

     

});
function removerow(productid)
{
    $("#product_"+productid).remove();
}

function deleteflatdiscount(obj)
{
    if(confirm("Are You Sure want to delete this Flat Discount Scheme?")) {

         var id                        =     $(obj).attr('id');
         var discountid                =     $(obj).attr('id').split('deleteflatdiscount_')[1];
        

        if(discountid.length > 0)
        {
        var data = {
            "deleted_id": discountid
        };
        var url = "flatdiscount_delete";
        var type = "POST";
        var dataType = "";
        callroute(url, type,dataType, data, function (data) {

            var dta = JSON.parse(data);

            if (dta['Success'] == "True")
            {
                toastr.success(dta['Message']);
                resettable('datewise_flatdiscount_detail', 'viewflatdiscountrecord');
              
               // $('.search_data').trigger("click");
                
            } else {
                toastr.error(dta['Message']);
            }
        })
            }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}

$(document).on('click', '#view_flatproducts_lists', function(){

        var url = "view_flatproducts"
        window.location.href  = url;
              

 });
function productflatdiscper(obj)
{
 if(obj.value != '' && obj.value != undefined)
    {
       obj.value = obj.value.replace(/[^\d.]/g, '');

    }
         var id                        =     $(obj).attr('id');
         var product_id                =     $(obj).attr('id').split('flatdiscountper_')[1];
         var discountpercent           =     $('#flatdiscountper_'+product_id).val();
         
         var mrp                       =     $('#mrp_'+product_id).html();
         
         var discountamount     =     Number(mrp) * Number(discountpercent) / 100;
         var offerprice         =     Number(mrp) - Number(discountamount);                     


          $('#flatdiscountper_'+product_id).val(discountpercent);
          $('#flatdiscountamt_'+product_id).val(Number(discountamount).toFixed(4));
          $('#offerprice_'+product_id).html(Number(offerprice).toFixed(4));
}
function productflatdiscamt(obj)
{
 if(obj.value != '' && obj.value != undefined)
    {
       obj.value = obj.value.replace(/[^\d.]/g, '');

    }
         var id                        =     $(obj).attr('id');
         var product_id                =     $(obj).attr('id').split('flatdiscountamt_')[1];
         var discountamount            =     $('#flatdiscountamt_'+product_id).val();
         
         var mrp                       =     $('#mrp_'+product_id).html();
         
         var discountpercent     =     (Number(discountamount) / Number(mrp)) * 100;
         var offerprice          =     Number(mrp) - Number(discountamount);                     


          $('#flatdiscountper_'+product_id).val(Number(discountpercent).toFixed(4));
          $('#flatdiscountamt_'+product_id).val(Number(discountamount));
          $('#offerprice_'+product_id).html(Number(offerprice).toFixed(4));
}
function applyFlatdiscountper()
{
  var error  = 0;
  
    var discountpercent       =  $('#flat_discount_per').val();
     $("#flatproductdetail").each(function (index,e)
      {
           
             $(this).find('tr').each(function ()
             {
                if($(this).attr('id') != undefined)
                {
                    rcolumn = $(this).attr('id').split('product_')[1];
                    
                 }    
                 
                
                  if(($("#productid_"+rcolumn).val())!='')
                  {
                     
                      var mrp                =     $('#mrp_'+rcolumn).html();
                     
                      var discountamount     =     Number(mrp) * Number(discountpercent) / 100;
                      var offerprice         =     Number(mrp) - Number(discountamount);   

                       if(Number(offerprice)<0 || Number(offerprice)==0)
                      {
                        error  = 1;
                        $('#product_'+rcolumn).css('background','#ffcfbe');
                        
                      } 
                      else
                      {
                        $('#product_'+rcolumn).css('background','transparent');
                      }                  
                  


                      $('#flatdiscountper_'+rcolumn).val(discountpercent);
                      $('#flatdiscountamt_'+rcolumn).val(Number(discountamount).toFixed(4));
                      $('#offerprice_'+rcolumn).html(Number(offerprice).toFixed(4));
                     
                  }
                


             });

          


        });

      if(error == 1)
      {
         toastr.error("Discount given is more than MRP for Coloured Rows.!"); 
      }
}
function applyFlatdiscountamt()
{
  
    var discountamount       =  $('#flat_discount_amt').val();
    var error  = 0;
     $("#flatproductdetail").each(function (index,e)
      {
           
             $(this).find('tr').each(function ()
             {
                if($(this).attr('id') != undefined)
                {
                    rcolumn = $(this).attr('id').split('product_')[1];
                    
                 }    
                 
                
                  if(($("#productid_"+rcolumn).val())!='')
                  {
                     
                      var mrp                =     $('#mrp_'+rcolumn).html();
                     
                      var discountpercent    =     (Number(discountamount) / Number(mrp)) * 100;
                      var offerprice         =     Number(mrp) - Number(discountamount);  

                      if(Number(offerprice)<0 || Number(offerprice)==0)
                      {
                        error  = 1;
                        $('#product_'+rcolumn).css('background','#ffcfbe');
                        
                      } 
                      else
                      {
                        $('#product_'+rcolumn).css('background','transparent');
                      }                  

                     // console.log(discountpercent);
                      $('#flatdiscountper_'+rcolumn).val(Number(discountpercent).toFixed(4));
                      $('#flatdiscountamt_'+rcolumn).val(Number(discountamount));
                      $('#offerprice_'+rcolumn).html(Number(offerprice).toFixed(4));
                     
                  }
                


             });

          


        });

      if(error == 1)
        {
           toastr.error("Discount given is more than MRP for Coloured Rows.!"); 
        }
}



$("#saveFlatDiscount").click(function (e) {

     $(this).prop('disabled', true);

  if(validate_flatdiscount('FlatDiscountForm'))
  {
      $("#saveFlatDiscount").prop('disabled', true);


      var array = [];



      $('#flatproductdetail tr').has('td').each(function()
      {
          var arrayItem = {};
          $('td', $(this)).each(function(index, item)
          {
              var inputname = $(item).attr('id');

                if(inputname != undefined && inputname != '')
                {
                    var wihoutidname = inputname.split('_');
                    var nameforarray = wihoutidname[0];

                   

                        if(nameforarray == 'caldiscountper')
                        {
                           
                            arrayItem['discount_percent'] =$(this).find("#flatdiscountper_"+wihoutidname[1]).val();
                          
                        }                        
                        else if(nameforarray == 'caldiscountamt')
                        {
                            arrayItem['discount_amount'] =$(this).find("#flatdiscountamt_"+wihoutidname[1]).val();

                        }
                        else if(nameforarray == 'showmrp')
                        {
                            arrayItem['mrp'] =$(this).find("#mrp_"+wihoutidname[1]).html();

                        }
                        else if(nameforarray == 'showoffer')
                        {
                            arrayItem['offer_price'] =$(this).find("#offerprice_"+wihoutidname[1]).html();
                            arrayItem['product_id'] =$(this).find("#productid_"+wihoutidname[1]).val();

                        }
                       
                }

          });
          array.push(arrayItem);
      });

      var arraydetail = [];
      arraydetail.push(array);


      var commondetail = {};
      

      commondetail['from_date'] = $("#start_date").val();
      commondetail['to_date'] = $("#end_date").val();

      arraydetail.push(commondetail);

 
       console.log(arraydetail);
      //return false;
   
      var data = arraydetail;

      var  url = "flatdiscount_create";
      var type = "POST";
      var dataType = '';
      callroute(url,type,dataType,data,function (data)
      {
          $("#saveFlatDiscount").prop('disabled', true);
          var dta = JSON.parse(data);

          if(dta['Success'] == "True")
          {
            
               toastr.success(dta['Message']);
               window.location = dta['url'];
              $("#FlatDiscountForm").trigger('reset');
              $("#flatproductdetail").empty('');

              
          }
          else
          {
            $("#saveFlatDiscount").prop('disabled', true);
               toastr.error(dta['Message']);
               

          }
      })

  }
   else
    {
        $("#saveFlatDiscount").prop('disabled', false);
        return false;
    }
});

function validate_flatdiscount(frmid)
{
    var error = 0;

    var flatdiscount  = 0;
    $('.productid').each(function(e){

        if($(this).val()!=0)
        {
            flatdiscount++;
        }
    });
   

    if(Number(flatdiscount)==0)
    {
        error = 1;
        toastr.error("Please choose Products to Apply Flat Discount");
        return false;

    }
    else
    {
        if(($('#flat_discount_per').val() == 0 || $('#flat_discount_per').val() == '') && $('#flat_discount_amt').val()=='')
        {
                
                 error = 1;
                 toastr.error("aPlease enter Flat Discount Percentage or Amount for products !");  
                   

        }
        else if(($('#flat_discount_amt').val() == 0 || $('#flat_discount_amt').val() == '') && $('#flat_discount_per').val()=='')
        {
              
                 error = 1;
                 toastr.error("bPlease enter Flat Discount Percentage or Amount for products !");  
              
        }
        else if($('#start_date').val() =='' || $('#end_date').val() == '')
        {
               error = 1;
               toastr.error("Please choose Date range for Flat discount Scheme !"); 
        }

    }
    
   if($('#start_date').val() !='' && $('#end_date').val() != '')
   {
  
        var from            =   $("#start_date").val();
        var to              =   $("#end_date").val();
        var fromdate        =   from.split('-');
        var todate          =   to.split('-');
        var newstartdate    =   new Date(fromdate[2]+'-'+fromdate[1]+'-'+fromdate[0]);
        var newtodate       =   new Date(todate[2]+'-'+todate[1]+'-'+todate[0]);
         

      

        if(newstartdate > newtodate){
               error = 1;
               toastr.error("End Date cannot be greater than Start Date range !"); 
        }
        
   }
   

    if(error == 1)
    {
        return false;
    }
    else
    {
        $('#saveFlatDiscount').prop('disabled', true);
       
        return true;
    }
}

