<?php

namespace MelisPlatformFrameworkSymfony\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Table(name="melis_cms_site")
 * @ORM\Entity(repositoryClass="MelisPlatformFrameworkSymfony\Repository\MelisCmsSiteRepository")
 */
class MelisCmsSite
{
    /**
	* @ORM\Id()
	* @ORM\GeneratedValue()
	* @ORM\Column(type="integer")
	*/
	private $site_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $site_name;


    /**
     * @return int|null
     */
	public function getSiteId(): ?int
	{
		return $this->site_id;
	}

    /**
     * @return string|null
     */
    public function getSiteName(): ?string
    {
        return $this->site_name;
    }
}
