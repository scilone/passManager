<?php

namespace Scilone\PassManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    "label"=>"form.account.name",
                    "required"=>true
                ]
            )
            ->add(
                'username',
                TextType::class,
                [
                    "label"=>"form.account.username"
                ]
            )
            ->add(
                'password',
                TextType::class,
                [
                    "label"=>"form.account.password"
                ]
            )
            ->add(
                'url',
                TextType::class,
                [
                    "label"=>"form.account.url"
                ]
            )
            ->add(
                'notes',
                TextType::class,
                [
                    "label"=>"form.account.notes"
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    "label"=>"form.account.save"
                ]
            );
    }
}