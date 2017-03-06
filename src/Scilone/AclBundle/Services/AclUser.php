<?php

namespace Scilone\AclBundle\Services;

use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\Entry;
use Scilone\PassManagerBundle\Entity\User;

/**
 * Class Encryption
 *
 * @package Scilone\AclBundle\Services
 */
class AclUser
{
    const MASK_VIEW     = MaskBuilder::MASK_VIEW;
    const MASK_CREATE   = MaskBuilder::MASK_CREATE;
    const MASK_EDIT     = MaskBuilder::MASK_EDIT;
    const MASK_DELETE   = MaskBuilder::MASK_DELETE;
    const MASK_UNDELETE = MaskBuilder::MASK_UNDELETE;
    const MASK_OPERATOR = MaskBuilder::MASK_OPERATOR;
    const MASK_MASTER   = MaskBuilder::MASK_MASTER;
    const MASK_OWNER    = MaskBuilder::MASK_OWNER;

    private static $maskAuth = [
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
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * AclUser constructor.
     *
     * @param MutableAclProvider   $aclProvider
     * @param AuthorizationChecker $authorizationChecker
     * @param TokenStorage         $tokenStorage
     */
    public function __construct(MutableAclProvider $aclProvider, AuthorizationChecker $authorizationChecker, TokenStorage $tokenStorage)
    {
        $this->aclProvider          = $aclProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage         = $tokenStorage;
    }

    /**
     * @param string    $attribute
     * @param           $object
     * @param User|null $user
     *
     * @return bool
     */
    public function isGranted(int $attribute, $object, User $user = null) :bool
    {
        if ($this->isValidAttribute($attribute)) {
            return false;
        }

        if ($user !== null) {
            return $this->isGrantedUser($attribute, $object, $user);
        }

        try {
            return $this->authorizationChecker->isGranted($attribute, $object);
        } catch (AuthenticationCredentialsNotFoundException $authenticationCredentialsNotFoundException) {
            return false;
        }
    }

    /**
     * @param string $attribute
     * @param        $object
     * @param User   $user
     *
     * @return bool
     */
    private function isGrantedUser(string $attribute, $object, User $user) :bool
    {
        if ($this->isValidAttribute($attribute)) {
            return false;
        }

        try {
            $objectIdentity   = ObjectIdentity::fromDomainObject($object);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);
        } catch (\Exception $exception) {
            return false;
        }

        try {
            $acl = $this->aclProvider->findAcl($objectIdentity, [$securityIdentity]);
        } catch (AclNotFoundException $aclNotFoundException) {
            return false;
        } catch (\RuntimeException $runtimeException) {
            return false;
        }

        if (!is_int($attribute)) {
            $builder = new MaskBuilder;

            try {
                $builder->add($attribute);
            } catch (\InvalidArgumentException $invalidArgumentException) {
                return false;
            }

            $attribute = $builder->get();
        }

        try {
            return $acl->isGranted([$attribute], [$securityIdentity], false);
        } catch (NoAceFoundException $e) {
            return false;
        }
    }


    public function grant(int $attribute, $object, User $user = null) :bool
    {
        if ($this->isValidAttribute($attribute)) {
            return false;
        }

        $user = $this->getRightUser($user);

        if ($this->isGranted($attribute, $object, $user) === true) {
            return true;
        }

        $acl = $this->getAcl($object);

        // retrieving the security identity of the currently logged-in user
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        // grant owner access
        $acl->insertObjectAce($securityIdentity, $attribute);
        $this->aclProvider->updateAcl($acl);

        return true;
    }

    private function getRightUser($user = null)
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
    private function getAcl($object)
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($object);

        try {
            return $this->aclProvider->createAcl($objectIdentity);
        } catch (\Exception $e) {
            return $this->aclProvider->findAcl($objectIdentity);
        }
    }

    public function remove(int $attribute, $object, User $user = null)
    {
        if ($this->isValidAttribute($attribute)) {
            return false;
        }

        $acl  = $this->getAcl($object);
        $aces = $acl->getObjectAces();

        $user = $this->getRightUser($user);
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        foreach ($aces as $i => $ace) {
            if ($securityIdentity->equals($ace->getSecurityIdentity())) {
                $this->revokeMask($i, $acl, $ace, $attribute);
            }
        }

        $this->aclProvider->updateAcl($acl);

        return true;
    }

    /**
     * @param int   $index
     * @param Acl   $acl
     * @param Entry $ace
     * @param int   $attribute
     *
     * @throws \OutOfBoundsException
     *
     * @return bool
     */
    private function revokeMask(int $index, Acl $acl, Entry $ace, int $attribute) :bool
    {
        $acl->updateObjectAce($index, $ace->getMask() & ~$attribute);

        return true;
    }

    /**
     * @param int $attribute
     *
     * @return bool
     */
    private function isValidAttribute(int $attribute) :bool
    {
        return in_array($attribute, self::$maskAuth);
    }
}
