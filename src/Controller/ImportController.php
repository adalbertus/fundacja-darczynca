<?php

namespace App\Controller;

use App\Constants\ErrorCodes;
use App\Constants\SessionKeys;
use App\Constants\UserRolesKeys;
use App\Service\BankHistoryImportService;
use App\Security\BankHistoryVoter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Exception\InvalidCsvFileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Dropzone\Form\DropzoneType;


class ImportController extends BaseController
{
    /**
     * Wczytanie pliku CSV z historią banku.
     */
    #[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/import', name: 'app_import_upload')]
    public function upload(Request $request, BankHistoryImportService $bankHistoryImport): Response
    {
        $form = $this->createFormBuilder()
            ->add('filename', DropzoneType::class, [
                'attr' => [
                    'accept' => ".csv",
                    'placeholder' => 'Przeciągnij i upuść lub kliknij żeby wybrać...',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $filename = $form->get('filename')->getData();

            try {
                $stats = $bankHistoryImport->cleanOldAndImportUniqueFromCSV($filename);

                $this->addFlashInfo('
                    Historia banku została wczytana.<br />
                    Ilość wierszy: ' . $stats['csv_records'] . '<br />
                    Ilość wierszy do importu: ' . $stats['import_records'] . '<br />
                    Ilość duplikatów (ignorowane): ' . $stats['csv_records'] - $stats['import_records']);
                $this->saveToSession($request, SessionKeys::BANK_HISTORY_ANALIZED, false);
                // żeby naprawić błąd 'Form responses must redirect to another location', który pojawia się przy Turbo należy
                // przy przekierowaniu w form submit zwócić 303 zamiast 302
                // https://turbo.hotwire.dev/handbook/drive#redirecting-after-a-form-submission
                return $this->redirectToRoute('app_import_confirm', [], 303);
            } catch (InvalidCsvFileException $ex) {
                $this->uploadError($form, "Wygląda na to, że wczytany plik jest niewłaściwy. Na pewno format pliku się zgadza?");
            }
        }

        return $this->render('import/upload.html.twig', [
            'form' => $form,
        ]);
    }

    private function uploadError($form, $message)
    {
        // ponieważ kożystam z Tubrbo - to trzeba wymusić odświeżenie frontu, żeby
        // pojawiło się flash message (nie umiem inaczej :))
        $form->addError(new FormError('Błąd wczytywania pliku CSV'));
        $this->addFlashError($message);
    }

    /**
     * Zatwierdzenie wcześniej wczytanej historii banku oraz jej analiza (automatyczna)
     */
    #[IsGranted(BankHistoryVoter::LIST , statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/import/confirm', name: 'app_import_confirm')]
    public function importConfirmation(Request $request, BankHistoryImportService $bankHistoryImport): Response
    {
        $form = $this->createFormBuilder()
            ->add('ignoreErrors', SubmitType::class, ['label' => 'Zatwierdź ignorując błędy'])
            ->getForm();

        if (!$this->getFromSession($request, SessionKeys::BANK_HISTORY_ANALIZED, false)) {
            $bankHistoryImport->analyzeAndSave();
            $this->saveToSession($request, SessionKeys::BANK_HISTORY_ANALIZED, true);
        }

        $bankHistoryList = $bankHistoryImport->findAllDraftAndValidate();
        $errorCount = count(
            array_filter(
                $bankHistoryList,
                function ($bh) {
                    return !$bh->isValid();
                }
            )
        );
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $ignorErrors = $form->get('ignoreErrors')->isClicked();

            if ($errorCount == 0 || $ignorErrors) {
                $bankHistoryImport->acceptAllDraft();
                $this->addFlashSuccess('Dane zostały zaimportowane.');
                return $this->redirectToRoute('app_bank_history');
            }
        }

        return $this->render('import/confirm.html.twig', [
            'bankHistoryList' => $bankHistoryList,
            'errorCount' => $errorCount,
            'form' => $form,
        ]);
    }

    #[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
    #[Route('/import/analyze', name: 'app_import_analyze')]
    public function analyze(Request $request, BankHistoryImportService $bankHistoryImport): Response
    {
        $bankHistoryImport->analyzeAndSave();
        $this->addFlashInfo("Importowana historia została ponownie przenalizowana.");
        return $this->json(['result' => 'OK']);
    }
}