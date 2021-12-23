<?php
declare(strict_types=1);

namespace App\Lib;

use Cake\I18n\I18n;
use Cake\I18n\Number;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PogoExport
{
    /**
     * Returns rome representation of number
     *
     * @param int $N number
     * @return string
     */
    public static function rome($N)
    {
        $natural_roman = [1000 => 'M', 500 => 'D', 100 => 'C', 50 => 'L', 10 => 'X', 5 => 'V', 1 => 'I'];
        $rn = '';
        foreach ($natural_roman as $key => $value) {
            while ($N >= $key) {
                $N -= $key;
                $rn .= $value;
            }
        }

        return str_replace(
            ['DCCCC', 'CCCC', 'LXXXX', 'XXXX', 'VIIII', 'IIII'],
            ['CM', 'CD', 'XC', 'XL', 'IX', 'IV'],
            $rn
        );
    }

    /**
     * Execute export.
     *
     * @param string $type Export Type
     * @param array $options Options
     * @return bool|string
     */
    public static function execute($type, $options)
    {
        $ret = true;

        // fetch project data
        $Projects = TableRegistry::getTableLocator()->get('Projects');
        /** @var \App\Model\Entity\Project $project */
        $project = $Projects->get($options['project']);

        // fetch categories
        $q = TableRegistry::getTableLocator()->get('Categories')->find()
            ->select(['Categories.id', 'Categories.title', 'Categories.sort_order'])
            ->distinct('Categories.id')
            ->where(['Categories.project_id' => $options['project']])
            ->order('Categories.sort_order');

        if (!empty($options['tag'])) {
            $q->innerJoinWith('QtiesTags', function ($q) use ($options) {
                return $q->where(['QtiesTags.tag IN' => (array)$options['tag']]);
            });
        }

        if (!empty($options['category'])) {
            $q->where(['Categories.id IN' => (array)$options['category']]);
        }

        $categories = $q->all();

        $category_rows = [];
        if (!$categories->isEmpty()) {
            switch ($type) {
                case 'xls':
                    $validLocale = \PhpOffice\PhpSpreadsheet\Settings::setLocale(I18n::getLocale());
                    //if (!$validLocale) {
                    //    return __('Unable to set locale.');
                    //}

                    $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

                    // defaults
                    $objPHPExcel->getDefaultStyle()->getFont()->setSize(9);

                    // data
                    $objPHPExcel->getProperties()->setCreator(__('www.pogo.si'));
                    $objPHPExcel->getProperties()->setLastModifiedBy('ARHIM d.o.o.');
                    $objPHPExcel->getProperties()->setTitle($project->title);
                    $objPHPExcel->getProperties()->setSubject(__('Builing estimates and calculations'));
                    $objPHPExcel->getProperties()->setDescription($project->descript);

                    // write first page
                    $objPHPExcel->setActiveSheetIndex(0);

                    $activeSheet = $objPHPExcel->getActiveSheet();

                    // header and footer
                    $activeSheet->getHeaderFooter()->setOddFooter(
                        '&L' . $objPHPExcel->getProperties()->getTitle() . '&R' . __('Page {0} of {1}', '&P', '&N')
                    );
                    $activeSheet->getHeaderFooter()->setEvenFooter(
                        '&L' . $objPHPExcel->getProperties()->getTitle() . '&R' . __('Page {0} of {1}', '&P', '&N')
                    );

                    // title
                    $activeSheet->setTitle(__('Recapitulation'));

                    // project
                    $activeSheet->SetCellValue('A1', __('Project') . ':');
                    $activeSheet->SetCellValue('A2', trim($project->no . ' ' . $project->title));
                    $activeSheet->getStyle('A2')->getFont()->setSize(16);
                    $activeSheet->getStyle('A2')->getFont()->setBold(true);

                    // investor
                    $activeSheet->SetCellValue('A4', __('Investor') . ':');
                    $activeSheet->SetCellValue('A5', $project->investor_title);
                    $activeSheet->getStyle('A5')->getFont()->setSize(12);
                    $activeSheet->getStyle('A5')->getFont()->setBold(true);
                    $activeSheet->SetCellValue('A6', $project->investor_address);
                    $activeSheet->SetCellValue('A7', $project->investor_zip . ' ' . $project->investor_post);

                    // contents
                    $projectSubtitle = '';
                    if (!empty($project->subtitle)) {
                        $projectSubtitle = ' :: ' . $project->subtitle;
                    }
                    $activeSheet->SetCellValue('A10', __('Recapitulation') . $projectSubtitle);
                    $activeSheet->mergeCells('A10:C10');
                    $activeSheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $activeSheet->getStyle('A10')->getFont()->setSize(16);
                    $activeSheet->getStyle('A10')->getFont()->setBold(true);
                    $styleArray = ['borders' => [
                        'outline' => [
                            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '00000000'],
                        ],
                    ]];
                    $activeSheet->getStyle('A10:C10')->applyFromArray($styleArray);

                    $activeSheet->getColumnDimension('B')->setWidth(70);
                    $activeSheet->getColumnDimension('C')->setWidth(30);

                    $j = 12;
                    $sectCntr = 1;
                    foreach ($categories as $category) {
                        $activeSheet->SetCellValue('A' . $j, chr(64 + $category->sort_order) . '.');
                        $activeSheet->SetCellValue('B' . $j, h($category->title));
                        $activeSheet->getStyle('A' . $j . ':C' . $j)->getFont()->setSize(14);
                        $activeSheet->getStyle('A' . $j . ':C' . $j)->getFont()->setBold(true);

                        $activeSheet->getStyle('A' . $j . ':C' . $j)->getBorders()
                                ->getBottom()
                                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('00000000'));

                        $category_row = $j;
                        $category_rows[] = $j;
                        $j++;

                        $q = TableRegistry::getTableLocator()->get('Sections')->find()
                            ->select(['Sections.id', 'Sections.title', 'Sections.sort_order', 'Sections.descript'])
                            ->distinct('Sections.id')
                            ->where(['Sections.category_id' => $category->id])
                            ->order('Sections.sort_order');
                        if (!empty($options['tag'])) {
                            $q->innerJoinWith('QtiesTags', function ($q) use ($options) {
                                return $q->where(['QtiesTags.tag IN' => (array)$options['tag']]);
                            });
                        }

                        /** @var \Cake\ORM\ResultSet $sections */
                        $sections = $q->all();

                        foreach ($sections as $section) {
                            /** @var \App\Model\Entity\Section $section */

                            // recap sheet
                            $activeSheet->SetCellValue('A' . $j, self::rome($sectCntr) . '.');
                            $activeSheet->SetCellValue('B' . $j, h($section->title));

                            $activeSheet->getCell('B' . $j)->getHyperlink()
                                ->setUrl("sheet://'" . h($section->title) . "'!A2");

                            // new sheet
                            $ws = $objPHPExcel->createSheet();

                            // title
                            $ws->setTitle(substr($section->title, 0, 30));

                            // footer
                            $hf = '&L' . $objPHPExcel->getProperties()->getTitle() .
                                ' :: ' . $section->title . '&R' . __('Page {0} of {1}', '&P', '&N');
                            $ws->getHeaderFooter()->setOddFooter($hf);
                            $ws->getHeaderFooter()->setEvenFooter($hf);

                            // contents
                            $ws->SetCellValue('A2', self::rome($sectCntr) . '. ' . $section->title);
                            $ws->getStyle('A2')->getFont()->setSize(16);

                            // set column width
                            $ws->getColumnDimension('A')->setWidth(9);
                            $ws->getColumnDimension('B')->setWidth(45);
                            $ws->getColumnDimension('C')->setWidth(9);
                            $ws->getColumnDimension('D')->setWidth(9);
                            $ws->getColumnDimension('E')->setWidth(9);
                            $ws->getColumnDimension('F')->setWidth(9);

                            $ws->SetCellValue('A3', $section->descript);
                            // Auto height on merged cells does not work so it's pointless to
                            // merge cells with section description
                            $ws->mergeCells('A3:F3');

                            $ws->getRowDimension(3)->setRowHeight(-1);
                            $ws->getStyle('A3')->getAlignment()->setWrapText(true);
                            $ws->getStyle('A3')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                            // dummy cell for auto height to work
                            $ws->getColumnDimension('M')->setWidth(90);
                            $ws->getStyle('M3')->getAlignment()->setWrapText(true);
                            $ws->getStyle('M3')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                            $ws->SetCellValue('M3', $section->descript);
                            $ws->getColumnDimension('M')->setVisible(false);

                            // header
                            $ws->SetCellValue('A5', __('No.'));
                            $ws->SetCellValue('B5', __('Description'));
                            $ws->SetCellValue('C5', __('Unit'));
                            $ws->SetCellValue('D5', __('Qty'));

                            $ws->SetCellValue(
                                'E5',
                                __('Price [{0}]', Number::formatter()->getSymbol(\NumberFormatter::CURRENCY_SYMBOL))
                            );
                            $ws->SetCellValue(
                                'F5',
                                __('Total [{0}]', Number::formatter()->getSymbol(\NumberFormatter::CURRENCY_SYMBOL))
                            );
                            //$ws->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(5);

                            $ws->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            $ws->getStyle('C5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            $ws->getStyle('D5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                            $ws->getStyle('E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                            $ws->getStyle('F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                            $ws->getStyle('A5:F5')->getFont()->setBold(true);
                            $ws->getStyle('A5:F5')->getBorders()
                                ->getBottom()
                                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('00000000'));

                            // target row counter
                            $i = 6;

                            $q = TableRegistry::getTableLocator()->get('Qties')->find()
                                ->select()
                                ->contain('Items')
                                ->where(['Items.section_id' => $section->id])
                                ->order(['Items.sort_order', 'Items.id', 'Qties.sort_order']);
                            if (!empty($options['tag'])) {
                                $q->innerJoinWith('QtiesTags', function ($q) use ($options) {
                                    return $q->where(['QtiesTags.tag IN' => (array)$options['tag']]);
                                });
                            }
                            $qties = $q->all();

                            $processQties = !empty($options['qties']);
                            $showItemPrice = empty($options['noprice']);

                            $currentItem = null;
                            $lastItemRow = 0;
                            $itemQty = 0;
                            $isOnlyOne = true;
                            $countItemQties = 0;

                            foreach ($qties as $k => $qty) {
                                if ($currentItem != $qty->item->id) {
                                    // do a real sum of previous item when filtered by tag
                                    if ($currentItem != null) {
                                        $i += 1;

                                        if ($processQties) {
                                            if ($countItemQties > 1) {
                                                $ws->SetCellValue(
                                                    'D' . $lastItemRow,
                                                    sprintf('=SUM(D%1$s:D%2$s)', $lastItemRow + 1, $i - 2)
                                                );
                                            } else {
                                                // remove qties when there is only one
                                                $ws->SetCellValue('D' . $lastItemRow, $itemQty);
                                                $ws->removeRow($i - 2, 1);
                                                $i--;
                                            }
                                        } else {
                                            $ws->SetCellValue('D' . $lastItemRow, $itemQty);
                                        }
                                    }

                                    $countItemQties = 0;

                                    $lastItemRow = $i;

                                    $ws->SetCellValue('A' . $i, $qty->item->sort_order);
                                    $ws->SetCellValue('B' . $i, $qty->item->descript);
                                    $ws->SetCellValue('C' . $i, $qty->item->unit);
                                    $ws->SetCellValue('D' . $i, $qty->item->qty);
                                    $ws->SetCellValue('E' . $i, $showItemPrice ? $qty->item->price : 0);
                                    $ws->SetCellValue('F' . $i, '=D' . $i . '*E' . $i);

                                    $ws->getStyle('D' . $i)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                                    // price field
                                    $ws->getStyle('E' . $i)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                                    $ws->getStyle('E' . $i)
                                        ->getProtection()
                                        ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
                                    if (!empty($options['accentprice'])) {
                                        $styleArray = [
                                            'borders' => [
                                                'outline' => [
                                                    'borderStyle' => Border::BORDER_THIN,
                                                    'color' => ['argb' => '00808080'],
                                                ],
                                            ],
                                            'fill' => [
                                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                                'startColor' => ['argb' => 'FFFFCC99'],
                                            ],
                                        ];
                                        $ws->getStyle('E' . $i)->applyFromArray($styleArray);
                                    }

                                    $ws->getStyle('F' . $i)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                                    $ws->getStyle('A' . $i)
                                        ->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                                    $ws->getStyle('A' . $i)
                                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                                    $ws->getStyle('B' . $i)
                                        ->getAlignment()->setWrapText(true);
                                    $ws->getStyle('B' . $i)
                                        ->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                                    $ws->getStyle('C' . $i)
                                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                                    $i += 1;

                                    $itemQty = 0;
                                    $currentItem = $qty->item->id;
                                }

                                $countItemQties++;

                                if ($processQties) {
                                    $ws->SetCellValue('B' . $i, $qty->descript);
                                    $ws->SetCellValue('D' . $i, $qty->qty_value);

                                    $ws->getStyle('D' . $i)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                                    $ws->getStyle('B' . $i)->getAlignment()->setWrapText(true);
                                    $ws->getStyle('B' . $i)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                                    $ws->getStyle('B' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                                    $ws->getStyle('A' . $i . ':F' . $i)->getFont()->setSize(7);

                                    $i += 1;
                                }

                                $itemQty += (float)$qty->qty_value;
                            }

                            // do a real sum for last item
                            if ($currentItem != null) {
                                if ($processQties) {
                                    if ($countItemQties > 1) {
                                        $ws->SetCellValue(
                                            'C' . $lastItemRow,
                                            sprintf('=SUM(D%1$s:D%2$s)', $lastItemRow + 1, $i - 1)
                                        );
                                    } else {
                                        $ws->SetCellValue('D' . $lastItemRow, $itemQty);
                                        $ws->removeRow($i - 1, 1);
                                        $i--;
                                    }
                                } else {
                                    $ws->SetCellValue('C' . $lastItemRow, $itemQty);
                                }
                                $i += 1;
                            }

                            // grand total
                            $ws->SetCellValue('A' . $i, __('Grand total') . ':');
                            if ($qties->count() == 0) {
                                $ws->SetCellValue('F' . $i, '0');
                            } else {
                                $ws->SetCellValue('F' . $i, '=SUM(F6:F' . ($i - 2) . ')');
                            }
                            $ws->getStyle('F' . $i)
                                ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                            $ws->getStyle('A' . $i . ':F' . $i)->getFont()->setBold(true);

                            $ws->getStyle('A' . $i . ':F' . $i)->getBorders()
                                ->getTop()
                                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('00000000'));

                            $ws->getPageSetup()->setPrintArea('A1:F' . $i);

                            if (isset($options['password'])) {
                                $protection = $ws->getProtection();
                                $protection->setPassword($options['password']);
                                $protection->setSheet(true);
                                $protection->setSort(true);
                                $protection->setInsertRows(true);
                                $protection->setFormatCells(true);
                            }

                            $ws = null;

                            // goto recap sheet and update numbers
                            $objPHPExcel->setActiveSheetIndex(0);

                            $activeSheet = $objPHPExcel->getActiveSheet();
                            $activeSheet->SetCellValue('C' . $j, '=\'' . $section->title . '\'!F' . $i);
                            $activeSheet->getStyle('C' . $j)
                                ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                            $sectCntr++;
                            $j++;
                        }

                        // category sum
                        $activeSheet->SetCellValue('B' . $j, __('Grand Total'));
                        $activeSheet->getStyle('A' . $j . ':C' . $j)->getFont()->setSize(18);
                        $activeSheet->getStyle('A' . $j . ':C' . $j)->getFont()->setBold(true);

                        if ($sections->count() == 0) {
                            $activeSheet->SetCellValue('C' . $category_row, 0);
                        } else {
                            $activeSheet->SetCellValue(
                                'C' . $category_row,
                                sprintf('=SUM(C%1$s:C%2$s)', $category_row + 1, $j - 1)
                            );
                        }
                        $activeSheet->getStyle('C' . $category_row)->getNumberFormat()->setFormatCode('#,##0.00 "€"');
                    }

                    // grand total
                    if ($categories->isEmpty()) {
                        $activeSheet->SetCellValue('C' . $j, 0);
                    } else {
                        $activeSheet->SetCellValue('C' . $j, '=C' . implode('+C', $category_rows));
                    }
                    $activeSheet->getStyle('C' . $j)->getNumberFormat()->setFormatCode('#,##0.00 "€"');

                    $activeSheet->getStyle('A' . $j . ':C' . $j)->getBorders()
                        ->getTop()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('00000000'));

                    $j = $j + 8;

                    $activeSheet->SetCellValue('C' . $j, __('Project Leader') . ':');
                    $activeSheet->getStyle('C' . $j)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $j++;

                    $activeSheet->SetCellValue('C' . $j, $project->creator_person);
                    $activeSheet->getStyle('C' . $j)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $activeSheet->getStyle('C' . $j)->getFont()->setBold(true);

                    $activeSheet->SetCellValue('A' . $j, $project->dat_place);
                    $activeSheet->getStyle('A' . $j)->getFont()->setBold(true);
                    $j++;

                    $activeSheet->getPageSetup()->setPrintArea('A1:C' . $j);

                    if (isset($options['password'])) {
                        $protection = $activeSheet->getProtection();
                        $protection->setPassword($options['password']);
                        $protection->setSheet(true);
                        $protection->setSort(true);
                        $protection->setInsertRows(true);
                        $protection->setFormatCells(true);
                    }

                    $excelFile = Text::slug($project->no . ' ' . $project->title);

                    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                    header('Cache-Control: no-store, no-cache, must-revalidate');
                    header('Cache-Control: post-check=0, pre-check=0', false);
                    header('Pragma: no-cache');
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $excelFile . '.xlsx"');

                    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
                    $writer->save('php://output');

                    $objPHPExcel->disconnectWorksheets();
                    $objPHPExcel->garbageCollect();
                    unset($objPHPExcel);
                    die;
            }
        } else {
            return __('Export error: No data found.');
        }

        return $ret;
    }
}
