<?php

namespace Scilone\AclBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AclRole
 *
 * @ORM\Table(name="acl_role")
 * @ORM\Entity(repositoryClass="Scilone\AclBundle\Repository\AclRoleRepository")
 */
class AclRole
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=255)
     */
    private $role;

    /**
     * @var int
     *
     * @ORM\Column(name="mask", type="integer")
     */
    private $mask;

    /**
     * @var string
     *
     * @ORM\Column(name="object", type="string", length=255)
     */
    private $object;

    /**
     * @var int
     *
     * @ORM\Column(name="object_id", type="integer")
     */
    private $objectId;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return AclRole
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set mask
     *
     * @param integer $mask
     *
     * @return AclRole
     */
    public function setMask($mask)
    {
        $this->mask = $mask;

        return $this;
    }

    /**
     * Get mask
     *
     * @return int
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * Set object
     *
     * @param string $object
     *
     * @return AclRole
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return string
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set objectId
     *
     * @param integer $objectId
     *
     * @return AclRole
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId
     *
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }
}

