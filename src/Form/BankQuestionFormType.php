<?php

declare(strict_types=1);

namespace Tvdt\Form;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tvdt\Entity\BankQuestion;
use Tvdt\Entity\QuestionLabel;
use Tvdt\Entity\Season;
use Tvdt\Repository\QuestionLabelRepository;

/** @extends AbstractType<BankQuestion> */
class BankQuestionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Season $season */
        $season = $options['season'];

        $builder
            ->add('question', TextType::class, [
                'label' => 'Question',
                'attr' => ['maxlength' => 255],
            ])
            ->add('reusable', CheckboxType::class, [
                'label' => 'Reusable',
                'required' => false,
                'label_attr' => ['class' => 'checkbox-switch'],
                'attr' => ['role' => 'switch', 'switch' => null],
            ])
            ->add('labels', EntityType::class, [
                'label' => 'Labels',
                'class' => QuestionLabel::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'query_builder' => static fn (QuestionLabelRepository $repository): QueryBuilder => $repository
                    ->createQueryBuilder('l')
                    ->where('l.season = :season')
                    ->orderBy('l.name', 'ASC')
                    ->setParameter('season', $season),
            ])
            ->add('answers', CollectionType::class, [
                'label' => 'Answers',
                'entry_type' => BankAnswerFormType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BankQuestion::class,
        ]);
        $resolver->setRequired('season');
        $resolver->setAllowedTypes('season', Season::class);
    }
}
