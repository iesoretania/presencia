<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditType extends AbstractType
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
