<?php

namespace Scilone\EncryptionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MasterKey
 *
 * @ORM\Table(name="master_key")
 * @ORM\Entity(repositoryClass="Scilone\EncryptionBundle\Repository\MasterKeyRepository")
 */
class MasterKey
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
     * @ORM\Column(name="textCrypt", type="text")
     */
    private $textCrypt;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=255)
     */
    private $salt;

    /**
     * @var string
     *
     * @ORM\Column(name="checksum", type="string", length=255)
     */
    private $checksum;


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
     * Set textCrypt
     *
     * @param string $textCrypt
     *
     * @return MasterKey
     */
    public function setTextCrypt($textCrypt)
    {
        $this->textCrypt = $textCrypt;

        return $this;
    }

    /**
     * Get textCrypt
     *
     * @return string
     */
    public function getTextCrypt()
    {
        return $this->textCrypt;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return MasterKey
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set checksum
     *
     * @param string $checksum
     *
     * @return MasterKey
     */
    public function setChecksum($checksum)
    {
        $this->checksum = $checksum;

        return $this;
    }

    /**
     * Get checksum
     *
     * @return string
     */
    public function getChecksum()
    {
        return $this->checksum;
    }
}
