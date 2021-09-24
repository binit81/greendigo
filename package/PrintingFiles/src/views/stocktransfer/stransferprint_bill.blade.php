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
<head>
<meta charset="UTF-8" />
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
<script type="text/javascript">window.print();</script>
<style type="text/css">
.text-right
{
    text-align:right;
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
  height: 170px;
}

.page-footer, .page-footer-space {
  /*height: 150px;*/

}

.page-footer {
  /*position: fixed;*/
  /*bottom: 0;*/
  width: 100%;
  border-top: 1px solid white; /* for demo */
  /*background: yellow; /* for demo */*/
}

.page-header {
  position: fixed;
  top: 0mm;
  width: 100%;
  border-bottom: 1px solid white; /* for demo */
  /*background: yellow; /* for demo */*/
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


<body>
<!--************************************************************Header Section Code********************************************* -->
  <div class="page-header" style="text-align: center">
     <div style="width:100%;float:left;border:0px solid;">
            <span style="font-size:24px;"><center>STOCK TRANSFER CHALLAN</center></span>

                  <div style="width:39% !important;border:0px solid !important;font-size:16px;float:left;">
                      <img class="img-fluid invoice-brand-img d-block pull-left" src="{{URL::to('/')}}/public/dist/img/rcslogo.png" width="120px" alt="logo"/>
                    
                 </div>
                  <div style="width:60% !important;border:0px solid !important;font-size:16px;float:left;text-align:right;">
                      <span class="d-block font-weight-600" style="font-size:24px;"><span class="pl-10 text-dark">{{$billingdata['company']['company_name']}}</span></span><br>
                      <span class="d-block font-weight-600"><span class="text-dark">{{strip_tags($billingdata['company']['company_address'])}}</span></span><br>
                      <span class="d-block font-weight-600"><span class="text-dark">{{strip_tags ($billingdata['company']['company_area'])}} {{$billingdata['company']['company_city']}} - {{$billingdata['company']['company_pincode']}}</span></span><br>
                      <?php
                      $company_mobile_code =  explode(',',$billingdata['company']['company_mobile_dial_code']);
                      if($billingdata['company']['company_mobile']!='' || $billingdata['company']['company_mobile']!=null)
                      {
                      ?>
                       <span class="d-block font-weight-600"><span class="text-dark">({{$company_mobile_code[0]}}){{$billingdata['company']['company_mobile']}}</span></span><br>
                       <?php
                        }
                       ?>
                        <span class="font-weight-600"><span class="text-dark">{{$billingdata['company']['company_email']}}</span></span><br>

                   
                  </div>
           </div>
       
                                
            </div>
          
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

                <div style="width:100%;border:0px solid;float:left;margin:5px 0 0 0;">
                                    <div style="width:50% !important;border:0px solid !important;font-size:16px;float:left;"> 
                                        <table style="float:left;">
                                            <?php
                                             if(isset($billingdata) && isset($billingdata->store_name) && $billingdata['store_name']['company_name']!=NULL)   
                                             {
                                                ?>
                                            <tr>
                                            <td colspan="3"><span class="d-block font-weight-600" style="font-size:20px;text-align:left;">Store Details</span></td>    
                                            </tr>
                                            <tr>
                                            <td class="d-block font-weight-600">Store</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600">{{$billingdata['store_name']['company_name']}}</td>
                                            </tr>
                                            <?php
                                            }

                                             if(isset($billingdata) && isset($billingdata->store_name) && $billingdata['store_name']['personal_mobile_no']!=NULL)   
                                             {
                                            ?>
                                            <tr>
                                            <td class="d-block font-weight-600">Mobile</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600">{{$billingdata['store_name']['personal_mobile_no']}}</td>
                                            </tr>
                                            <?php
                                            }
                                            if(isset($billingdata) && isset($billingdata['store_name'])
                                                && $billingdata['store_name']['company_address'] != NULL)
                                            {
                                                
                                                $customer_address = $billingdata['store_name']['company_address']; 
                                                ?>
                                                 <tr>
                                                <td class="d-block font-weight-600">Address</td>
                                                <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                                <td class="font-weight-600">{{strip_tags($customer_address)}}</td>
                                                </tr>
                                                <?php
                                            } 
                                            ?>
                                            

                                  
                                        </table>                                      
                                        
                           
                                          
                                    </div>
                                    <div style="width:49% !important;border:0px solid !important;font-size:16px;float:right;">
                                        
                                       <table style="float:right;">
                                            <tr>
                                            <td class="d-block font-weight-600">Transfer No.</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600 text-right">{{$billingdata->stock_transfer_no}}</td>
                                            </tr>
                                            <tr>
                                            <td class="d-block font-weight-600">Transfer Date</td>
                                            <td class="font-weight-600">&nbsp;:&nbsp;</td>
                                            <td class="font-weight-600 text-right">{{$billingdata->stock_transfer_date}}</td>
                                            </tr>                                            
                                            
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
 
       ?>
             <table width="100%" cellpadding="6" frame="box" border="1" style="border-collapse:collapse;float:left;">
                    <thead>
                        <tr style="background:#999;border-bottom:1px #999 solid;border-top:1px #999 solid;">         
                            <th class="text-dark font-12 font-weight-600 leftAlign" style="width:10% !important;">Sr.No.</th>
                            <th class="text-dark font-12 font-weight-600 leftAlign" style="width:30% !important;">Item Description</th>
                            <th class="text-dark font-12 font-weight-600 leftAlign" style="width:10% !important;">Barcode</th>
                            <th class="text-right text-dark font-12 font-weight-600" style="width:15% !important;'.$billing_calculation_case.'">MRP</th>
                            <th class="text-right text-dark font-12 font-weight-600" style="width:10% !important;'.$billing_calculation_case.'">Qty</th>
                            <th class="text-right text-dark font-12 font-weight-600" style="width:15% !important;'.$billing_calculation_case.'">Total MRP</th>
                          
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
                    
                    $sno  =   $billingproduct_key + 1;

                   
                    $total_amount =  $billingproduct_value->product_mrp * $billingproduct_value->product_qty;

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
                    <tr style="border-bottom:1px solid #C0C0C0 !important;font-size:14px !important;">
                        <td>{{$sno}}</td>
                        <td><?php echo html_entity_decode($billingproduct_value->product->product_name)?></td>
                        <td>{{$barcode}}</td>
                        <td class="text-right" style="{{$billing_calculation_case}}">{{round($billingproduct_value->product_mrp,2)}}</td>
                        <td class="text-right">{{round($billingproduct_value->product_qty)}}</td>
                        <td class="text-right tottotamt" style="{{$billing_calculation_case}}">{{round($total_amount,2)}}</td>
                        
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
                        <td class="text-dark" style="{{$billing_calculation_case}}"></td>
                        <td class="text-right text-dark"></td>
                        <td class="text-right text-dark" style="{{$billing_calculation_case}}"></td>
                       
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
                           $colspan = 4 + $dynamic_cnt;
                            ?><th colspan="<?php echo $colspan;?>" class="text-right font-14 font-weight-600">Total</th><?php
                        }
                       ?>

                        
                        <th class="text-right font-weight-600">{{round($billingdata->total_qty)}}</th>
                        <th class="text-right text-dark font-18 font-weight-600" style="{{$billing_calculation_case}}"><?php
                  if($currency_title=='INR')
                  {
                    ?>&#x20b9<?php
                  }
                  else
                  {
                    echo $currency_title;
                  }
                  ?> {{round($billingdata->total_mrp,$nav_type[0]['decimal_points'])}}</th>
                        
                      
                    </tr>
                <!-- </tfoot> -->
                 </tbody>
            </table>


                            <div style="width:100%;border:0px solid;margin:20px 0 0 0;float:left;">
                               
                                  <div style="width:70% !important;border:0px solid !important;float:left;"></div>
                                  <div style="width:29% !important;border:0px solid !important;font-size:16px;float:right;"></div>
                                 
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
                       <br clear="all">
                               <center><span class="d-block font-weight-600" style="font-size:18px;">{{strip_tags($billingdata['company']['additional_message'])}}</span></center>
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