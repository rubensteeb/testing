<?php
namespace RubenSteeb\TestModelRelations\Domain\Model;

use RubenSteeb\TestModelRelations\Domain\Model\RelationClass;

class SecondRelationClass extends RelationClass
{
	/**
	* only extend classes have this
	*
	* @var string
	*/
	protected $extendProperty;
	
	/**
	* objectStorage towards second Level Object	
	*
	* @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RubenSteeb\TestModelRelations\Domain\Model\SecondLevelObjectWithStorage>
	*/
	protected $secondLevel;
	
	/**
	* initialize ObjectStorage
	*/
	protected function initObjectStorage()
	{
		$this->setSecondLevel(new \TYPO3\CMS\Extbase\Persistence\ObjectStorage());
	}
	
	/**
	* SecondRelationClass Constructor
	*/
	public function __construct()
	{
		$this->initObjectStorage();
	}
	
	/**
	* set ObjectStorage
	*
	* @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage>\RubenSteeb\TestModelRelations\Domain\Model\SecondLevelObjectWithStorage>
	* @return void
	*/
	public function setSecondLevel(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage)
	{
		$this->secondLevel = $objectStorage;
	}
	
	/**
	* getObjectStorage
	*
	* @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RubenSteeb\TestModelRelations\Domain\Model\SecondLevelObjectWithStorage>	
	*/
	public function getSecondLevel()
	{
		return clone $this->secondLevel;
	}
	
	
	/**
	* set extend property,
	*
	* @param string
	* @return string
	*/
	public function setExtendProperty(string $extendProperty)
	{
		$this->extendProperty = $extendProperty;
	}
	
	/**
	* get extend Property
	*
	* @return string
	*/
	public function getExtendProperty()
	{
		return $this->extendProperty;
	}
	
}