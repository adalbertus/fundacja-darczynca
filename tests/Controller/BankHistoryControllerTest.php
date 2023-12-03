<?php

namespace App\Tests\Controller;

use App\Factory\BankHistoryFactory;
use App\Factory\DonorFactory;
use App\Tests\BaseWebTestCase;

class BankHistoryControllerTest extends BaseWebTestCase
{
    public function testOpenListRedirectToLogin(): void
    {
        // Request a specific page
        $this->open('app_bank_history');

        $this->assertRedirectsToLogin();
    }


    public function testOpenListRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_bank_history');
        $this->assertAccessDenied();
    }

    public function testOpenListRoleDonorDenied(): void
    {
        $this->loginDonor();
        $this->open('app_bank_history');
        $this->assertAccessDenied();
    }

    public function testOpenListRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_bank_history');
        $this->assertResponseIsSuccessful();
    }





    public function testOpenUpdateRedirectToLogin(): void
    {
        $bhId = BankHistoryFactory::createOne()->object()->getId();
        // Request a specific page
        $this->open('app_bank_history_update', ['id' => $bhId]);

        $this->assertRedirectsToLogin();
    }

    public function testOpenUpdateRoleUserDenied(): void
    {
        $bhId = BankHistoryFactory::createOne()->object()->getId();

        $this->loginUser();
        $this->open('app_bank_history_update', ['id' => $bhId]);
        $this->assertAccessDenied();
    }

    public function testOpenUpdateRoleDonorDenied(): void
    {
        $bhId = BankHistoryFactory::createOne()->object()->getId();

        $this->loginDonor();
        $this->open('app_bank_history_update', ['id' => $bhId]);
        $this->assertAccessDenied();
    }


    public function testOpenUpdateRoleAdminIsOK(): void
    {
        $bhId = BankHistoryFactory::createOne()->object()->getId();

        $this->loginAdmin();
        $this->open('app_bank_history_update', ['id' => $bhId]);
        $this->assertResponseIsSuccessful();
    }





    /**************** DETAILS */
    public function testOpenDetailsRedirectToLogin(): void
    {
        $bhId = BankHistoryFactory::createOne()->object()->getId();
        // Request a specific page
        $this->open('app_bank_history_details', ['id' => $bhId]);

        $this->assertRedirectsToLogin();
    }

    public function testOpenDetailsRoleUserDenied(): void
    {
        $bhId = BankHistoryFactory::createOne()->object()->getId();

        $this->loginUser();
        $this->open('app_bank_history_details', ['id' => $bhId]);
        $this->assertAccessDenied();
    }

    public function testOpenDetailsOwnDonorAccessOK(): void
    {
        $donor = $this->_createDonorWithUser();
        $bhId = BankHistoryFactory::createOne(['donor' => $donor])->object()->getId();

        $this->loginDonor();
        $this->open('app_bank_history_details', ['id' => $bhId]);
        $this->assertResponseIsSuccessful();
    }

    public function testOpenDetailsNotOwnDonorDenied(): void
    {
        $donor = DonorFactory::createOne();
        $bhId = BankHistoryFactory::createOne(['donor' => $donor])->object()->getId();

        $this->loginDonor();
        $this->open('app_bank_history_details', ['id' => $bhId]);
        $this->assertAccessDenied();
    }

    public function testOpenDetailsRoleDonorWithoutDonorDenied(): void
    {
        $bhId = BankHistoryFactory::createOne()->object()->getId();

        $this->loginDonor();
        $this->open('app_bank_history_details', ['id' => $bhId]);
        $this->assertAccessDenied();
    }


    public function testOpenDetailsRoleAdminIsOK(): void
    {
        $bhId = BankHistoryFactory::createOne()->object()->getId();

        $this->loginAdmin();
        $this->open('app_bank_history_details', ['id' => $bhId]);
        $this->assertResponseIsSuccessful();
    }
    /**************** DETAILS */

}