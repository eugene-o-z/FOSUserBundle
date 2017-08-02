<?php

namespace FOS\UserBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Services\EmailConfirmation\EmailUpdateConfirmation;
use Symfony\Component\HttpFoundation\RequestStack;


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
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param RequestStack $requestStack
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
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

            $user = $object;

            if (null === $this->emailUpdateConfirmation) {
                $this->emailUpdateConfirmation = $this->container->get('fos_user.email_update_confirmation');
            }

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
                    $this->emailUpdateConfirmation->generateConfirmationLink($this->requestStack->getCurrentRequest()),
                    $newEmail
                );
            }

            if($user->getConfirmationToken() == $this->emailUpdateConfirmation->getEmailConfirmedToken()){

                $user->setConfirmationToken(null);
            }
        }
    }

}