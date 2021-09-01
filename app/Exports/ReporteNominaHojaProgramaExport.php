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

class ReporteNominaHojaProgramaExport implements FromCollection, WithEvents, WithTitle, WithColumnFormatting
{
    use Exportable;

    protected $titulo_hoja = '';
    protected $max_rows_datos = 0;

    public function __construct($titulo, $datos_programa, $datos_archivo){
        $this->titulo_hoja = $titulo;
        //Preparamos los Headers del reporte
        $table_data = [
            ['RESUMEN DE  NOMINA CORRESPONDIENTE A LA QUINCENA '.$datos_archivo['tipo_anio'].' '.$datos_archivo['quincena'].'/'.$datos_archivo['anio'],'','','','','','','','','','','','','','','','','','','',''],
            ['UNIDAD RESPONSABLE: '.$datos_archivo['unidad_responsable'].'      PROGRAMA:  '.$titulo,'','','','','','','','','','','','','','','','','','','',''],
            ['','','','','','','','','','','','','','','','','','','','',''],
            ['ORDINARIO','','','','','','','EXTRAORDINARIO','','','','','','','TOTAL'],
            ['PERCEPCIONES','','','','DEDUCCIONES','','','PERCEPCIONES','','','','DEDUCCIONES','','','PERCEPCIONES','','','','DEDUCCIONES'],
            ['CL','PTDA','IMPORTE','PA','CL','IMPORTE','PA','CL','PTDA','IMPORTE','PA','CL','IMPORTE','PA','CL','PTDA','IMPORTE','PA','CL','IMPORTE','PA'],
        ];

        //
        $lineas_maximas = 0;

        if( count($datos_programa['TOTAL']['DEDUCCIONES']) > count($datos_programa['TOTAL']['PERCEPCIONES'])){
            $lineas_maximas = count($datos_programa['TOTAL']['DEDUCCIONES']);
        }else{
            $lineas_maximas = count($datos_programa['TOTAL']['PERCEPCIONES']);
        }
        
        for($i = 0; $i <= $lineas_maximas; $i++){
            $linea_datos = [];
            if(isset($datos_programa['ORDINARIO']['PERCEPCIONES'][$i])){
                array_push($linea_datos,$datos_programa['ORDINARIO']['PERCEPCIONES'][$i]['CL'],$datos_programa['ORDINARIO']['PERCEPCIONES'][$i]['PTDA'],$datos_programa['ORDINARIO']['PERCEPCIONES'][$i]['IMPORTE'],$datos_programa['ORDINARIO']['PERCEPCIONES'][$i]['PA']);
            }else{
                array_push($linea_datos,'','','','');
            }

            if(isset($datos_programa['ORDINARIO']['DEDUCCIONES'][$i])){
                array_push($linea_datos,$datos_programa['ORDINARIO']['DEDUCCIONES'][$i]['CL'],$datos_programa['ORDINARIO']['DEDUCCIONES'][$i]['IMPORTE'],$datos_programa['ORDINARIO']['DEDUCCIONES'][$i]['PA']);
            }else{
                array_push($linea_datos,'','','');
            }

            if(isset($datos_programa['EXTRAORDINARIO']['PERCEPCIONES'][$i])){
                array_push($linea_datos,$datos_programa['EXTRAORDINARIO']['PERCEPCIONES'][$i]['CL'],$datos_programa['EXTRAORDINARIO']['PERCEPCIONES'][$i]['PTDA'],$datos_programa['EXTRAORDINARIO']['PERCEPCIONES'][$i]['IMPORTE'],$datos_programa['EXTRAORDINARIO']['PERCEPCIONES'][$i]['PA']);
            }else{
                array_push($linea_datos,'','','','');
            }

            if(isset($datos_programa['EXTRAORDINARIO']['DEDUCCIONES'][$i])){
                array_push($linea_datos,$datos_programa['EXTRAORDINARIO']['DEDUCCIONES'][$i]['CL'],$datos_programa['EXTRAORDINARIO']['DEDUCCIONES'][$i]['IMPORTE'],$datos_programa['EXTRAORDINARIO']['DEDUCCIONES'][$i]['PA']);
            }else{
                array_push($linea_datos,'','','');
            }

            if(isset($datos_programa['TOTAL']['PERCEPCIONES'][$i])){
                array_push($linea_datos,$datos_programa['TOTAL']['PERCEPCIONES'][$i]['CL'],$datos_programa['TOTAL']['PERCEPCIONES'][$i]['PTDA'],$datos_programa['TOTAL']['PERCEPCIONES'][$i]['IMPORTE'],$datos_programa['TOTAL']['PERCEPCIONES'][$i]['PA']);
            }else{
                array_push($linea_datos,'','','','');
            }

            if(isset($datos_programa['TOTAL']['DEDUCCIONES'][$i])){
                array_push($linea_datos,$datos_programa['TOTAL']['DEDUCCIONES'][$i]['CL'],$datos_programa['TOTAL']['DEDUCCIONES'][$i]['IMPORTE'],$datos_programa['TOTAL']['DEDUCCIONES'][$i]['PA']);
            }else{
                array_push($linea_datos,'','','');
            }

            $table_data[] = $linea_datos;
        }

        $contador_filas = $lineas_maximas + 8;
        $this->max_rows_datos = $contador_filas;

        $table_data[] = ['','','','','','','','','','','','','','','','','','','','',''];
        //$table_data[] = ['','','','','','','','','','','','','','','','','','','','',''];
        $table_data[] = ['TOTALES','','=SUM(C7:C'.($contador_filas-1).')','','','=SUM(F7:F'.($contador_filas-1).')','','','','=SUM(J7:J'.($contador_filas-1).')','','','=SUM(M7:M'.($contador_filas-1).')','','','','=SUM(Q7:Q'.($contador_filas-1).')','','','=SUM(T7:T'.($contador_filas-1).')'];
        $table_data[] = ['NETOS','','','','','=C'.($contador_filas+1).'-F'.($contador_filas+1),'','','','','','','=J'.($contador_filas+1).'-M'.($contador_filas+1),'','','','','','','=Q'.($contador_filas+1).'-T'.($contador_filas+1)];

        $this->data = $table_data;
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $letra = 'A';
                $anchos = [4,9,18,4,4,18,4,4,9,18,4,4,18,4,4,9,18,4,4,18,4];
                for ($i=0; $i < count($anchos); $i++) {
                    $event->sheet->getDelegate()->getColumnDimension($letra)->setWidth($anchos[$i]);
                    $letra++;
                }
                
                $event->sheet->getDelegate()->mergeCells('A1:'.$event->sheet->getHighestColumn().'1');
                $event->sheet->getDelegate()->mergeCells('A2:'.$event->sheet->getHighestColumn().'2');
                $event->sheet->getDelegate()->mergeCells('A3:'.$event->sheet->getHighestColumn().'3');

                $event->sheet->getDelegate()->mergeCells('A4:G4');
                $event->sheet->getDelegate()->mergeCells('H4:N4');
                $event->sheet->getDelegate()->mergeCells('O4:U4');

                $event->sheet->getDelegate()->mergeCells('A5:D5');
                $event->sheet->getDelegate()->mergeCells('E5:G5');
                $event->sheet->getDelegate()->mergeCells('H5:K5');
                $event->sheet->getDelegate()->mergeCells('L5:N5');
                $event->sheet->getDelegate()->mergeCells('O5:R5');
                $event->sheet->getDelegate()->mergeCells('S5:U5');

                $event->sheet->getDelegate()->mergeCells('A'.($this->max_rows_datos+1).':B'.($this->max_rows_datos+1));
                $event->sheet->getDelegate()->mergeCells('A'.($this->max_rows_datos+2).':B'.($this->max_rows_datos+2));

                //$event->sheet->getDelegate()->getStyle('A1:'.($event->sheet->getHighestColumn()).$event->sheet->getHighestRow())->getAlignment()->setWrapText(true);
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

                $alignment = ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT];

