<?php

namespace FOS\UserBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Services\EmailConfirmation\EmailUpdateConfirmation;


/**
 * Class EmailUpdateListener
 * @package FOS\UserBundle\Doctrine
 */
class EmailUpdateListener implements EventSubscriber
{
    /**
     * @var EmailUpdateConfirmation
     */
    private $emailUpdateConfirmation;

    /**
     * Constructor
     *
     * @param EmailUpdateConfirmation $emailUpdateConfirmation
     */
    public function __construct(EmailUpdateConfirmation $emailUpdateConfirmation)
    {
        $this->emailUpdateConfirmation = $emailUpdateConfirmation;
    }

    public function getSubscribedEvents()
    {
        return array(
            'preUpdate',
        );
    }

    /**
     * Pre update listener based on doctrine common
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $user = $args->getObject();

        if ($user instanceof UserInterface) {

            if($user->getConfirmationToken() != $this->emailUpdateConfirmation->getEmailConfirmedToken() && isset($args->getEntityChangeSet()['email'])){

                $oldEmail = $args->getEntityChangeSet()['email'][0];
                $newEmail = $args->getEntityChangeSet()['email'][1];

                $user->setEmail($oldEmail);

                // Configure email confirmation
                $this->emailUpdateConfirmation->setUser($user);
                $this->emailUpdateConfirmation->setEmail($newEmail);
                $this->emailUpdateConfirmation->setConfirmationRoute('fos_user_update_email_confirm');
                $this->emailUpdateConfirmation->getMailer()->sendUpdateEmailConfirmation(
                    $user,
                    $this->emailUpdateConfirmation->generateConfirmationLink(),
                    $newEmail
                );
            }

            if($user->getConfirmationToken() == $this->emailUpdateConfirmation->getEmailConfirmedToken()){

                $user->setConfirmationToken(null);
            }
        }
    }

}