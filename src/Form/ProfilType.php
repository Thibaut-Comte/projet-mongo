<?php
/**
 * Created by PhpStorm.
 * User: Thibaut
 * Date: 01/11/2019
 * Time: 14:55
 */

namespace App\Form;


use App\Document\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, array(
                'label' => 'Prénom',
                'attr' => array(
                    'placeholder' => 'Prénom'
                )
            ))
            ->add('lastname', TextType::class, array(
                'label' => 'Nom',
                'attr' => array(
                    'placeholder' => 'Nom'
                )
            ))
            ->add('email', EmailType::class, array(
                'label' => 'Email',
                'attr' => array(
                    'placeholder' => 'Email'
                )
            ))
            ->add('username', TextType::class, array(
                'label' => 'Username',
                'attr' => array(
                    'placeholder' => 'Nom d\'utilisateur'
                )
            ))
            ->add('rawPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Retapez votre mot de passe'],
            ]);
        ;
    }
}