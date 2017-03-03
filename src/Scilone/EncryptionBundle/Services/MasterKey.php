<?php

namespace Scilone\EncryptionBundle\Services;

use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;

/**
 * Class MasterKey
 *
 * @package Scilone\EncryptionBundle\Services
 */
class MasterKey
{
    const SESSION_NAME_MASTER_KEY = 'masterKey';

    /**
     * @var EntityRepository
     */
    private $masterKeyRepository;

    /**
     * @var string
     */
    private $key;

    /**
     * MasterKey constructor.
     *
     * @param EntityRepository $masterKeyRepository
     */
    public function __construct(EntityRepository $masterKeyRepository)
    {
        $this->masterKeyRepository = $masterKeyRepository;
    }

    /**
     * @return string
     */
    public function get() :string
    {
        if (empty($this->key)) {
            $this->refreshKey();
        }

        return $this->key;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return string|null
     */
    public function getTextCrypt()
    {
        if ($this->checkMasterKeyExist() === false) {
            return null;
        }

        return $this->getMasterKey()->getTextCrypt();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return string|null
     */
    public function getSalt() :string
    {
        if ($this->checkMasterKeyExist() === false) {
            return null;
        }

        return $this->getMasterKey()->getSalt();
    }

    /**
     * Check if master was already init for this app
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return bool
     */
    public function checkMasterKeyExist() :bool
    {
        return $this->getMasterKey() === null ? false : true;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return null|object
     */
    private function getMasterKey()
    {
        return $this->masterKeyRepository->find(1);
    }

    /**
     * refresh the key from the session and return this new
     *
     * @return $this
     */
    private function refreshKey()
    {
        $session = new Session;

        $this->key = $session->get(self::SESSION_NAME_MASTER_KEY);

        return $this;
    }

    /**
     * Check if masterKey is required and if is already registred by current user.
     *
     * @return bool
     */
    public function askMasterKey() :bool
    {
        if ($this->isNeededMasterKey() === false) {
            return false;
        }

        if ($this->isRegisteredMasterKey() === false) {
            return true;
        }

        return false;
    }

    /**
     * @param string $masterKey
     */
    public function setMasterKey(string $masterKey)
    {
        $session = new Session();

        $session->set(self::SESSION_NAME_MASTER_KEY, $masterKey);
    }

    /**
     * @return bool
     */
    private function isRegisteredMasterKey() :bool
    {
        $session = new Session();

        if ($session->has(self::SESSION_NAME_MASTER_KEY)
            && empty($session->get(self::SESSION_NAME_MASTER_KEY)) === false
        ) {
            return true;
        }

        return false;
    }

    /**
     * Only return true for the moment
     *
     * @return bool
     */
    private function isNeededMasterKey() :bool
    {
        return true;
    }

    /**
     * @param string $checksum
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return bool
     */
    public function isValidMasterKey(string $checksum)
    {
        return $this->getMasterKey()->getChecksum() === $checksum;
    }
}
