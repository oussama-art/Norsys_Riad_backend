<?php

namespace App\Controller;

use App\Service\LoginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends AbstractController
{
    private LoginService $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    #[Route('/login', name: 'login_check', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $credentials = json_decode($request->getContent(), true);
        $username = $credentials['username'] ?? null;
        $password = $credentials['password'] ?? null;

        if (!$username || !$password) {
            return $this->json(['message' => 'Missing username or password'], Response::HTTP_BAD_REQUEST);
        }

        return $this->loginService->login($username, $password);
    }
}
