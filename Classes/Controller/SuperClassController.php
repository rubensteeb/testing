<?php
namespace RubenSteeb\TestModelRelations\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use RubenSteeb\TestModelRelations\Domain\Repository\SuperClassRepository;

class SuperClassController extends ActionController
{
	/*
	*@var SuperClassRepository
	*/
	protected $superClassRepository;
	
	/**	
	* @param SuperClassRepository
	*/
	public function injectSuperClassRepository(SuperClassRepository $superClassRepository)
	{
		$this->superClassRepository = $superClassRepository;
	}
	
	/**
	* List Action
	*
	* @return void
	*/
	public function listAction()
	{			
		$this->view->assign('superclass', $this->superClassRepository->findAll());
	}	
}