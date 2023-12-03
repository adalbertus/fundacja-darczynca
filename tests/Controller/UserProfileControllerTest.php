<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;

class UserProfileControllerTest extends BaseWebTestCase
{
    public function testOpenProfileRedirectToLogin(): void
    {
        $this->open('app_user_profile');

        $this->assertRedirectsToLogin();
    }

    public function testOpenProfileRoleUserOK(): void
    {
        $this->loginUser();
        $this->open('app_user_profile');

        $this->assertResponseIsSuccessful();
    }

    public function testOpenProfileRoleDonorOK(): void
    {
        $this->loginDonor();
        $this->open('app_user_profile');

        $this->assertResponseIsSuccessful();
    }

    public function testOpenProfileSuperOK(): void
    {
        $this->loginAdmin();
        $this->open('app_user_profile');

        $this->assertResponseIsSuccessful();
    }

    public function testOpenUpdateRedirectToLogin(): void
    {
        // Request a specific page
        $this->open('app_user_profile_update');

        $this->assertRedirectsToLogin();
    }

    public function testOpenUpdateRoleUserOK(): void
    {
        $this->loginUser();
        $this->open('app_user_profile_update');

        $this->assertResponseIsSuccessful();
    }

    public function testOpenUpdateRoleDonorOK(): void
    {
        $this->loginDonor();
        $this->open('app_user_profile_update');

        $this->assertResponseIsSuccessful();
    }


    public function testOpenUpdateRoleAdminOK(): void
    {
        $this->loginAdmin();
        $this->open('app_user_profile_update');

        $this->assertResponseIsSuccessful();
    }
}