<?php

namespace App\Controller;

use App\Constants\UserRolesKeys;
use App\Constants\ErrorCodes;
use App\Service\BankHistoryService;
use App\Service\DonorService;
use App\Service\SummaryService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[IsGranted(UserRolesKeys::USER, statusCode: 404, message: ErrorCodes::NIE_ZNALEZIONO_STRONY_TEXT)]
class HomeController extends BaseController
{
    #[Route('/', name: 'app_homepage')]
    public function homepage(BankHistoryService $bankHistoryService, SummaryService $summaryService, DonorService $donorService): Response
    {
        if ($this->isGranted(UserRolesKeys::ADMIN)) {
            return $this->_homepage_admin($bankHistoryService, $summaryService);
        }

        if ($this->isGranted(UserRolesKeys::DONOR)) {
            return $this->_homepage_donor($summaryService, $donorService);
        }

        return $this->render('home/dashboard.html.twig');
    }

    private function _homepage_admin(BankHistoryService $bankHistoryService, SummaryService $summaryService): Response
    {
        $draftCount = $bankHistoryService->countDrafts();
        $summary = $summaryService->getTotals();

        return $this->render('home/dashboard_admin.html.twig', [
            'draft_count' => $draftCount > 0,
            'summary' => $summary,
        ]);
    }

    private function _homepage_donor(SummaryService $summaryService, DonorService $donorService): Response
    {
        $donor = $donorService->getDonorByUser($this->getUser());
        if ($donor == null) {
            return $this->render('home/dashboard.html.twig');
        }
        $summary = $summaryService->getTotalsForDonor($donor);

        return $this->render('home/dashboard_donor.html.twig', [
            'summary' => $summary,
            'donor' => $donor,
        ]);
    }
}