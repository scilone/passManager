<?php

namespace Scilone\PassManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Scilone\PassManagerBundle\Entity\Account;
use Scilone\PassManagerBundle\Form\AccountType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AccountController
 *
 * @package Scilone\PassManagerBundle\Controller
 */
class AccountController extends Controller
{
    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Twig_Error
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \UnexpectedValueException
     *
     * @return Response
     */
    public function indexAction()
    {

        if ($this->get('scilone_encryption.master_key')->askMasterKey() === true) {
            return $this->redirectToRoute('scilone_pass_manager_ask_master_key');
        }

        $repositoryAccount =
            $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('ScilonePassManagerBundle:Account');

        return $this->render(
            'ScilonePassManagerBundle:Account:index.html.twig',
            ['accounts'=>$repositoryAccount->findAll()]
        );
    }

    /**
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\RuntimeException
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @throws \Twig_Error
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \UnexpectedValueException
     *
     * @return RedirectResponse|Response
     */
    public function addAction(Request $request)
    {
        $account = new Account;
        $account->setSalt($this->get('scilone_generator.service')->getSalt());

        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $account = $form->getData();
            $account->setPassword(
                $this->get('scilone_encryption.service')->crypt(
                    $account->getPassword(),
                    $account->getSalt()
                )
            );

            $em = $this->getDoctrine()->getManager();
            $em->persist($account);
            $em->flush();

            return $this->redirectToRoute(
                'scilone_pass_manager_account_homepage',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render(
            'ScilonePassManagerBundle:Account:new.html.twig',
            ['form' => $form->createView()]
        );
    }
}
