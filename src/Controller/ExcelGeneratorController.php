<?php

namespace App\Controller;

use App\Entity\Admin;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/generar-excel")
 */
class ExcelGeneratorController extends AbstractController
{
    // /**
    //  * @Route("/{className}/{excluded}", name="generar-excel")
    //  */
    // public function generateExcel(string $className, string $excluded = "")
    // {
    //     $date = new DateTime();
    //     $fileName = $className . $date->format('Y-m-d H:i:s') . ".xlsx";

    //     $className = "App\Entity\\" . str_replace("-sep-", "\\", $className);
    //     $excluded = explode("-sep-", $excluded);
        
    //     $repository = $this->getDoctrine()->getRepository($className);

    //     $admins = $repository->findAllOnArray();

    //     $headersArray = [];
    //     $adminsArray = [];

    //     for ($i=0; $i < count($admins); $i++) { 
    //         foreach ($admins[$i] as $propertyName => $property) {
    //             if (in_array($propertyName, $excluded)) {
    //                 continue;
    //             }

    //             if (is_array($property)) {
    //                 $property = implode(", ", $property);
    //             }

    //             $headersArray[] = $propertyName;
    //             $adminsArray[$i][] = $property;
    //         }
    //     }
        
    //     $headersArray = array_unique($headersArray);

    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();

    //     $sheet->fromArray($headersArray, null, 'A1');
    //     $sheet->fromArray($adminsArray, null, 'A2');

    //     $writter = new Xlsx($spreadsheet);

    //     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //     header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');

    //     $writter->save('php://output');
    // }


    /**
     * @Route("/admin", name="admin-excel")
     */
    public function adminExcel()
    {
        $date = new DateTime();
        $fileName = "admin" . $date->format('Y-m-d H:i:s') . ".xlsx";
        
        $repository = $this->getDoctrine()->getRepository(Admin::class);

        $admins = $repository->findAllOnArray();

        $tableArray = $this->createExcelTable($admins);
        $headersArray = $tableArray['headersArray'];
        $adminsArray = $tableArray['recordsArray'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($headersArray, null, 'A1');
        $sheet->fromArray($adminsArray, null, 'A2');

        $writter = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');

        $writter->save('php://output');
    }


    public function createExcelTable($objectsArray)
    {
        $headersArray = [];
        $recordsArray = [];

        for ($i=0; $i < count($objectsArray); $i++) { 
            foreach ($objectsArray[$i] as $propertyName => $property) {
                if (is_array($property)) {
                    $property = implode(", ", $property);
                }

                $headersArray[] = $propertyName;
                $recordsArray[$i][] = $property;
            }
        }

        $headersArray = array_unique($headersArray);

        return [
            'headersArray' => $headersArray, 
            'recordsArray' => $recordsArray
        ];
    }
}