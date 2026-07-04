<?php

declare(strict_types=1);

namespace Tvdt\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tvdt\Entity\BankAnswer;

/** @extends AbstractType<BankAnswer> */
class BankAnswerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Answer', 'maxlength' => 255],
            ])
            ->add('isRightAnswer', CheckboxType::class, [
                'label' => 'Correct',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BankAnswer::class,
            'empty_data' => static fn (): BankAnswer => new BankAnswer(''),
        ]);
    }
}
