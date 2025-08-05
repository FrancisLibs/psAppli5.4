<?php

namespace App\Service;

use App\Entity\Extract;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelExportService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function exportToExcel(array $data, array $headers, $user)
    {

        // Enregistrement de l'extraction

        $extract = new Extract();
        $date = new \DateTime();
        $extract
            ->setDate($date)
            ->setUser($user);

        $this->entityManager->persist($extract);
        $this->entityManager->flush();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers
        $column = 'A';
        foreach ($headers as $header) {

            $lastColumn = $column;
            $this->styleHeaderRow($sheet, $lastColumn);

            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        // Add data
        $row = 2;
        foreach ($data as $item) {
            $column = 'A';
            foreach ($headers as $property => $header) {
                $value = $this->getPropertyValue($item, $property);
                $sheet->setCellValue($column . $row, $value);
                $column++;
            }
            $row++;
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);
        $fileName = 'export_' . date('Y-m-d_H-i-s') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return $tempFile;
    }

    private function getPropertyValue($object, $property)
    {
        if (is_array($object)) {
            return $object[$property] ?? null; // Récupère la valeur par clé
        }

        $getter = 'get' . ucfirst($property);
        if (method_exists($object, $getter)) {
            return $object->$getter();
        }
        return null;
    }

    private function styleHeaderRow($sheet, $lastColumn)
    {
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray(
            [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E0E0E0',
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            ]
        );
    }
}