<?php
namespace App\Tests\Controller;

use App\Factory\UserFactory;
use App\Tests\BaseWebTestCase;

class UserControllerTest extends BaseWebTestCase
{
    public function testOpenListRedirectToLogin(): void
    {
        $this->open('app_users');

        $this->assertRedirectsToLogin();
    }

    public function testOpenListRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_users');
        $this->assertAccessDenied();
    }

    public function testOpenListRoleDonorDenied(): void
    {
        $this->loginDonor();
        $this->open('app_users');
        $this->assertAccessDenied();
    }


    public function testOpenListRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_users');
        $this->assertResponseIsSuccessful();
    }



    public function testOpenCreateRedirectToLogin(): void
    {
        $this->open('app_user_create');

        $this->assertRedirectsToLogin();
    }

    public function testOpenCreateRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_user_create');
        $this->assertAccessDenied();
    }

    public function testOpenCreateRoleDonorDenied(): void
    {
        $this->loginDonor();
        $this->open('app_user_create');
        $this->assertAccessDenied();
    }

    public function testOpenCreateRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_user_create');
        $this->assertResponseIsSuccessful();
    }




    public function testOpenApiUsersRedirectToLogin(): void
    {
        $this->open('app_api_users');

        $this->assertRedirectsToLogin();
    }

    public function testOpenApiUsersRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_api_users');
        $this->assertAccessDenied();
    }

    public function testOpenApiUsersRoleDonorDenied(): void
    {
        $this->loginDonor();
        $this->open('app_api_users');
        $this->assertAccessDenied();
    }

    public function testOpenApiUsersRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_api_users');
        $this->assertResponseIsSuccessful();
    }



    public function testOpenSendRegistrationRedirectToLogin(): void
    {
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_send_registration', ['id' => $uId]);

        $this->assertRedirectsToLogin();
    }

    public function testOpenSendRegistrationRoleUserDenied(): void
    {
        $this->loginUser();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_send_registration', ['id' => $uId]);
        $this->assertAccessDenied();
    }

    public function testOpenSendRegistrationRoleDonorDenied(): void
    {
        $this->loginDonor();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_send_registration', ['id' => $uId]);
        $this->assertAccessDenied();
    }


    public function testOpenSendRegistrationRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_send_registration', ['id' => $uId]);
        $url = $this->router->generate('app_users');
        $this->assertResponseRedirects($url);
    }




    public function testOpenSendPasswordResetRedirectToLogin(): void
    {
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_send_password_reset', ['id' => $uId]);

        $this->assertRedirectsToLogin();
    }

    public function testOpenSendPasswordResetRoleUserDenied(): void
    {
        $this->loginUser();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_send_password_reset', ['id' => $uId]);
        $this->assertAccessDenied();
    }

    public function testOpenSendPasswordResetRoleDonorDenied(): void
    {
        $this->loginDonor();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_send_password_reset', ['id' => $uId]);
        $this->assertAccessDenied();
    }


    public function testOpenSendPasswordResetRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_send_password_reset', ['id' => $uId]);
        $url = $this->router->generate('app_users');
        $this->assertResponseRedirects($url);
    }




    public function testOpenUpdateRedirectToLogin(): void
    {
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_update', ['id' => $uId]);

        $this->assertRedirectsToLogin();
    }

    public function testOpenUpdateRoleUserDenied(): void
    {
        $this->loginUser();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_update', ['id' => $uId]);
        $this->assertAccessDenied();
    }

    public function testOpenUpdateRoleDonorDenied(): void
    {
        $this->loginDonor();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_update', ['id' => $uId]);
        $this->assertAccessDenied();
    }


    public function testOpenUpdateRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_update', ['id' => $uId]);
        $this->assertResponseIsSuccessful();
    }





    public function testOpenDeleteRedirectToLogin(): void
    {
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_delete', ['id' => $uId]);

        $this->assertRedirectsToLogin();
    }

    public function testOpenDeleteRoleUserDenied(): void
    {
        $this->loginUser();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_delete', ['id' => $uId]);
        $this->assertAccessDenied();
    }

    public function testOpenDeleteRoleDonorDenied(): void
    {
        $this->loginDonor();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_delete', ['id' => $uId]);
        $this->assertAccessDenied();
    }


    public function testOpenDeleteRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_delete', ['id' => $uId]);
        $this->assertResponseIsSuccessful();
    }





    public function testOpenDetailsRedirectToLogin(): void
    {
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_details', ['id' => $uId]);

        $this->assertRedirectsToLogin();
    }

    public function testOpenDetailsRoleUserDenied(): void
    {
        $this->loginUser();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_details', ['id' => $uId]);
        $this->assertAccessDenied();
    }

    public function testOpenDetailsRoleDonorDenied(): void
    {
        $this->loginDonor();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_details', ['id' => $uId]);
        $this->assertAccessDenied();
    }


    public function testOpenDetailsRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $uId = UserFactory::createOne()->object()->getId();
        $this->open('app_user_details', ['id' => $uId]);
        $this->assertResponseIsSuccessful();
    }





    public function testOpenApiListRedirectToLogin(): void
    {
        $this->open('app_api_users');

        $this->assertRedirectsToLogin();
    }

    public function testOpenApiListRoleUserDenied(): void
    {
        $this->loginUser();
        $this->open('app_api_users');
        $this->assertAccessDenied();
    }

    public function testOpenApiListRoleDonorDenied(): void
    {
        $this->loginDonor();
        $this->open('app_api_users');
        $this->assertAccessDenied();
    }

    public function testOpenApiListRoleAdminIsOK(): void
    {
        $this->loginAdmin();
        $this->open('app_api_users');
        $this->assertResponseIsSuccessful();
    }
}