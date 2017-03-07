<?php

namespace Scilone\EncryptionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MasterKeyController
 *
 * @package Scilone\EncryptionBundle\Controller
 */
class EncryptionController extends Controller
{
    /**
     * @param string $text urlencode
     * @param string $salt
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     *
     * @return JsonResponse
     */
    public function xhrDecryptAction(string $text, string $salt)
    {
        return new JsonResponse(
            $this->get('scilone_encryption.service')->decrypt(urldecode($text), $salt)
        );
    }
}
