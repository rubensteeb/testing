<?php
return [
	'ctrl' => [
		'label' => 'name',
		'title' => 'Model Relations RelationClass',	
		'type' => 'field_type',
		'tstamp' => 'tstamp',		
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'crdate',		
		'delete' => 'deleted',		
		'hideAtCopy' => true,
		'prependAtCopy' => ' - Copy',
		'enableColumns' => [
			'disabled' => 'hidden',
		],
		'searchFields' => 'title, property',
	],
	'columns' => [
		'field_type' => [
			'label' => 'Extended or Base RelationClass',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['RelationClass', 'RubenSteeb\TestModelRelations\Domain\Model\RelationClass'],
					['SecondRelationClass', 'RubenSteeb\TestModelRelations\Domain\Model\SecondRelationClass'],
				]
			]
		],
		'name' => [
			'label' => 'Name of the Relation Instance',
			'config' => [
				'type' => 'text',
				'eval' => 'trim',
				'max' => 255,
				'placeholder' => 'Name of the Relation Class',
			]
		],
		'property' => [
			'label' => 'Example Property for the Relation Class',
			'config' => [
				'type' => 'text',
				'eval' => 'trim',
				'max' => 255,
				'placeholder' => 'Example Property for the Relation Class',
			]			
		],
		'extend_property' => [
			'label' => 'Extend Property',
			'config' => [
				'type' => 'text',
				'eval' => 'trim',
				'max' => 255,
			]
		],
		'superclasses' => [
			'label' => 'BaseClasses that relate to this Instance',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_testmodelrelations_domain_model_superclass',
				'MM' => 'tx_testmodelrelations_superclass_relationclass_mm',
				'MM_opposite_field' => 'relations',
				'readOnly' => true,
			]
		],
		'second_level' => [
			'label' => 'secondLevelObject',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'tx_testmodelrelations_domain_model_secondlevelobjectwithstorage'
			]
		]
	],
	'types' => [
		'0' => ['showitem' => 'field_type, name, property, superclasses'],
		'RubenSteeb\TestModelRelations\Domain\Model\RelationClass' => ['showitem' => 'field_type, name, property, superclasses'],
		'RubenSteeb\TestModelRelations\Domain\Model\SecondRelationClass' => ['showitem' => 'field_type, name, property, extend_property, superclasses, second_level'],
 	]
];