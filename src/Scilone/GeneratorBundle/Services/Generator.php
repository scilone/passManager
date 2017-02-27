<?php

namespace Scilone\GeneratorBundle\Services;

/**
 * Class Generator
 * @package Scilone\GeneratorBundle\Services
 */
class Generator {

    public function getSalt()
    {
        return sha1(uniqid(mt_rand(), true));
    }
}
