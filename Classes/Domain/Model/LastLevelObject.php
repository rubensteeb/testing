<?php
namespace RubenSteeb\TestModelRelations\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class LastLevelObject extends AbstractEntity
{
	/**
	* names
	*
	* @var string
	*/
	protected $name;
	
	/**
	* set name
	*
	* @param string
	* @return void
	*/
	public function setName(string $name)
	{
		$this->name = $name;
	}
	
	/**
	* get name
	*
	* @return string
	*/
	public function getName()
	{
		return $this->name;
	}
}

