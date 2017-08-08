<?php

namespace FOS\UserBundle\Services\EmailConfirmation;

use FOS\UserBundle\Services\EmailConfirmation\Interfaces\EmailEncryptionInterface;

/**
 * Class EmailEncryption
 *
 * Use this class for encryption/decryption of email value based on specified
 * token.
 *
 * @package FOS\UserBundle\Services\EmailConfirmation
 */
class EmailEncryption implements EmailEncryptionInterface
{

    /**
     * @var string $cipher
     */
    private $cipher = 'rijndael-128';

    /**
     * @var string $mcryptMode
     */
    private $mcryptMode = 'cbc';

    /**
     * @var string User confirmation token. Use for email encryption
     */
    private $userConfirmationToken;

    /**
     * @var string Email value to be encrypted
     */
    private $email;

    /**
     * EmailEncryption constructor.
     */
    public function __construct()
    {
    }

    /**
     * Encrypt email value with specified user confirmation token.
     *
     * @return string Encrypted email
     */
    public function encryptEmailValue()
    {
        $iv = mcrypt_create_iv($this->getMcryptIvSize(), MCRYPT_RAND);

        $encryptedEmail = mcrypt_encrypt(
            $this->cipher,
            $this->getConfirmationToken(),
            $this->email,
            $this->mcryptMode,
            $iv
        );

        $encryptedEmail = $iv . $encryptedEmail;

        return base64_encode($encryptedEmail);
    }

    /**
     * Decrypt email value with specified user confirmation token.
     *
     * @param string $encryptedEmail
     *
     * @return string Decrypted email.
     */
    public function decryptEmailValue($encryptedEmail)
    {
        $preparedEncryptedEmail = base64_decode($encryptedEmail);

        $ivSize = $this->getMcryptIvSize();

        // Select IV part from encrypted value
        $ivDec = substr($preparedEncryptedEmail, 0, $ivSize);

        // Select email part from encrypted value
        $preparedEncryptedEmail = substr($preparedEncryptedEmail, $ivSize);

        $decryptedEmail = mcrypt_decrypt(
            $this->cipher,
            $this->getConfirmationToken(),
            $preparedEncryptedEmail,
            $this->mcryptMode,
            $ivDec
        );

        // Trim decrypted email from nul byte before return
        $email = rtrim($decryptedEmail, "\0");
        
        if(!preg_match('/^.+\@\S+\.\S+$/', $email)){

            throw new \InvalidArgumentException('Wrong email format was provided for decryptEmailValue function');
        }
        
        return $email;
    }

    /**
     * Set user confirmation token. Will be used for email encryption/decryption.
     * User confirmation token size should be either 16, 24 or 32 byte.
     *
     * @param string $userConfirmationToken
     * @return $this
     */
    public function setUserConfirmationToken($userConfirmationToken)
    {
        if (!$userConfirmationToken || !is_string($userConfirmationToken)) {
            throw new \InvalidArgumentException(
                'Invalid user confirmation token value.'
            );
        }

        $this->userConfirmationToken = $userConfirmationToken;

        return $this;
    }

    /**
     * Set email value to be encrypted.
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        if (!is_string($email)) {
            throw new \InvalidArgumentException(
                'Email to be encrypted should a string. '
                . gettype($email) . ' given.'
            );
        }

        $this->email = trim($email);

        return $this;
    }

    /**
     * Get confirmation token.
     *
     * @return string
     */
    public function getConfirmationToken()
    {
        if (!$this->userConfirmationToken) {
            throw new \InvalidArgumentException(
                'User confirmation token should be specified.'
            );
        }

        // Generate the random binary string based on hashed hexadecimal token
        return pack('H*', hash('sha256', $this->userConfirmationToken));
    }

    /**
     * Return IV size.
     *
     * @return int
     */
    protected function getMcryptIvSize()
    {
        return mcrypt_get_iv_size($this->cipher, $this->mcryptMode);
    }
}