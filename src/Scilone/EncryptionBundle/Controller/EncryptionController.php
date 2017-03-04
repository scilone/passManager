<?php

namespace Scilone\EncryptionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Scilone\EncryptionBundle\Form\MasterKeyType;
use Scilone\EncryptionBundle\Entity\MasterKey;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;

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
