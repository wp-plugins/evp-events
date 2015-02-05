<?php
/**
 * SeatManager for Event seat management tasks
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Seat\Area;
use Evp\Bundle\TicketBundle\Entity\Seat\Matrix;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;
use Evp\Bundle\TicketBundle\Entity\User;
use Evp\Bundle\TicketBundle\Exception\SeatTogglingException;

/**
 * Class SeatManager
 */
class SeatManager extends ManagerAbstract {

    const DEFAULT_STATUS = Matrix::STATUS_FREE;
    const DEFAULT_VISIBILITY = true;
    const SEAT_ENTITY = 'Evp\Bundle\TicketBundle\Entity\Seat\Matrix';
    const ORDER_DETAILS_ENTITY = 'Evp\Bundle\TicketBundle\Entity\Step\OrderDetails';

    const RESULT_OK = 'ok';
    const RESULT_FAIL = 'fail';
    const STATUS_OK = 200;
    const STATUS_FAIL = 409;

    /**
     * @var array
     */
    private $oppositeDimensions = array(
        'col' => 'row',
        'row' => 'col',
    );

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Event
     */
    private $event;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Seat\Area
     */
    private $area;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\User
     */
    private $user;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Seat\Matrix
     */
    private $seat;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * Sets the current Event
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $event
     *
     * @return self
     */
    public function setEvent(Event $event) {
        $this->event = $event;
        return $this;
    }

    /**
     * Sets current user
     *
     * @param User $user
     * @return self
     */
    public function setUser(User $user) {
        $this->user = $user;
        return $this;
    }

    /**
     * Returns last status code
     *
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Gets Seat Entity
     *
     * @param int $id
     * @return self
     */
    public function setSeatId($id) {
        $this->seat = $this->entityManager->getRepository(self::SEAT_ENTITY)
            ->findOneBy(
                array(
                    'id' => $id,
                )
            );
        return $this;
    }

    /**
     * Frees the Seats by OrderDetails
     *
     * @param OrderDetails $orderDetails
     */
    public function freeSeatsByOrderDetails(OrderDetails $orderDetails) {
        $seats = $this->entityManager->getRepository(self::SEAT_ENTITY)
            ->findBy(
                array(
                    'orderDetails' => $orderDetails,
                )
            );
        foreach ($seats as $seat) {
            $seat->setOrderDetails(null);
            $seat->setStatus(Matrix::STATUS_FREE);
            $this->entityManager->persist($seat);
        }
        $this->entityManager->flush();
    }

    /**
     * Tries to reserve current Seat for current User
     *
     * @return string
     */
    public function reserveSeat() {
        try {
            $this->logger->addDebug('Trying to reserve seat for user', array($this->seat, $this->user));
            $this->toggleSeatReservation();
        } catch (SeatTogglingException $e) {
            $this->logger->addDebug('Seat reservation failed', array($this->seat, $this->user, $e));
            $this->statusCode = self::STATUS_FAIL;
            return self::RESULT_FAIL;
        }
        $this->updateOrderDetails();
        $this->statusCode = self::STATUS_OK;
        return self::RESULT_OK;
    }

    /**
     * Resizes the SeatMatrix by given SeatArea
     *
     * @param Area $area
     */
    public function resizeMatrix(Area $area) {
        $this->area = $area;
        $repo = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Seat\Matrix');
        $cols = (int)$repo->getAreaMatrixDimension($area, Matrix::MATRIX_COL);
        $rows = (int)$repo->getAreaMatrixDimension($area, Matrix::MATRIX_ROW);

        if (empty($cols) || empty($rows)) {
            $this->createMatrix();
            return;
        }
        $this->logger->addDebug('Seat Matrix resize request', array($cols, $rows, $area->getColumns(), $area->getRows(), $this->area));
        $this->resizeDimension($area->getColumns(), $cols, $rows, Matrix::MATRIX_ROW);
        $this->resizeDimension($area->getRows(), $rows, $cols, Matrix::MATRIX_COL);
    }

    /**
     * Resizes given Matrix dimension
     *
     * @param int $newSize          new dimension size
     * @param int $curSize          current dimension size
     * @param int $opSize           opposite dimension size
     * @param string $dimension     dimension name
     */
    private function resizeDimension($newSize, $curSize, $opSize, $dimension) {
        if ($newSize === $curSize) {
            return;
        }
        if ($newSize > $curSize) {
            $this->increase($curSize, $newSize, $opSize, $dimension);
            return;
        }
        if ($newSize < $curSize) {
            $this->reduce($newSize, $this->oppositeDimensions[$dimension]);
            return;
        }
    }

