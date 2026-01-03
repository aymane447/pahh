<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_utilisateur')
            ->add('roles', ChoiceType::class, [
        'choices' => [
            'agent' => 'ROLE_AGENT',
            'client' => 'ROLE_CLIENT',
            'Admin' => 'ROLE_ADMIN',
        ],
        'expanded' => true,   // cases à cocher
        'multiple' => true,   // accepte plusieurs rôles
        'label' => 'Rôles',
    ])
            ->add('nom')
            ->add('prenom')
            ->add('email')
            ->add('password')
            ->add('statut')
            ->add('date_creation')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
