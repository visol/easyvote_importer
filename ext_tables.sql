#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (

	customer_number varchar(255) DEFAULT '' NOT NULL,
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
	column_configuration text NOT NULL,
	firstrow_columnnames tinyint(1) unsigned DEFAULT '0' NOT NULL,

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
# Table structure for table 'tx_easyvoteimporter_domain_model_dataset'
#
CREATE TABLE tx_easyvoteimporter_domain_model_dataset (

	businessuser  int(11) unsigned DEFAULT '0' NOT NULL,

);