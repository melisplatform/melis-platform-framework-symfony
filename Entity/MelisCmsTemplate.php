<?php

namespace MelisPlatformFrameworkSymfony\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Table(name="melis_cms_template")
 * @ORM\Entity(repositoryClass="MelisPlatformFrameworkSymfony\Repository\MelisCmsTemplateRepository")
 */
class MelisCmsTemplate
{
    /**
	* @ORM\Id()
	* @ORM\GeneratedValue()
	* @ORM\Column(type="integer")
	*/
	private $tpl_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $tpl_site_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tpl_name;


    /**
     * @return int|null
     */
	public function getTplId(): ?int
	{
		return $this->tpl_id;
	}

    /**
     * @return int|null
     */
    public function getTplSiteId(): ?int
    {
        return $this->tpl_site_id;
    }

    /**
     * @return string|null
     */
    public function getTplName(): ?string
    {
        return $this->tpl_name;
    }
}
