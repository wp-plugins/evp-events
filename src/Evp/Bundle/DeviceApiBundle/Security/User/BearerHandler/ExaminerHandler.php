<?php
/**
 * Ticket Examiner authentication handler
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\DeviceApiBundle\Security\User\BearerHandler;

use Evp\Bundle\DeviceApiBundle\Security\User\HandlerAbstract;
use Evp\Bundle\DeviceApiBundle\Security\User\HandlerInterface;

/**
 * Class ExaminerHandler
 * @package Evp\Bundle\DeviceApiBundle\Security\Authentication\Provider\BearerHandler
 */
class ExaminerHandler extends HandlerAbstract implements HandlerInterface {

    /**
     * @var \Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer
     */
    private $examiner;

    /**
     * {@inheritdoc}
     *
     * @param $token
     * @return bool
     */
    public function validate($token) {
        $examiner = $this->entityManager->getRepository('Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer')
            ->findOneBy(
                array(
                    'token' => $token,
                )
            );

        if (!empty($examiner)) {
            $this->examiner = $examiner;
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer
     */
    public function getEntity() {
        return $this->examiner;
    }
}
