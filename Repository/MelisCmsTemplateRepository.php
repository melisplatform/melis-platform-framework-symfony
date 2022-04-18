<?php

namespace MelisPlatformFrameworkSymfony\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use MelisPlatformFrameworkSymfony\Entity\MelisCmsTemplate;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MelisCmsTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method MelisCmsTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method MelisCmsTemplate[]    findAll()
 * @method MelisCmsTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MelisCmsTemplateRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MelisCmsTemplate::class);
    }
}
