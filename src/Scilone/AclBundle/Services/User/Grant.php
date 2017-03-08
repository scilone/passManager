<?php

namespace Scilone\AclBundle\Services\User;

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Scilone\PassManagerBundle\Entity\User;

/**
 * Class Grant
 *
 * @package Scilone\AclBundle\Services\User
 */
class Grant
{
    /**
     * @var Core
     */
    private $core;

    /**
     * Grant constructor.
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
    public function grant(int $attribute, $object, User $user) :bool
    {
        $acl = $this->core->getAcl($object);

        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $acl->insertObjectAce($securityIdentity, $attribute);
        $this->core->updateAcl($acl);

        return true;
    }
}
