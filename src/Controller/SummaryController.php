<?php

namespace App\Controller;

use App\Constants\ErrorCodes;
use App\Security\SummaryVoter;
use App\Service\SummaryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;



class SummaryController extends BaseController
{
    #[IsGranted(SummaryVoter::TOTALS, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/summary', name: 'app_summary')]
    public function index(Request $request, SummaryService $summaryService): Response
    {
        $summary = $summaryService->getTotals();
        return $this->render('summary/index.html.twig', [
            'summary' => $summary,
        ]);
    }
}