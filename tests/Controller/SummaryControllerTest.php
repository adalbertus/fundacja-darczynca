<?php

namespace App\Tests\Controller;

use App\Constants\AccountKeys;
use App\Entity\BankHistory;
use App\Factory\BankHistoryFactory;
use App\Tests\BaseWebTestCase;

class SummaryControllerTest extends BaseWebTestCase
{
    public function testOpenSummaryRedirectToLogin(): void
    {
        // Request a specific page
        $this->open('app_summary');

        $this->assertRedirectsToLogin();
    }

    public function testOpenSummaryRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_summary');
        $this->assertAccessDenied();
    }

    public function testOpenSummaryRoleDonorDenied(): void
    {
        $this->loginDonor();
        $this->open('app_summary');
        $this->assertAccessDenied();
    }


    public function testOpenSummaryRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_summary');
        $this->assertResponseIsSuccessful();
    }



}