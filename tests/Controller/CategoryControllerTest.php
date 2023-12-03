<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;

class CategoryControllerTest extends BaseWebTestCase
{
    public function testCategoryAPIRedirectToLogin(): void
    {
        // Request a specific page
        $this->open('app_api_category');

        $this->assertRedirectsToLogin();
    }

    public function testCategoryAPIRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_api_category');
        $this->assertAccessDenied();
    }

    public function testCategoryAPIRoleDonorDenied(): void
    {
        $this->loginDonor();
        $this->open('app_api_category');
        $this->assertAccessDenied();
    }


    public function testCategoryAPIRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_api_category');
        $this->assertResponseIsSuccessful();
    }


}