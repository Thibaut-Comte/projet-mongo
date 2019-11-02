<?php


namespace App\Repository;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class ProductRepository extends DocumentRepository
{

    public function findAllByCategory($category)
    {
        $qb = $this->createQueryBuilder()
            ->sort('dateInsert', 'desc');

        if($category){
            $qb->field('category')->equals($category);
        }
        return $qb->getQuery();
    }
}