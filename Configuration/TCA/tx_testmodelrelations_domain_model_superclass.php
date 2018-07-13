<?php
return [
	'ctrl' => [
		'label' => 'name',
		'title' => 'Model Relations SuperClass',		
		'tstamp' => 'tstamp',
		'type' => 'field_type',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'crdate',		
		'delete' => 'deleted',		
		'hideAtCopy' => true,
		'prependAtCopy' => ' - Copy',		
		'translationSource' => 'l18n_parent',
		'transOrigDiffsource' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'enableColumns' => [
			'disabled' => 'hidden',
			],
		'searchFields' => 'title, property',
	],
	'columns' => [
		'name' => [
			'label' => 'Name of the Instance',
			'config' => [
				'type' => 'text',
				'eval' => 'trim',
				'max' => 255,
			]			
		],
		'field_type' => [
			'label' => 'SuperClass or BaseClass?',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['SuperClass', 'RubenSteeb\TestModelRelations\Domain\Model\SuperClass'],
					['BaseClass', 'RubenSteeb\TestModelRelations\Domain\Model\BaseClass'],
				]
			]
		],
		'relations' => [
			'label' => 'Relations to RelationClass',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_testmodelrelations_domain_model_relationclass',
				'MM' => 'tx_testmodelrelations_superclass_relationclass_mm',
				'MM_opposite_field' => 'superclasses',
			]
		]
	],
	'types' => [
		'0' => ['showitem' => 'field_type, name'],
		'RubenSteeb\TestModelRelations\Domain\Model\SuperClass' => ['showitem' => 'field_type, name'],
		'RubenSteeb\TestModelRelations\Domain\Model\BaseClass' => ['showitem' => 'field_type, name, relations'],
	]
];