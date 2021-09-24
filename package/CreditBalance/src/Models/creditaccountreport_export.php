<?php

namespace Retailcore\CreditBalance\Models;

use Retailcore\CreditBalance\Models\customer_creditaccount;
use Retailcore\CreditBalance\Models\customer_creditreceipt;
use Retailcore\CreditBalance\Models\customer_creditreceipt_detail;
use Retailcore\CreditBalance\Models\customer_crerecp_payment;
use App\Http\Controllers\Controller;
use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\Customer\Models\customer\customer;
use Retailcore\Customer\Models\customer\customer_address_detail;
use Retailcore\Sales\Models\payment_method;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromCollection;
//use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;
use PHPExcel_Worksheet;


// , WithBatchInserts, WithChunkReading
class creditaccountreport_export implements FromArray, WithHeadings
{

    //use Exportable;
    private $myArray;
    private $myHeadings;

    public function __construct($myArray, $myHeadings){
        $this->myArray = $myArray;
        $this->myHeadings = $myHeadings;
    }

                

    public function array(): array{

      
           
        $newArray  = array();
        
   
        foreach($this->myArray['creditaccount'] as $customerbal_value)
        {
                $count = '';
                $rows    = [];
                $hrows  =  [];

          
               
               $rows[] = $customerbal_value['customer']['customer_name'];
               $rows[] = $customerbal_value['customer']['customer_mobile'];
               $rows[] = $customerbal_value['totalinvoices'];
               $rows[] = $customerbal_value->totalcreditamount;
               $rows[] = $customerbal_value->recdamt!=''?$customerbal_value->recdamt:'0';           
               $rows[] = $customerbal_value->totalbalance!=''?$customerbal_value->totalbalance:'0';    

               $newArray[]  = $rows;

   
                
               foreach($customerbal_value['customer_creditaccount'] as $creditdetail)   
               {    
                        $recdamount   =  $creditdetail['credit_amount'] - $creditdetail['balance_amount'];
                         $crows   = [];
                         $crows[] = '';
                         $crows[] = '';
                         $crows[] = '';
                         $crows[] = '';
                         $crows[] = '';
                         $crows[] = '';
                         $crows[] = $creditdetail['sales_bill']['bill_no'];
                         $crows[] = $creditdetail['sales_bill']['bill_date'];
                         $crows[] = $creditdetail['credit_amount'];
                         $crows[] = $recdamount!=''?$recdamount:'0';;
                         $crows[] = $creditdetail['balance_amount']!=''?$creditdetail['balance_amount']:'0';
                         $newArray[]  = $crows;
               } 


              
               


 
        }

        
       // echo '<pre>';
       // print_r($newArray);
       // exit;
       
       return $newArray;

    }

    public function headings(): array{
        return $this->myHeadings;
   
        
    }
   

   public function registerEvents(): array
    {
        return [
            BeforeExport::class  => function(BeforeExport $event) {
                $event->writer->setCreator('Patrick');
            },
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                $event->sheet->styleCells(
                    'B2:G8',
                    [
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                                'color' => ['argb' => 'FFFF0000'],
                            ],
                        ]
                    ]
                );
            },
        ];
    }
 
}

