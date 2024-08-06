<?php

// src/Controller/ReactController.php

// src/Controller/ReactController.php

namespace App\Controller\PasswordReset;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReactController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('redirect-to-page-reset-password/{token}', name: 'react_password_reset-page', methods: ['GET'])]
    public function resetPassword(string $token): Response
    {
        // Log the received token for debugging
        $this->logger->info('Received token for password reset', ['token' => $token]);

        // Construct the URL to redirect to without the token in the URL
        $reactAppUrl = 'http://localhost:3000/password-reset/';

        // Create a response with a cookie containing the encrypted token
        $response = new RedirectResponse($reactAppUrl, 303);
        $response->headers->setCookie(new Cookie('token', $token, 0, '/', null, false, true, false, 'Strict'));

        // Log the redirection for debugging
        $this->logger->info('Redirecting to React app URL', ['url' => $reactAppUrl]);

        return $response;
    }
}
