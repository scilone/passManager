<?php

namespace Scilone\PassManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Scilone\PassManagerBundle\Entity\Account;
use Scilone\PassManagerBundle\Form\AccountType;

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction()
    {
        $account = new Account;

        $form = $this->createForm(AccountType::class, $account);

        return $this->render('ScilonePassManagerBundle:Account:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
