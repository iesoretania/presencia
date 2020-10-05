<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Worker;
use App\Repository\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkerEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastName', TextType::class, [
                'label' => 'form.last_name'
            ])
            ->add('firstName', TextType::class, [
                'label' => 'form.first_name'
            ])
            ->add('internalCode', TextType::class, [
                'label' => 'form.internal_code',
                'required' => false
            ])
            ->add('tags', EntityType::class, [
                'label' => 'form.tags',
                'class' => Tag::class,
                'choice_label' => 'name',
                'query_builder' => function (TagRepository $repository) {
                    return $repository->findAllSortedQueryBuilder();
                },
                'multiple' => true,
                'expanded' => true,
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Worker::class,
                'translation_domain' => 'worker'
            ]);
    }
}
