<?php

namespace App\Message\Messagetype;

class SendPasswordChangeConfirmationMessage
{
    private string $email;
    private string $username;

    public function __construct(string $email, string $username)
    {
        $this->email = $email;
        $this->username = $username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
