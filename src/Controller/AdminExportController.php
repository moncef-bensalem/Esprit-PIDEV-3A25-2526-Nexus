<?php

namespace App\Controller;

use App\Repository\PlanificationRepository;
use App\Repository\UserRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/export')]
#[IsGranted(new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_RH')"))]
class AdminExportController extends AbstractController
{
    #[Route('/dashboard/pdf', name: 'admin_export_dashboard_pdf', methods: ['GET'])]
    public function exportDashboardPdf(UserRepository $userRepository, PlanificationRepository $planificationRepository): Response
    {
        $totalUsers = $userRepository->count([]);
        $totalCandidates = $userRepository->countCandidates();
        $totalInterviews = $planificationRepository->count([]);
        $roleDistribution = $userRepository->getRoleDistribution();

        $html = $this->renderView('dashboard/export_pdf.html.twig', [
            'totalUsers' => $totalUsers,
            'totalCandidates' => $totalCandidates,
            'totalInterviews' => $totalInterviews,
            'roleDistribution' => $roleDistribution,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'Rapport_Nexus_' . date('Y-m-d_H-i') . '.pdf';

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    #[Route('/users/excel', name: 'admin_export_users_excel', methods: ['GET'])]
    public function exportUsersExcel(UserRepository $userRepository): StreamedResponse
    {
        $users = $userRepository->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Liste des Utilisateurs');

        // Headers
        $headers = ['ID', 'Prénom', 'Nom', 'Email', 'Rôles', 'Vérifié', 'Dernière Connexion'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Style headers
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF20B2AA');
        $sheet->getStyle('A1:G1')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

        // Data
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                $user->getId(),
                $user->getFirstName(),
                $user->getLastName(),
                $user->getEmail(),
                implode(', ', $user->getRoles()),
                $user->isVerified() ? 'Oui' : 'Non',
                $user->getLastLoginAt() ? $user->getLastLoginAt()->format('d/m/Y H:i') : 'Jamais'
            ];
        }
        $sheet->fromArray($data, null, 'A2');

        // Auto size columns
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $filename = 'Utilisateurs_Nexus_' . date('Y-m-d_H-i') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
