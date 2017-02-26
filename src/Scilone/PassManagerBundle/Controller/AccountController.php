<?php

namespace Scilone\PassManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Scilone\PassManagerBundle\Entity\Account;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type;

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

    public function addAction()
    {
        $account = new Account;

        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $account);
        $formBuilder
            ->add('name', Type\TextType::class)
            ->add('username', Type\TextType::class)
            ->add('password', Type\TextType::class)
            ->add('url', Type\TextType::class)
            ->add('notes', Type\TextType::class)
            ->add('save', Type\SubmitType::class);

        $form = $formBuilder->getForm();

        return $this->render('ScilonePassManagerBundle:Account:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
