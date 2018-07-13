<?php
return [
	'ctrl' => [
		'label' => 'name',
		'title' => 'Model Relations SecondLevelWithObjectStorage',		
		'tstamp' => 'tstamp',
		'type' => 'field_type',
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
		'name' => [
			'label' => 'Name',
			'config' => [
				'type' => 'input',
				'max' => 255,
				'eval' => 'trim, required',
			]
		],
		'last_level_object' => [
			'label' => 'lastlevelObjects',
			'config' => [
				'type' => 'select',
				'foreign_table' => 'tx_testmodelrelations_domain_model_lastlevelobject',				
			]
		],
		'field_type' => [
			'label' => 'fieldtype',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['SecondLevelWithObjectStorage', 'RubenSteeb\TestModelRelations\Domain\Model\SecondLevelObjectWithStorage'],
				]
			]
		]
	],
	'types' => [
		'0' => ['showitem' => 'field_type, name, last_level_object']
	]
];
	