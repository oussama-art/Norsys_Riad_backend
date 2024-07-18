<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EncryptionController extends AbstractController
{
    private string $encryptionKey;

    public function __construct()
    {
        // Ensure to fetch your encryption key securely
        $this->encryptionKey = $this->getEncryptionKeyFromSecureStorage();
    }
    public function decryptToken(string $encryptedToken): string
    {
        $cipher = 'aes-256-cbc';
        $ivLength = openssl_cipher_iv_length($cipher);
        $data = base64_decode($encryptedToken);

        // Extract the IV and the encrypted token
        $iv = substr($data, 0, $ivLength);
        $encryptedData = substr($data, $ivLength);

        // Decrypt the token
        $decryptedToken = openssl_decrypt($encryptedData, $cipher, $this->encryptionKey, 0, $iv);

        if ($decryptedToken === false) {
            throw new \RuntimeException('Failed to decrypt token.');
        }

        return $decryptedToken;
    }



    private function getEncryptionKeyFromSecureStorage(): string
    {
        // Fetch the encryption key from an environment variable or a secure key management service
        $encryptionKey = $_ENV['ENCRYPTION_KEY'] ?? null;

        if (!$encryptionKey) {
            throw new \RuntimeException('Encryption key not set.');
        }

        return $encryptionKey;
    }
}
