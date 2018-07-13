<?php
$EM_CONF[$_EXTKEY] = [
	'title' => 'Test Model Relations',
	'desciption' => 'An extension to test model Relations',
	'category' => 'plugin',
	'author' => 'Ruben Steeb',
	'author_company' => 'FARO',
	'author_email' => 'ruben.steeb@faro.com',
	'state' => 'alpha',
	'clearCacheOnLoad' => true,
	'version' => '0.0.0',
	'constrains' => [
		'depends' => [
			'typo3' => '8.7.0-8.9.99',
		]
	],
	'autoload' => [
		'psr-4' => [
			'RubenSteeb\\TestModelRelations\\' => 'Classes',
		]
	]	
];