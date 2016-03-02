<?php

namespace CTO\AppBundle\Controller\DashboardControllers\CTO;

use Carbon\Carbon;
use CTO\AppBundle\Entity\DTO\ExportFilterDTO;
use CTO\AppBundle\Form\DTO\ExportFilterDTOType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class ExportController
 * @package CTO\AppBundle\Controller\DashboardControllers\CTO
 *
 * @Route("/export")
 */
class ExportController extends Controller
{
    /**
     * @Route("/csv", name="cto_export_csv")
     * @Method("GET")
     * @Template()
     */
    public function exportToCSVAction()
    {
        $exportDTO = new ExportFilterDTO();
        $form = $this->createForm(ExportFilterDTOType::class, $exportDTO);

        return [
            "form" => $form->createView()
        ];
    }

    /**
     * @param Request $request
     * @return StreamedResponse
     *
     * @Route("/csv/do", name="cto_export_csvdo")
     */
    public function doCSVExportAction(Request $request)
    {
        $exportDTO = new ExportFilterDTO();
        $form = $this->createForm(ExportFilterDTOType::class, $exportDTO);
        $form->handleRequest($request);

        $response = new StreamedResponse();
        $carJobRepository = $this->getDoctrine()->getManager()->getRepository("CTOAppBundle:CarJob");

        $response->setCallback(
            function() use ($carJobRepository, $exportDTO) {
                $handle = fopen('php://output', 'w+');
                fputcsv($handle, ['Date', 'Day of week', 'Category', 'Description', 'Client', 'Master', 'Salary'],';');

                $result = $carJobRepository->getDataForExportToCSV($exportDTO);

                foreach ($result as $item) {
                    fputcsv(
                        $handle,
                        [
                            $item['jdate']->format("d.m.Y"),
                            $item['jdate']->format("l"),
                            $item['catName'],
                            $item['description'],
                            $item['clientName'],
                            $item['masterFName'] . ' ' . $item['masterLName'],
                            $item['price'],
                        ],
                        ';'
                    );
                }

                fclose($handle);
            }
        );

        $now = Carbon::now()->format("d.m.Y");
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition','attachment; filename='."export_for_{$now}.csv");

        return $response;
    }
}
