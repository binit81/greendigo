   <?php
   $show_dynamic_feature = '';

   $dynamic_header = '';
   $dynamic_cnt = 0;



   if (isset($product_features) && $product_features != '' && !empty($product_features))
   {
       foreach ($product_features AS $feature_key => $feature_value)
       {
           if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
           {
               $search = 'print_bill';

               if (strstr($feature_value['show_feature_url'],$search) )
               {
                   if($show_dynamic_feature == '')
                   {
                       $show_dynamic_feature =$feature_value['html_id'];
                   }
                   else
                   {
                       $show_dynamic_feature = $show_dynamic_feature.','.$feature_value['html_id'];
                   }


                   $dynamic_header .= '<th class="text-dark font-12" style="width:8% !important;">'.$feature_value['product_features_name'].'</th>';
                   $dynamic_cnt++;
               }

           }
       }
   }
   ?>

    <!DOCTYPE html>
<!--
Template Name: Mintos - Responsive Bootstrap 4 Admin Dashboard Template
Author: Hencework
Contact: https://hencework.ticksy.com/

License: You must have a valid license purchased only from templatemonster to legally use the template for your project.
-->
<html lang="en">


<!-- Mirrored from hencework.com/theme/mintos/dashboard1.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 05 Mar 2019 07:02:35 GMT -->
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title>Print Bill</title>
<meta name="description" content="A responsive bootstrap 4 admin dashboard template by hencework" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<!-- Favicon -->
<link rel="shortcut icon" href="favicon.ico">
<link rel="icon" href="favicon.ico" type="image/x-icon">

<!-- vector map CSS -->

<!-- Custom CSS -->
<!-- <link href="{{URL::to('/')}}/public/dist/css/style.css" rel="stylesheet" type="text/css">  -->
<!--<link href="{{URL::to('/')}}/public/template/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"> -->

<link href="{{URL::to('/')}}/public/dist/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href='https://fonts.googleapis.com/css?family=Roboto Condensed' rel='stylesheet'>
<script type="text/javascript">window.print();</script>
<style type="text/css">
 .taxinvoice { 
            display: flex; 
            flex-direction: row; 
        } 
          
        .taxinvoice:before, 
        .taxinvoice:after { 
            content: ""; 
            flex: 1 1; 
            border-bottom: 2px solid #000; 
            margin: auto; 
        } 
.text-right
{
    text-align:right;
}
.text-center
{
    text-align:center;
}
.font-weight-600
{
    font-weight:600;
}
.underline{
    border-bottom:1px solid #C0C0C0;
}
.upperline{
    border-top:1px solid #C0C0C0;
}
.bottomdarkline{
    border-bottom:1px solid;
}
.upperdarkline
{
  border-top:1px solid;
}
.page-header, .page-header-space {
  height: 130px;
}

.page-footer, .page-footer-space {
  /*height: 150px;*/

}

.page-footer {
  /*position: fixed;*/
  /*bottom: 0;*/
  width: 100%;
  border-top: 0px solid white; /* for demo */
  /*background: yellow; /* for demo */*/
}

.page-header {
  position: fixed;
  top: 0mm;
  width: 100%;
  border-bottom: 0px solid white;
   /* for demo */
  /*background: yellow; /* for demo */*/
}
.even
{
  background: #ffffff;
}
.odd
{
  background: #f3f3f3;
}

/*.page {
  page-break-after: always;
}*/

@page {
  margin: 20mm
}




@media print {
   thead {display: table-header-group;}
   tfoot {display: table-footer-group;}

   button {display: none;}

   body {margin: 0;}
}
</style>


<body style="font-family: 'Roboto Condensed';padding:0;margin:0;">
<!--************************************************************Header Section Code********************************************* -->
   

  <div class="page-header" style="text-align: center;margin:15px 0 0 0 !important;float:left;">
     <div style="width:100%;float:left;border:0px solid;">            

        <div style="text-align:left !important;width:33% !important;border:0px solid !important;font-size:15px;float:left;">
                <div style="margin:0 5px 0 8px;">                 
                      <span class="d-block font-weight-600" style="font-size:18px;"><span class="pl-10 text-dark">{{$billingdata->company->company_name}}</span></span><br>
                      <span class="d-block"><span class="pl-10 text-dark">{{strip_tags($billingdata->company->company_address)}}</span></span><br>
                      <span class="d-block"><span class="pl-10 text-dark">{{strip_tags ($billingdata->company->company_area)}} {{$billingdata->company->company_city}} - {{$billingdata->company->company_pincode}}</span></span><br>
                      <?php
                      $company_mobile_code =  explode(',',$billingdata->company->company_mobile_dial_code);
                      if($billingdata->company->company_mobile!='' || $billingdata->company->company_mobile!=null)
                      {
                      ?>
                       <span class="d-block"><span class="pl-10 text-dark">({{$company_mobile_code[0]}}){{$billingdata->company->company_mobile}}</span></span><br>
                       <?php
                        }
                       ?>
                        <span class=""><span class="pl-10 text-dark">{{$billingdata->company->company_email}}</span></span><br>

                 </div>
                  
                  
                 
              </div>          
        <?php
            $whtapp_mobile_code =  explode(',',$billingdata->company->whatsapp_mobile_dial_code);
        ?>
        <div style="width:34% !important;border:0px solid !important;font-size:16px;float:left;margin:-20px 0 0 0;">
          <img class="img-fluid invoice-brand-img d-block" src="{{URL::to('/')}}/public/dist/img/rcslogo.png" width="220px" alt="logo" style="margin:0 auto;border:0px solid;"/>
        </div>
              

        <div style="width:33% !important;border:0px solid !important;font-size:16px;float:right;text-align:right;">
             <table style="text-align:right !important;float:right;">
                      <tr>
                      <td colspan="3" class="font-weight-600">{{strip_tags ($billingdata->company->website)}}</td>
                      
                      </tr>
                       <?php 
                       if(isset($billingdata) && isset($billingdata['company'])
                           && $billingdata['company']['whatsapp_mobile_number']!=NULL)   
                       {
                          ?>
                      <tr>
                      <td class="d-block font-weight-600"><a class="fa fa-whatsapp"></a></td>
                      <td class="font-weight-600">&nbsp;:&nbsp;</td>
                      <td class="font-weight-600">({{$whtapp_mobile_code[0]}}){{$billingdata->company->whatsapp_mobile_number}}</td>
                      </tr>
                      <?php                                            
                      
                      } 
                       
                     if(isset($billingdata) && isset($billingdata['company'])
                           && $billingdata['company']['facebook']!=NULL)   
                       {
                          ?>
                          <tr>
                          <td class="d-block font-weight-600"><a class="fa fa-facebook"></a></td>
                          <td class="font-weight-600">&nbsp;:&nbsp;</td>
                          <td class="font-weight-600">{{$billingdata->company->facebook}}</td>
                          </tr><?php
                        
                      
                      } 
                       
                      if(isset($billingdata) && isset($billingdata['company'])
                           && $billingdata['company']['instagram']!=NULL)   
                        {
                          ?>
                        <tr>                                          
                          <td class="d-block font-weight-600"><a class="fa fa-instagram"></a></td>
                          <td class="font-weight-600">&nbsp;:&nbsp;</td>
                          <td class="font-weight-600">{{$billingdata->company->instagram}}</td>                                              
                         </tr>
                         <?php
                        
                      
                      } 
                       if(isset($billingdata) && isset($billingdata['company'])
                           && $billingdata['company']['pinterest']!=NULL)   
                        {
                          ?>
                       <tr>
                          <td class="d-block font-weight-600"><a class="fa fa-pinterest"></a></td>
                          <td class="font-weight-600">&nbsp;:&nbsp;</td>
                          <td class="">{{$billingdata->company->pinterest}}</td>                                             
                      </tr>
                       <?php
                        
                      
                      } 
                      ?>
                  </table> 
         </div>
                                
            </div>
           <?php
          
           ?>  
      </div>
    
  </div>
