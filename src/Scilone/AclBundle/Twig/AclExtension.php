<?php

namespace Scilone\AclBundle\Twig;

use Scilone\AclBundle\Services\AclUser;
use Scilone\PassManagerBundle\Entity\User;

/**
 * Class AclExtension
 *
 * @package Scilone\AclBundle\Twig
 */
class AclExtension extends \Twig_Extension
{
    /**
     * @var AclUser
     */
    private $aclUser;

    /**
     * AclExtension constructor.
     *
     * @param AclUser $aclUser
     */
    public function __construct(AclUser $aclUser)
    {
        $this->aclUser = $aclUser;
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
     * @return bool
     */
    public function isGrantedFilter(int $attribute, $object, User $user = null) :bool
    {
        return $this->aclUser->isGranted($attribute, $object, $user);
    }

    /**
     * @return string
     */
    public function getName() :string
    {
        return 'encryption_extension';
    }
}
