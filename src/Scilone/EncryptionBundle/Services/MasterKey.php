<?php

namespace Scilone\EncryptionBundle\Services;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class MasterKey
 *
 * @package Scilone\EncryptionBundle\Services
 */
class MasterKey
{
    const SESSION_NAME_MASTER_KEY = 'masterKey';

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
     * @return bool
     */
    private function isNeededMasterKey() :bool
    {
        return true;
    }
}
