<?php

namespace App\Form;

use App\Form\Model\FileImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeacherImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'form.teacher_csv',
                'required' => true,
                'attr' => [
                    'placeholder' => 'form.select_file_placeholder'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => FileImport::class,
                'translation_domain' => 'worker'
            ]);
    }
}