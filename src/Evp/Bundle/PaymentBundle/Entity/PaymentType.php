<?php

namespace Evp\Bundle\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentType
 */
class PaymentType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $logoUrl;

    /**
     * @var string
     */
    private $handlerClass;

    /**
     * Set name
     *
     * @param string $name
     * @return self
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
     * Set title
     *
     * @param string $title
     * @return static
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set logoUrl
     *
     * @param string $logoUrl
     * @return static
     */
    public function setLogoUrl($logoUrl)
    {
        $this->logoUrl = $logoUrl;
    
        return $this;
    }

    /**
     * Get logoUrl
     *
     * @return string 
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * @param string $handlerClass
     * @return static
     */
    public function setHandlerClass($handlerClass)
    {
        $this->handlerClass = $handlerClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getHandlerClass()
    {
        return $this->handlerClass;
    }

    /**
     * Sets the ISO_3166-1_alpha country code
     *
     * @return self
     */
    public static function create()
    {
        return new self();
    }
}
