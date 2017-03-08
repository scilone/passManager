<?php

namespace Scilone\AclBundle\Services\User;

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Scilone\PassManagerBundle\Entity\User;

/**
 * Class Delete
 *
 * @package Scilone\AclBundle\Services\User
 */
class Delete
{
    /**
     * @var Core
     */
    private $core;

    /**
     * Delete constructor.
     *
     * @param Core $core
     */
    public function __construct(
        Core $core
    ) {
        $this->core = $core;
    }

    /**
     * @param int  $attribute
     * @param      $object
     * @param User $user
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
    public function remove(int $attribute, $object, User $user) :bool
    {
        $acl  = $this->core->getAcl($object);
        $aces = $acl->getObjectAces();

        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        foreach ($aces as $i => $ace) {
            /** @noinspection PhpUndefinedMethodInspection */
            if ($securityIdentity->equals($ace->getSecurityIdentity())) {
                /** @noinspection PhpUndefinedMethodInspection */
                $acl->updateObjectAce($i, $ace->getMask() & ~$attribute);
            }
        }

        $this->core->updateAcl($acl);

        return true;
    }

    /**
     * @param      $object
     * @param User $user
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
    public function removeAllAttributes($object, User $user) :bool
    {
        /** @noinspection PhpUndefinedVariableInspection */
        foreach ($this->core::$maskAuth as $attribute) {
            $this->remove($attribute, $object, $user);
        }

        return true;
    }
}
