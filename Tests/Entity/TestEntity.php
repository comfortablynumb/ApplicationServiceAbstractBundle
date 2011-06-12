<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class TestEntity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string")
	 * @Assert\NotBlank
	 */
	private $name;
	
	/**
	 * @ORM\Version
	 */
	private $version;
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setName( $name )
	{
		$this->name = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function setIsoCode()
	{
	}
	
	public function setVersion( $version )
	{
		$this->version = $version;
	}
	
	public function getVersion()
	{
		return $this->version;
	}
}