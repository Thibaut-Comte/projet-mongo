<?php

namespace App\DataFixtures;

use App\Document\Category;
use App\Document\Product;
use App\Document\User;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $documentManager;
    private $encoder;

    public function __construct(DocumentManager $documentManager, UserPasswordEncoderInterface $encoder)
    {
        $this->documentManager = $documentManager;
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        $categories = array(
            'Voitures',
            'Motos',
            'Locations',
            'Hôtels',
            'Vélos',
            'Vêtements',
            'Informatique',
            'Téléphonie',
            'Autres'
        );

        foreach ($categories as $category) {
            $cat = new Category();
            $cat->setName($category);

            $this->documentManager->persist($cat);
            $this->documentManager->flush();
        }

        $admin = new User();
        $admin->setUsername('admin');
        $admin->setCreationDate($faker->dateTimeBetween($startDate = '-2 years', $endDate = '-1 years', $timezone = null));
        $admin->setEmail('admin@admin.com');
        $admin->setFirstname($faker->lastName);
        $admin->setLastname($faker->firstName);
        $admin->setPassword($this->encoder->encodePassword($admin, $admin->getUsername()));
        $admin->setRoles('ROLE_ADMIN');

        $this->documentManager->persist($admin);
        $this->documentManager->flush();

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setUsername($faker->userName);
            $user->setCreationDate($faker->dateTimeBetween($startDate = '-2 years', $endDate = '-1 years', $timezone = null));
            $user->setEmail($faker->email);
            $user->setFirstname($faker->lastName);
            $user->setLastname($faker->firstName);
            $user->setPassword($this->encoder->encodePassword($user, $user->getUsername()));
            $user->setRoles('ROLE_USER');

            $this->documentManager->persist($user);
            $this->documentManager->flush();


            for ($i = 0; $i < $faker->numberBetween(1, 20); $i++) {
                $product = new Product();
                $product->setName($faker->word);
                $product->setCategory($this->documentManager->getRepository(Category::class)->findOneBy(array('name' => $categories[$faker->numberBetween(0, count($categories) - 1)])));
                $product->setCity($faker->city);
                $product->setDateInsert($faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now', $timezone = null));
                $product->setPrice($faker->numberBetween(1, 10000));
                $product->setUser($user);
                $product->setDescription($faker->text);
                $product->setImageFilename($faker->imageUrl(400, 200, 'technics'));

                $this->documentManager->persist($product);
                $this->documentManager->flush();

            }
        }

    }
}
