<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Test\Entity;

/**
 * @Entity
 */
class TestEntity
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue(strategy="IDENTITY")
	 */
	private $id;
	
	/**
	 * @Column(type="string")
	 * @validation:NotBlank
	 */
	private $name;
	
	/**
	 * @Version
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