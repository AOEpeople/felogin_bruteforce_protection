#
# Table structure for table 'tx_feloginbruteforceprotection_domain_model_entry'
#
CREATE TABLE tx_feloginbruteforceprotection_domain_model_entry (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	identifier varchar(255) DEFAULT '' NOT NULL,
	failures int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);