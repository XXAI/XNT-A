<?php 

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteNominaExport implements WithMultipleSheets{
    use Exportable;

    protected $data;
    
    public function __construct($data){
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function sheets(): array {
        $sheets = [];
        foreach ($this->data['programas'] as $programa => $datos) {
            $sheets[] = new ReporteNominaHojaProgramaExport($programa,$datos,$this->data['resumen_general']['datos_archivo']);
        }
        $sheets[] = new ReporteNominaHojaResumenExport($this->data['resumen_general']);
        return $sheets;
    }
}