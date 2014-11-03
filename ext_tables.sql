#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (

	customer_number varchar(255) DEFAULT '' NOT NULL,
	user_language int(11) unsigned DEFAULT '0' NOT NULL,
	kanton int(11) unsigned DEFAULT '0' NOT NULL,
	target_group_start varchar(255) DEFAULT '' NOT NULL,
	target_group_end varchar(255) DEFAULT '' NOT NULL,
	datasets int(11) unsigned DEFAULT '0' NOT NULL,

	tx_extbase_type varchar(255) DEFAULT '' NOT NULL,

);

#
# Table structure for table 'tx_easyvoteimporter_domain_model_dataset'
#
CREATE TABLE tx_easyvoteimporter_domain_model_dataset (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	businessuser int(11) unsigned DEFAULT '0' NOT NULL,
	file text NOT NULL,
	voting_day int(11) unsigned DEFAULT '0',
	source_dataset int(11) unsigned DEFAULT '0',
	column_configuration text NOT NULL,
	firstrow_columnnames tinyint(1) unsigned DEFAULT '0' NOT NULL,
	imported_addresses int(11) unsigned DEFAULT '0',
	processed int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),

 KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_easyvoteimporter_domain_model_address'
#
CREATE TABLE tx_easyvoteimporter_domain_model_address (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	import_file_name varchar(255) DEFAULT '' NOT NULL,
	voting_day int(11) unsigned DEFAULT '0',
	dataset int(11) unsigned DEFAULT '0',
	businessuser int(11) unsigned DEFAULT '0' NOT NULL,
	blacklisted tinyint(1) unsigned DEFAULT '0' NOT NULL,
	salutation varchar(255) DEFAULT '' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
	street varchar(255) DEFAULT '' NOT NULL,
	city varchar(255) DEFAULT '' NOT NULL,
	customer_number varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),

 KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_easyvoteimporter_domain_model_blacklist'
#
CREATE TABLE tx_easyvoteimporter_domain_model_blacklist (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	first_name varchar(255) DEFAULT '' NOT NULL,
	last_name varchar(255) DEFAULT '' NOT NULL,
	street varchar(255) DEFAULT '' NOT NULL,
	zip_code varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),

 KEY language (l10n_parent,sys_language_uid)

);