<?php

namespace FOS\UserBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Model\UserInterface;


/**
 * Class EmailUpdateListener
 * @package FOS\UserBundle\Doctrine
 */
class EmailUpdateListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        $object = $args->getObject();
        if ($object instanceof UserInterface) {

            $emailUpdateConfirmation = $this->container->get('azine_platform.emailupdateconfirmation');

            if($object->getConfirmationToken() != $emailUpdateConfirmation->getEmailConfirmedToken() && isset($args->getEntityChangeSet()['email'])){

                $currentEmail = $args->getEntityChangeSet()['email'][0];
                $newEmail = $args->getEntityChangeSet()['email'][1];

                $object->setEmail($currentEmail);

                // Configure email confirmation
                $emailUpdateConfirmation->setUser($object);
                $emailUpdateConfirmation->setEmail($newEmail);
                $emailUpdateConfirmation->setConfirmationRoute('fos_user_update_email_confirm');
                $emailUpdateConfirmation->getMailer()->sendUpdateEmailConfirmation(
                    $object,
                    $emailUpdateConfirmation->generateConfirmationLink(),
                    $newEmail
                );
            }

            if($object->getConfirmationToken() == $emailUpdateConfirmation->getEmailConfirmedToken()){

                $object->setConfirmationToken(null);
            }
        }
    }

}