<?php
/**
 * General Delete action for Entities
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action;

/**
 * Class DeleteAction
 */
class DeleteAction extends ActionAbstract implements ActionInterface {

    /**
     * @var string
     */
    protected $actionName = 'delete';

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_REDIRECT;

    /**
     * Gets the current Router route name
     *
     * @return string
     */
    public function getResponseName() {
        return self::ROUTE_INDEX;
    }

    /**
     * Sets Parameters for Action
     *
     * @param array $params
     * @return self
     */
    public function setParameters($params) {
        $this->fqcn = $params['fqcn'];
        return $this;
    }

    /**
     * Builds template parameters by FQCN
     *
     * @return mixed
     */
    public function buildResponseParameters() {
        $formObject = $this->entityManager->getRepository($this->fqcn)
            ->findOneBy(
                array(
                    'id' => $this->targetId,
                )
            );
        try {
            $this->entityManager->remove($formObject);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->logger->addDebug('Unable to delete record ', array($e->getMessage(), $formObject));
        }
        return array(
            'menu' => $this->shortClassName
        );
    }
}
