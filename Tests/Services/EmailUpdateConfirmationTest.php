<?php


namespace FOS\UserBundle\Tests\Util;

use FOS\UserBundle\Services\EmailConfirmation\EmailUpdateConfirmation;
use FOS\UserBundle\Services\EmailConfirmation\EmailEncryption;

class EmailUpdateConfirmationTest extends \PHPUnit_Framework_TestCase
{

    private $provider;
    private $router;
    private $tokenGenerator;
    private $mailer;
    private $emailEncryption;
    private $eventDispatcher;
    private $emailUpdateConfirmation;
    private $user;
    

    protected function setUp()
    {
        $this->provider = $this->getMockBuilder('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')->getMock();
        $this->user = $this->getMockBuilder('FOS\UserBundle\Model\User')
                ->disableOriginalConstructor()
                ->getMock();
        $this->router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenGenerator = $this->getMockBuilder('FOS\UserBundle\Util\TokenGenerator')->disableOriginalConstructor()->getMock();
        $this->mailer = $this->getMockBuilder('FOS\UserBundle\Mailer\TwigSwiftMailer')->disableOriginalConstructor()->getMock();
        $this->emailEncryption = new EmailEncryption();
        $this->eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();

        $this->emailUpdateConfirmation = new EmailUpdateConfirmation($this->router, $this->tokenGenerator, $this->mailer, $this->emailEncryption, $this->eventDispatcher);
        $this->user->expects($this->any())
            ->method('getConfirmationToken')
            ->will($this->returnValue('test_token'));
        $this->emailUpdateConfirmation->setUser($this->user);
    }

    public function testFetchEncryptedEmailFromConfirmationLinkMethod()
    {
        $emailEncryption = new EmailEncryption();
        $emailEncryption->setEmail('foo@example.com');
        $emailEncryption->setUserConfirmationToken('test_token');

        $encryptedEmail = $emailEncryption->encryptEmailValue();

        $email = $this->emailUpdateConfirmation->fetchEncryptedEmailFromConfirmationLink($encryptedEmail);
        $this->assertEquals('foo@example.com', $email);
    }

    public function testFetchEncryptedEmailWithSpacesInsteadOfPlusSigns()
    {
        $encryptedEmail = 'dlx/ nzDXuaRWJ8q3STRneuZtkjGvyIY m4Z4pkT1SM=';
        $email = $this->emailUpdateConfirmation->fetchEncryptedEmailFromConfirmationLink($encryptedEmail);
        $this->assertEquals('foo@example.com', $email);
    }
}