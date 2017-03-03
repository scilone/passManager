<?php

namespace Scilone\EncryptionBundle\Twig;

use Scilone\EncryptionBundle\Services\Encryption;

/**
 * Class EncryptionExtension
 * @package Scilone\EncryptionBundle\Twig
 */
class EncryptionExtension extends \Twig_Extension
{
    /**
     * @var Encryption
     */
    private $encryption;

    /**
     * EncryptionExtension constructor.
     * @param Encryption $encryption
     */
    public function __construct(Encryption $encryption)
    {
        $this->encryption = $encryption;
    }

    /**
     * @return array
     */
    public function getFilters():array
    {
        return [
            new \Twig_SimpleFilter('decrypt', [$this, 'decryptFilter']),
        ];
    }

    /**
     * @param string $password
     * @param string $salt
     * @return string
     */
    public function decryptFilter(string $password, string $salt):string
    {
        return $this->encryption->decrypt($password, $salt);
    }

    /**
     * @return string
     */
    public function getName():string
    {
        return 'encryption_extension';
    }
}
