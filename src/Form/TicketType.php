<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Ticket;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, [
                'label' => 'Titre de votre demande',
                'attr' => ['placeholder' => 'Ex: Problème d\'accès au serveur...']
            ])
            ->add('description', null, [
                'label' => 'Description détaillée',
                'attr' => ['rows' => 6, 'placeholder' => 'Décrivez votre problème ici...']
            ])
            ->add('priorite', ChoiceType::class, [
                'choices' => [
                    'Basse' => 'Basse',
                    'Moyenne' => 'Moyenne',
                    'Haute' => 'Haute',
                ],
                'label' => 'Priorité'
            ])
            ->add('id_categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'label' => 'Catégorie'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
