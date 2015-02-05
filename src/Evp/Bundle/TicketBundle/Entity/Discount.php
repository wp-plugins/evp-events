<?php
/**
 * Discount Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;

/**
 * Discount
 *
 * @ORM\Table(name="evp_discounts", uniqueConstraints={
 *              @ORM\UniqueConstraint(name="unq_idx_evp_discounts_token", columns={"token"})
 *          })
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\DiscountRepository")
 */
class Discount
{
    const LABEL_DISCOUNT_TYPE = 'entity.discount.discount_type';
    const LABEL_TOKEN = 'entity.discount.token';
    const LABEL_VALUE = 'entity.discount.value';
    const LABEL_NAME = 'entity.discount.name';

    const STATUS_USED = 'used';
    const STATUS_AVAILABLE = 'available';


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
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\DiscountType", inversedBy="discounts")
     * @ORM\JoinColumn(name="discount_type_id", referencedColumnName="id", onDelete="RESTRICT")
     * @TicketAdmin\ListedColumn("entity.discount.discount_type")
     */
    private $discountType;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     * @TicketAdmin\ListedColumn("entity.discount.token")
     */
    private $token;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="decimal", scale=4, precision=10, nullable=false)
     * @TicketAdmin\ListedColumn("entity.discount.value")
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=true)
     * @TicketAdmin\ListedColumn("entity.discount.name")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64)
     * @TicketAdmin\ListedColumn("entity.discount.status")
     */
    private $status;

    public function __construct()
    {
        $this->status = self::STATUS_AVAILABLE;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Discount
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
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
     * @param DiscountType $discountType
     * @return self
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;

        return $this;
    }

    /**
     * @return DiscountType
     */
    public function getDiscountType()
    {
        return $this->discountType;
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
     * @param string $token
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param float $value
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @Assert\True(message = "message.error.discount_negative")
     */
    public function isTicketsCountPositive() {
        if (intval($this->value) < 0 ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @Assert\True(message = "message.error.discount_not_integer")
     */
    public function isTicketsCountInteger() {
        if (!is_numeric($this->value)) {
            return false;
        } else {
            return true;
        }
    }
}
