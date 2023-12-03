<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;

class ImportControllerTest extends BaseWebTestCase
{

    public function testOpenUploadAnonumousRedirectToLogin(): void
    {
        // Request a specific page
        $this->open('app_import_upload');

        $this->assertRedirectsToLogin();
    }

    public function testOpenUploadRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_import_upload');
        $this->assertAccessDenied();
    }

    public function testOpenUploadRoleDonorDenied(): void
    {
        $this->loginDonor();
        $this->open('app_import_upload');
        $this->assertAccessDenied();
    }

    public function testOpenUploadRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_import_upload');
        $this->assertResponseIsSuccessful();
    }

    public function testOpenImportConfirmationAnonumousRedirectToLogin(): void
    {
        // Request a specific page
        $this->open('app_import_confirm');
        $this->assertRedirectsToLogin();
    }

    public function testOpenImportConfirmRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_import_confirm');
        $this->assertAccessDenied();
    }

    public function testOpenImportConfirmRoleDonorDenied(): void
    {
        $this->loginDonor();
        $this->open('app_import_confirm');
        $this->assertAccessDenied();
    }


    public function testOpenImportConfirmRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_import_confirm');
        $this->assertResponseIsSuccessful();
    }



    public function testOpenImportAnalizeAnonumousRedirectToLogin(): void
    {
        // Request a specific page
        $this->open('app_import_analyze');
        $this->assertRedirectsToLogin();
    }

    public function testOpenImportAnalizeRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_import_analyze');
        $this->assertAccessDenied();
    }

    public function testOpenImportAnalizeRoleDonorDenied(): void
    {
        $this->loginDonor();
        $this->open('app_import_analyze');
        $this->assertAccessDenied();
    }

    public function testOpenImportAnalizeRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_import_analyze');
        $this->assertResponseIsSuccessful();

    }

}