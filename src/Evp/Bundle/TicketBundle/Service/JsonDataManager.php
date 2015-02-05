<?php
/**
 * Manages Json codec tasks for various needs
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;

/**
 * Class JsonDataManager
 * @package Evp\Bundle\TicketBundle\Service
 */
class JsonDataManager extends ManagerAbstract
{

    /**
     * Gets the JSON for updated OrderDetails during AJAX request
     *
     * @param OrderDetails $orderDetails
     *
     * @return string
     */
    public function getJsonForOrderDetailsAjaxUpdate(OrderDetails $orderDetails)
    {
        $dataClass = new \stdClass;
        $ticketRepo = $this->entityManager->getRepository('EvpTicketBundle:Ticket');
        $oneDetailSum = $ticketRepo->sumTicketPriceByOrderDetails($orderDetails);
        $totalDiscountedSum = $ticketRepo->sumTicketPriceByUser($orderDetails->getUser());
        $totalSumBeforeDiscount = 0;
        $allOrderDetails = $this->entityManager->getRepository('EvpTicketBundle:Step\OrderDetails')
            ->findBy(
                array('user' => $orderDetails->getUser())
            );
        foreach ($allOrderDetails as $detail) {
            $totalSumBeforeDiscount += $ticketRepo->sumTicketPriceByOrderDetails($detail);
        }

        $dataClass->oneDetailSum = number_format((float)$oneDetailSum, 2, ',', ' ') . ' ' .$orderDetails->getEvent()->getCurrency();
        $dataClass->totalDiscountedSum = number_format((float)$totalDiscountedSum, 2, ',', ' ') . ' ' .$orderDetails->getEvent()->getCurrency();
        $dataClass->totalSumBeforeDiscount = number_format((float)$totalSumBeforeDiscount, 2, ',', ' ') . ' ' .$orderDetails->getEvent()->getCurrency();
        $dataClass->discountAmount = number_format((float)$totalSumBeforeDiscount - $totalDiscountedSum, 2, ',', ' ') . ' ' .$orderDetails->getEvent()->getCurrency();

        return json_encode($dataClass);
    }
}
