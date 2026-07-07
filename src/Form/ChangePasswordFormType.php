<?php

declare(strict_types=1);

namespace Tvdt\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tvdt\Entity\User;

/** @extends AbstractType<array{plainPassword: string}> */
final class ChangePasswordFormType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'first_options' => [
                    'label' => $this->translator->trans('New password'),
                    'constraints' => [
                        new NotBlank(message: 'Please enter a password'),
                        new Length(
                            min: User::PASSWORD_MIN_LENGTH,
                            max: User::PASSWORD_MAX_LENGTH,
                            minMessage: 'Your password should be at least {{ limit }} characters',
                        ),
                    ],
                ],
                'second_options' => [
                    'label' => $this->translator->trans('Repeat Password'),
                ],
                'invalid_message' => $this->translator->trans('The password fields must match.'),
                'mapped' => false,
                'translation_domain' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
