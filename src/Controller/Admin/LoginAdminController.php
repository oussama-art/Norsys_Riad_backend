<?php

namespace App\Controller\Admin;
use App\Service\Admin\AdminService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LoginAdminController extends AbstractController
{
    private AdminService  $adminService;
    public function __construct(AdminService $adminService){
        $this->adminService = $adminService;
    }


    #[Route('/admin/login', name: 'app_login_admin')]
    public function login(Request $request): JsonResponse
    {
        $credentials = json_decode($request->getContent(), true);
        return $this->adminService->login($credentials);

    }
}
