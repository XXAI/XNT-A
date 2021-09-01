<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use \Maatwebsite\Excel\Sheet;


Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ReporteNominaHojaResumenExport implements FromCollection, WithEvents, WithTitle, WithColumnFormatting
{
    use Exportable;

    protected $titulo_hoja = '';

    public function __construct($datos_generales){
        $this->titulo_hoja = 'RESUMEN GENERAL';

        $datos_archivo = $datos_generales['datos_archivo'];
        $total_deducciones = $datos_generales['total_deducciones'];
        $total_percepciones = $datos_generales['total_percepciones'];

        //Preparamos los Headers del reporte
        $table_data = [
            ['RESUMEN DE  NOMINA CORRESPONDIENTE A LA QUINCENA '.$datos_archivo['tipo_anio'].' '.$datos_archivo['quincena'].'/'.$datos_archivo['anio'],'',''],
            ['','',''],
            ['','',''],
            ['PERCEPCIONES','DEDUCCIONES','LIQUIDO'],
            [$total_percepciones, $total_deducciones,($total_percepciones - $total_deducciones)],
        ];

        $this->data = $table_data;
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $letra = 'A';
                $anchos = [35,35,35];
                for ($i=0; $i < count($anchos); $i++) {
                    $event->sheet->getDelegate()->getColumnDimension($letra)->setWidth($anchos[$i]);
                    $letra++;
                }
                
                $event->sheet->getDelegate()->mergeCells('A1:C1');
                $event->sheet->getDelegate()->mergeCells('A2:C2');
                $event->sheet->getDelegate()->mergeCells('A3:C3');
                
                $event->sheet->styleCells(
                    'A1:'.$event->sheet->getHighestColumn().$event->sheet->getHighestRow(),
                    [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' =>  \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                        'font' => [
                            'size' => 11,
                            'name' => 'Courier New'
                        ]
                    ]
                );

                $borders = [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED
                    ],
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED
                    ]
                ];

                $event->sheet->styleCells('A4:C4', [ 'borders' => $borders, ] );
            }
        ];
    }

    public function columnFormats(): array{
        return [
            "A5:C5" => '#,##0.00'
        ];
    }

    public function title(): string{
        return $this->titulo_hoja;
    }

    public function collection(){
        return collect($this->data);
    }
}