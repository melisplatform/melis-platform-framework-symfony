<?php

namespace MelisPlatformFrameworkSymfony\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use MelisPlatformFrameworkSymfony\Entity\MelisCmsLanguage;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MelisCmsLanguage|null find($id, $lockMode = null, $lockVersion = null)
 * @method MelisCmsLanguage|null findOneBy(array $criteria, array $orderBy = null)
 * @method MelisCmsLanguage[]    findAll()
 * @method MelisCmsLanguage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MelisCmsLanguageRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MelisCmsLanguage::class);
    }
}
