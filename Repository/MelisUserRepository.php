<?php

namespace MelisPlatformFrameworkSymfony\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use MelisPlatformFrameworkSymfony\Entity\MelisUser;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MelisUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method MelisUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method MelisUser[]    findAll()
 * @method MelisUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MelisUserRepository extends ServiceEntityRepository
{
    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MelisUser::class);
    }
}
