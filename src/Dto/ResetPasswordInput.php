<?php

namespace App\Dto;

// src/Dto/ResetPasswordInput.php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordInput
{
    #[Assert\NotBlank(message: "Current password should not be blank.")]
    private string $currentPassword;

    #[Assert\NotBlank(message: "New password should not be blank.")]
    #[Assert\Length(
        min: 8,
        minMessage: "Your password must be at least {{ limit }} characters long."
    )]
    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).*$/",
        message: "Your password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character."
    )]
    private string $newPassword;

    #[Assert\NotBlank(message: "Password confirmation should not be blank.")]
    #[Assert\EqualTo(propertyPath: "newPassword", message: "The password confirmation does not match.")]
    private string $confirmation;

    public function getCurrentPassword(): string
    {
        return $this->currentPassword;
    }

    public function setCurrentPassword(string $currentPassword): self
    {
        $this->currentPassword = $currentPassword;

        return $this;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getConfirmation(): string
    {
        return $this->confirmation;
    }

    public function setConfirmation(string $confirmation): self
    {
        $this->confirmation = $confirmation;

        return $this;
    }
}
