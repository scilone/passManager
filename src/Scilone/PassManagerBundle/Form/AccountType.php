<?php

namespace Scilone\PassManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * Class AccountType
 *
 * @package Scilone\PassManagerBundle\Form
 */
class AccountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'form.account.name',
                    'attr'  => [
                        'class'        => 'form-control',
                        'autocomplete' => 'off'
                    ]
                ]
            )
            ->add(
                'username',
                TextType::class,
                [
                    'label'    =>'form.account.username',
                    'required' => false,
                    'attr'     => [
                        'class'        => 'form-control',
                        'autocomplete' => 'off'
                    ]
                ]
            )
            ->add(
                'password',
                PasswordType::class,
                [
                    'label'    =>'form.account.password',
                    'required' => false,
                    'attr'     => [
                        'class'        => 'form-control',
                        'autocomplete' => 'off'
                    ]
                ]
            )
            ->add(
                'url',
                TextType::class,
                [
                    'label'    =>'form.account.url',
                    'required' => false,
                    'attr'     => [
                        'class'        => 'form-control',
                        'autocomplete' => 'off'
                    ]
                ]
            )
            ->add(
                'notes',
                TextType::class,
                [
                    'label'    =>'form.account.notes',
                    'required' => false,
                    'attr'     => [
                        'class'        => 'form-control',
                        'autocomplete' => 'off'
                    ]
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' =>'form.account.save',
                    'attr'  => ['class'=>'btn btn-primary pull-right']
                ]
            );
    }
}
