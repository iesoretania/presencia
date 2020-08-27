<?php

namespace App\Form;

use App\Form\Model\DateRange;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateRangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstDate', DateType::class, [
                'label' => 'form.first_date',
                'widget' => 'single_text'
            ])
            ->add('lastDate', DateType::class, [
                'label' => 'form.last_date',
                'widget' => 'single_text'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => DateRange::class,
                'translation_domain' => 'worker'
            ]);
    }
}
