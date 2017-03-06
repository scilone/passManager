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
    public function xhrDecryptAction(Request $request, string $salt)
    {
        $text = $request->get('text');
        return new JsonResponse($this->get('scilone_encryption.service')->decrypt($text, $salt));
    }
}
