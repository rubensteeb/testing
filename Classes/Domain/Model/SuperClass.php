<?php
namespace RubenSteeb\TestModelRelations\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class SuperClass extends AbstractEntity
{
	/**
	* the name of instance
	*
	* @var string
	*/
	protected $name;
	
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
}