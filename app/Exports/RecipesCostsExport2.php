<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

/**
 * Class ItemExport
 *
 * @package App\Exports
 */
class RecipesCostsExport2 implements FromView, ShouldAutoSize
{
    use Exportable;

    public function records($records) {
        $this->records = $records;

        return $this;
    }

    /**
     * @return array
     */
    public function getExtraData()
    : array {
        return $this->extra_data;
    }

    /**
     * @param array $extra_data
     *
     * @return ItemExport
     */
    public function setExtraData(array $extra_data)
    : RecipesCostsExport2 {
        $this->extra_data = $extra_data;
        return $this;
    }

    public function view(): View {
        return view('tenant.recipescosts.exports.recipescosts2', [
            'records'=> $this->records,
            'extra_data'=> $this->extra_data,
        ]);
    }


}
