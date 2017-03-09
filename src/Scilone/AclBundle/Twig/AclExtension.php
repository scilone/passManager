<?php

namespace Scilone\AclBundle\Twig;

use Scilone\AclBundle\Services\User\Manager;
use Scilone\PassManagerBundle\Entity\User;

/**
 * Class AclExtension
 *
 * @package Scilone\AclBundle\Twig
 */
class AclExtension extends \Twig_Extension
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * AclExtension constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return array
     */
    public function getFunctions() :array
    {
        return [
            new \Twig_SimpleFunction('isAclGranted', [$this, 'isGrantedFilter']),
        ];
    }

    /**
     * @param int       $attribute
     * @param           $object
     * @param User|null $user
     *
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     *
     * @return bool
     */
    public function isGrantedFilter(int $attribute, $object, User $user = null) :bool
    {
        return $this->manager->isGranted($attribute, $object, $user);
    }

    /**
     * @return string
     */
    public function getName() :string
    {
        return 'acl_extension';
    }
}
