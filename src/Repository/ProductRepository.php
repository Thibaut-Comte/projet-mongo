<?php


namespace App\Repository;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use MongoDB\BSON\Regex;

class ProductRepository extends DocumentRepository
{

    public function findAllByCategoryAndSearch($category, $search, $onlyTitle)
    {
        $qb = $this->createQueryBuilder()
            ->sort('dateInsert', 'desc');

        if ($category) {
            $qb->field('category')->equals($category);
        }


        if ($search) {
            $qb->addOr($qb->expr()->field('name')->equals(new Regex($search, "i")));
            if (!$onlyTitle)
                $qb->addOr($qb->expr()->field('description')->equals(new Regex($search, "i")));
        }

        return $qb->getQuery();
    }
}