<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class UserNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'form.username'
            ])
            ->add('name', TextType::class, [
                'label' => 'form.name'
            ])
            ->add('profile', ChoiceType::class, [
                'label' => 'form.profile',
                'choices' => [
                    'text.role_display' => User::ROLE_DISPLAY,
                    'text.role_reporter' => User::ROLE_REPORTER,
                    'text.role_manager' => User::ROLE_MANAGER,
                ],
                'expanded' => true,
                'required' => true
            ])
            ->add('new_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'error.non_matching_passwords',
                'mapped' => false,
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => [
                    'label' => 'form.new_password',
                    'constraints' => [
                        new Length(['min' => 6])
                    ]
                ],
                'second_options' => [
                    'label' => 'form.repeat_new_password',
                    'constraints' => [
                        new Length(['min' => 6])
                    ]
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => User::class,
                'translation_domain' => 'user'
            ]);
    }
}
