<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;

class DoctrineHelper
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param object $entity
     * @return string
     */
    public function getEntityClass($entity)
    {
        return ClassUtils::getClass($entity);
    }

    /**
     * @param object $entity
     * @return array
     * @throws NotManageableEntityException
     */
    public function getEntityIdentifier($entity)
    {
        $entityManager = $this->getEntityManager($entity);
        $metadata = $entityManager->getClassMetadata(ClassUtils::getClass($entity));
        $identifier = $metadata->getIdentifierValues($entity);

        return $identifier;
    }

    /**
     * @param object $entity
     * @return integer
     * @throws WorkflowException
     */
    public function getSingleEntityIdentifier($entity)
    {
        $entityIdentifier = $this->getEntityIdentifier($entity);
        if (count($entityIdentifier) != 1) {
            throw new WorkflowException('Can\'t get single identifier for the entity');
        }

        return current($entityIdentifier);
    }

    /**
     * @param object $entity
     * @return bool
     */
    public function isManageableEntity($entity)
    {
        $entityClass = $this->getEntityClass($entity);
        $entityManager = $this->registry->getManagerForClass($entityClass);

        return !empty($entityManager);
    }

    /**
     * @param string $entityOrClass
     * @return EntityManager
     * @throws NotManageableEntityException
     */
    public function getEntityManager($entityOrClass)
    {
        if (is_object($entityOrClass)) {
            $entityClass = $this->getEntityClass($entityOrClass);
        } else {
            $entityClass = $entityOrClass;
        }

        $entityManager = $this->registry->getManagerForClass($entityClass);
        if (!$entityManager) {
            throw new NotManageableEntityException($entityClass);
        }

        return $entityManager;
    }

    /**
     * @param string $entityClass
     * @param mixed $entityId
     * @return mixed
     * @throws NotManageableEntityException
     */
    public function getEntityReference($entityClass, $entityId)
    {
        $entityManager = $this->getEntityManager($entityClass);
        return $entityManager->getReference($entityClass, $entityId);
    }
}
