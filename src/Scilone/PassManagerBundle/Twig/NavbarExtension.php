<?php

namespace Scilone\PassManagerBundle\Twig;

/**
 * Class NavbarExtension
 * @package Scilone\PassManagerBundle\Twig
 */
class NavbarExtension extends \Twig_Extension
{

    /**
     * @return array
     */
    public function getFunctions():array
    {
        return [
            new \Twig_SimpleFunction('activeCurrentTab', [$this, 'activeCurrentTabFilter']),
        ];
    }

    /**
     * @param string $tabRouteId
     * @param string $currentRouteId
     * @return string
     */
    public function activeCurrentTabFilter(string $tabRouteId, string $currentRouteId):string
    {
        return ($tabRouteId === $currentRouteId) ?'active' :'';
    }

    /**
     * @return string
     */
    public function getName():string
    {
        return 'navbar_function';
    }
}
