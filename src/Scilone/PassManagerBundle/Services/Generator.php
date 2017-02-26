<?php

namespace Scilone\PassManagerBundle\Services;

/**
 * Class Generator
 * @package Scilone\PassManagerBundle\Services
 */
class Generator {

    public function getSalt()
    {
        return sha1(uniqid(mt_rand(), true));
    }
}
