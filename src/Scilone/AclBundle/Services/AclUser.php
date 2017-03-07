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
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Scilone\PassManagerBundle\Entity\User;
use Symfony\Component\Security\Acl\Model\EntryInterface;

/**
 * Class Encryption
 *
 * @package Scilone\AclBundle\Services
 */
class AclUser
{
    //@TODO refacto class, split set rights and get them

    const MASK_VIEW     = MaskBuilder::MASK_VIEW;
    const MASK_CREATE   = MaskBuilder::MASK_CREATE;
    const MASK_EDIT     = MaskBuilder::MASK_EDIT;
    const MASK_DELETE   = MaskBuilder::MASK_DELETE;
    const MASK_UNDELETE = MaskBuilder::MASK_UNDELETE;
    const MASK_OPERATOR = MaskBuilder::MASK_OPERATOR;
    const MASK_MASTER   = MaskBuilder::MASK_MASTER;
    const MASK_OWNER    = MaskBuilder::MASK_OWNER;

    const EQUAL = 'equal';
    const ALL = 'all';
    const ANY = 'any';

    /**
     * @var array
     */
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
    public function __construct(
        MutableAclProvider $aclProvider,
        AuthorizationChecker $authorizationChecker,
        TokenStorage $tokenStorage
    ) {
        $this->aclProvider          = $aclProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage         = $tokenStorage;
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
        if ($this->isValidAttribute($attribute) === false) {
            return false;
        }

        $user = $this->getRightUser($user);

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

        try {
            return $this->isGrantedAcl($acl, $attribute, $securityIdentity);
        } catch (NoAceFoundException $noAceFoundException) {
            return false;
        } catch (\RuntimeException $runtimeException) {
            return false;
        }
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
        if ($this->isValidAttribute($attribute) === false) {
            return false;
        }

        $user = $this->getRightUser($user);

        if ($this->isGranted($attribute, $object, $user) === true) {
            return true;
        }

        $acl = $this->getAcl($object);

        // retrieving the security identity of the currently logged-in user
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $acl->insertObjectAce($securityIdentity, $attribute);
        $this->aclProvider->updateAcl($acl);

        return true;
    }

    /**
     * @param User|null $user
     *
     * @return User
     */
    private function getRightUser(User $user = null) :User
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
        if ($this->isValidAttribute($attribute) === false) {
            return false;
        }

        $acl  = $this->getAcl($object);
        $aces = $acl->getObjectAces();

        $user = $this->getRightUser($user);
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        foreach ($aces as $i => $ace) {
            /** @noinspection PhpUndefinedMethodInspection */
            if ($securityIdentity->equals($ace->getSecurityIdentity())) {
                /** @noinspection PhpUndefinedMethodInspection */
                $acl->updateObjectAce($i, $ace->getMask() & ~$attribute);
            }
        }

        $this->aclProvider->updateAcl($acl);

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
        $user = $this->getRightUser($user);

        foreach (self::$maskAuth as $attribute) {
            $this->remove($attribute, $object, $user);
        }

        return true;
    }

    /**
     * @param AclInterface         $acl
     * @param int                  $mask
     * @param UserSecurityIdentity $sid
     *
     * @throws NoAceFoundException
     * @throws \RuntimeException
     *
     * @return bool
     */
    private function isGrantedAcl(
        AclInterface $acl,
        int $mask,
        UserSecurityIdentity $sid
    ):bool {
        try {
            $aces = $acl->getObjectAces();

            if ($aces === false) {
                $aces = $acl->getClassAces();
            }

            return $this->hasSufficientPermissions($aces, $mask, $sid);
        } catch (NoAceFoundException $noAceFoundException) {
            $parentAcl = $acl->getParentAcl();

            if ($acl->isEntriesInheriting() && $parentAcl !== null) {
                return $parentAcl->isGranted([$mask], [$sid]);
            }

            throw $noAceFoundException;
        }
    }

    /**
     * @param array                $aces
     * @param int                  $mask
     * @param UserSecurityIdentity $sid
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    private function hasSufficientPermissions(
        array $aces,
        int $mask,
        UserSecurityIdentity $sid
    ):bool {
        $firstRejectedAce = null;

        foreach ($aces as $ace) {
            if ($sid->equals($ace->getSecurityIdentity()) && $this->isAceApplicable($mask, $ace)) {
                if ($ace->isGranting()) {
                    return true;
                }

                break;
            }
        }

        return false;
    }

    /**
     * @param int            $requiredMask
     * @param EntryInterface $ace
     *
     * @throws \RuntimeException
     * @return bool
     */
    private function isAceApplicable(int $requiredMask, EntryInterface $ace) :bool
    {
        switch ($ace->getStrategy()) {
            case self::ALL:
                return $requiredMask <= $ace->getMask();
            case self::ANY:
                return 0 !== ($ace->getMask() & $requiredMask);
            case self::EQUAL:
                return $requiredMask === $ace->getMask();
            default:
                throw new \RuntimeException(
                    sprintf('The strategy "%s" is not supported.', $ace->getStrategy())
                );
        }
    }
}
