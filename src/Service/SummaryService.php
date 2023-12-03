<?php
namespace App\Service;

use App\Entity\Donor;
use App\Repository\BankHistoryRepository;


class SummaryService
{
    public function __construct(
        private BankHistoryRepository $bankHistoryRepository,
    ) {
    }


    /**
     * Zwrócenie podstawowego podsumowania.
     * - łączna kwota
     * - ostatnia aktualizacja
     * - ostatnie 5 wpisów
     *
     * @return array [
     *  'total' => value,
     *  'updated' => date,
     *  'last5' => [],
     *  ]
     */
    public function getTotals(): array
    {
        $summary = $this->bankHistoryRepository->getTotals();
        $summary['last5'] = $this->bankHistoryRepository->findBy(['is_draft' => false], ['date' => 'DESC'], 5);
        return $summary;
    }

    /**
     * Podsumowanie wpłat darczyńcy:
     * - suma wszystkich wpłat
     * - suma wpłat poprzedniego roku
     * - suma wpłat bieżącego roku
     * - ostatnie 5 wpłat
     *
     * @param  mixed $donor
     * @return array ['total' => 0, 'prev_year' => 0, 'cur_year' => 0, 'last5' => [...rows]]
     */public function getTotalsForDonor(Donor $donor): array
    {
        $summary = $this->bankHistoryRepository->getTotalsForDonor($donor);
        $summary['last5'] = $this->bankHistoryRepository->findBy([
            'is_draft' => false,
            'donor' => $donor,
        ], ['date' => 'DESC'], 5);
        return $summary;
    }
}