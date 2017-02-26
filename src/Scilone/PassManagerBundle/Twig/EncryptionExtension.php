<?php

namespace Scilone\PassManagerBundle\Twig;

use Scilone\PassManagerBundle\Services\Encryption;

class EncryptionExtension extends \Twig_Extension
{
    private $encryption;

    public function __construct(Encryption $encryption)
    {
        $this->encryption = $encryption;
    }
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('decrypt', array($this, 'decryptFilter')),
        );
    }

    public function decryptFilter($password, $salt)
    {
        return $this->encryption->decrypt($password, $salt);
    }

    public function getName()
    {
        return 'encryption_extension';
    }
}