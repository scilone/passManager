<?php

namespace Scilone\PassManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $repositoryAccount = $this->getDoctrine()
            ->getManager()
            ->getRepository('ScilonePassManagerBundle:Account');

        return $this->render(
            'ScilonePassManagerBundle:Default:index.html.twig',
            ['accounts'=>$repositoryAccount->findAll()]
        );
    }
}
