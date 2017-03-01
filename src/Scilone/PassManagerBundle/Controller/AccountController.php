<?php

namespace Scilone\PassManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Scilone\PassManagerBundle\Entity\Account;
use Scilone\PassManagerBundle\Form\AccountType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    /**
     * List all accounts.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $repositoryAccount = $this->getDoctrine()
            ->getManager()
            ->getRepository('ScilonePassManagerBundle:Account');

        return $this->render(
            'ScilonePassManagerBundle:Account:index.html.twig',
            ['accounts'=>$repositoryAccount->findAll()]
        );
    }

    /**
     * @param Request $request
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
