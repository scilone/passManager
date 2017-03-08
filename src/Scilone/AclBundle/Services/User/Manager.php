<?php

namespace Scilone\AclBundle\Services\User;

use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Scilone\PassManagerBundle\Entity\User;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Scilone\AclBundle\Services\User\Delete;
use Scilone\AclBundle\Services\User\Core;
use Scilone\AclBundle\Services\User\Check;
use Scilone\AclBundle\Services\User\Grant;

/**
 * Class Manager
 *
 * @package Scilone\AclBundle\Services\User
 */
class Manager
{
    const MASK_VIEW     = Core::MASK_VIEW;
    const MASK_CREATE   = Core::MASK_CREATE;
    const MASK_EDIT     = Core::MASK_EDIT;
    const MASK_DELETE   = Core::MASK_DELETE;
    const MASK_UNDELETE = Core::MASK_UNDELETE;
    const MASK_OPERATOR = Core::MASK_OPERATOR;
    const MASK_MASTER   = Core::MASK_MASTER;
    const MASK_OWNER    = Core::MASK_OWNER;

    /**
     * @var Delete
     */
    private $delete;

    /**
     * @var Core
     */
    private $core;

    /**
     * @var Check
     */
    private $check;

    /**
     * @var Grant
     */
    private $grant;

    /**
     * Manager constructor.
     *
     * @param MutableAclProvider   $aclProvider
     * @param AuthorizationChecker $authorizationChecker
     * @param TokenStorage         $tokenStorage
     */
    public function __construct(
        Core $core,
        Delete $delete,
        Check $check,
        Grant $grant
    ) {
        $this->delete = $delete;
        $this->core   = $core;
        $this->check  = $check;
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
    public function grant(int $attribute, $object, User $user = null) :bool
    {
        if ($this->core->isValidAttribute($attribute) === false) {
            return false;
        }

        $user = $this->core->getRightUser($user);

        if ($this->check->isGranted($attribute, $object, $user) === true) {
            return true;
        }

        // only 1 attribute at the same time for object/user
        $this->delete->removeAllAttributes($object, $user);

        return $this->grant->grant($attribute, $object, $user);
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
     * @throws \OutOfBoundsException
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
     * @throws \OutOfBoundsException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     * @throws \Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException
     *
     * @return bool
     */
    public function removeAllAttributes($object, User $user = null) :bool
    {
        $user = $this->core->getRightUser($user);

        return $this->delete->removeAllAttributes($object, $user);
    }
}
