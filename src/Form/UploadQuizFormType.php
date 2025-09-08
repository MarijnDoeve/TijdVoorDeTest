<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

/** @extends AbstractType<Quiz> */
class UploadQuizFormType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->translator->trans('Quiz name'),
                'translation_domain' => false,
            ])
            ->add('sheet', FileType::class, [
                'label' => $this->translator->trans('Quiz (xlsx)'),
                'mapped' => false,
                'required' => true,
                'translation_domain' => false,
                'constraints' => [
                    new File(maxSize: '1024k', mimeTypes: [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ], mimeTypesMessage: $this->translator->trans('Please upload a valid XLSX file')),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}
