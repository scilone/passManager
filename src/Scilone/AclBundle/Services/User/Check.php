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
use Scilone\AclBundle\Services\User\Core;

/**
 * Class Check
 *
 * @package Scilone\AclBundle\Services\User
 */
class Check
{
    /**
     * @var MutableAclProvider
     */
    private $aclProvider;

    /**
     * @var Core
     */
    private $core;


    public function __construct(MutableAclProvider $aclProvider, Core $core) {
        $this->aclProvider = $aclProvider;
        $this->core = $core;
    }

    /**
     * @param int       $attribute
     * @param           $object
     * @param User|null $user
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
            case $this->core::ALL:
                return $requiredMask <= $ace->getMask();
            case $this->core::ANY:
                return 0 !== ($ace->getMask() & $requiredMask);
            case $this->core::EQUAL:
                return $requiredMask === $ace->getMask();
            default:
                throw new \RuntimeException(
                    sprintf('The strategy "%s" is not supported.', $ace->getStrategy())
                );
        }
    }
}
