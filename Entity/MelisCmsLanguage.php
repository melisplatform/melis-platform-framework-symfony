<?php

namespace MelisPlatformFrameworkSymfony\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Table(name="melis_cms_lang")
 * @ORM\Entity(repositoryClass="MelisPlatformFrameworkSymfony\Repository\MelisCmsLanguageRepository")
 */
class MelisCmsLanguage
{
    /**
	* @ORM\Id()
	* @ORM\GeneratedValue()
	* @ORM\Column(type="integer")
	*/
	private $lang_cms_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lang_cms_locale;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lang_cms_name;

    /**
     * @return int|null
     */
	public function getLangCmsId(): ?int
	{
		return $this->lang_cms_id;
	}

    /**
     * @return string|null
     */
    public function getLangCmsLocale(): ?string
    {
        return $this->lang_cms_locale;
    }

    /**
     * @return string|null
     */
    public function getLangCmsName(): ?string
    {
        return $this->lang_cms_name;
    }

    /**
     * @return string|null
     */
    public function getRawData(): ?string
    {
        return $this->getLangCmsId();
    }
}
