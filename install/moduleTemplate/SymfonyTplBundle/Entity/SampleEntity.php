<?php

namespace App\Bundle\SymfonyTplBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Table(name="sample_table_name")
 * @ORM\Entity(repositoryClass="App\Bundle\SymfonyTplBundle\Repository\SampleEntityRepository")
 */
class SampleEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $sample_primary_id;

    public function getSamplePrimaryId(): ?int
    {
        return $this->sample_primary_id;
    }
}
