<?php
/**
 * Listens on postLoad Doctrine Event and mutates \DateTime fields according to offset
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Evp\Bundle\TicketBundle\Entity\UtcOffsetMutableInterface;

/**
 * Mutates the \DateTime properties in marked class
 *
 * Class UtcOffsetMutator
 */
class UtcOffsetMutator implements EventSubscriber {

    /**
     * @var int
     */
    private $utcOffset;

    /**
     * Sets parameters
     *
     * @param string $offset
     */
    public function __construct($offset) {
        $this->utcOffset = (string)$offset;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getSubscribedEvents() {
        return array(
            'postLoad',
        );
    }

    /**
     * Executes postLoad action
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args) {
//        $entity = $args->getEntity();
//        if ($entity instanceof UtcOffsetMutableInterface) {
//            $reflected = new \ReflectionObject($entity);
//            foreach ($reflected->getProperties() as $property) {
//                $method = 'get' .ucfirst($property->getName());
//                if ($reflected->hasMethod($method)) {
//                    $value = call_user_func(array($entity, $method));
//                    if ($value instanceof \DateTime) {
//                        $value->add(\DateInterval::createFromDateString($this->utcOffset .' hour'));
//                        call_user_func(array($entity, 'set' .ucfirst($property->getName())), $value);
//                    }
//                }
//            }
//        }
    }
}
