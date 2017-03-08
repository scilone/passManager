<?php

namespace Scilone\AclBundle\Services\User;

use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Scilone\PassManagerBundle\Entity\User;

/**
 * Class Core
 *
 * @package Scilone\AclBundle\Services\User
 */
class Core
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
     * @var array
     */
    public static $maskAuth = [
        self::MASK_VIEW,
        self::MASK_CREATE,
        self::MASK_EDIT,
        self::MASK_DELETE,
        self::MASK_UNDELETE,
        self::MASK_OPERATOR,
        self::MASK_MASTER,
        self::MASK_OWNER
    ];

    /**
     * @var MutableAclProvider
     */
    private $aclProvider;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * Core constructor.
     *
     * @param MutableAclProvider $aclProvider
     * @param TokenStorage       $tokenStorage
     */
    public function __construct(
        MutableAclProvider $aclProvider,
        TokenStorage $tokenStorage
    ) {
        $this->aclProvider  = $aclProvider;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param User|null $user
     *
     * @return User
     */
    public function getRightUser(User $user = null) :User
    {
        if ($user === null) {
            return $this->tokenStorage->getToken()->getUser();
        }

        return $user;
    }

    /**
     * @param $object
     *
     * @throws AclNotFoundException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     * @throws \Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException
     *
     * @return mixed|AclInterface|MutableAclInterface
     */
    public function getAcl($object)
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($object);

        try {
            return $this->aclProvider->createAcl($objectIdentity);
        } catch (\Exception $e) {
            return $this->aclProvider->findAcl($objectIdentity);
        }
    }

    /**
     * @param int $attribute
     *
     * @return bool
     */
    public function isValidAttribute(int $attribute) :bool
    {
        return in_array($attribute, self::$maskAuth);
    }

    /**
     * @param MutableAclInterface $acl
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function updateAcl(MutableAclInterface $acl)
    {
        return $this->aclProvider->updateAcl($acl);
    }
}
