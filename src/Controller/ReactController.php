<?php

// src/Controller/ReactController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReactController extends AbstractController
{
    #[Route('/password-reset/{token}', name: 'react_password_reset')]
    public function resetPassword(string $token): Response
    {
        // Construct the URL to redirect to without the token in the URL
        $reactAppUrl = 'http://localhost:3000/password-reset/';
        // Create a response with a cookie containing the encrypted token
        $response = new RedirectResponse($reactAppUrl, 303);
        $response->headers->setCookie(new Cookie('token', $token, 0, '/', null, false, true, false, 'Strict'));

        return $response;
    }
}
