<?php

declare(strict_types=1);

namespace Tvdt\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tvdt\Entity\SeasonSettings;

/** @extends AbstractType<SeasonSettings> */
class SettingsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('showNumbers', options: [
                'label_attr' => ['class' => 'checkbox-switch'],
                'attr' => ['role' => 'switch', 'switch' => null]])
            ->add('confirmAnswers', options: [
                'label_attr' => ['class' => 'checkbox-switch'],
                'attr' => ['role' => 'switch', 'switch' => null]])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SeasonSettings::class,
        ]);
    }
}
