<?php

namespace Scilone\PassManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Scilone\PassManagerBundle\Entity\Account;
use Scilone\PassManagerBundle\Form\AccountType;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            return $this->redirectToRoute('scilone_encryption_set_master_key');
        }

        $em = $this->getDoctrine()->getManager();

        $repositoryAccount = $em->getRepository('ScilonePassManagerBundle:Account');
        $repositoryUser    = $em->getRepository('ScilonePassManagerBundle:User');

        $aclRole = $this->get('scilone_acl.role.manager');
        $aclRole->grant($aclRole::MASK_VIEW, $repositoryAccount->find(1), 'ROLE_ADMIN');
        exit;

        return $this->render(
            'ScilonePassManagerBundle:Account:index.html.twig',
            [
                'accounts' => $repositoryAccount->findAll(),
                'users'    => $repositoryUser->findAll()
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \RuntimeException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\RuntimeException
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @throws \Symfony\Component\Security\Acl\Exception\AclNotFoundException
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     * @throws \Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException
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

            $acl = $this->get('scilone_acl.user.manager');
            $acl->grant($acl::MASK_OWNER, $account);

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

    public function xhrModalRightAction(int $idAccount)
    {
        $acl               = $this->get('scilone_acl.user.manager');
        $em                = $this->getDoctrine()->getManager();
        $repositoryAccount = $em->getRepository('ScilonePassManagerBundle:Account');
        $repositoryUser    = $em->getRepository('ScilonePassManagerBundle:User');

        return $this->render(
            'ScilonePassManagerBundle:Account:modalRight.html.twig',
            [
                'account' => $repositoryAccount->find($idAccount),
                'users'   => $repositoryUser->findAll(),
                'acl'     => $acl
            ]
        );
    }

    public function xhrFormChangeRightsUserAction(Request $request)
    {
        $idUsers    = (array) $request->request->get('idUser');
        $usersRight = (array) $request->request->get('rightUser');
        $idAccount  = $request->request->getInt('idAccount');

        $acl               = $this->get('scilone_acl.user.manager');
        $em                = $this->getDoctrine()->getManager();
        $repositoryAccount = $em->getRepository('ScilonePassManagerBundle:Account');
        $repositoryUser    = $em->getRepository('ScilonePassManagerBundle:User');

        $account = $repositoryAccount->find($idAccount);

        if ($acl->isGranted($acl::MASK_MASTER, $account) === false) {
            return new JsonResponse(false, Response::HTTP_FORBIDDEN);
        }

        $error = 0;
        foreach ($idUsers as $key => $idUser) {
            $user = $repositoryUser->find($idUser);
            $attributeUser = (int) $usersRight[$key];

            if ($attributeUser === 0) {
                if ($acl->removeAllAttributes($account, $user) === false) {
                    ++ $error;
                }
            } elseif ($acl->grant($attributeUser, $account, $user) === false) {
                ++$error;
            }
        }

        if ($error > 0) {
            return new JsonResponse(['error' => $error], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(true);
    }
}
