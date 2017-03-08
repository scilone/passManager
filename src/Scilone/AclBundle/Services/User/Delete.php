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


    public function __construct(
        Core $core
    ) {
        $this->core = $core;
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
    public function removeAllAttributes($object, User $user) :bool
    {
        foreach ($this->core::$maskAuth as $attribute) {
            $this->remove($attribute, $object, $user);
        }

        return true;
    }
}
