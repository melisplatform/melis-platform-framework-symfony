<?php

namespace MelisPlatformFrameworkSymfony\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Table(name="melis_core_user")
 * @ORM\Entity(repositoryClass="MelisPlatformFrameworkSymfony\Repository\MelisUserRepository")
 */
class MelisUser
{
    /**
	* @ORM\Id()
	* @ORM\GeneratedValue()
	* @ORM\Column(type="integer")
	*/
	private $usr_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $usr_firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $usr_lastname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $usr_login;

    /**
     * @return int|null
     */
	public function getUsrId(): ?int
	{
		return $this->usr_id;
	}

    /**
     * @return string|null
     */
    public function getUsrFirstname(): ?string
    {
        return $this->usr_firstname;
    }

    /**
     * @return string|null
     */
    public function getUsrLastname(): ?string
    {
        return $this->usr_firstname;
    }

    /**
     * @return string|null
     */
    public function getUsrLogin(): ?string
    {
        return $this->usr_login;
    }


    /**
     * @return string|null
     */
    public function getUsrName(): ?string
    {
        return $this->usr_firstname. ' ' .$this->usr_lastname;
    }

    /**
     * @return string|null
     */
    public function getRawData(): ?string
    {
        return $this->getUsrId();
    }
}
