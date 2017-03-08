<?php

namespace Scilone\AclBundle\Services\User;

use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Scilone\PassManagerBundle\Entity\User;
use Symfony\Component\Security\Acl\Model\EntryInterface;

/**
 * Class Check
 *
 * @package Scilone\AclBundle\Services\User
 */
class Check
{
    const EQUAL = 'equal';
    const ALL   = 'all';
    const ANY   = 'any';

    /**
     * @var MutableAclProvider
     */
    private $aclProvider;

    /**
     * @var Core
     */
    private $core;

    /**
     * Check constructor.
     *
     * @param MutableAclProvider $aclProvider
     * @param Core               $core
     */
    public function __construct(MutableAclProvider $aclProvider, Core $core)
    {
        $this->aclProvider = $aclProvider;
        $this->core        = $core;
    }

    /**
     * @param int  $attribute
     * @param      $object
     * @param User $user
     *
     * @return bool
     */
    public function isGranted(int $attribute, $object, User $user) :bool
    {
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
     * @param AclInterface         $acl
     * @param int                  $mask
     * @param UserSecurityIdentity $sid
     *
     * @throws NoAceFoundException
     * @throws \RuntimeException
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
     *
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
