<?php

declare(strict_types=1);

namespace Tvdt\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tvdt\Entity\BankAnswer;

/** @extends AbstractBaseAnswerFormType<BankAnswer> */
class BankAnswerFormType extends AbstractBaseAnswerFormType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BankAnswer::class,
            'empty_data' => static fn (): BankAnswer => new BankAnswer(''),
        ]);
    }
}
