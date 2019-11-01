<?php
/**
 * Created by PhpStorm.
 * User: Thibaut
 * Date: 01/11/2019
 * Time: 14:55
 */

namespace App\Form;


use App\Document\Category;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('price')
            ->add('category', DocumentType::class, array(
                'class' => Category::class,
                'choice_label' => 'name'
            ))
            ->add('city')
            ->add('description')
        ;
    }
}