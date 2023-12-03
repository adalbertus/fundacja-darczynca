<?php

namespace App\Controller;

use App\Constants\CategoryKeys;
use App\Constants\ErrorCodes;
use App\Constants\UserRolesKeys;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Constants\AccountKeys;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(UserRolesKeys::ADMIN, statusCode: 403, message: ErrorCodes::BRAK_UPRAWNIEN_TEXT)]
class CategoryController extends AbstractController
{
    #[Route('/api/category', name: 'app_api_category')]
    public function index(): Response
    {
        $categories = [
            'results' => [CategoryKeys::ALL_VALUES]
        ];
        return $this->json($categories);
    }
}