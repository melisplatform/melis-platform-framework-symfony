<?php

namespace MelisPlatformFrameworkSymfony\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use MelisPlatformFrameworkSymfony\Entity\MelisCmsLanguage;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MelisCmsLanguage|null find($id, $lockMode = null, $lockVersion = null)
 * @method MelisCmsLanguage|null findOneBy(array $criteria, array $orderBy = null)
 * @method MelisCmsLanguage[]    findAll()
 * @method MelisCmsLanguage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MelisCmsLanguageRepository extends ServiceEntityRepository
{
    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MelisCmsLanguage::class);
    }
}
