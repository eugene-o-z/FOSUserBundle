<?php

namespace FOS\UserBundle\Tests\Util;

use FOS\UserBundle\Services\EmailConfirmation\EmailEncryption;

class EmailEncryptionTest extends \PHPUnit_Framework_TestCase
{
    public function testEncryptDecryptEmail()
    {
        $emailEncryption = new EmailEncryption();
        $emailEncryption->setEmail('foo@example.com');
        $emailEncryption->setUserConfirmationToken('test_token');

        $encryptedEmail = $emailEncryption->encryptEmailValue();
        $this->assertEquals('foo@example.com', $emailEncryption->decryptEmailValue($encryptedEmail));
    }

    public function testDecryptFromWrongEmailFormat()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $emailEncryption = new EmailEncryption();
        $emailEncryption->setEmail('fooexample.com');
        $emailEncryption->setUserConfirmationToken('test_token');

        $encryptedEmail = $emailEncryption->encryptEmailValue();
        $emailEncryption->decryptEmailValue($encryptedEmail);
    }

    public function testIntegerIsSetInsteadOfEmailString()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $emailEncryption = new EmailEncryption();
        $emailEncryption->setEmail(123);
    }

    public function testIntegerIsSetInsteadOfConfirmationTokenString()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $emailEncryption = new EmailEncryption();
        $emailEncryption->setUserConfirmationToken(123);
    }

    public function testNullIsSetInsteadOfConfirmationTokenString()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $emailEncryption = new EmailEncryption();
        $emailEncryption->setUserConfirmationToken(null);
    }

    public function testGetConfirmationToken()
    {
        $emailEncryption = new EmailEncryption();
        $emailEncryption->setUserConfirmationToken('test_token');

        $confirmationToken = $emailEncryption->getConfirmationToken();
        $expectedConfirmationToken = pack('H*', hash('sha256', 'test_token'));
        $this->assertEquals($expectedConfirmationToken, $confirmationToken);

    }

    public function testGetConfirmationTokenIfUserConfirmationTokenIsNotSet()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $emailEncryption = new EmailEncryption();
        $emailEncryption->getConfirmationToken();
    }
}