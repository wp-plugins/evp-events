<?php
/**
 * DiscountType Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as GEDMO;
use Doctrine\Common\Collections\ArrayCollection;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * DiscountType
 *
 * @ORM\Table(name="evp_discount_types")
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\DiscountTypeRepository")
 * @ORM\HasLifecycleCallbacks
 * @GEDMO\TranslationEntity(class="Evp\Bundle\TicketBundle\Entity\Translation")
 */
class DiscountType
{
    const LABEL_TICKETS_COUNT = 'entity.discount.tickets_count';
    const LABEL_NAME = 'entity.discount.name';
    const LABEL_DESCRIPTION = 'entity.discount.description';
    const LABEL_DATE_STARTS = 'entity.discount.date_starts';
    const LABEL_DATE_ENDS = 'entity.discount.date_ends';
    const LABEL_STATUS = 'admin.status.general_label';
    const LABEL_DISCOUNT_STRATEGY = 'admin.discount.strategy';
    const LABEL_DISCOUNT_TYPE = 'admin.discount.type';
    const LABEL_DISCOUNT_SCOPE = 'admin.discount.scope';
    const LABEL_LOCALE = 'admin.entity.locale.general_label';
    const LABEL_DISCOUNT_TYPE_MULTIPLE = 'admin.discount.multiple';
    const LABEL_DISCOUNT_TYPE_UPLOAD_FILE = 'admin.discount.upload_file';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @TicketAdmin\ListedColumn("admin.index.entity.id")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="tickets_count", type="integer", nullable=true)
     * @TicketAdmin\ListedColumn("entity.discount.tickets_count")
     */
    private $ticketsCount;

    /**
     * @var string
     *
     * @GEDMO\Translatable
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @TicketAdmin\ListedColumn("entity.discount.name")
     */
    private $name;

    /**
     * @var string
     *
     * @GEDMO\Translatable
     * @ORM\Column(name="description", type="string", length=511, nullable=true)
     * @TicketAdmin\ListedColumn("entity.discount.description")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    private $dateCreated;

    /**
     * @var string
     *
     * @ORM\Column(name="date_starts", type="datetime", nullable=false)
     * @TicketAdmin\ListedColumn("entity.discount.date_starts")
     */
    private $dateStarts;

    /**
     * @var string
     *
     * @ORM\Column(name="date_ends", type="datetime", nullable=false)
     * @TicketAdmin\ListedColumn("entity.discount.date_ends")
     */
    private $dateEnds;

    /**
     * @var bool
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     * @TicketAdmin\ListedColumn("admin.status.general_label")
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\OneToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\Discount", mappedBy="discountType")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $discounts;

    /**
     * @var string
     *
     * @ORM\Column(name="strategy", type="string", length=255, nullable=true)
     * @TicketAdmin\ListedColumn("admin.discount.strategy")
     */
    private $discountStrategy;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64, nullable=true)
     * @TicketAdmin\ListedColumn("admin.discount.type")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=64, nullable=true)
     * @TicketAdmin\ListedColumn("admin.discount.scope")
     */
    private $scope;

    /**
     * @var string
     * @GEDMO\Locale
     */
    private $locale;

    /**
     * @var bool
     *
     * @ORM\Column(name="multiple", type="boolean", nullable=false)
     * @TicketAdmin\ListedColumn("admin.discount.multiple")
     */
    private $multiple;

    /**
     * @var TicketType[]
     *
     * @ORM\ManyToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\TicketType", inversedBy="discountTypes")
     * @ORM\JoinTable(name="evp_discount_type_ticket_type")
     */
    private $ticketTypes;

    /**
     * @var UploadedFile
     */
    private $uploadedFile;

    /**
     * Constructs array collections.
     */
    public function __construct() {
        $this->discounts = new ArrayCollection();
        $this->ticketTypes = new ArrayCollection();
        $this->dateStarts = new \DateTime();
        $this->dateEnds = new \DateTime();
    }

    /**
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return TicketType[]
     */
    public function getTicketTypes()
    {
        return $this->ticketTypes;
    }

    /**
     * @param TicketType[] $ticketTypes
     *
     * @return DiscountType
     */
    public function setTicketTypes($ticketTypes)
    {
        $this->ticketTypes = $ticketTypes;
        return $this;
    }

    /**
     * @param TicketType $ticketType
     *
     * @return DiscountType
     */
    public function addTicketType($ticketType)
    {
        if (!$this->ticketTypes->contains($ticketType)) {
            $this->ticketTypes->add($ticketType);
            $ticketType->addDiscountType($this);
        }

        return $this;
    }

    /**
     * @param TicketType $ticketType
     *
     * @return DiscountType
     */
    public function removeTicketType($ticketType)
    {
        if ($this->ticketTypes->removeElement($ticketType)) {
            $ticketType->removeDiscountType($this);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return stripslashes($this->description);
    }

    /**
     * @param string $scope
     * @return self
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param mixed $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $discountStrategy
     * @return self
     */
    public function setDiscountStrategy($discountStrategy)
    {
        $this->discountStrategy = $discountStrategy;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscountStrategy()
    {
        return $this->discountStrategy;
    }

    /**
     * @var int
     *
     * @param $locale
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $dateCreated
     * @return self
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param string $dateEnds
     * @return self
     */
    public function setDateEnds($dateEnds)
    {
        $this->dateEnds = $dateEnds;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateEnds()
    {
        return $this->dateEnds;
    }

    /**
     * @param string $dateStarts
     * @return self
     */
    public function setDateStarts($dateStarts)
    {
        $this->dateStarts = $dateStarts;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateStarts()
    {
        return $this->dateStarts;
    }

    /**
     * @param int $discounts
     * @return self
     */
    public function setDiscounts($discounts)
    {
        $this->discounts = $discounts;

        return $this;
    }

    /**
     * @return Discount[]
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return stripslashes($this->name);
    }

    /**
     * @param bool $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $ticketsCount
     * @return self
     */
    public function setTicketsCount($ticketsCount)
    {
        $this->ticketsCount = $ticketsCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getTicketsCount()
    {
        return $this->ticketsCount;
    }

    /**
     * @ORM\PrePersist
     *
     */
    public function doStuffOnPrePersist()
    {
        $this->dateCreated = new \DateTime;
    }

    /**
     * @return boolean
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * @param boolean $multiple
     *
     * @return DiscountType
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return DiscountType
     */
    public function setUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
        return $this;
    }
}
