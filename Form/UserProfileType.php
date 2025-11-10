<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Form;

use KimaiPlugin\LiePayrollBundle\Entity\PayrollUserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'PLZ',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'Ort',
                'required' => false,
            ])
            ->add('birthdate', DateType::class, [
                'label' => 'Geburtsdatum',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('ahvNumber', TextType::class, [
                'label' => 'AHV-Nummer',
                'required' => false,
            ])
            ->add('hireDate', DateType::class, [
                'label' => 'Eintrittsdatum',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('terminationDate', DateType::class, [
                'label' => 'Austrittsdatum',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('position', TextType::class, [
                'label' => 'Position',
                'required' => false,
            ])
            ->add('department', TextType::class, [
                'label' => 'Abteilung',
                'required' => false,
            ])
            ->add('maritalStatus', ChoiceType::class, [
                'label' => 'Zivilstand',
                'required' => false,
                'choices' => [
                    'Ledig' => 'ledig',
                    'Verheiratet' => 'verheiratet',
                    'Geschieden' => 'geschieden',
                    'Verwitwet' => 'verwitwet',
                ],
                'placeholder' => '-- Bitte wählen --'
            ])
            ->add('numberOfChildren', IntegerType::class, [
                'label' => 'Anzahl Kinder',
                'required' => false,
            ])
            ->add('municipality', TextType::class, [
                'label' => 'Wohngemeinde',
                'required' => false,
            ])
            ->add('taxMunicipality', TextType::class, [
                'label' => 'Steuergemeinde',
                'required' => false,
            ])
            ->add('iban', TextType::class, [
                'label' => 'IBAN',
                'required' => false,
            ])
            ->add('employmentLevel', IntegerType::class, [
                'label' => 'Beschäftigungsgrad (%)',
                'required' => false,
                'data' => 100,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PayrollUserProfile::class,
        ]);
    }
}