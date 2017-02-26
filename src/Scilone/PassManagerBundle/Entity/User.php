<?php

namespace Scilone\PassManagerBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="checksum", type="string", nullable=true)
     */
    private $checksum;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // your own logic
    }


}

