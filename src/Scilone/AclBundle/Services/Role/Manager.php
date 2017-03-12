<?php

namespace Scilone\AclBundle\Services\Role;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Class Manager
 *
 * @package Scilone\AclBundle\Services\Role
 */
class Manager
{
    const MASK_VIEW     = MaskBuilder::MASK_VIEW;
    const MASK_CREATE   = MaskBuilder::MASK_CREATE;
    const MASK_EDIT     = MaskBuilder::MASK_EDIT;
    const MASK_DELETE   = MaskBuilder::MASK_DELETE;
    const MASK_UNDELETE = MaskBuilder::MASK_UNDELETE;
    const MASK_OPERATOR = MaskBuilder::MASK_OPERATOR;
    const MASK_MASTER   = MaskBuilder::MASK_MASTER;
    const MASK_OWNER    = MaskBuilder::MASK_OWNER;

    /**
     * @var Grant
     */
    private $grant;

    /**
     * Manager constructor.
     *
     * @param Grant $grant
     */
    public function __construct(
        Grant $grant
    ) {
        //$this->delete = $delete;
        //$this->core   = $core;
        //$this->check  = $check;
        $this->grant  = $grant;
    }

    /**
     * @param int       $attribute
     * @param           $object
     * @param User|null $user
     *
     * @return bool
     */
    public function isGranted(int $attribute, $object, User $user = null) :bool
    {
        if ($this->core->isValidAttribute($attribute) === false) {
            return false;
        }

        $user = $this->core->getRightUser($user);

        return $this->check->isGranted($attribute, $object, $user);
    }

    /**
     * @param           $object
     * @param User|null $user
     *
     * @return int
     */
    public function getMaxGranted($object, User $user = null) :int
    {
        $user = $this->core->getRightUser($user);

        return $this->check->getMaxGranted($object, $user);
    }

    /**
     * @param int       $attribute
     * @param           $object
     * @param User|null $user
     *
     * @throws AclNotFoundException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     * @throws \Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException
     *
     * @return bool
     */
    public function grant(int $attribute, $object, string $role = null) :bool
    {
        /*if ($this->core->isValidAttribute($attribute) === false) {
            return false;
        }
        $user = $this->core->getRightUser($user);

        if ($this->check->getMaxGranted($object, $user) === $attribute) {
            return true;
        }

        // only 1 attribute at the same time for object/user
        $this->delete->removeAllAttributes($object, $user);*/

        return $this->grant->grant($attribute, $object, $role);
    }

    /**
     * @param int       $attribute
     * @param           $object
     * @param User|null $user
     *
     * @throws AclNotFoundException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     * @throws \Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException
     *
     * @return bool
     */
    public function remove(int $attribute, $object, User $user = null) :bool
    {
        if ($this->core->isValidAttribute($attribute) === false) {
            return false;
        }

        $user = $this->core->getRightUser($user);

        return $this->delete->remove($attribute, $object, $user);
    }

    /**
     * @param           $object
     * @param User|null $user
     *
     * @throws AclNotFoundException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     * @throws \Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException
     *
     * @return bool
     */
    public function removeAllAttributes($object, User $user = null) :bool
    {
        $user = $this->core->getRightUser($user);

        if ($this->getMaxGranted($object, $user ) === 0) {
            return true;
        }

        return $this->delete->removeAllAttributes($object, $user);
    }
}
