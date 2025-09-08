<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<User>
 */
class RegistrationFormType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('Email'),
                'attr' => ['autocomplete' => 'email'],
                'translation_domain' => false,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => $this->translator->trans('The password fields must match.'),
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => ['label' => $this->translator->trans('Password')],
                'second_options' => ['label' => $this->translator->trans('Repeat Password')],
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: 'Please enter a password'),
                    new Length(min: 8, max: 4096, minMessage: 'Your password should be at least {{ limit }} characters'),
                ],
                'translation_domain' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
