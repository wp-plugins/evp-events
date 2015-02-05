<?php
/**
 * Ajax manager for refreshing the entity based on target locale
 * @author Khalid Hameed <k.hameed@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service;

use Doctrine\ORM\EntityManager;
use \Evp\Bundle\TicketAdminBundle\Service\Ajax;

class AjaxManager
{
    /**
     * @var string[]
     */
    private $subfolderMap = array(
        'FieldSchema' => 'Form',
    );

    /**
     * @var string
     */
    private $classDir = 'Evp\Bundle\TicketBundle\Entity';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * ID Of the entity object to find
     *
     * @var Int
     */
    private $id;

    /**
     * Target locale of the entity object
     *
     * @var String
     */
    private $targetLocale;

    /**
     * @var String
     */
    private $entityClass;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\TicketAdminGedmoAnnotationReader
     */
    private $gedmoAnnotationReader;

    /**
     * Collection of services passed from services.xml
     *
     * @var \Evp\Bundle\TicketAdminBundle\Service\Ajax\AjaxInterface[]
     */
    private $scopes;

    /**
     * Service loaded from services collection based on input service key
     *
     * @var \Evp\Bundle\TicketAdminBundle\Service\Ajax\AjaxInterface
     */
    private $currentScope;

    /**
     * @var string[]
     */
    private $params;

    /**
     * @var string[]
     */
    private $actionSupplements;

    /**
     * Sets required parameters
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Evp\Bundle\TicketAdminBundle\Service\TicketAdminGedmoAnnotationReader $gedmoAnnotationReader
     * @param array $params
     * @param array $actionSupplements
     */
    public function __construct(
        EntityManager $em,
        TicketAdminGedmoAnnotationReader $gedmoAnnotationReader,
        $params,
        $actionSupplements
    ) {
        $this->em = $em;
        $this->gedmoAnnotationReader = $gedmoAnnotationReader;
        $this->params = $params;
        $this->actionSupplements = $actionSupplements;
    }

    /**
     * @param mixed $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @return mixed
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param mixed $targetLocale
     */
    public function setTargetLocale($targetLocale)
    {
        $this->targetLocale = $targetLocale;
    }

    /**
     * @return mixed
     */
    public function getTargetLocale()
    {
        return $this->targetLocale;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getScopeId()
    {
        return $this->scopeId;
    }


    /**
     *
     * @param \Evp\Bundle\TicketAdminBundle\Service\Ajax\AjaxInterface[] $scopes
     */
    public function setScopes($scopes) {
        $this->scopes = $scopes;
    }

    /**
     * Sets the locale and refresh entity
     *
     * @param string $entity
     * @throws \Exception
     * @return array of translated columns.
     */
    public function refreshEntityBasedOnTargetLocale($entity)
    {
        if (!isset($this->targetLocale)) {
            throw new \Exception ('Target locale not set for entity : ' . $this->entityClass);
        }

        $subDir = '\\';
        if (array_key_exists($entity, $this->subfolderMap)) {
            $subDir .= $this->subfolderMap[$entity] . '\\';
        }

        $entityClass = $this->classDir .$subDir .$entity;
        $repo = $this->em->getRepository($entityClass);


        // update locale and get translated columns based in input locale
        $entityObj = $repo->find($this->id);
        $entityObj->setLocale($this->targetLocale);
        $this->em->refresh($entityObj);

        // get translatable columns based on @GEDMO\Translatable annotation in entity class.
        $translatableColumns = $this->gedmoAnnotationReader->getListedColumns(new $entityClass);

        $translatedColumns = array();
        foreach($translatableColumns as $col) {
            $method= 'get' . ucfirst($col);
            $translatedColumns[$col] = stripslashes(call_user_func(array($entityObj, $method)));
        }
        return $translatedColumns;
    }

    /**
     * Sets the scope to the current scope, e.g events
     *
     * @param $scope
     * @return $this
     */

    public function setResponseScope($scope) {
        $this->currentScope = $this->scopes[$scope];
        return $this;
    }

    /**
     * Sets the id of the current scope
     *
     * @param $key
     * @return $this
     */
    public function setResponseScopeKey($key) {
        $this->currentScope->setScopeId($key);
        return $this;
    }

    /**
     * Sets the method of the target Scope (e.g events)
     *
     * @param $target
     * @return $this
     */
    public function setResponseTarget($target) {
        $this->currentScope->setTarget($target);
        return $this;
    }

    /**
     * Returns json response for ajax calls
     *
     * @return mixed|string|void
     */
    public function getResult()
    {
        $this->currentScope->setParams($this->params);
        $this->currentScope->setActionSupplements($this->actionSupplements);

        return json_encode($this->currentScope->getResult());
    }
}
