<?php
namespace RubenSteeb\Testing\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class SecondLevelObjectWithStorage extends AbstractEntity
{
	/**
	* name
	*
	* @var string
	*/
	protected $name;
	
	/**
	* objectStorage 
	*
	* @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RubenSteeb\TestModelRelations\Domain\Model\LastLevelObject>
	*/
	protected $lastLevelObject;
	
	/**
	* initialize objectStorage
	*/
	protected function initObjectStorage()
	{
		$this->setLastLevelObject(new \TYPO3\CMS\Extbase\Persistence\ObjectStorage());
	}
	
	/**
	* SecondLevelObjectWithStorage Constructor
	*/
	public function __construct()
	{
		$this->initObjectStorage();		
	}
	
	/**
	* set LastLevelObjectStorage
	*
	* @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RubenSteeb\TestModelRelations\Domain\Model\LastLevelObject>
	* @return void
	*/
	public function setLastLevelObject(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage)
	{
		$this->lastLevelObject = $objectStorage;
	}
	
	/**
	* get LastLevelObjectStorage
	*
	* @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RubenSteeb\TestModelRelations\Domain\Model\LastLevelObject>
	*/
	public function getLastLevelObject()
	{
		return clone $this->lastLevelObject;
	}
	
	/**
	* add LastLevelObject
	*
	* @param \RubenSteeb\TestModelRelations\Domain\Model\LastLevelObject
	* @return void
	*/
	public function addLastLevelObject(\RubenSteeb\TestModelRelations\Domain\Model\LastLevelObject $lastLevelObject)
	{
		$this->lastLevelObject->attach($lastLevelObject);
	}
	
	/**
	* remove LastLevelObject
	*
	* @param \RubenSteeb\TestModelRelations\Domain\Model\LastLevelObject
	* @return void
	*/
	public function removeLastLevelObject(\RubenSteeb\TestModelRelations\Domain\Model\LastLevelObject $lastLevelObject)
	{
		$this->lastLevelObject->detach($lastLevelObject);
	}
	
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