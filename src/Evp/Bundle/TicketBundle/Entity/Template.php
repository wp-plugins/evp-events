<?php
/**
 * Twig template Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as GEDMO;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;

/**
 * UserTemplateEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="evp_templates",
 *          uniqueConstraints={
 *              @ORM\UniqueConstraint(name="unq_idx_evp_templates", columns={"parent_class", "foreign_key", "type", "name"})
 *          },
 *          indexes={
 *              @ORM\Index(name="foreign_key_idx", columns={"foreign_key"})
 *          }
 * )
 * @GEDMO\TranslationEntity(class="Evp\Bundle\TicketBundle\Entity\Translation")
 * @ORM\HasLifecycleCallbacks
 */
class Template {

    const LABEL_LOCALE = 'admin.entity.locale.general_label';
    const LABEL_SOURCE = 'admin.template.entity.source';
    const LABEL_NAME = 'admin.template.entity.template_name';
    const LABEL_TYPE = 'admin.template.entity.template_type';
    const LABEL_SUBJECT = 'admin.template.entity.template_subject';
    const LABEL_ATTACHMENT_NAME = 'admin.template.entity.template_attachment_name';
    const LABEL_FROM_EMAIL = 'admin.template.entity.template_from_email';

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
     * @ORM\Column(name="parent_class", type="string", length=255, nullable=false)
     */
    private $parentClass;

    /**
     * @var int
     *
     * @ORM\Column(name="foreign_key", type="string", length=255, nullable=false)
     */
    private $foreignKey;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @TicketAdmin\ListedColumn("admin.index.entity.template_name")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=63, nullable=false)
     * @TicketAdmin\ListedColumn("admin.index.entity.template_type")
     */
    private $type;

    /**
     * @var string
     *
     * @GEDMO\Locale
     */
    private $locale;

    /**
     * @var string
     *
     * @GEDMO\Translatable
     * @ORM\Column(name="source", type="text", nullable=false)
     * @TicketAdmin\ListedColumn("admin.index.entity.template_source")
     */
    private $source;

    /**
     * @var string
     *
     * @GEDMO\Translatable
     * @ORM\Column(name="from_email", type="string", length=127, nullable=true)
     * @TicketAdmin\ListedColumn("admin.index.entity.template_from_email")
     */
    private $fromEmail;

    /**
     * @var string
     *
     * @GEDMO\Translatable
     * @ORM\Column(name="subject", type="string", length=127, nullable=true)
     * @TicketAdmin\ListedColumn("admin.index.entity.template_subject")
     */
    private $subject;

    /**
     * @var string
     *
     * @GEDMO\Translatable
     * @ORM\Column(name="attachment_name", type="string", length=127, nullable=true)
     * @TicketAdmin\ListedColumn("admin.index.entity.template_attachment_name")
     */
    private $attachmentName;

    /**
     * @var object
     */
    private $parent;

    /**
     * @param object $parent
     * @return self
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        $this->parentClass = get_class($parent);
        $this->foreignKey = $parent->getId();

        return $this;
    }

    /**
     * @return object
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param string $fromEmail
     * @return self
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return stripslashes($this->fromEmail);
    }

    /**
     * @param string $subject
     * @return self
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return stripslashes($this->subject);
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
     * @param string $locale
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
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
     * @param string $source
     * @return self
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return stripslashes($this->source);
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $attachmentName
     * @return self
     */
    public function setAttachmentName($attachmentName)
    {
        $this->attachmentName = $attachmentName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttachmentName()
    {
        return stripslashes($this->attachmentName);
    }

    /**
     * @param int $foreignKey
     * @return self
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @param int $parentClass
     * @return self
     */
    public function setParentClass($parentClass)
    {
        $this->parentClass = $parentClass;

        return $this;
    }

    /**
     * @return int
     */
    public function getParentClass()
    {
        return $this->parentClass;
    }
}
