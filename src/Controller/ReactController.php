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
    #[Route('/password-reset/{token}', name: 'api_reset_password')]
    public function resetPassword(string $token): Response
    {
        // Encrypt the token using the generated key
        $encryptedTokenHex = $this->encryptToken($token);

        // Construct the URL to redirect to without the token in the URL
        $reactAppUrl = 'http://localhost:3000/password-reset/';

        // Create a response with a cookie containing the encrypted token
        $response = new RedirectResponse($reactAppUrl, 303);
        $response->headers->setCookie(new Cookie('encrypted_token', $encryptedTokenHex, 0, '/', null, false, true, false, 'Strict'));

        return $response;
    }


    #[Route('/api/get-encryption-key', name: 'api_get_encryption_key')]
    public function getEncryptionKey(): Response
    {
        // Retrieve the encryption key from environment or secure storage
        $encryptionKey = $_ENV['ENCRYPTION_KEY']; // Replace with your actual secure key retrieval logic

        return $this->json(['encryption_key' => $encryptionKey]);
    }

    private function encryptToken(string $token): string
    {
        // Generate a random IV (Initialization Vector) for AES-256 CBC
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));

        // Get the AES-256 key (replace 'your-secret-key' with your actual key)
        $key = base64_decode($_ENV['ENCRYPTION_KEY']);

        // Encrypt the token using AES-256 CBC encryption with the generated key and IV
        $encryptedToken = openssl_encrypt($token, 'aes-256-cbc', $key, 0, $iv);

        // Combine IV and ciphertext and convert to hexadecimal for safe transmission
        $encryptedTokenWithIV = $iv . $encryptedToken;
        return bin2hex($encryptedTokenWithIV);
    }
}
