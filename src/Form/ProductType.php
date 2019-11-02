<?php
/**
 * Created by PhpStorm.
 * User: Thibaut
 * Date: 01/11/2019
 * Time: 14:55
 */

namespace App\Form;

use App\Document\Category;
use App\Document\Product;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'product.name',
                'attr' => array('placeholder' => 'product.name')
            ))
            ->add('price', MoneyType::class, array(
                'label' => 'product.price',
                'attr' => array('placeholder' => 'product.price')
            ))
            ->add('category', DocumentType::class, array(
                'class' => Category::class,
                'choice_label' => 'name'
            ))
            ->add('city', TextType::class, array(
                'label' => 'product.city',
                'attr' => array('placeholder' => 'product.city')
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'product.description',
                'attr' => array('placeholder' => 'product.description')
            ))
            ->add('image', FileType::class, [
                'label' => 'product.image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}