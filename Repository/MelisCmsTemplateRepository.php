<?php

namespace MelisPlatformFrameworkSymfony\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use MelisPlatformFrameworkSymfony\Entity\MelisCmsTemplate;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MelisCmsTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method MelisCmsTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method MelisCmsTemplate[]    findAll()
 * @method MelisCmsTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MelisCmsTemplateRepository extends ServiceEntityRepository
{
    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MelisCmsTemplate::class);
    }
}
