<?php

// src/Controller/RegistrationAdminController.php

namespace App\Controller\Admin;

use App\Service\Admin\AdminService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationAdminController extends AbstractController
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

    #[Route('/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        // Check if the user has the ROLE_ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        // Return dashboard content
        return new JsonResponse(['message' => 'Welcome to the admin dashboard!'], Response::HTTP_OK);
    }
}