<!--************************************************************End Header Section Code********************************************* -->
<!--************************************************************Footer Section Code********************************************* -->
  <div class="page-footer">
    
  </div>
<!--************************************************************End Footer Section Code********************************************* -->
   <table width="100%">

    <thead>
      <tr>
        <td>
          <!--place holder for the fixed-position header-->
          <div class="page-header-space"></div>
        </td>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>
          <!--*** CONTENT GOES HERE ***-->
          <!-- <div class="page">PAGE 1</div>
          <div class="page">PAGE 2</div> -->
          <div class="page">
            <!--*** CONTENT GOES HERE ***-->
                <span class="taxinvoice">TAX INVOICE</span>
                <div style="width:100%;border:0px solid;float:left;margin:5px 0 0 0;">
                                    <div style="width:50% !important;border:0px solid !important;font-size:16px;float:left;"> 
                                        <table style="float:left;">
                                            <?php
                                             if(isset($billingdata) && isset($billingdata->customer) && $billingdata['customer']['customer_name']!=NULL)   
                                             {
                                                ?>
                                            <tr>
                                            <td colspan="3"><span class="d-block font-weight-600" style="font-size:20px;text-align:left;">Customer Details</span></td>    
                                            </tr>
                                            <tr>
                                            <td class="d-block font-weight-600">Customer</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="">{{$billingdata->customer->customer_name}}</td>
                                            </tr>
                                            <?php
                                            }

                                             if(isset($billingdata) && isset($billingdata->customer) && $billingdata['customer']['customer_mobile']!=NULL)   
                                             {
                                            ?>
                                            <tr>
                                            <td class="d-block font-weight-600">Mobile</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="">{{$billingdata->customer->customer_mobile}}</td>
                                            </tr>
                                            <?php
                                            }
                                            if(isset($billingdata) && isset($billingdata['customer_address_detail'])
                                                && $billingdata['customer_address_detail']['customer_address'] != NULL)
                                            {
                                                
                                                $customer_address = $billingdata['customer_address_detail']['customer_address'].' ,'.$customer_country; 
                                                ?>
                                                 <tr>
                                                <td class="d-block font-weight-600">Address</td>
                                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                                <td class="">{{$customer_address}}</td>
                                                </tr>
                                                <?php
                                            } 
                                            ?>
                                            

                                            <?php
                                           
                                            if(isset($billingdata) && isset($billingdata['customer_address_detail'])
                                            && $billingdata['customer_address_detail']['customer_gstin']!=NULL)
                                            {
                                            ?>
                                            
                                            <tr>
                                            <td class="d-block font-weight-600">GSTIN</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="">{{$billingdata['customer_address_detail']['customer_gstin']}}</td>
                                            </tr>
                                            <?php
                                            }
                                            ?>
                                  
                                        </table>                                      
                                        
                           
                                          
                                    </div>
                                    <div style="width:49% !important;border:0px solid !important;font-size:16px;float:right;">
                                        <?php

                                        
                                        $invoicedate        =         date("d-m-Y",strtotime($billingdata->bill_date));

                                        if(isset($billingdata) && isset($billingdata['customer_address_detail'])
                                            && isset($billingdata['customer_address_detail']['customer_gstin']))
                                        {
                                            $customer_gstin = $billingdata['customer_address_detail']['customer_gstin']; 
                                        } else {
                                            $customer_gstin = '';
                                        }
                                        ?>
                                       <table style="float:right;">
                                            <tr>
                                            <td class="d-block font-weight-600">Invoice No.</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="text-right">{{$billingdata->bill_no}}</td>
                                            </tr>
                                            <tr>
                                            <td class="d-block font-weight-600">Invoice Date</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="text-right">{{$invoicedate}}</td>
                                            </tr>
                                            <?php
                                           
                                            if(isset($billingdata) && isset($billingdata['company'])
                                            && $billingdata['company']['gstin']!=NULL)
                                            {
                                            ?>
                                            <tr>
                                            <td class="d-block font-weight-600">GSTIN</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="text-right">{{$billingdata->company->gstin}}</td>
                                            </tr>
                                           <?php
                                           }
                                           ?>
                                            <tr>
                                            <td class="d-block font-weight-600">Place</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="text-right">{{$billingdata['company']['state_name']['state_name']}}</td>
                                            </tr>
                                             <?php
                                             if(isset($billingdata) && isset($billingdata['reference'])
                                            && isset($billingdata['reference']['reference_name']))
                                             {
                                            ?>
                                            <tr>
                                            <td class="d-block font-weight-600">Reference</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="text-right">{{$billingdata['reference']['reference_name']}}</td>
                                            </tr>
                                            <?php
                                             }
                                             $username  = $billingdata['user']['employee_firstname'].' '.$billingdata['user']['employee_lastname'].' '.$billingdata['user']['employee_middlename'];
                                            
                                            ?>
                                            <!--<tr>
                                            <td class="d-block font-weight-600">Cashier Name</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600 text-right">{{$username}}</td>
                                            </tr>-->
                                        </table>   
                                       
                                    </div>
                               
                            </div>                          
                          
         <?php
         if($bill_calculation ==1)
         {
              $billing_calculation_case  = "";
         }
         else
         {
              $billing_calculation_case  = "display:none;";
         }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************If tax type is international vat*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////      

        if($tax_type==1)
        {
            ?>
            <table width="100%" cellpadding="6" frame="box" border="1" style="border-collapse:collapse;float:left;margin:15px 0 0 0;">
                                    <thead>
                                        <tr style="background:#999;border-bottom:1px #999 solid;border-top:1px #999 solid;">
                                           
                                            <?php
 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************show discount column if discount is given*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////                                            
                                             if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                             {
                                                 // <th class="text-right text-dark font-12 font-weight-600" style="width:6% !important;">MRP</th>
                                                    echo '<th class="text-dark font-12" style="width:5% !important;">Sr No.</th>
                                                    <th class="text-dark font-12" style="width:37% !important;">Item Description</th>
                                                    <th class="text-dark font-12" style="width:10% !important;">Barcode</th>';

                                                    echo  $dynamic_header;

                                                    echo '<th class="text-right text-dark font-12" style="width:8% !important;'.$billing_calculation_case.'">Rate</th>
                                                    <th class="text-right text-dark font-12" style="width:6% !important;">Qty</th>
                                                    <th class="text-right text-dark font-12" style="width:7% !important;'.$billing_calculation_case.'">Disc. Amt.</th>
                                                    <th class="text-right text-dark font-12" style="width:10% !important;'.$billing_calculation_case.'">Taxable<br>Amount</th>
                                                     <th class="text-right text-dark font-12" style="width:7% !important;'.$billing_calculation_case.'" colspan="2">'.$taxname.'<br>% & Amt.</th>
                                                    <th class="text-right text-dark font-12" style="width:12% !important;'.$billing_calculation_case.'">Total Amount</th>';
                                                }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************Hide discount column if discount is not given*****************************************************************************************************/////////////////////////////////////////////////////////////////////////////////////////// 
                                                else
                                                {
                                                    // <th class="text-right text-dark font-12 font-weight-600" style="width:6% !important;">MRP</th>

                                                    echo '<th class="text-dark font-12" style="width:5% !important;">Sr No.</th>
                                                    <th class="text-dark font-12" style="width:42% !important;">Item Description</th>
                                                    <th class="text-dark font-12" style="width:10% !important;">Barcode</th>';
                                                    echo  $dynamic_header;
                                                    echo '<th class="text-right text-dark font-12" style="width:8% !important;'.$billing_calculation_case.'">Rate</th>
                                                    <th class="text-right text-dark font-12" style="width:6% !important;">Qty</th>
                                                    <th class="text-right text-dark font-12" style="width:10% !important;'.$billing_calculation_case.'">Taxable<br>Amount</th>
                                                     <th class="text-right text-dark font-12" style="width:7% !important;'.$billing_calculation_case.'" colspan="2">'.$taxname.'<br>% & Amt.</th>
                                                    <th class="text-right text-dark font-12" style="width:12% !important;'.$billing_calculation_case.'">Total Amount</th>';
                                                }
                                                ?>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                $ttaldiscount = 0;
                                                $totalcgst = 0;
                                                $totalsgst = 0;
                                                $totaligst = 0;
                                                $tottaxable = 0;
                                                foreach($billingproductdata AS $billingproduct_key=>$billingproduct_value)
                                                {
                                               
                                                    if ($billingproduct_key % 2 == 0) {
                                                            $tblclass = '#fff;';
                                                        } else {
                                                            $tblclass = '#f3f3f3;';
                                                        } 


                                                $totaldiscount   =   $billingproduct_value->discount_amount + $billingproduct_value->overalldiscount_amount;

                                                $sno  =   $billingproduct_key + 1;
                                                $ttaldiscount  +=  $totaldiscount;
                                                
                                                $totaligst  +=  $billingproduct_value->igst_amount;
                                                $tottaxable +=  $billingproduct_value->sellingprice_afteroverall_discount;
                                                if($billingproduct_value['product']['supplier_barcode']!='' && $billingproduct_value['product']['supplier_barcode']!=null)
                                                {
                                                  $barcode     =     $billingproduct_value['product']['supplier_barcode'];
                                                }
                                                else
                                                {
                                                  $barcode     =     $billingproduct_value['product']['product_system_barcode'];
                                                }
                                                $feature_show_val = "";

                                                if($show_dynamic_feature != '')
                                                {
                                                    $feature = explode(',',$show_dynamic_feature);

                                                    foreach($feature AS $fea_key=>$fea_val)
                                                    {
                                                        $feature_show_val .= '<td class="text-left">'.$billingproduct_value['product'][$fea_val].'</td>';
                                                    }
                                                }

                                                ?>
                                                <tr style="border-bottom:1px solid #C0C0C0 !important;">
                                                    <td>{{$sno}}</td>
                                                    <td><?php echo html_entity_decode($billingproduct_value->product->product_name)?></td>
                                                    <td>{{$barcode}}</td>
                                                <?php

                                                echo $feature_show_val;

                                                ?>
                                                    <!-- <td class="text-right">{{round($billingproduct_value->mrp)}}</td> -->
                                                    <td class="text-right">{{round($billingproduct_value->sellingprice_before_discount,2)}}</td>
                                                    
                                                    <td class="text-right">{{round($billingproduct_value->qty)}}</td>
                                                    <?php
                                                     if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                                     {
                                                        ?>
                                                        <td class="text-right">{{round($totaldiscount,2)}}</td>
                                                       
                                                        <?php
                                                     }
                                                    ?>
                                                    <td class="text-right totcgstamt">{{round($billingproduct_value->sellingprice_afteroverall_discount,2)}}</td>
                                                    <td class="text-right">{{$billingproduct_value->igst_percent}}</td>
                                                    <td class="text-right totsgstamt">{{round($billingproduct_value->igst_amount,2)}}</td>
                                                    <td class="text-right tottotamt">{{round($billingproduct_value->total_amount,2)}}</td>
                                                    
                                                </tr>

                                               
                                                <?php
                                            }
                                                
                                                $rest   =   1 - $productcount;
                                                for($x=1; $x<=$rest; $x++)
                                                {
                                                    ?>
                                                <tr>
                                                    <td class="text-dark">&nbsp;</td>
                                                    <td class="text-dark"></td>
                                                    <td class="text-dark"></td>

                                                    <?php
                                                        for($dk=1;$dk<=$dynamic_cnt;$dk++)
                                                            {
                                                        ?>
                                                    <td class="text-dark"></td>
                                                         <?php } ?>


                                                    <!-- <td class="text-dark"></td> -->
                                                    <td class="text-right text-dark"></td>
                                                    <?php
                                                     if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                                     {
                                                            echo '<td class="text-right text-dark"></td>';
                                                     }
                                                     ?>
                                                    
                                                    <td class="text-right text-dark"></td>
                                                    <td class="text-right text-dark totcgstamt"></td>
                                                    <td class="text-right text-dark"></td>
                                                    <td class="text-right text-dark totsgstamt"></td>
                                                    <td class="text-right text-dark tottotamt"></td>
                                                    
                                                </tr>


                                                <?php
                                                 }



                                                    ?>              
                                               
                                           <!--  <tfoot style="border-bottom:1px solid #999 !important;border-top:1px solid #999 !important;"> -->
                                                <tr>
                                                        <?php $colspan = 4 + $dynamic_cnt?>
                                                     <th colspan="<?php echo $colspan ?>" class="text-right font-14 font-weight-600">Total</th>
                                                      <th class="text-right text-dark font-14 font-weight-600">{{round($billingdata->total_qty)}}</th>
                                                      <?php
                                                     if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                                     {
                                                        ?>
                                                         <th class="text-right text-dark font-14 font-weight-600">{{round($ttaldiscount,2)}}</th>
                                                        <?php
                                                     }

                                                    ?>
                                                    
                                                     <th class="text-right text-dark font-14 font-weight-600">{{round($tottaxable,2)}}</th>
                                                    <th></th>
                                                    <th class="text-right text-dark font-14 font-weight-600">{{round($totaligst,2)}}</th>
                                                    <th class="text-right text-dark font-18 font-weight-600"><?php
                                              if($currency_title=='INR')
                                              {
                                                ?>&#x20b9<?php
                                              }
                                              else
                                              {
                                                echo $currency_title;
                                              }
                                              ?> {{round($billingdata->total_bill_amount,$nav_type[0]['decimal_points'])}}</th>
                                                    
                                                  
                                                </tr>
                                         <!--    </tfoot> -->
                                          </tbody>
                                      </table>
            <?php
        }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************End code for tax type international vat*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////         
        else
        {

 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************Start code If tax type is Indian GST*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////          

       

                            if(($billingdata->state_id)==($billingdata->company->state_id))
                            {
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************if custome state is same as Company state then show CGST and SGST*****************************************************************************************************/////////////////////////////////////////////////////////////                             

                          ?>
                                          <table width="100%" cellpadding="6" frame="box" border="1" style="border-collapse:collapse;float:left;margin:15px 0 0 0;">
                                            <thead>
                                                    <tr style="background:#B5B5B5;border-bottom:1px #999 solid;border-top:1px #999 solid;font-size:14px;">
                                                    
                                                     <?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************Show discount column if discount is given*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////                                                      
                                                     if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                                     {
                                                        echo '
                                                        <th class="text-dark font-12 font-weight-600 leftAlign" style="width:5% !important;">Sr.<br>No.</th>
                                                        <th class="text-dark font-12 font-weight-600 leftAlign" style="width:27% !important;">Item Description</th>
                                                        <th class="text-dark font-12 font-weight-600 leftAlign" style="width:9% !important;">Barcode</th>';

                                                        echo $dynamic_header;
                                                      echo  '<th class="text-right text-dark font-12 font-weight-600" style="width:10% !important;'.$billing_calculation_case.'">SellingPrice</th>
                                                        <th class="text-right text-dark font-12 font-weight-600" style="width:4% !important;">Qty</th>
                                                        <th class="text-right text-dark font-12 font-weight-600" style="width:7% !important;'.$billing_calculation_case.'">Disc. Amt.</th>
                                                        <th class="text-right text-dark font-12 font-weight-600" style="width:7% !important;'.$billing_calculation_case.'">Taxable Amount</th>
                                                        <th class="text-right text-dark font-12 font-weight-600" style="width:7% !important;'.$billing_calculation_case.'" colspan="2">CGST<br><small>%/Amt.</small></th>
                                                        <th class="text-right text-dark font-12 font-weight-600" style="width:7% !important;'.$billing_calculation_case.'" colspan="2">SGST<br><small>%/Amt.</small></th>
                                                        <th class="text-right text-dark font-12 font-weight-600" style="width:11% !important;'.$billing_calculation_case.'">Total Amount</th>';
                                                     }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************Hide discount column if discount is not given*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////                                                      
                                                     else
                                                     {
                                                        echo '
                                                        <th class="text-dark font-12 font-weight-600  leftAlign" style="width:5% !important;">Sr.<br>No.</th>
                                                        <th class="text-dark font-12 font-weight-600  leftAlign" style="width:36% !important;">Item Description</th>
                                                        <th class="text-dark font-12 font-weight-600  leftAlign" style="width:6% !important;">Barcode</th>';
                                                          echo $dynamic_header;
                                                        echo '<th class="text-right text-dark font-12 font-weight-600" style="width:10% !important;'.$billing_calculation_case.'">Rate</th>
                                                        <th class="text-right text-dark font-12 font-weight-600" style="width:4% !important;">Qty</th>
                                                        <th class="text-right text-dark font-12 font-weight-600" style="width:7% !important;'.$billing_calculation_case.'">Taxable Amount</th>
                                                        <th class="text-right text-dark font-12 font-weight-600" style="width:7% !important;'.$billing_calculation_case.'" colspan="2">CGST<br><small>%/Amt.</small></th>
                                                        <th class="text-right text-dark font-12 font-weight-600" style="width:7% !important;'.$billing_calculation_case.'" colspan="2">SGST<br><small>%/Amt.</small></th>
                                                        <th class="text-right text-dark font-12 font-weight-600" style="width:11% !important;'.$billing_calculation_case.'">Total Amount</th>';
                                                     }
                                                     ?>
                                                      
                                                      
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                $ttaldiscount = 0;
                                                $totalcgst = 0;
                                                $totalsgst = 0;
                                                $totaligst = 0;
                                                $tottaxable = 0;
                                                foreach($billingproductdata AS $billingproduct_key=>$billingproduct_value)
                                                {
                                                
                                                  if ($billingproduct_key % 2 == 0) {
                                                            $tblclass = '#fff;';
                                                        } else {
                                                            $tblclass = '#f3f3f3;';
                                                        }  
                                                $totaldiscount   =   $billingproduct_value->discount_amount + $billingproduct_value->overalldiscount_amount;
                                                $sno  =   $billingproduct_key + 1;

                                                $totalcgst  +=  $billingproduct_value->cgst_amount;
                                                $totalsgst  +=  $billingproduct_value->sgst_amount;
                                                $tottaxable +=  $billingproduct_value->sellingprice_afteroverall_discount;

                                                $ttaldiscount  +=  $totaldiscount;

                                                if($billingproduct_value['product']['supplier_barcode']!='' && $billingproduct_value['product']['supplier_barcode']!=null)
                                                {
                                                  $barcode     =     $billingproduct_value['product']['supplier_barcode'];
                                                }
                                                else
                                                {
                                                  $barcode     =     $billingproduct_value['product']['product_system_barcode'];
                                                }

                                                $feature_show_val = "";

                                                if($show_dynamic_feature != '')
                                                {
                                                    $feature = explode(',',$show_dynamic_feature);

                                                    foreach($feature AS $fea_key=>$fea_val)
                                                    {
                                                        $feature_show_val .= '<td class="text-center">'.$billingproduct_value['product'][$fea_val].'</td>';
                                                    }
                                                }

                                                ?>
                                                <tr style="border-bottom:1px solid #C0C0C0 !important;font-size:14px !important;">
                                                    <td>{{$sno}}</td>
                                                    <td><?php echo html_entity_decode($billingproduct_value->product->product_name)?></td>
                                                    <td>{{$barcode}}</td>
                                                    <?php echo $feature_show_val; ?>
                                                    
                                                    <td class="text-right" style="{{$billing_calculation_case}}">{{round($billingproduct_value->sellingprice_before_discount,2)}}</td>
                                                     <td class="text-right">{{round($billingproduct_value->qty)}}</td>
                                                     <?php
                                                     if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                                     {
                                                        ?>
                                                        <td class="text-right" style="{{$billing_calculation_case}}">{{round($totaldiscount,2)}}</td>
                                                        <?php
                                                     }
                                                     ?>
                                                    
                                                      <td class="text-right" style="{{$billing_calculation_case}}">{{round($billingproduct_value->sellingprice_afteroverall_discount,2)}}</td>                                                      
                                                    <td class="text-right" style="{{$billing_calculation_case}}">{{$billingproduct_value->cgst_percent}}</td>
                                                    <td class="text-right totcgstamt" style="{{$billing_calculation_case}}">{{round($billingproduct_value->cgst_amount,2)}}</td>
                                                    <td class="text-right" style="{{$billing_calculation_case}}">{{$billingproduct_value->sgst_percent}}</td>
                                                    <td class="text-right totsgstamt" style="{{$billing_calculation_case}}">{{round($billingproduct_value->sgst_amount,2)}}</td>
                                                    <td class="text-right tottotamt" style="{{$billing_calculation_case}}">{{round($billingproduct_value->total_amount,2)}}</td>
                                                    
                                                </tr>
                                            <?php
                                               }
                                                
                                                
                                                $rest   =   1 - $productcount;
                                                for($x=1; $x<=$rest; $x++)
                                                {
                                                    ?>
                                                <tr>
                                                    <td class="text-dark">&nbsp;</td>
                                                    <td class="text-dark"></td>
                                                    <td class="text-dark"></td>
                                                    <?php
                                                    for($dk=1;$dk<=$dynamic_cnt;$dk++)
                                                    {
                                                    ?>
                                                    <td class="text-dark"></td>
                                                    <?php } ?>
                                                    
                                                    <td class="text-right text-dark"></td>
                                                    <td class="text-right text-dark" style="{{$billing_calculation_case}}"></td>
                                                   <td class="text-right text-dark" style="{{$billing_calculation_case}}"></td>
                                                   <?php
                                                   if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                                     {
                                                        echo '<td class="text-right text-dark" style="{{$billing_calculation_case}}"></td>';
                                                     }
                                                   ?>
                                                  
                                                  <td class="text-right text-dark tottariff" style="{{$billing_calculation_case}}"></td>
                                                        
                                                    <td class="text-right text-dark" style="{{$billing_calculation_case}}"></td>
                                                    <td class="text-right text-dark totcgstamt" style="{{$billing_calculation_case}}"></td>
                                                    <td class="text-right text-dark" style="{{$billing_calculation_case}}"></td>
                                                    <td class="text-right text-dark totsgstamt" style="{{$billing_calculation_case}}"></td>
                                                    
                                                    
                                                </tr>


                                                <?php
                                                 }


                                                    ?>              
                                               
                                            <!-- <tfoot style="border-bottom:1px solid #999 !important;border-top:1px solid #999 !important;"> -->
                                                <tr>
                                                   <?php
                                                    if($bill_calculation == 1)
                                                    {
                                                         $colspan = 4 + $dynamic_cnt;
                                                        ?><th colspan="<?php echo $colspan;?>" class="text-right font-14 font-weight-600">Total</th><?php
                                                    }
                                                    else
                                                    {
                                                       $colspan = 2 + $dynamic_cnt;
                                                        ?><th colspan="<?php echo $colspan;?>" class="text-right font-14 font-weight-600">Total</th><?php
                                                    }
                                                   ?>

                                                    
                                                    <th class="text-right font-weight-600">{{round($billingdata->total_qty)}}</th>
                                                    <?php
                                                    if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                                    {
                                                        ?>
                                                            <th class="text-right font-weight-600" style="{{$billing_calculation_case}}">{{round($ttaldiscount,2)}}</th>
                                                        <?php
                                                    }
                                                    ?>
                                                    
                                                     <th class="text-right font-weight-600" style="{{$billing_calculation_case}}">{{round($tottaxable,2)}}</th>
                                                    <th class="text-right text-dark font-14 font-weight-600" style="{{$billing_calculation_case}}" colspan="2">{{round($totalcgst,2)}}</th>
                                                    <th class="text-right text-dark font-14 font-weight-600" style="{{$billing_calculation_case}}" colspan="2">{{round($totalsgst,2)}}</th>
                                                    <th class="text-right text-dark font-18 font-weight-600" style="{{$billing_calculation_case}}"><?php
                                              if($currency_title=='INR')
                                              {
                                                ?>&#x20b9<?php
                                              }
                                              else
                                              {
                                                echo $currency_title;
                                              }
                                              ?> {{round($billingdata->total_bill_amount,$nav_type[0]['decimal_points'])}}</th>
                                                    
                                                  
                                                </tr>
                                                <tr>
                                                  <?php
                                                  $total_colspan = $colspan + 6;
                                                  ?>
                                                  <td colspan="<?php echo $total_colspan;?>">In Words: <span style="font-weight:600"><?php echo currencyword::convertCurrencyintoWord($billingdata->total_bill_amount,$nav_type[0]['decimal_points'])?> </span></td>
                                                </tr>
                                            <!-- </tfoot> -->
                                             </tbody>
                                        </table>
                             <?php
                             }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************End code of custome state is same as Company state then show CGST and SGST*****************************************************************************************************/////////////////////////////////////////////////////////////                             
                             else
                             {
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************If customer state is not same as company state then to show IGST *****************************************************************************************************/////////////////////////////////////////////////////////////                              
                                ?>
                                  <table width="100%" cellpadding="6" frame="box" border="1" style="border-collapse:collapse;float:left;margin:15px 0 0 0;">
                                    <thead>
                                        <tr style="background:#999;border-bottom:1px #999 solid;border-top:1px #999 solid;font-size:14px;">
                                           
                                            <?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************Show discount column if discount is given*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////                                              
                                             if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                             {
                                                    echo '<th class="text-dark font-12 font-weight-600" style="width:5% !important;">Sr No.</th>
                                                    <th class="text-dark font-12 font-weight-600" style="width:29% !important;">Item Description</th>
                                                    <th class="text-dark font-12 font-weight-600" style="width:10% !important;">Barcode</th>';

                                                    echo $dynamic_header;

                                                    echo '<th class="text-right text-dark font-12 font-weight-600" style="width:8% !important;">Rate</th>
                                                    <th class="text-right text-dark font-12 font-weight-600" style="width:6% !important;">Qty</th>
                                                    <th class="text-right text-dark font-12 font-weight-600" style="width:7% !important;">Disc. Amt.</th>
                                                    <th class="text-right text-dark font-12 font-weight-600" style="width:10% !important;">Taxable<br>Amount</th>
                                                     <th class="text-right text-dark font-12 font-weight-600" style="width:7% !important;" colspan="2">IGST<br>% & Amt.</th>
                                                    <th class="text-right text-dark font-12 font-weight-600" style="width:12% !important;">Total Amount</th>';
                                                }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************Hide discount column if discount is not given*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////  
                                                else
                                                {
                                                    echo '<th class="text-dark font-12 font-weight-600" style="width:5% !important;">Sr No.</th>
                                                    <th class="text-dark font-12 font-weight-600" style="width:36% !important;">Item Description</th>
                                                    <th class="text-dark font-12 font-weight-600" style="width:10% !important;">Barcode</th>';

                                                    echo $dynamic_header;

                                                    echo '<th class="text-right text-dark font-12 font-weight-600" style="width:8% !important;">Rate</th>
                                                    <th class="text-right text-dark font-12 font-weight-600" style="width:6% !important;">Qty</th>
                                                    <th class="text-right text-dark font-12 font-weight-600" style="width:10% !important;">Taxable<br>Amount</th>
                                                    <th class="text-right text-dark font-12 font-weight-600" style="width:7% !important;" colspan="2">IGST<br>% & Amt.</th>
                                                    <th class="text-right text-dark font-12 font-weight-600" style="width:12% !important;">Total Amount</th>';
                                                }
                                                ?>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                $ttaldiscount = 0;
                                                $totalcgst = 0;
                                                $totalsgst = 0;
                                                $totaligst = 0;
                                                $tottaxable = 0;
                                                foreach($billingproductdata AS $billingproduct_key=>$billingproduct_value)
                                                {
                                               
                                                    if ($billingproduct_key % 2 == 0) {
                                                            $tblclass = '#fff;';
                                                        } else {
                                                            $tblclass = '#f3f3f3;';
                                                        } 


                                                $totaldiscount   =   $billingproduct_value->discount_amount + $billingproduct_value->overalldiscount_amount;

                                                $sno  =   $billingproduct_key + 1;
                                                $ttaldiscount  +=  $totaldiscount;
                                                
                                                $totaligst  +=  $billingproduct_value->igst_amount;
                                                $tottaxable +=  $billingproduct_value->sellingprice_afteroverall_discount;
                                                if($billingproduct_value['product']['supplier_barcode']!='' && $billingproduct_value['product']['supplier_barcode']!=null)
                                                {
                                                  $barcode     =     $billingproduct_value['product']['supplier_barcode'];
                                                }
                                                else
                                                {
                                                  $barcode     =     $billingproduct_value['product']['product_system_barcode'];
                                                }

                                                $feature_show_val = "";

                                                if($show_dynamic_feature != '')
                                                {
                                                    $feature = explode(',',$show_dynamic_feature);

                                                    foreach($feature AS $fea_key=>$fea_val)
                                                    {
                                                        $feature_show_val .= '<td class="text-center">'.$billingproduct_value['product'][$fea_val].'</td>';
                                                    }
                                                }
                                                ?>
                                                <tr style="border-bottom:1px solid #C0C0C0 !important;">
                                                    <td>{{$sno}}</td>
                                                    <td><?php echo html_entity_decode($billingproduct_value->product->product_name)?></td>
                                                    <td>{{$barcode}}</td>
                                                    <?php echo $feature_show_val ?>
                                                    
                                                    <td class="text-right">{{round($billingproduct_value->sellingprice_before_discount,2)}}</td>
                                                    
                                                    <td class="text-right">{{round($billingproduct_value->qty)}}</td>
                                                    <?php
                                                     if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                                     {
                                                        ?>
                                                        <td class="text-right">{{round($totaldiscount,2)}}</td>
                                                       
                                                        <?php
                                                     }
                                                    ?>
                                                    <td class="text-right totcgstamt">{{round($billingproduct_value->sellingprice_afteroverall_discount,2)}}</td>
                                                    <td class="text-right">{{$billingproduct_value->igst_percent}}</td>
                                                    <td class="text-right totsgstamt">{{round($billingproduct_value->igst_amount,2)}}</td>
                                                    <td class="text-right tottotamt">{{round($billingproduct_value->total_amount,2)}}</td>
                                                    
                                                </tr>

                                               
                                                <?php
                                            }
                                                
                                                $rest   =  1 - $productcount;
                                                for($x=1; $x<=$rest; $x++)
                                                {
                                                    ?>
                                                <tr>
                                                    <td class="text-dark">&nbsp;</td>
                                                    <td class="text-dark"></td>
                                                    <td class="text-dark"></td>
                                                    <?php
                                                    for($dk=1;$dk<=$dynamic_cnt;$dk++)
                                                    {
                                                    ?>
                                                    <td class="text-dark"></td>
                                                    <?php } ?>
                                                    
                                                    <td class="text-right text-dark"></td>
                                                    <?php
                                                     if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                                     {
                                                            echo '<td class="text-right text-dark"></td>';
                                                     }
                                                     ?>
                                                    
                                                    <td class="text-right text-dark"></td>
                                                    <td class="text-right text-dark totcgstamt"></td>
                                                    <td class="text-right text-dark"></td>
                                                    <td class="text-right text-dark totsgstamt"></td>
                                                    <td class="text-right text-dark tottotamt"></td>
                                                    
                                                </tr>


                                                <?php
                                                 }



                                                    ?>              
                                                
                                            <!-- <tfoot style="border-bottom:1px solid #999 !important;border-top:1px solid #999 !important;"> -->
                                                <tr>
                                                        <?php $colspan = 4 + $dynamic_cnt; ?>
                                                     <th colspan="<?php echo $colspan;?>" class="text-right font-14 font-weight-600">Total</th>
                                                      <th class="text-right text-dark font-14 font-weight-600">{{round($billingdata->total_qty)}}</th>
                                                      <?php
                                                     if($billingdata['overalldiscount']!=0 && $billingdata['overalldiscount']!='' && $billingdata['overalldiscount']!=null)
                                                     {
                                                        ?>
                                                         <th class="text-right text-dark font-14 font-weight-600">{{round($ttaldiscount,2)}}</th>
                                                        <?php
                                                     }

                                                    ?>
                                                    
                                                     <th class="text-right text-dark font-14 font-weight-600">{{round($tottaxable,2)}}</th>
                                                    <th></th>
                                                    <th class="text-right text-dark font-14 font-weight-600">{{round($totaligst,2)}}</th>
                                                    <th class="text-right text-dark font-18 font-weight-600"><?php
                                              if($currency_title=='INR')
                                              {
                                                ?>&#x20b9<?php
                                              }
                                              else
                                              {
                                                echo $currency_title;
                                              }
                                              ?> {{round($billingdata->total_bill_amount,$nav_type[0]['decimal_points'])}}</th>
                                                    
                                                  
                                                </tr>
                                                <tr>
                                                  <?php
                                                  $total_colspan = $colspan + 6;
                                                  ?>
                                                  <td colspan="<?php echo $total_colspan;?>">In Words: <span style="font-weight:600"><?php echo currencyword::convertCurrencyintoWord($billingdata->total_bill_amount,$nav_type[0]['decimal_points'])?> </span></td>
                                                </tr>
                                            <!-- </tfoot> -->
                                            </tbody>
                                      </table>
                                       <?php 
                                     }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************End code for customer state is not same as company state then to show IGST *****************************************************************************************************/////////////////////////////////////////////////////////////
                   }   
            ?>     

                            <div style="width:100%;border:0px solid;margin:20px 0 0 0;float:left;">
                               
                              <div style="width:70% !important;border:0px solid !important;float:left;">
                            <?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************GST Breakup details code*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////                              
                            if($tax_type!=1 && $bill_calculation==1)
                            {
                            ?>
                                <span class="font-weight-600" style="font-size:16px;"><center><--------GST Breakup Details--------></center></span>

                                      <table width="100%" cellpadding="6" frame="box" border="1" style="border-collapse:collapse;">
                                            <thead>
                                                <tr style="border-bottom:1px #999 solid;border-top:1px #999 solid;">
                                                   
                                                    <th class="text-dark font-12 font-weight-600 bottomdarkline" style="width:8% !important;text-align:left;">GST%</th>
                                                    <th class="text-right text-dark font-12 font-weight-600 bottomdarkline" style="width:16% !important;">Taxable Amt.</th>
                                                    <?php
                                                    if(($billingdata->state_id)==($billingdata->company->state_id))
                                                    {
                                                    ?>
                                                    <th class="text-right text-dark font-12 font-weight-600 bottomdarkline" style="width:16% !important;" colspan="2">CGST<br><small>% & Amt.</small></th>
                                                    <th class="text-right text-dark font-12 font-weight-600 bottomdarkline" style="width:16% !important;" colspan="2" colspan="2">SGST<br><small>% & Amt.</small></th>
                                                    <?php
                                                    }
                                                    else
                                                    {

                                                    ?>
                                                    <th class="text-right text-dark font-12 font-weight-600 bottomdarkline" style="width:16% !important;" colspan="2">IGST<br><small>% & Amt.</small></th>
                                                    <?php
                                                    }
                                                    ?>
                                                    <th class="text-right text-dark font-12 font-weight-600 bottomdarkline" style="width:26% !important;">Total Amount</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                $ttaldiscount = 0;
                                                foreach($gstdata AS $gst_key=>$gst_value)
                                                {
                                               
                                                    if ($billingproduct_key % 2 == 0) {
                                                            $tblclass = '#fff;';
                                                        } else {
                                                            $tblclass = '#f3f3f3;';
                                                        } 

                                                ?>

                                                <tr style="border-bottom:1px solid #C0C0C0 !important;font-size:14px !important;">
                                                    <td>{{round($gst_value->igst_percent,2)}}%</td>
                                                    <td class="text-right">{{round($gst_value->tottaxablevalue,2)}}</td>
                                                    <?php
                                                    if($billingdata['company']['state_id']==$billingdata['state_id'])
                                                    {
                                                        ?>
                                                        <td class="text-right">{{round($gst_value->cgst_percent,2)}}</td>
                                                        <td class="text-right">{{round($gst_value->totcgstamount,2)}}</td>
                                                        <td class="text-right">{{round($gst_value->sgst_percent,2)}}</td>
                                                        <td class="text-right">{{round($gst_value->totsgstamount,2)}}</td>
                                                        
                                                        <?php


                                                    }
                                                    else
                                                    {
                                                        ?>
                                                           
                                                            <td class="text-right">{{round($gst_value->igst_percent,2)}}</td>
                                                            <td class="text-right">{{round($gst_value->totigstamount,2)}}</td>
                                                           
                                                    <?php
                                                    }
                                                    ?>
                                                     <td class="text-right tottotamt">{{round($gst_value->totgrand,2)}}</td>  
                                                    
                                                </tr>

                                               
                                                <?php
                                           }
                                                
                                          
                                            ?>
                                              
  
                                                
                                            <!-- tfoot style="border-bottom:1px solid #999 !important;border-top:1px solid #999 !important;"> -->
                                                <tr>
                                                    
                                                     <th class="text-right font-14 font-weight-600 upperdarkline">Total</th>
                                                     <th class="text-right text-dark font-14 font-weight-600 upperdarkline">{{round($tottaxable,2)}}</th>
                                                      <?php
                                                   if($billingdata['company']['state_id']==$billingdata['state_id'])
                                                    {
                                                        ?>
                                                          <th class="text-right text-dark font-14 font-weight-600 upperdarkline" colspan="2">{{round($totalcgst,2)}}</th>
                                                          <th class="text-right text-dark font-14 font-weight-600 upperdarkline" colspan="2">{{round($totalsgst,2)}}</th>
                                                        
                                                        <?php
                                                    }
                                                    else
                                                    {
                                                        ?>
                                                      
                                                             <th class="text-right text-dark font-14 font-weight-600 upperdarkline" colspan="2">{{round($totaligst,2)}}</th>
                                                        <?php
                                                    }
                                                    ?>
                                                    
                                     
                                                    
                                                    <th class="text-right text-dark font-18 font-weight-600 upperdarkline"><?php
                                                  if($currency_title=='INR')
                                                  {
                                                    ?>&#x20b9<?php
                                                  }
                                                  else
                                                  {
                                                    echo $currency_title;
                                                  }
                                                  ?> {{round($billingdata->total_bill_amount,$nav_type[0]['decimal_points'])}}</th>
                                                    
                                                  
                                                </tr>
                                           <!--  </tfoot> -->
                                           </tbody>
                           
                                      </table> 
                                 <?php
                                  }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************End code for GST Breakup details code*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////                                   
                                  ?>
                                       
                                    </div>
                                    <?php 
                                    if($bill_calculation==1)
                                    {
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************Payment methods Code*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////////////////                                       
                                      ?>
                                    
                                    <div style="width:29% !important;border:0px solid !important;font-size:16px;float:right;">
                                        <table style="float:right;font-size:16px;">
                                            <tr>
                                            <td colspan="3" class="text-right font-weight-600" style="font-size:14px;">PAYMENT METHODS</td>                                      
                                            </tr>
                                            @foreach($billingdata->sales_bill_payment_detail AS $salespayment_key=>$salespayment_value)
                                            <tr>
                                            <td class="d-block">{{$salespayment_value['payment_method'][0]['payment_method_name']}} </td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600">
                                              <?php
                                              if($currency_title=='INR')
                                              {
                                                ?>&#x20b9<?php
                                              }
                                              else
                                              {
                                                echo $currency_title;
                                              }
                                              ?>
                                             {{$salespayment_value->total_bill_amount}}</td>
                                            </tr>                                          
                                            @endforeach
                                           
                                  
                                        </table> 
                                        <br clear="all">
                                        <?php 
                                          if($customer_id!='' && ($previouscreditamount!=0 || $currentcreditamount!=0))
                                          {
                                              $totalcreditbalance  = $previouscreditamount  + $currentcreditamount;

                                        ?>
                                        <table style="float:right;font-size:16px;margin:10px 0 0 0;">
                                            <tr>
                                            <td colspan="3" class="text-right font-weight-600" style="font-size:16px;">Customer Balance history</td>                                      
                                            </tr>
                                           
                                            <tr>
                                            <td class="d-block">Current Balance Amt.</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600">
                                              <?php
                                              if($currency_title=='INR')
                                              {
                                                ?>&#x20b9<?php
                                              }
                                              else
                                              {
                                                echo $currency_title;
                                              }
                                              ?>
                                              {{$currentcreditamount}}</td>
                                            </tr>                                          
                                            <tr>
                                            <td class="d-block">Previous Balance Amt.</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600">
                                              <?php
                                              if($currency_title=='INR')
                                              {
                                                ?>&#x20b9<?php
                                              }
                                              else
                                              {
                                                echo $currency_title;
                                              }
                                              ?>
                                              {{$previouscreditamount}}</td>
                                            </tr>                                          
                                           <tr>
                                            <td class="d-block">Total Balance Amt.</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600">
                                              <?php
                                              if($currency_title=='INR')
                                              {
                                                ?>&#x20b9<?php
                                              }
                                              else
                                              {
                                                echo $currency_title;
                                              }
                                              ?>
                                              {{$totalcreditbalance}}</td>
                                            </tr>        
                                           
                                  
                                        </table> 
                                        <?php
                                        }
                                        ?>
                                        
                                    </div>
                                    <?php
                                     }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////***********************************************End code for Payment Methods code*****************************************************************************************************///////////////////////////////////////////////////////////////////////////////////////////                                      
                                  ?>
                                    
                               
                               
                               <div style="width:60% !important;border:0px solid !important;float:left;">
                                <?php
                                if($billingdata['company']['account_holder_name']!=NULL ||$billingdata['company']['account_number']!=NULL ||$billingdata['company']['ifsc_code']!=NULL||$billingdata['company']['branch']!=NULL)
                                {
                                ?>
                               
                                        <table style="float:left;font-size:16px;">
                                            <tr>
                                            <td colspan="3" class="d-block font-weight-600" style="font-size:14px;">BANK DETAILS</td>
                                            
                                            </tr>
                                            <?php 
                                             if(isset($billingdata) && isset($billingdata['company'])
                                                 && $billingdata['company']['account_holder_name']!=NULL)   
                                             {
                                                ?>
                                            <tr>
                                            <td class="d-block font-weight-600">AC HOLDER NAME</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600">{{$billingdata->company-> account_holder_name}}</td>
                                            </tr>
                                            <?php
                                            }

                                             if(isset($billingdata) && isset($billingdata['company'])
                                                 && $billingdata['company']['account_number']!=NULL)   
                                             {
                                                ?>
                                            <tr>
                                            <td class="d-block font-weight-600">ACCOUNT NO.</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600">{{$billingdata->company->account_number}}</td>
                                            </tr>
                                             <?php
                                             }
                                             if(isset($billingdata) && isset($billingdata['company'])
                                                 && $billingdata['company']['ifsc_code']!=NULL)   
                                             {
                                                ?>
                                            <tr>
                                            <td class="d-block font-weight-600">IFSC CODE</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600">{{$billingdata->company->ifsc_code}}</td>
                                            </tr>
                                             <?php
                                             }
                                             if(isset($billingdata) && isset($billingdata['company'])
                                                 && $billingdata['company']['branch']!=NULL)   
                                             {
                                                ?>
                                            <tr>
                                            <td class="d-block font-weight-600">BRANCH</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600">{{$billingdata->company->branch}}</td>
                                            </tr>
                                             <?php
                                             } 
                                             ?>
                                        </table> 
                                        <?php
                                      }
                                        ?>  
                                       
                              </div>
                                    
                                <div style="width:39% !important;border:0px solid !important;font-size:16px;float:left;">
                                    
                                    
                                </div>
