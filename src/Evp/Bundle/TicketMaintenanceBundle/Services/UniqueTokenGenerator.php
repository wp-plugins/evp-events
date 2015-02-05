<?php

namespace Evp\Bundle\TicketMaintenanceBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;

/**
 * Generates a unique token, makes sure the token doesn't exist for the specified entity
 *
 * Class UniqueTokenGenerator
 * @package Evp\Bundle\TicketMaintenanceBundle\Services
 */
class UniqueTokenGenerator
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Entity $entity
     * @param string $fieldName
     * @return string
     */
    public function generateForEntityAndField($entity, $fieldName)
    {
        $repository = $this->entityManager->getRepository(get_class($entity));
        $uniqueTokenFound = false;
        $token = null;

        while (!$uniqueTokenFound) {
            $token = str_replace('.', '!', uniqid(md5(rand()), true));
            $similarTokens = $repository->findBy(
                array($fieldName => $token)
            );

            if (count($similarTokens) == 0) {
                break;
            }
        }

        return $token;
    }
} 