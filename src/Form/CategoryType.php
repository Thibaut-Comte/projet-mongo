<?php
/**
 * Created by PhpStorm.
 * User: Thibaut
 * Date: 01/11/2019
 * Time: 14:55
 */

namespace App\Form;


use App\Document\Category;
use Doctrine\ODM\MongoDB\Types\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}