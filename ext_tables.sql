CREATE TABLE tx_testmodelrelations_domain_model_superclass (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	
	name varchar(255) DEFAULT '0' NOT NULL,
	field_type varchar(255) DEFAULT'0' NOT NULL,
	relations int(11) DEFAULT'0' NOT NULL,
			
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	access_group int(11) DEFAULT '0' NOT NULL,	
		
	PRIMARY KEY(uid),	
);

CREATE TABLE tx_testmodelrelations_domain_model_relationclass (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	
	field_type varchar(255) DEFAULT '0' NOT NULL,
	name varchar(255) DEFAULT '0' NOT NULL,
	property varchar (255) DEFAULT '0' NOT NULL,
	extend_property varchar(255) DEFAULT '0' NOT NULL,
	superclasses int(11) DEFAULT '0' NOT NULL,
	second_level int(11) DEFAULT '0' NOT NULL,
	
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	access_group int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY(uid),	
);

CREATE TABLE tx_testmodelrelations_superclass_relationclass_mm (
	
	uid int(11) DEFAULT '0' NOT NULL auto_increment,
	
	uid_local int(11) NOT NULL DEFAULT '0',
	uid_foreign int(11) NOT NULL DEFAULT '0',
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY(uid),
	KEY uid_local(uid_local),
	KEY uid_foreign(uid_foreign),
);

CREATE TABLE tx_testmodelrelations_domain_model_secondlevelobjectwithstorage (	
	uid int(11) DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	
	name varchar(255) DEFAULT '0' NOT NULL,
	last_level_object int(11) DEFAULT '0' NOT NULL,
	field_type varchar(255) DEFAULT '0' NOT NULL,
		
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	access_group int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY(uid),
);
CREATE TABLE tx_testmodelrelations_domain_model_lastlevelobject (	
	uid int(11) DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	
	name varchar(255) DEFAULT '0' NOT NULL,
	field_type varchar(255) DEFAULT '0' NOT NULL,
	
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	access_group int(11) DEFAULT '0' NOT NULL,	
	
	PRIMARY KEY(uid),
);

