<?php

namespace Retailcore\Inward_Stock\Models\inward;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class inward_stock_export implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public $from_date = '';
    public $to_date = '';
    public $supplier_name = '';
    public $invoice_no = '';

    public function __construct($from_date,$to_date,$invoice_no,$supplier_name) {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->supplier_name = $supplier_name;
        $this->invoice_no = $invoice_no;

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('tax_type','tax_title','currency_title','inward_calculation')->first();

        $this->tax_type = $inward_type_from_comp['tax_type'];
        $this->tax_title = $inward_type_from_comp['tax_title'];
        $this->tax_currency = $inward_type_from_comp['currency_title'];
        $this->inward_calculation = $inward_type_from_comp['inward_calculation'];
    }


    public function headings(): array
    {
        $inward_stock_header = [];
            $inward_stock_header[] = 'Invoice No.';
            $inward_stock_header[] = 'Po No.';
            $inward_stock_header[] = 'Inward Date';
            $inward_stock_header[] = 'Supplier Name	';

            if($this->inward_calculation != 3)
            {
                $inward_stock_header[] = 'Total Cost Rate';
                if ($this->tax_type == 1)
                {

                }
                else
                {
                    $inward_stock_header[] = 'Total Cost CGST ₹';
                    $inward_stock_header[] = 'Total Cost SGST ₹';
                    $inward_stock_header[] = 'Total Cost IGST ₹';
                }
            }
            $inward_stock_header[] = 'Total Qty';
            if($this->inward_calculation != 3)
            {
                $inward_stock_header[] = 'Total Grand ₹';
            }
            $inward_stock_header[] = 'Note';

            return $inward_stock_header;
    }

    public function map($inward_result): array
    {
        $count = '';

        $rows    = [];
        $rows[] = $inward_result->invoice_no;
        $rows[] = $inward_result->po_no;
        $rows[] = $inward_result->inward_date;
        $rows[] = $inward_result['supplier_gstdetail']['supplier_company_info']['supplier_first_name'] . $inward_result['supplier_gstdetail']['supplier_company_info']['supplier_last_name'];

        if($this->inward_calculation != 3) {
            $rows[] = number_format($inward_result->cost_rate != '' ? $inward_result->cost_rate : '0');
            $rows[] = number_format($inward_result->total_cost_cgst_amount != '' ? $inward_result->total_cost_cgst_amount : '0');
            $rows[] = number_format($inward_result->total_cost_sgst_amount != '' ? $inward_result->total_cost_sgst_amount : '0');
            $rows[] = number_format($inward_result->total_cost_igst_amount != '' ? $inward_result->total_cost_igst_amount : '0');
        }

        $rows[] = $inward_result->total_qty != '' ? $inward_result->total_qty : '0';

        if($this->inward_calculation != 3) {
            $rows[] = number_format($inward_result->total_grand_amount != '' ? $inward_result->total_grand_amount : '0');
        }

        $rows[] = $inward_result->note != '' ? $inward_result->note : '';
        return $rows;
    }



    public function query()
    {

        $from_date   =   $this->from_date;
        $to_date   =   $this->to_date;
        $supplier_name =   $this->supplier_name;
        $invoice_no =   $this->invoice_no;

        $inward_result = inward_stock::query()->where('deleted_at','=',NULL)->where('company_id', Auth::user()->company_id);

        if($from_date !='' && $to_date !='')
        {
            $inward_start_date = date("Y-m-d",strtotime($from_date));
            $inward_end_date  =  date("Y-m-d",strtotime($to_date));
            //$inward_result->where('inward_date','>=',$from_date)->where('inward_date','<=',$to_date);

            $inward_result->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$inward_start_date' and '$inward_end_date'");

        }
        if($invoice_no !='')
        {
            $inward_result->where('invoice_no','=',$invoice_no);
        }
        if($supplier_name !='')
        {
            $inward_result->where('supplier_gst_id','=',$supplier_name);
        }

        return $inward_result;


    }

}
