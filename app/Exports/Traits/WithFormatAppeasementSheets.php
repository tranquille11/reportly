<?php

namespace App\Exports\Traits;

use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterSheet;

trait WithFormatAppeasementSheets
{
    use RegistersEventListeners;

    public static function afterSheet(AfterSheet $event)
    {
        $delegate = $event->getDelegate();
        $max = $delegate->getHighestRowAndColumn();
        $totalRow = $max['row'] + 2;

        $delegate->setAutoFilter("A1:{$max['column']}{$max['row']}");

        $delegate->getStyle('C2:C'.$totalRow)
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING_USD);

        $delegate->getStyle("D2:D{$max['row']}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

        $delegate->getStyle("E2:E{$max['row']}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

        $delegate->getStyle("A{$totalRow}:C{$totalRow}")->getFont()->setBold(true);

        $delegate->setCellValue('A'.$totalRow, 'Total');
        $delegate->setCellValue('B'.$totalRow, '=SUM(B2:B'.$max['row'].')');
        $delegate->setCellValue('C'.$totalRow, '=SUM(C2:C'.$max['row'].')');
        $delegate->getStyle('A'.$max['row'] + 2 .':C'.$max['row'] + 2)->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ],
            ],
        ]);

        for ($i = 2; $i <= $max['row']; $i++) {
            $delegate->setCellValue("D{$i}", "=B{$i}/B$".$totalRow);
            $delegate->setCellValue("E{$i}", "=C{$i}/C$".$totalRow);
        }
    }
}
