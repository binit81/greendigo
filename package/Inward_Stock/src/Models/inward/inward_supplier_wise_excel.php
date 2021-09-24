<?php

namespace Retailcore\Inward_Stock\Models\Inward;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
class inward_supplier_wise_excel implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public $from_date = '';
    public $to_date = '';
    public $barcode = '';
    public $product_name = '';
    public $invoice_no = '';

    public function __construct($from_date,$to_date,$supplier_name,$invoice_no)
    {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->supplier_name = $supplier_name;
        $this->invoice_no = $invoice_no;

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)
            ->select('inward_type','tax_type','tax_title','currency_title','inward_calculation')->first();

        $this->tax_type = $inward_type_from_comp['tax_type'];
        $this->tax_title = $inward_type_from_comp['tax_title'];
        $this->currency_title = $inward_type_from_comp['currency_title'];
        $this->inward_calculation = $inward_type_from_comp['inward_calculation'];
    }

    public function headings(): array
    {
        $tax_name = 'GSTIN';
        if($this->tax_type == 1)
        {
            $tax_name = $this->tax_title;
        }
       $supplier_wise_header = [];
       $supplier_wise_header[] = 'Invoice No.';
       $supplier_wise_header[] = 'Po No.';
       $supplier_wise_header[] = 'Inward Date';
       $supplier_wise_header[] = 'Invoice Date';
       $supplier_wise_header[] = 'Supplier Name';
       $supplier_wise_header[] = 'Supplier '.$tax_name;
if ($this->inward_calculation != 3) {
    $supplier_wise_header[] = 'Total Cost Rate';

    if ($this->tax_type == 1) {
        $supplier_wise_header[] = 'Total Cost ' . $this->tax_title . ' ' . $this->currency_title;
    } else {
        $supplier_wise_header[] = 'Total Cost CGST ₹';
        $supplier_wise_header[] = 'Total Cost SGST ₹';
        $supplier_wise_header[] = 'Total Cost IGST ₹';
    }
}

       $supplier_wise_header[] = 'Total Qty';
        if ($this->inward_calculation != 3) {
            $supplier_wise_header[] = 'Total Cost ₹';
        }

       return $supplier_wise_header;

    }

    public function map($supplier_wise_report): array
    {
        $count = '';

        $rows    = [];

        if($supplier_wise_report['inward_product_detail'] != ''){
            $cost_rate = 0;
            foreach ($supplier_wise_report['inward_product_detail'] AS $key=>$value)
            {
                // print_r($value['cost_rate']);
                $cost_rate += $value['cost_rate'] * ($value['product_qty']+ $value['free_qty']);
            }
        }
        $supplier_name =  '';
        $supplier_gstin =  '';

        if($supplier_wise_report['stock_inward_type'] == 2)
        {
            $supplier_name =  $supplier_wise_report->warehouse->company_name;
            $supplier_gstin = $supplier_wise_report->warehouse->gstin;
        }
        if($supplier_wise_report['stock_inward_type'] == 0)
        {
            $supplier_name =  $supplier_wise_report->supplier_gstdetail->supplier_company_info->supplier_first_name;
            $supplier_gstin = $supplier_wise_report->supplier_gstdetail->supplier_gstin;
        }

        $rows[] = $supplier_wise_report->invoice_no;
        $rows[] = $supplier_wise_report->po_no;
        $rows[] = $supplier_wise_report->inward_date;
        $rows[] = $supplier_wise_report->invoice_date;
        $rows[] = $supplier_name;
        $rows[] = $supplier_gstin;
        if ($this->inward_calculation != 3) {
            $rows[] = $cost_rate;

            if ($this->tax_type == 1) {
                $rows[] = $supplier_wise_report->total_cost_igst_amount != '' ? $supplier_wise_report->total_cost_igst_amount : '0';
            } else {
                $rows[] = $supplier_wise_report->total_cost_cgst_amount != '' ? $supplier_wise_report->total_cost_cgst_amount : '0';
                $rows[] = $supplier_wise_report->total_cost_sgst_amount != '' ? $supplier_wise_report->total_cost_sgst_amount : '0';
                $rows[] = $supplier_wise_report->total_cost_igst_amount != '' ? $supplier_wise_report->total_cost_igst_amount : '0';
            }
        }
        $rows[] = $supplier_wise_report->total_qty != '' ? $supplier_wise_report->total_qty : '0';
        if ($this->inward_calculation != 3) {
            $rows[] = $supplier_wise_report->total_grand_amount != '' ? $supplier_wise_report->total_grand_amount : '0';
        }

        return $rows;
    }

    public function query()
    {
        $from_date   =   $this->from_date;
        $to_date   =   $this->to_date;
        $supplier_name =   $this->supplier_name;
        $invoice_no =   $this->invoice_no;

        $supplier_wise_report = inward_stock::query()->where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->with('supplier_gstdetail')
            ->with('inward_product_detail');

        if($from_date !='' && $to_date !='')
        {
            $start_date = date("Y-m-d",strtotime($from_date));
            $end_date  =  date("Y-m-d",strtotime($to_date));

            $supplier_wise_report->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$start_date' and '$end_date'");
        }

        if($supplier_name !='')
        {
            $supplier_wise_report->whereHas('supplier_gstdetail', function ($q) use ($supplier_name)
            {
               $q->where('supplier_gst_id', '=',$supplier_name);
            });
        }
        if ($invoice_no != '') {
            $supplier_wise_report->where('invoice_no', '=',$invoice_no);
        }




        $supplier_wise_report->orderBy('inward_stock_id', 'desc');

        return $supplier_wise_report;


    }
}
