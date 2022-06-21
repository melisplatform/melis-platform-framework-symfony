<?php

namespace MelisPlatformFrameworkSymfony\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use MelisPlatformFrameworkSymfony\Entity\MelisCmsSite;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MelisCmsSite|null find($id, $lockMode = null, $lockVersion = null)
 * @method MelisCmsSite|null findOneBy(array $criteria, array $orderBy = null)
 * @method MelisCmsSite[]    findAll()
 * @method MelisCmsSite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MelisCmsSiteRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MelisCmsSite::class);
    }
}
