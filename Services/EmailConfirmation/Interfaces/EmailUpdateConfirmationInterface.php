<?php

namespace FOS\UserBundle\Services\EmailConfirmation\Interfaces;

use FOS\UserBundle\Model\User;

/**
 * Interface EmailUpdateConfirmationInterface
 * @package FOS\UserBundle\Services\EmailConfirmation\Interfaces
 */
interface EmailUpdateConfirmationInterface
{

    /**
     * @param string $hashedEmail
     * @return string
     */
    public function fetchEncryptedEmailFromConfirmationLink($hashedEmail);

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user);

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * @param string $confirmationRoute
     * @return $this
     */
    public function setConfirmationRoute($confirmationRoute);
}