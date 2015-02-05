<?php

namespace Evp\Bundle\DeviceApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketMaintenanceBundle\Entity\TokenAwareInterface;
use Evp\Bundle\TicketMaintenanceBundle\Annotation as Maintenance;
use JMS\Serializer\Annotation as JMS;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * A ticket examiner is a person, who examines tickets
 *
 * class TicketExaminer
 *
 * @ORM\Table(name="evp_ticket_examiners", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="token_idx_evp_ticket_examiners", columns={"token"}),
 *      @ORM\UniqueConstraint(name="name_idx_evp_ticket_examiners", columns={"name", "event_id"})
 * })
 * @ORM\Entity(repositoryClass="Evp\Bundle\DeviceApiBundle\Repository\TicketExaminerRepository")
 * @JMS\ExclusionPolicy("all")
 */
class TicketExaminer implements TokenAwareInterface, UserInterface, BearerUserInterface
{
    const TYPE_EXAMINER_INIT = 'evp_event_init';
    const LABEL_NAME = 'admin.entity.ticket_examiner.name';
    const LABEL_TEXT_UNUSED = 'admin.entity.ticket_examiner.text.unused';
    const LABEL_TEXT_USED = 'admin.entity.ticket_examiner.text.used';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @TicketAdmin\ListedColumn("admin.index.entity.id")
     */
    private $id;

    /**
     * @var string
     * @JMS\Expose
     */
    private $type = self::TYPE_EXAMINER_INIT;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @JMS\Expose
     * @TicketAdmin\ListedColumn("admin.index.entity.ticket_examiner.name")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     * @Maintenance\UniqueToken
     * @TicketAdmin\ListedColumn("admin.index.entity.ticket_examiner.token")
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false, onDelete="RESTRICT")
     */
    private $event;

    /**
     * @var string
     *
     * @ORM\Column(name="text_unused", type="string", length=255, nullable=false)
     * @TicketAdmin\ListedColumn("admin.index.ticket_examiner.text.unused")
     */
    private $textUnused;

    /**
     * @var string
     *
     * @ORM\Column(name="text_used", type="string", length=255, nullable=false)
     * @TicketAdmin\ListedColumn("admin.index.ticket_examiner.text.used")
     */
    private $textUsed;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var array
     */
    private $roles = array('ROLE_API');

    /**
     * @param string $textUnused
     * @return TicketExaminer
     */
    public function setTextUnused($textUnused)
    {
        $this->textUnused = $textUnused;

        return $this;
    }

    /**
     * @return string
     */
    public function getTextUnused()
    {
        return $this->textUnused;
    }

    /**
     * @param string $textUsed
     * @return TicketExaminer
     */
    public function setTextUsed($textUsed)
    {
        $this->textUsed = $textUsed;

        return $this;
    }

    /**
     * @return string
     */
    public function getTextUsed()
    {
        return $this->textUsed;
    }

    /**
     * @param string $apiUrl
     * @return TicketExaminer
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("url")
     *
     * @return string
     */
    public function getApiUrl()
    {
        return rtrim($this->apiUrl, '/');
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return TicketExaminer
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return TicketExaminer
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("key")
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return array|\Symfony\Component\Security\Core\User\Role[]
     */
    public function getRoles() {
        return $this->roles;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return '';
    }

    /**
     * @return string
     */
    public function getSalt() {
        return '';
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->name;
    }

    /**
     *
     */
    public function eraseCredentials() {

    }
}