    /**
     * Reduces the SeatMatrix
     *
     * @param int $to               new size of dimension
     * @param string $dimension     dimension name
     */
    private function reduce($to, $dimension) {
        $this->logger->addDebug('reducing Seat Matrix', array($to, $dimension, $this->area));
        foreach($this->area->getMatrix() as $matrix) {
            $dimIndex = call_user_func(array($matrix, 'get' .ucfirst($dimension)));
            if ($dimIndex <= $to) {
                continue;
            } else {
                $this->entityManager->remove($matrix);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Increases the SeatMatrix
     *
     * @param int $from             current size of dimension
     * @param int $to               new size of dimension
     * @param int $opSize           opposite dimension size
     * @param string $dimension     dimension name
     */
    private function increase($from, $to, $opSize, $dimension) {
        $this->logger->addDebug('increasing Seat Matrix', array($to, $dimension, $this->area));
        for($i = $from + 1; $i < $to + 1; $i++) {
            for($j = 1; $j < $opSize + 1; $j++) {
                $matrix = new Matrix;
                $matrix
                    ->setArea($this->area)
                    ->setVisible(self::DEFAULT_VISIBILITY)
                    ->setStatus(self::DEFAULT_STATUS);
                call_user_func(array($matrix, 'set' .ucfirst($dimension)), $j);
                call_user_func(array($matrix, 'set' .ucfirst($this->oppositeDimensions[$dimension])), $i);
                $this->entityManager->persist($matrix);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Creates new SeatMatrix by SeatArea
     */
    private function createMatrix() {
        $this->logger->addDebug('creating new Seat Matrix', array($this->area->getColumns(), $this->area->getRows(), $this->area));
        for($i = 1; $i < $this->area->getRows() + 1; $i++) {
            for($j = 1; $j < $this->area->getColumns() + 1; $j++) {
                $matrix = new Matrix;
                $matrix
                    ->setRow($i)
                    ->setCol($j)
                    ->setArea($this->area)
                    ->setVisible(self::DEFAULT_VISIBILITY)
                    ->setStatus(self::DEFAULT_STATUS);
                $this->entityManager->persist($matrix);
            }
        }
        $this->entityManager->flush();
    }


    /**
     * Toggles Seat Status for current user
     */
    private function toggleSeatReservation() {
        $statusBefore = $this->seat->getStatus();
        if ($this->seat->getOrderDetails()) {
            if ($this->seat->getOrderDetails()->getUser()->getId() == $this->user->getId()) {
                if ($this->seat->getStatus() === Matrix::STATUS_FREE) {
                    $this->seat->setStatus(Matrix::STATUS_RESERVED);
                } elseif ($this->seat->getStatus() === Matrix::STATUS_RESERVED) {
                    $this->seat->setStatus(Matrix::STATUS_FREE);
                }
            }
        } else {
            if ($this->seat->getStatus() === Matrix::STATUS_FREE) {
                $this->seat->setStatus(Matrix::STATUS_RESERVED);
            } elseif ($this->seat->getStatus() === Matrix::STATUS_RESERVED) {
                $this->seat->setStatus(Matrix::STATUS_FREE);
            }
        }
        $this->entityManager->flush($this->seat);
        $statusAfter = $this->seat->getStatus();
        if ($statusAfter === $statusBefore) {
            throw new SeatTogglingException;
        }
    }

    /**
     * Updates or creates OrderDetails for given Seat
     */
    private function updateOrderDetails() {
        $orderDetails = $this->seat->getOrderDetails();
        if ($orderDetails) {
            if ($this->seat->getStatus() === Matrix::STATUS_FREE) {
                $orderDetails->setTicketsCount($orderDetails->getTicketsCount() - 1);
                $this->seat->setOrderDetails(null);
            }
            if ($this->seat->getStatus() === Matrix::STATUS_RESERVED) {
                $orderDetails->setTicketsCount($orderDetails->getTicketsCount() + 1);
            }

            if ($orderDetails->getTicketsCount() < 1) {
                $this->entityManager->remove($orderDetails);
                $this->seat->setOrderDetails(null);
            } else {
                $this->entityManager->persist($orderDetails);
            }
        } else {
            $orderDetails = $this->findExistingOrderDetails();
            if ($orderDetails) {
                $this->seat->setOrderDetails($orderDetails);
                $orderDetails->setTicketsCount($orderDetails->getTicketsCount() + 1);
                $this->entityManager->persist($orderDetails);
            } else {
                $orderDetails = $this->createOrderDetails();
                $this->seat->setOrderDetails($orderDetails);
            }
        }
        $this->entityManager->persist($this->seat);
        $this->entityManager->flush();
    }

    /**
     * Creates new OderDetails Entity
     *
     *  @return \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails
     */
    private function createOrderDetails() {
        $ticketType = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\TicketType')
            ->findOneBy(
                array(
                    'id' => $this->seat->getArea()->getForeignKey(),
                )
            );
        $od = self::ORDER_DETAILS_ENTITY;
        $orderDetails = new $od;

        $orderDetails
            ->setOrder($this->user->getOrder())
            ->setEvent($ticketType->getEvent())
            ->setUser($this->user)
            ->setTicketType($ticketType)
            ->setTicketsCount(1);
        $this->entityManager->persist($orderDetails);
        $this->entityManager->flush();
        return $orderDetails;
    }

    /**
     * Tries to find already existing OrderDetails
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails|null
     */
    private function findExistingOrderDetails() {
        $ticketType = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\TicketType')
            ->findOneBy(
                array(
                    'id' => $this->seat->getArea()->getForeignKey(),
                )
            );
        return $this->entityManager->getRepository(self::ORDER_DETAILS_ENTITY)
            ->findOneBy(
                array(
                    'order' => $this->user->getOrder(),
                    'user' => $this->user,
                    'ticketType' => $ticketType,
                    'event' => $ticketType->getEvent(),
                )
            );
    }
}
