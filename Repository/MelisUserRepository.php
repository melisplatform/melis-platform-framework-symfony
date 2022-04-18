<?php

namespace MelisPlatformFrameworkSymfony\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use MelisPlatformFrameworkSymfony\Entity\MelisUser;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MelisUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method MelisUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method MelisUser[]    findAll()
 * @method MelisUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MelisUserRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MelisUser::class);
    }
}
