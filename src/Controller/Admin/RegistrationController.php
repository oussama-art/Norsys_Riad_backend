<?php

// src/Controller/RegistrationController.php

namespace App\Controller\Admin;

use App\Service\Admin\AdminService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    #[Route('/admin/register', name: 'admin_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Validate request data
        if (!isset($data['email'], $data['username'], $data['password'])) {
            return new JsonResponse(['error' => 'Email, username, and password are required.'], Response::HTTP_BAD_REQUEST);
        }

        // Register the admin user
        return $this->adminService->registerAdmin($data);
    }

}
