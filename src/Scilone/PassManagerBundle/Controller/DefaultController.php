<?php

namespace Scilone\PassManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $serviceEncryption = $this->get('scilone_pass_manager.encryption');
        $serviceGenerator  = $this->get('scilone_pass_manager.generator');

        $salt = $serviceGenerator->getSalt();
        $text = '5T69Xe!B53_?kR63w';

        $crypt = $serviceEncryption->crypt($text, $salt);
        $decrypt = null;
        $decrypt = $serviceEncryption->decrypt($crypt, $salt);

        dump($salt,$crypt, $decrypt, $text==$decrypt);
        exit;
        /*$doctrine = $this->get('doctrine');
        $em = $doctrine->getManager();

        $user = $this->getUser();
        $user->addRole('ROLE_ADMIN');
        //$user->setRoles(array('ROLE_USER'));

        $em->persist($user);
        $em->flush();
        dump($user->getRoles());*/
        return $this->render('ScilonePassManagerBundle:Default:index.html.twig');
    }
}
