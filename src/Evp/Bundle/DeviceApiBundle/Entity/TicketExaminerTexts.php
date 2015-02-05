<?php

namespace Evp\Bundle\DeviceApiBundle\Entity;

use Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer;
use JMS\Serializer\Annotation as JMS;

/**
 * class TicketExaminerTexts
 *
 * @JMS\ExclusionPolicy("all")
 */
class TicketExaminerTexts
{
    const EMPTY_FIELD = null;

    /**
     * @var TicketExaminer
     */
    private $ticketExaminer;

    /**
     * @param TicketExaminer $ticketExaminer
     */
    function __construct(TicketExaminer $ticketExaminer)
    {
        $this->ticketExaminer = $ticketExaminer;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("text_used")
     *
     * @return string
     */
    public function getTextUsed()
    {
        if ($this->ticketExaminer->getTextUsed() === null) {
            return self::EMPTY_FIELD;
        }

        return $this->ticketExaminer->getTextUsed();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("text_unused")
     *
     * @return string
     */
    public function getTextUnused()
    {
        if ($this->ticketExaminer->getTextUnused() === null) {
            return self::EMPTY_FIELD;
        }

        return $this->ticketExaminer->getTextUnused();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("event_name")
     *
     * @return string
     */
    public function getEventName()
    {
        if ($this->ticketExaminer === null) {
            return self::EMPTY_FIELD;
        }

        return $this->ticketExaminer->getEvent()->getName();
    }

    /**
     * @param TicketExaminer $ticketExaminer
     * @return self
     */
    public static function createFromTicketExaminer(TicketExaminer $ticketExaminer)
    {
        return new static($ticketExaminer);
    }
}
