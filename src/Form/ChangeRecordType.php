<?php

namespace App\Form;

use App\Entity\Presence\Record;
use App\Entity\Worker;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeRecordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('worker', EntityType::class, [
                'label' => 'form.worker',
                'class' => Worker::class,
                'choice_label' => 'fullName',
                'disabled' => true
            ])
            ->add('inTimestamp', TimeType::class, [
                'label' => 'form.in_timestamp',
                'widget' => 'single_text'
            ])
            ->add('outTimestamp', TimeType::class, [
                'label' => 'form.out_timestamp',
                'widget' => 'single_text',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Record::class,
                'translation_domain' => 'worker'
            ]);
    }
}
