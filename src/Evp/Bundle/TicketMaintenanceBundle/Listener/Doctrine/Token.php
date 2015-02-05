<?php
namespace Evp\Bundle\TicketMaintenanceBundle\Listener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Evp\Bundle\TicketMaintenanceBundle\Entity\TokenAwareInterface;
use Evp\Bundle\TicketMaintenanceBundle\Services\UniqueTokenAnnotationReader;
use Evp\Bundle\TicketMaintenanceBundle\Services\UniqueTokenGenerator;

/**
 * Listens to pre-persist callbacks and
 * generates a token in a marked field
 *
 * Class Token
 * @package Evp\Bundle\TicketMaintenanceBundle\Listener\Doctrine
 */
class Token implements EventSubscriber
{
    /**
     * @var UniqueTokenAnnotationReader
     */
    protected $tokenAnnotationReader;

    /**
     * @var UniqueTokenGenerator
     */
    protected $tokenGenerator;

    /**
     * @param UniqueTokenAnnotationReader $uniqueTokenAnnotationReader
     * @param UniqueTokenGenerator $tokenGenerator
     */
    function __construct(
        UniqueTokenAnnotationReader $uniqueTokenAnnotationReader,
        UniqueTokenGenerator $tokenGenerator
    ) {
        $this->tokenAnnotationReader = $uniqueTokenAnnotationReader;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();

        $this->tokenGenerator->setEntityManager($em);

        if ($entity instanceof TokenAwareInterface) {
            $tokenProperties = $this->tokenAnnotationReader->getListedColumns($entity);

            foreach ($tokenProperties as $tokenProperty) {
                $uniqueToken = $this->tokenGenerator->generateForEntityAndField($entity, $tokenProperty);

                $setterName = sprintf('set%s', ucfirst($tokenProperty));
                $entity->$setterName($uniqueToken);
            }

            $em->flush();
        }
    }

}