<?php

namespace Scilone\EncryptionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class MasterKeyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'textCrypt',
                PasswordType::class,
                [
                    'label' => 'form.masterKey.textCrypt',
                    'attr'  => [
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