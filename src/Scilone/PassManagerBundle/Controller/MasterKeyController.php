<?php

namespace Scilone\PassManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Scilone\EncryptionBundle\Form\MasterKeyType;
use Scilone\EncryptionBundle\Entity\MasterKey;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AccountController
 *
 * @package Scilone\PassManagerBundle\Controller
 */
class MasterKeyController extends Controller
{

    public function setAction(Request $request)
    {
        //si oui -> form pour entrer la clé
            //check si la clé est valide
            //si oui -> redirection home
            //si non -> form pour entrer la clé

        $em                  = $this->getDoctrine()->getManager();
        $repositoryMasterKey = $em->getRepository('SciloneEncryptionBundle:MasterKey');
        $serviceGenerator    = $this->get('scilone_generator.service');
        $serviceEncryption   = $this->get('scilone_encryption.service');
        $serviceMasterKey    = $this->get('scilone_encryption.master_key');

        //masterKey not found
        if ($repositoryMasterKey->find(1) === null) {
            //@TODO refacto this shit, too long
            $masterKey = new MasterKey;
            $masterKey->setSalt($serviceGenerator->getSalt());

            $form = $this->createForm(MasterKeyType::class, $masterKey);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $masterKey = $form->getData();

                //push master key on session
                $masterKeyTmp = $masterKey->getTextCrypt();
                $serviceMasterKey->setMasterKey($masterKeyTmp);

                //crypt random text with the master key
                $textOriginal = $serviceGenerator->getSalt();
                $textCrypted  = $serviceEncryption->crypt($textOriginal, $masterKey->getSalt());

                if ($serviceEncryption
                        ->isValidEncryption($textOriginal, $textCrypted, $masterKey->getSalt()) === true
                ) {
                    $masterKey
                        ->setChecksum($serviceEncryption->getKeyHash($textOriginal))
                        ->setTextCrypt($textCrypted);

                    $em->persist($masterKey);
                    $em->flush();

                    return $this->redirectToRoute(
                        'scilone_pass_manager_account_homepage',
                        [],
                        Response::HTTP_SEE_OTHER
                    );
                } else {
                    //invalid the master key
                    $serviceMasterKey->setMasterKey('');
                    //@TODO trans this
                    $form->get('textCrypt')->addError(new FormError('ERROR'));
                }
            }

            return $this->render(
                'SciloneEncryptionBundle:MasterKey:set.html.twig',
                ['form' => $form->createView()]
            );
        } else {
            //
        }
    }
}
