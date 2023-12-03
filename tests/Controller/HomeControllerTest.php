<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;

class HomeControllerTest extends BaseWebTestCase
{

    public function testOpenListRedirectToLogin(): void
    {
        // Request a specific page
        $this->open('app_homepage');

        $this->assertRedirectsToLogin();
    }

    public function testOpenListRoleUserIsOK(): void
    {
        $this->loginUser();
        $this->open('app_homepage');
        $this->assertResponseIsSuccessful();
    }

    public function testOpenListRoleDonorIsOK(): void
    {
        $this->loginDonor();
        $this->open('app_homepage');
        $this->assertResponseIsSuccessful();
    }

    public function testOpenListRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_homepage');
        $this->assertResponseIsSuccessful();
    }

}