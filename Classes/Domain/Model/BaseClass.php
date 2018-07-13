<?php
namespace RubenSteeb\TestModelRelations\Domain\Model;

use RubenSteeb\TestModelRelations\Domain\Model\SuperClass;

class BaseClass extends SuperClass
{
	/**
	* objectStorage for RelationClass
	*
	* @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RubenSteeb\TestModelRelations\Domain\Model\RelationClass>
	*/
	protected $relations;
	
	/**
	* initialize ObjectStorage
	*/
	protected function initObjectStorage()
	{
		$this->setRelations(new \TYPO3\CMS\Extbase\Persistence\ObjectStorage());
	}
	
	/*
	* constructor
	*/
	public function __construct()
	{
		$this->initObjectStorage();
	}
	
	/**
	* set the objectStorage
	*
	* @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RubenSteeb\TestModelRelations\Domain\Model\RelationClass>
	* @return void
	*/
	public function setRelations(\TYP03\CMS\Extbase\Persistence\ObjectStorage $objectStorage)
	{
		$this->relations = $objectStorage;
	}
	
	/**
	* get the objectstorage
	*
	* @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RubenSteeb\TestModelRelations\Domain\Model\RelationClass>
	*/
	public function getRelations()
	{
		return clone $this->relations;
	}
	
	/**
	* add a relation to the objectstorage
	*
	* @param \RubenSteeb\TestModelRelations\Domain\Model\RelationClass
	* @return void
	*/
	public function addRelation(\RubenSteeb\TestModelRelations\Domain\Model\RelationClass $relation)
	{
		$this->relations->attach($relation);
	}
	
	/**
	* detach a relation from the objectstorage
	*
	* @param \RubenSteeb\TestModelRelations\Domain\Model\RelationClass
	* @return void
	*/
	public function removeRelation(\RubenSteeb\TestModelRelations\Domain\Model\RelationClass $relation)
	{
		$this->relations->detach($relation);
	}
}