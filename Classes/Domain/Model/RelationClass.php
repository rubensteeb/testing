<?php
namespace RubenSteeb\Testing\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class RelationClass extends AbstractEntity
{
	/**
	* name of the relationClass
	*
	* @var string
	*/
	protected $name;
	
	/**
	* another property of the relation class
	*
	* @var string
	*/
	protected $property;


	
	/**
	* set the name
	*
	* @param string
	* @return void
	*/
	public function setName(string $name)
	{
		$this->name = $name;
	}
	
	/**
	* get the name
	*
	* @return string
	*/
	public function getName()
	{
		return $this->name;
	}
	
	/**
	* set the property
	*
	* @param string
	* @return void
	*/
	public function setProperty(string $property)
	{
		$this->property = $property;
	}
	
	/**
	* get the property
	*
	* @return string
	*/
	public function getProperty()
	{
		return $this->property;
	}
	
}