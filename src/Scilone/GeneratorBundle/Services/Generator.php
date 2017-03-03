<?php

namespace Scilone\GeneratorBundle\Services;

/**
 * Class Generator
 *
 * @package Scilone\GeneratorBundle\Services
 */
class Generator
{
    /**
     * @return string
     */
    public function getSalt() :string
    {
        return sha1(uniqid(mt_rand(), true));
    }
}