<!--*****************************************************Start Footer section**************************************************************-->
                    <div style="width:100% !important;border:0px solid !important;margin:0px 0 0 0;float:left;">
                        <div style="width:70% !important;border:0px solid !important;margin:0px 0 0 0;float:left;">
                          <?php
                              if(isset($billingdata['print_note']) && $billingdata['print_note']!=NULL)
                              {
                              ?>
                                    <table style="float:left;font-size:13px;margin:0 0 5px 0 !important;">
                                        <tr>
                                        <td colspan="3" class="font-weight-600" style="font-size:14px;">Customer Note</td>
                                       </tr>
                                        <tr>
                                        <td colspan="3" class="font-weight-600"><?php echo $billingdata['print_note']; ?></td>
                                       </tr>                                          
                              
                                    </table> 
                                    <?php
                                  }
                                    
                                    if(isset($billingdata) && isset($billingdata['company'])&& $billingdata['company']['terms_and_condition']!=NULL)
                                    {
                                    ?>
                                    <table style="float:left;font-size:12px;">
                                        <tr>
                                        <td colspan="3" class="font-weight-600" style="font-size:14px;">TERMS & CONDITIONS</td>
                                       </tr>
                                        <tr>
                                        <td colspan="3" class="font-weight-600"><?php echo html_entity_decode($billingdata->company->terms_and_condition); ?></td>
                                       </tr>                                          
                              
                                    </table> 
                                  <?php
                                    }
                                  ?>
                                             
                          </div>
                                                 
                          <div style="width:29% !important;border:0px solid !important;float:right;text-align:right;">
                              <?php
                              if(isset($billingdata->company->authorized_signatory_for) && $billingdata->company->authorized_signatory_for!='')
                              {
                                  ?>
                                   <span class="d-block font-weight-600" style="font-size:16px;">For {{$billingdata->company->authorized_signatory_for}}</span><br><br>
                                   <span class="d-block font-weight-600" style="font-size:16px;">Authorized Signatory</span>
                                  <?php
                              }
                              ?>
                          
                          </div>
                       <br clear="all"><br>
                               <center><span class="d-block font-weight-600" style="font-size:18px;">{{strip_tags($billingdata->company->additional_message)}}</span></center>
                               <center><span class="d-block font-weight-600" style="font-size:10px;">"Software by: RETAILCORE TECHNOLOGIES | Tel: 83697 23300 | www.retailcore.in"</span></center>
                      <!--       </div>
                        </div> -->
                    </div>

<!--*****************************************************End Footer section**************************************************************-->
            </div>

                           </div> 

            <!--*** CONTENT GOES HERE ***-->
          </div>
        </td>
      </tr>
    </tbody>

    <tfoot>
      <tr>
        <td>
          <!--place holder for the fixed-position footer-->
          <div class="page-footer-space"></div>
        </td>
      </tr>
    </tfoot>

  </table>

</body>

</html>