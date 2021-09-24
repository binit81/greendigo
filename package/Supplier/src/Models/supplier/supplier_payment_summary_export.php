<?php

namespace Retailcore\Supplier\Models\supplier;


use App\Http\Controllers\Controller;
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
class supplier_payment_summary_export implements FromArray, WithHeadings
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
           
        foreach($this->myArray as $key=>$value)
        {
                $count = '';
                $rows    = [];
                $hrows  =  [];
               $rows[] = $value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'];
               $rows[] = $value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_first_name'];
               $rows[] = $value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_last_name'];
               $rows[] = $value[0]['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_mobile_no'];
               
               $total_outstanding_amt = 0;
               $total_paid_amt = 0;
              if($value[0]['outstanding_payment'] != '' && $value[0]['outstanding_payment'] != NULL)
             {
                $search_string = ',';
                if(strpos($value[0]['outstanding_payment'],$search_string) !== false)
                    {

                        $outstanding_amount = explode(',',$value[0]['outstanding_payment']);

                        $amount = explode(',',$value[0]['amount']);


                        foreach($amount AS $key=>$valuee)
                        {
                            $total_outstanding_amt += $valuee;
                             $total_paid_amt += ($valuee - $outstanding_amount[$key]);
                        }
                    }
                else
                    {
                        $total_outstanding_amt += $value[0]['amount'];
                         $total_paid_amt += ($value[0]['amount'] - $value[0]['outstanding_payment']);

                    }

             }


              $rows[] = $total_outstanding_amt;
                    $amount_to_pay = 0;
                    $amount_to_pay = ($total_outstanding_amt - $total_paid_amt);
              $rows[] = $total_paid_amt;           
              $rows[] = $amount_to_pay;    
              $newArray[]  = $rows;

               foreach($value['payment_summary'] as $k=>$paymentdetail)   
               {    

                 $amount = $paymentdetail['amount'];
                 $paid_amt =$paymentdetail['amount'] - $paymentdetail['outstanding_payment'];
                 $remaining_amt = $amount - $paid_amt;
            
            
                         $crows   = [];
                         $crows[] = '';
                         $crows[] = '';
                         $crows[] = '';
                         $crows[] = '';
                         $crows[] = '';
                         $crows[] = '';
                         $crows[] = '';
                         $crows[] = $paymentdetail['inward_stock']['invoice_no'];
                         $crows[] = $paymentdetail['inward_stock']['invoice_date'];
                         $crows[] = $paymentdetail['inward_stock']['due_date'];
                         $crows[] = $amount!=''?$amount:'0';
                         $crows[] = $paid_amt!=''?$paid_amt:'0';
                         $crows[] = $remaining_amt!=''?$remaining_amt:'0';
                         $newArray[]  = $crows;


               }

        }
    
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

