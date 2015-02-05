<?php

namespace Evp\Bundle\TicketBundle\Entity\Seat;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Area
 *
 * @ORM\Table(name="evp_seats_area")
 * @ORM\Entity
 */
class Area
{
    const LABEL_COLUMN = 'admin.entity.seat.area.column';
    const LABEL_ROW = 'admin.entity.seat.area.row';
    const LABEL_SHAPE_TEMPLATE = 'admin.entity.seat.area.shape_template';
    const LABEL_SHAPE_OFFSET_X = 'admin.entity.seat.area.shape_template.offset.x';
    const LABEL_SHAPE_OFFSET_Y = 'admin.entity.seat.area.shape_template.offset.y';
    const LABEL_SHAPE_FILL_COLOR = 'admin.entity.seat.area.shape_fill_color';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="columns", type="integer")
     */
    private $columns;

    /**
     * @var integer
     *
     * @ORM\Column(name="rows", type="integer")
     */
    private $rows;

    /**
     * @var int
     *
     * @ORM\Column(name="parent_class", type="string", length=255, nullable=false)
     */
    private $parentClass;

    /**
     * @var int
     *
     * @ORM\Column(name="foreign_key", type="string", length=5, nullable=false)
     */
    private $foreignKey;

    /**
     * @var string
     *
     * @ORM\Column(name="shape_template", type="string", length=255, nullable=false)
     */
    private $shapeTemplate;

    /**
     * @var int
     *
     * @ORM\Column(name="shape_offset_x", type="integer")
     */
    private $shapeOffsetX;

    /**
     * @var int
     *
     * @ORM\Column(name="shape_offset_y", type="integer")
     */
    private $shapeOffsetY;

    /**
     * @var string
     *
     * @ORM\Column(name="shape_fill_color", type="string", length=15, nullable=false)
     */
    private $shapeFillColor;

    /**
     * @var \stdClass
     *
     * @ORM\OneToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\Seat\Matrix", mappedBy="area")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $matrix;

    /**
     * Constructs Area
     */
    public function __construct() {
        $this->matrix = new ArrayCollection;
    }

    /**
     * @param string $shapeFillColor
     * @return Area
     */
    public function setShapeFillColor($shapeFillColor)
    {
        $this->shapeFillColor = $shapeFillColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getShapeFillColor()
    {
        return $this->shapeFillColor;
    }

    /**
     * @param int $shapeOffsetX
     * @return Area
     */
    public function setShapeOffsetX($shapeOffsetX)
    {
        $this->shapeOffsetX = $shapeOffsetX;

        return $this;
    }

    /**
     * @return int
     */
    public function getShapeOffsetX()
    {
        return $this->shapeOffsetX;
    }

    /**
     * @param int $shapeOffsetY
     * @return Area
     */
    public function setShapeOffsetY($shapeOffsetY)
    {
        $this->shapeOffsetY = $shapeOffsetY;

        return $this;
    }

    /**
     * @return int
     */
    public function getShapeOffsetY()
    {
        return $this->shapeOffsetY;
    }

    /**
     * @param string $shapeTemplate
     * @return Area
     */
    public function setShapeTemplate($shapeTemplate)
    {
        $this->shapeTemplate = $shapeTemplate;

        return $this;
    }

    /**
     * @return string
     */
    public function getShapeTemplate()
    {
        return $this->shapeTemplate;
    }

    /**
     * @param \stdClass $matrix
     * @return Area
     */
    public function setMatrix($matrix)
    {
        $this->matrix = $matrix;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getMatrix()
    {
        return $this->matrix;
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
     * Set columns
     *
     * @param integer $columns
     * @return Area
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    
        return $this;
    }

    /**
     * Get columns
     *
     * @return integer 
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set rows
     *
     * @param integer $rows
     * @return Area
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    
        return $this;
    }

    /**
     * Get length
     *
     * @return integer 
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param int $foreignKey
     * @return Area
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
     * @return Area
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
