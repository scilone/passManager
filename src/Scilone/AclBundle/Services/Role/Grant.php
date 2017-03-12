<?php

namespace Scilone\AclBundle\Services\Role;

use Scilone\AclBundle\Entity\AclRole;
use Doctrine\ORM\EntityManager;

/**
 * Class Grant
 *
 * @package Scilone\AclBundle\Services\Role
 */
class Grant
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function grant(int $attribute, $object, string $role) :bool
    {
        //@TODO throw if object haven't getId()
        $aclRole = new AclRole();
        $aclRole
            ->setMask($attribute)
            ->setObject(get_class($object))
            ->setObjectId($object->getId())
            ->setRole($role);

        $this->entityManager->persist($aclRole);
        $this->entityManager->flush();

        return true;
    }
}
