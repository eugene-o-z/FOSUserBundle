<?php

namespace FOS\UserBundle\Services\EmailConfirmation\Interfaces;

/**
 * Interface EmailEncryptionInterface
 *
 * @package FOS\UserBundle\Services\EmailConfirmation\Interfaces
 */
interface EmailEncryptionInterface
{
    /**
     * @return string Encrypted email value
     */
    public function encryptEmailValue();

    /**
     * @param string $encryptedEmail Encrypted email value
     *
     * @return string Decrypted email value
     */
    public function decryptEmailValue($encryptedEmail);

    /**
     * @param string $email Email to be encrypt/decrypt
     * @return $this
     */
    public function setEmail($email);

    /**
     * @param string $userConfirmationToken
     * @return $this
     */
    public function setUserConfirmationToken($userConfirmationToken);
}