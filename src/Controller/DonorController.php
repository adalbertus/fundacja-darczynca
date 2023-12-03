<?php

namespace App\Controller;

use App\Constants\ErrorCodes;
use App\Entity\Donor;
use App\Form\DonorFormType;
use App\Form\DonorDeleteFormType;
use App\Repository\DonorRepository;
use App\Service\BankHistoryService;
use App\Service\DonorService;
use App\Security\DonorVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class DonorController extends BaseController
{
    use PagerTriat;

    #[IsGranted(DonorVoter::INDEX, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/donors', name: 'app_donors')]
    public function index(Request $request, DonorRepository $donorRepository): Response
    {
        $queryCriteria = $this->prepareCriteria($request->query);
        $queryBuilder = $donorRepository->getPagerQueryBuilder($queryCriteria);
        $pager = $this->getPager($queryBuilder, $request);

        return $this->render('donor/index.html.twig', [
            'pager' => $pager,
            'totalRows' => $pager->getNbResults(),
        ]);
    }

    #[IsGranted(DonorVoter::DETAILS, subject: 'donor', statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/donor/{id<\d+>}', name: 'app_donor_details')]
    public function details(Donor $donor, Request $request, BankHistoryService $bankHistoryService): Response
    {
        $latestsTransactions = $bankHistoryService->getLastDonorTransactions($donor);
        $form = $this->createForm(DonorFormType::class, $donor, [
            'autocomplete_email_url' => $this->generateUrl('app_api_users')
        ]);
        return $this->render('donor/details.html.twig', [
            'donor' => $donor,
            'form' => $form,
            'latestsTransactions' => $latestsTransactions,
        ]);
    }

    #[IsGranted(DonorVoter::TRANSACTIONS, subject: 'donor', statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/donor/{id<\d+>}/transactions', name: 'app_donor_transactions')]
    public function transactions(Donor $donor, Request $request, BankHistoryService $bankHistoryService): Response
    {
        $queryCriteria = $this->prepareCriteria($request->query);
        $queryBuilder = $bankHistoryService->getPagerQueryBuilderForDonor($donor, $queryCriteria);
        $pager = $this->getPager($queryBuilder, $request, 1, 10);


        return $this->render('donor/transactions.html.twig', [
            'donor' => $donor,
            'pager' => $pager,
            'totalRows' => $pager->getNbResults(),
        ]);
    }

    #[IsGranted(DonorVoter::CREATE, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/donor/create', name: 'app_donor_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, DonorService $donorService): Response
    {
        $info = "<div>Darczyńcy zazwyczaj są tworzeni automatycznie w czasie importu danych z banku.</div>";
        $info .= "<div>Ręczne tworzenie darczyńcy wymaga utworzenia frazy wyszukiwania (wyrażenie regularne), które służy do rozpoznawania nadawcy przelewu.</div>";
        $this->addFlashInfo($info);
        $donor = new Donor();
        return $this->_createOrUpdate($donor, $request, $entityManager, $donorService);
    }

    #[IsGranted(DonorVoter::UPDATE, subject: 'donor', statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/donor/{id<\d+>}/update', name: 'app_donor_update')]
    public function update(Donor $donor, Request $request, EntityManagerInterface $entityManager, DonorService $donorService): Response
    {
        return $this->_createOrUpdate($donor, $request, $entityManager, $donorService);
    }

    private function _createOrUpdate(Donor $donor, Request $request, EntityManagerInterface $entityManager, DonorService $donorService): Response
    {
        $originalSearchPatterns = new ArrayCollection();

        // Create an ArrayCollection of the current SearchPattern objects in the database
        foreach ($donor->getDonorSearchPatterns() as $searchPattern) {
            $originalSearchPatterns->add($searchPattern);
        }

        $form = $this->createForm(DonorFormType::class, $donor, [
            'autocomplete_email_url' => $this->generateUrl('app_api_users')
        ]);

        $form->get('autocomplete_email')->setData($donor->getUserEmailOrEmpty());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // remove the relationship between the tag and the Task
            foreach ($originalSearchPatterns as $searchPattern) {
                if (false === $donor->getDonorSearchPatterns()->contains($searchPattern)) {
                    $searchPattern->setDonor(null);
                    $entityManager->persist($searchPattern);
                }
            }

            $autocompleteEmails = $form->get('autocomplete_email')->getData();
            $donorService->addOrRemoveUserBasedOnEmails($donor, $autocompleteEmails);

            $donor->setIsAuto(false);
            $entityManager->persist($donor);
            $entityManager->flush();

            if ($donor->isNew()) {
                $this->addFlashSuccess("Darczyńca <strong>{$donor}</strong> został utworzony.");
            } else {
                $this->addFlashSuccess("Darczyńca <strong>{$donor}</strong> został zaktualizowany.");
            }

            return $this->redirectToRoute('app_donors');
        }

        return $this->render('donor/create_update.html.twig', [
            'donor' => $donor,
            'form' => $form,
        ]);
    }


    #[IsGranted(DonorVoter::UPDATE, subject: 'donor', statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/donor/{id<\d+>}/delete', name: 'app_donor_delete')]
    public function delete(Donor $donor, Request $request, DonorService $donorService, BankHistoryService $bankHistoryService): Response
    {
        $latestsTransactions = $bankHistoryService->getLastDonorTransactions($donor);
        $donorTransferRequired = count($latestsTransactions) > 0;
        $form = $this->createForm(DonorDeleteFormType::class, $donor, [
            'donor_transfer' => $donorTransferRequired,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Donor $newDonor */
            $newDonor = $form->get('donor')->getData();

            $donorName = $donor->getDisplayName();
            $flashMessage = "<p>Darczyńca <strong>{$donorName}</strong> został usunięty.</p>";
            if ($donorTransferRequired) {
                $newDonorUrl = $this->generateUrl('app_donor_details', ['id' => $newDonor->getId()]);
                $flashMessage .= "Wszystkie jego operacje bankowe zostały przeniesione do darczyńcy <a href=\"{$newDonorUrl}\">{$newDonor}</a>.";
            }
            $donorService->deleteAndTransferIfNeeded($donor, $newDonor);
            $this->addFlashSuccess($flashMessage);
            return $this->redirectToRoute('app_donors');
        }

        return $this->render('donor/delete.html.twig', [
            'donor' => $donor,
            'form' => $form,
            'latestsTransactions' => $latestsTransactions,
        ]);
    }
}