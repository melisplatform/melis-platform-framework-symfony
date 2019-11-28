<?php

namespace App\Bundle\SymfonyTpl\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Table(name="sample_table_name")
 * @ORM\Entity(repositoryClass="App\Bundle\SymfonyTpl\Repository\SampleEntityRepository")
 */
class SampleEntity
{
    //ENTITY_SETTERS_GETTERS
}
