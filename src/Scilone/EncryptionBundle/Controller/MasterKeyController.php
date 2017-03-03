<?php

namespace Scilone\EncryptionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Scilone\EncryptionBundle\Form\MasterKeyType;
use Scilone\EncryptionBundle\Entity\MasterKey;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;

/**
 * Class MasterKeyController
 *
 * @package Scilone\EncryptionBundle\Controller
 */
class MasterKeyController extends Controller
{

    /**
     * @param Request $request
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\OutOfBoundsException
     * @throws \Symfony\Component\Form\Exception\RuntimeException
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @throws \Twig_Error
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \UnexpectedValueException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function setAction(Request $request)
    {
        $form = $this->createFormMasterKey();
        $form->handleRequest($request);

        if ($this->formProcessing($form) === true) {
            return $this->redirectToRoute(
                'scilone_pass_manager_account_homepage',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render(
            'SciloneEncryptionBundle:MasterKey:set.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @param Form $form
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\OutOfBoundsException
     * @throws \Symfony\Component\Form\Exception\RuntimeException
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     *
     * @return bool
     */
    private function formProcessing(Form $form) :bool
    {
        $serviceMasterKey = $this->get('scilone_encryption.master_key');

        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return false;
        }

        if ($serviceMasterKey->checkMasterKeyExist() === false) {
            return $this->saveFormNewMasterKey($form);
        } else {
            return $this->validFormMasterKey($form);
        }
    }

    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     *
     * @return Form
     */
    private function createFormMasterKey()
    {
        $serviceGenerator = $this->get('scilone_generator.service');

        $masterKey = new MasterKey;
        $masterKey->setSalt($serviceGenerator->getSalt());

        return $this->createForm(MasterKeyType::class, $masterKey);
    }

    /**
     * @param Form $form
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\OutOfBoundsException
     * @throws \Symfony\Component\Form\Exception\RuntimeException
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     *
     * @return bool
     */
    private function validFormMasterKey(Form $form)
    {
        $serviceEncryption = $this->get('scilone_encryption.service');
        $serviceMasterKey  = $this->get('scilone_encryption.master_key');

        $masterKey = $form->getData();

        //push master key on session
        $serviceMasterKey->setMasterKey($masterKey->getTextCrypt());

        $textDecrypt = $serviceEncryption->decrypt($serviceMasterKey->getTextCrypt(), $serviceMasterKey->getSalt());

        if ($serviceMasterKey->isValidMasterKey($serviceEncryption->getKeyHash($textDecrypt)) === true) {
            return true;
        }

        $this->invalidFormMasterKey($form);

        return false;
    }

    /**
     * @param Form $form
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\OutOfBoundsException
     * @throws \Symfony\Component\Form\Exception\RuntimeException
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     *
     * @return bool
     */
    private function saveFormNewMasterKey(Form $form) :bool
    {
        $serviceGenerator  = $this->get('scilone_generator.service');
        $serviceEncryption = $this->get('scilone_encryption.service');
        $serviceMasterKey  = $this->get('scilone_encryption.master_key');

        $masterKey = $form->getData();

        //push master key on session
        $serviceMasterKey->setMasterKey($masterKey->getTextCrypt());

        //crypt random text with the master key
        $textOriginal = $serviceGenerator->getSalt();
        $textCrypted  = $serviceEncryption->crypt($textOriginal, $masterKey->getSalt());

        if ($serviceEncryption
                ->isValidEncryption($textOriginal, $textCrypted, $masterKey->getSalt()) === true
        ) {
            $masterKey
                ->setChecksum($serviceEncryption->getKeyHash($textOriginal))
                ->setTextCrypt($textCrypted);

            $em = $this->getDoctrine()->getManager();
            $em->persist($masterKey);

            // congratulations!
            $this->getUser()->addRole('ROLE_ADMIN');

            $em->flush();

            return true;
        }

        $this->invalidFormMasterKey($form);

        return false;
    }

    /**
     * @param Form $form
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Form\Exception\OutOfBoundsException
     */
    private function invalidFormMasterKey(Form $form)
    {
        $serviceMasterKey = $this->get('scilone_encryption.master_key');

        $serviceMasterKey->setMasterKey('');

        $form->get('textCrypt')->addError(
            new FormError($this->get('translator')->trans('form.masterKey.error'))
        );
    }
}
