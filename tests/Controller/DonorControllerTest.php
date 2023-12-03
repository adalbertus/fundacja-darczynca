<?php

namespace App\Tests\Controller;

use App\Factory\DonorFactory;
use App\Tests\BaseWebTestCase;

class DonorControllerTest extends BaseWebTestCase
{

    public function testOpenListRedirectToLogin(): void
    {
        // Request a specific page
        $this->open('app_donors');

        $this->assertRedirectsToLogin();
    }


    public function testOpenListRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_donors');
        $this->assertAccessDenied();
    }

    public function testOpenListRoleDonorDenied(): void
    {
        $this->loginDonor();
        $this->open('app_donors');
        $this->assertAccessDenied();
    }

    public function testOpenListRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_donors');
        $this->assertResponseIsSuccessful();
    }


    /****************** DETAILS */
    public function testOpenDetailsRedirectToLogin(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->open('app_donor_details', ['id' => $donorId]);

        $this->assertRedirectsToLogin();
    }


    public function testOpenDetailsRoleUserDenied(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginUser();
        $this->open('app_donor_details', ['id' => $donorId]);
        $this->assertAccessDenied();
    }

    public function testOpenDetailsOwnDonorAccessOK(): void
    {
        $donorWithUser = $this->_createDonorWithUser();
        $this->loginDonor();
        $this->open('app_donor_details', ['id' => $donorWithUser->getId()]);
        $this->assertResponseIsSuccessful();
    }

    public function testOpenDetailsOtherDonorAccessDenied(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginDonor();
        $this->open('app_donor_details', ['id' => $donorId]);
        $this->assertAccessDenied();
    }


    public function testOpenDetailsRoleAdminIsOK(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginAdmin();
        $this->open('app_donor_details', ['id' => $donorId]);
        $this->assertResponseIsSuccessful();
    }

    /****************** DETAILS */



    /****************** TRANSACTIONS */
    public function testOpenTransactionsRedirectToLogin(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->open('app_donor_transactions', ['id' => $donorId]);

        $this->assertRedirectsToLogin();
    }


    public function testOpenTransactionsRoleUserDenied(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginUser();
        $this->open('app_donor_transactions', ['id' => $donorId]);
        $this->assertAccessDenied();
    }

    public function testOpenTransactionsOwnDonorAccessOK(): void
    {
        $donorWithUser = $this->_createDonorWithUser();
        $this->loginDonor();
        $this->open('app_donor_transactions', ['id' => $donorWithUser->getId()]);
        $this->assertResponseIsSuccessful();
    }

    public function testOpenTransactionsOtherDonorAccessDenied(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginDonor();
        $this->open('app_donor_transactions', ['id' => $donorId]);
        $this->assertAccessDenied();
    }

    public function testOpenTransactionsRoleAdminIsOK(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginAdmin();
        $this->open('app_donor_transactions', ['id' => $donorId]);
        $this->assertResponseIsSuccessful();
    }

    /****************** TRANSACTIONS */

    /****************** CREATE */
    public function testOpenCreateRedirectToLogin(): void
    {
        $this->open('app_donor_create');

        $this->assertRedirectsToLogin();
    }


    public function testOpenCreateRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_donor_create');
        $this->assertAccessDenied();
    }

    public function testOpenCreateOwnDonorAccessDenied(): void
    {
        $donorWithUser = $this->_createDonorWithUser();
        $this->loginDonor();
        $this->open('app_donor_create');
        $this->assertAccessDenied();
    }

    public function testOpenCreateOtherDonorAccessDenied(): void
    {
        $this->loginDonor();
        $this->open('app_donor_create');
        $this->assertAccessDenied();
    }

    public function testOpenCreateRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_donor_create');
        $this->assertResponseIsSuccessful();
    }

    /****************** CREATE */


    /****************** UPDATE */
    public function testOpenUpdateRedirectToLogin(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->open('app_donor_update', ['id' => $donorId]);

        $this->assertRedirectsToLogin();
    }


    public function testOpenUpdateRoleUserDenied(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginUser();
        $this->open('app_donor_update', ['id' => $donorId]);
        $this->assertAccessDenied();
    }

    public function testOpenUpdateOwnDonorAccessDenied(): void
    {
        $donorWithUser = $this->_createDonorWithUser();
        $this->loginDonor();
        $this->open('app_donor_update', ['id' => $donorWithUser->getId()]);
        $this->assertAccessDenied();
    }

    public function testOpenUpdateOtherDonorAccessDenied(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginDonor();
        $this->open('app_donor_transactions', ['id' => $donorId]);
        $this->assertAccessDenied();
    }

    public function testOpenUpdateRoleAdminIsOK(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginAdmin();
        $this->open('app_donor_update', ['id' => $donorId]);
        $this->assertResponseIsSuccessful();
    }

    /****************** UPDATE */



    /****************** DELETE */
    public function testOpenDeleteRedirectToLogin(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->open('app_donor_delete', ['id' => $donorId]);

        $this->assertRedirectsToLogin();
    }


    public function testOpenDeleteRoleUserDenied(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginUser();
        $this->open('app_donor_delete', ['id' => $donorId]);
        $this->assertAccessDenied();
    }

    public function testOpenDeleteRoleDonorDenied(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginDonor();
        $this->open('app_donor_delete', ['id' => $donorId]);
        $this->assertAccessDenied();
    }

    public function testOpenDeleteRoleAdminIsOK(): void
    {
        $donorId = DonorFactory::createOne()->getId();
        $this->loginAdmin();
        $this->open('app_donor_delete', ['id' => $donorId]);
        $this->assertResponseIsSuccessful();
    }

    /****************** DELETE */

}