<?php

declare(strict_types=1);

namespace Tvdt\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tvdt\Entity\Answer;

/** @extends AbstractBaseAnswerFormType<Answer> */
class AnswerFormType extends AbstractBaseAnswerFormType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Answer::class,
            'empty_data' => static fn (): Answer => new Answer(''),
        ]);
    }
}
