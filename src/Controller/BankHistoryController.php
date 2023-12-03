<?php

namespace App\Controller;

use App\Constants\CategoryKeys;
use App\Constants\ErrorCodes;
use App\Form\BankHistoryFormType;
use App\Repository\BankHistoryRepository;
use App\Security\BankHistoryVoter;
use App\Service\BankHistoryService;
use App\Service\SummaryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Entity\BankHistory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BankHistoryController extends BaseController
{
    use PagerTriat;

    #[IsGranted(BankHistoryVoter::LIST , statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/history', name: 'app_bank_history')]
    public function index(Request $request, BankHistoryRepository $bankHistoryRepository, ValidatorInterface $validator, SummaryService $summaryService): Response
    {
        $queryCriteria = $this->prepareCriteria($request->query);
        $queryBuilder = $bankHistoryRepository->getPagerQueryBuilder($queryCriteria);
        $pager = $this->getPager($queryBuilder, $request);

        foreach ($pager->getCurrentPageResults() as $row) {
            $validator->validate($row);
        }

        $summary = $summaryService->getTotals();

        return $this->render('bank_history/index.html.twig', [
            'pager' => $pager,
            'totalRows' => $pager->getNbResults(),
            'summary' => $summary,
        ]);
    }

    #[IsGranted(BankHistoryVoter::UPDATE, subject: 'bankHistory', statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/history/{id<\d+>}/update', name: 'app_bank_history_update')]
    public function update(BankHistory $bankHistory, Request $request, BankHistoryService $bankHistoryService, RouterInterface $router): Response
    {
        $form = $this->createForm(BankHistoryFormType::class, $bankHistory);

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $this->saveRefererUrl($request);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $bankHistory = $form->getData();
            $bankHistoryService->updateBankHistory($bankHistory);
            $this->addFlashSuccess('Dane historii banku zostały zapisane.');
            $redirectUrl = $this->getRefererUrl($request);
            if ($redirectUrl) {
                // $route = $router->match($redirectUrl)['_route'];
                // żeby naprawić błąd 'Form responses must redirect to another location', który pojawia się przy Turbo należy
                // przy przekierowaniu w form submit zwócić 303 zamiast 302
                // https://turbo.hotwire.dev/handbook/drive#redirecting-after-a-form-submission
                return $this->redirect($redirectUrl, 303);
            }
        }

        return $this->render('bank_history/update.html.twig', [
            'form' => $form,
            'data' => $bankHistory,
            'categories' => CategoryKeys::ALL_VALUES
        ]);
    }

    #[IsGranted(BankHistoryVoter::DETAILS, subject: 'bankHistory', statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/history/{id<\d+>}/details', name: 'app_bank_history_details')]
    public function details(BankHistory $bankHistory, Request $request, BankHistoryService $bankHistoryService, RouterInterface $router): Response
    {
        return $this->render('bank_history/details.html.twig', [
            'data' => $bankHistory,
        ]);
    }
}