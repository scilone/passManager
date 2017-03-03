<?php

namespace Scilone\EncryptionBundle\Services;

/**
 * Class Encryption
 *
 * @package Scilone\EncryptionBundle\Services
 */
class Encryption
{
    const HASH_STRENGTH_WEAK     = 'weak';
    const HASH_STRENGTH_STANDARD = 'standard';
    const HASH_STRENGTH_STRONG   = 'strong';

    /**
     * @var array
     */
    private static $authorizedHashStrength = array(
        self::HASH_STRENGTH_WEAK,
        self::HASH_STRENGTH_STANDARD,
        self::HASH_STRENGTH_STRONG
    );

    /**
     * @var MasterKey
     */
    private $masterKey;

    /**
     * @var string
     */
    private $cipher;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $hashStrength;

    /**
     * @var resource
     */
    private $td;

    /**
     * Encryption constructor.
     *
     * @param MasterKey $masterKey
     * @param string    $cipher
     * @param string    $mode
     * @param string    $hashStrength
     */
    public function __construct(MasterKey $masterKey, string $cipher, string $mode, string $hashStrength)
    {
        if (in_array($hashStrength, self::$authorizedHashStrength) === false) {
            $hashStrength = self::HASH_STRENGTH_STANDARD;
        }

        $this->masterKey    = $masterKey;
        $this->cipher       = $cipher;
        $this->mode         = $mode;
        $this->hashStrength = $hashStrength;

        $this->td = mcrypt_module_open($cipher, '', $mode, '');
    }

    /**
     * @param string $text
     * @param string $salt
     *
     * @return string
     */
    public function crypt(string $text, string $salt) :string
    {
        return base64_encode(
            $this->mcryptEncrypt($text, $salt)
        );
    }

    /**
     * @param string $textCrypt
     * @param string $salt
     *
     * @return string
     */
    public function decrypt(string $textCrypt, string $salt) :string
    {
        return rtrim(
            $this->mcryptDecrypt(base64_decode($textCrypt), $salt)
        );
    }

    /**
     * @param string $text
     * @param string $textCrypt
     * @param string $salt
     *
     * @return bool
     */
    public function isValidEncryption(string $text, string $textCrypt, string $salt) :bool
    {
        return $text === $this->decrypt($textCrypt, $salt);
    }

    /**
     * @param string $salt
     *
     * @return string
     */
    public function getKeyHash(string $salt) :string
    {
        ini_set('memory_limit', '2056M');

        $keyHash = null;
        $masterKey = $this->masterKey->get();

        if ($this->hashStrength === self::HASH_STRENGTH_WEAK) {
            $keyHash = md5(crypt($salt, $salt) . sha1($masterKey));
            for ($i = 0; $i < 10000; $i ++) {
                $keyHash = md5(sha1(crypt($keyHash, $salt)));
            }
        } elseif ($this->hashStrength === self::HASH_STRENGTH_STANDARD) {
            $keyHash = sha1(md5($salt) . crypt($masterKey, $salt));
            for ($i = 0; $i < 50000; $i ++) {
                $keyHash = sha1(md5(crypt($keyHash, $salt)));
            }
        } elseif ($this->hashStrength === self::HASH_STRENGTH_STRONG) {
            $keyHash = sha1(crypt(sha1($salt) . md5($masterKey), $salt));
            for ($i = 0; $i < 100000; $i ++) {
                $keyHash = sha1(crypt(md5(sha1($keyHash)), $salt));
            }
        }

        return $keyHash;
    }

    /**
     * @param string $salt
     *
     * @return string
     */
    private function getKey(string $salt) :string
    {
        return substr(
            $this->getKeyHash($salt),
            0,
            mcrypt_enc_get_key_size($this->td)
        );
    }

    /**
     * @param string $salt
     *
     * @return string
     */
    private function getIv(string $salt) :string
    {
        return substr(
            $this->getKeyHash($salt),
            0,
            mcrypt_enc_get_block_size($this->td)
        );
    }

    /**
     * @param string $text
     * @param string $salt
     *
     * @return string
     */
    private function mcryptEncrypt(string $text, string $salt) :string
    {
        return mcrypt_encrypt(
            $this->cipher,
            $this->getKey($salt),
            $text,
            $this->mode,
            $this->getIv($salt)
        );
    }

    /**
     * @param string $text
     * @param string $salt
     *
     * @return string
     */
    private function mcryptDecrypt(string $text, string $salt) :string
    {
        return mcrypt_decrypt(
            $this->cipher,
            $this->getKey($salt),
            $text,
            $this->mode,
            $this->getIv($salt)
        );
    }
}