                $event->sheet->styleCells("C7:C".($this->max_rows_datos+2),['alignment' => $alignment]);
                $event->sheet->styleCells("F7:F".($this->max_rows_datos+2),['alignment' => $alignment]);
                $event->sheet->styleCells("J7:J".($this->max_rows_datos+2),['alignment' => $alignment]);
                $event->sheet->styleCells("M7:M".($this->max_rows_datos+2),['alignment' => $alignment]);
                $event->sheet->styleCells("Q7:Q".($this->max_rows_datos+2),['alignment' => $alignment]);
                $event->sheet->styleCells("T7:T".($this->max_rows_datos+2),['alignment' => $alignment]);
                
                $event->sheet->styleCells(
                    'A'.($this->max_rows_datos+1).':B'.($this->max_rows_datos+2),
                    [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
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

                $event->sheet->styleCells('A4:U6', [ 'borders' => $borders, ] );
                $event->sheet->styleCells('A'.($this->max_rows_datos+1).':U'.($this->max_rows_datos+2), [ 'borders' => $borders, ] );
            }
        ];
    }

    public function columnFormats(): array{
        return [
            "C7:C".($this->max_rows_datos+2) => '#,##0.00',
            "F7:F".($this->max_rows_datos+2) => '#,##0.00',
            "J7:J".($this->max_rows_datos+2) => '#,##0.00',
            "M7:M".($this->max_rows_datos+2) => '#,##0.00',
            "Q7:Q".($this->max_rows_datos+2) => '#,##0.00',
            "T7:T".($this->max_rows_datos+2) => '#,##0.00'
        ];
    }

    public function title(): string{
        return $this->titulo_hoja;
    }

    public function collection(){
        return collect($this->data);
    }
}