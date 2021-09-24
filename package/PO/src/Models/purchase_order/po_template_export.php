<?php

namespace Retailcore\PO\Models\purchase_order;

use Illuminate\Database\Eloquent\Model;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Retailcore\Company_Profile\Models\company_profile\company_profile;


class po_template_export implements WithHeadings
{
    use Exportable;

    public $po_with_unique_barcode = '';

    public function __construct($po_with_unique_barcode)
    {
        $this->po_with_unique_barcode = $po_with_unique_barcode;

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type','tax_type','tax_title','currency_title','po_calculation')->first();

        $this->tax_type = $inward_type_from_comp['tax_type'];
        $this->tax_title = $inward_type_from_comp['tax_title'];
        $this->tax_currency = $inward_type_from_comp['currency_title'];
        $this->po_calculation = $inward_type_from_comp['po_calculation'];
    }

    public function headings(): array
    {
        if($this->po_with_unique_barcode == 0)
        {
            $po_headers = [];
            $po_headers[] = 'Barcode';
            if($this->po_calculation == 1) {
                $po_headers[] = 'Cost Rate';
                if ($this->tax_type == 1) {
                    $po_headers[] = 'Cost ' . $this->tax_title . ' %';
                } else {
                    $po_headers[] = 'Cost GST %';
                }
            }
            $po_headers[] = 'Qty';
            $po_headers[] = 'Remarks';

            return $po_headers;
        }
        else
        {
            $po_headers = [];
            $po_headers[] = 'Barcode';
            if($this->po_calculation == 1) {
                $po_headers[] = 'Cost Rate';
                if ($this->tax_type == 1) {
                    $po_headers[] = 'Cost ' . $this->tax_title . ' %';
                } else {
                    $po_headers[] = 'Cost GST %';
                }
            }
            $po_headers[] = 'Qty';
            $po_headers[] = 'Mfg date(DD)';
            $po_headers[] = 'Mfg month(MM)';
            $po_headers[] = 'Mfg year(YYYY)';
            $po_headers[] = 'Expiry date(DD)';
            $po_headers[] = 'Expiry month(MM)';
            $po_headers[] = 'Expiry year(YYYY)';
            $po_headers[] = 'Remarks';

            return $po_headers;

        }
    }
}
