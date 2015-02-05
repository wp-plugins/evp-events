<?php

namespace Evp\Bundle\TicketBundle\Entity\Seat;

use Doctrine\ORM\Mapping as ORM;

/**
 * Matrix
 *
 * @ORM\Table(name="evp_seats_matrix",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="unq_idx_evp_seats_matrix", columns={"area_id", "row", "col"}
 *          )
 *      })
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\SeatMatrixRepository")
 */
class Matrix
{
    const STATUS_TAKEN = 'taken';
    const STATUS_FREE = 'free';
    const STATUS_RESERVED = 'reserved';

    const MATRIX_COL = 'col';
    const MATRIX_ROW = 'row';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \stdClass
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Seat\Area", inversedBy="matrix")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $area;

    /**
     * @var integer
     *
     * @ORM\Column(name="row", type="integer")
     */
    private $row;

    /**
     * @var integer
     *
     * @ORM\Column(name="col", type="integer")
     */
    private $col;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    private $visible;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=63)
     */
    private $status;

    /**
     * @var \stdClass
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Step\OrderDetails")
     * @ORM\JoinColumn(name="order_details_id", onDelete="RESTRICT")
     */
    private $orderDetails;

    /**
     * @param \stdClass $orderDetails
     * @return Matrix
     */
    public function setOrderDetails($orderDetails)
    {
        $this->orderDetails = $orderDetails;

        return $this;
    }

    /**
     * @return \stdClass
     */
    public function getOrderDetails()
    {
        return $this->orderDetails;
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
     * Set area
     *
     * @param \stdClass $area
     * @return Matrix
     */
    public function setArea($area)
    {
        $this->area = $area;
    
        return $this;
    }

    /**
     * Get area
     *
     * @return \stdClass 
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set row
     *
     * @param integer $row
     * @return Matrix
     */
    public function setRow($row)
    {
        $this->row = $row;
    
        return $this;
    }

    /**
     * Get row
     *
     * @return integer 
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Set col
     *
     * @param integer $col
     * @return Matrix
     */
    public function setCol($col)
    {
        $this->col = $col;
    
        return $this;
    }

    /**
     * Get col
     *
     * @return integer 
     */
    public function getCol()
    {
        return $this->col;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     * @return Matrix
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    
        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Matrix
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }
}
