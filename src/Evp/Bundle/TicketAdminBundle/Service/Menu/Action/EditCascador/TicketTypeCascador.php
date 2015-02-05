<?php
/**
 * Class for Cascade actions on TicketType Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\EditCascador;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;

/**
 * Class TicketTypeCascador
 */
class TicketTypeCascador extends ActionAbstract implements CascadorInterface
{
    /**
     * @var string
     */
    private $discountTypeClass = 'Evp\Bundle\TicketBundle\Entity\DiscountType';

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\TicketType
     */
    private $ticketType;

    /**
     * @var array
     */
    private $discountsToDelete = array();

    /**
     * @var array
     */
    private $discountsToAdd = array();

    /**
     * Cascades the specific relations
     * @param object $obj
     */
    public function cascade($obj) {
        $this->ticketType = $obj;
        $this->updateTicketTypeDiscountTypes();
    }

    /**
     * Updates TicketTypeDiscounts if needed
     */
    public function updateTicketTypeDiscountTypes()
    {
        if (!$this->checkForDiscountChanges()) {
            return;
        }

        $this->logger->addDebug('discount types to remove:', $this->discountsToDelete);
        foreach ($this->discountsToDelete as $discountId) {
            $discountType = $this->entityManager->getRepository($this->discountTypeClass)
                ->findOneBy(
                    array(
                        'id' => $discountId,
                    )
                );
            $this->ticketType->removeDiscountType($discountType);
        }


        $this->logger->addDebug('discount types to add:', $this->discountsToAdd);
        foreach ($this->discountsToAdd as $discountId) {
            $discountType = $this->entityManager->getRepository($this->discountTypeClass)
                ->findOneBy(
                    array(
                        'id' => $discountId,
                    )
                );
            $this->ticketType->addDiscountType($discountType);
        }

        $this->entityManager->persist($this->ticketType);
        $this->entityManager->flush();
    }

    /**
     * Checks if there are changes in discount mappings from Request compared to existing map
     * @return bool
     */
    private function checkForDiscountChanges()
    {
        $discountTypes = $this->ticketType->getDiscountTypes();

        $existingDiscounts = array();
        foreach ($discountTypes as $type) {
            $existingDiscounts[] = $type->getId();
        }

        $discountsRequest = $this->ticketType->getDiscountTypesChanges();
        $this->discountsToDelete = array_diff($existingDiscounts, $discountsRequest);
        $this->discountsToAdd = array_diff($discountsRequest, $existingDiscounts);

        if (empty($this->discountsToAdd) && empty($this->discountsToDelete)) {
            return false;
        }
        return true;
    }
}
