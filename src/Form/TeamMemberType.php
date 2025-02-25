<?php
// src/Form/TeamMemberType.php
namespace App\Form;

use App\Entity\TeamMember;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TeamMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Member Name',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter the team member\'s full name',
                ],
            ])
            ->add('role', TextType::class, [
                'label' => 'Role',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter the role of the team member',
                ],
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name', // Display the team name
                'label' => 'Team',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
          
              
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TeamMember::class,
        ]);
    }
}
