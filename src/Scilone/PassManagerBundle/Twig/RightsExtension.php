<?php

namespace Scilone\PassManagerBundle\Twig;

use Scilone\AclBundle\Services\User\Manager;
use Scilone\PassManagerBundle\Entity\User;

/**
 * Class RightsExtension
 *
 * @package Scilone\PassManagerBundle\Twig
 */
class RightsExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction(
                'getSelectUserRights',
                [$this, 'getSelectUserRightsFilter'],
                array('is_safe' => array('html'))
            )
        ];
    }

    /**
     * @param User $user
     *
     * @return string
     */
    public function getSelectUserRightsFilter(User $user) :string
    {
        return 1;
        //$this->manager->isGranted($this->manager::)
    }

    /**
     * @return string
     */
    public function getName() :string
    {
        return 'rights_extension';
    }
}
