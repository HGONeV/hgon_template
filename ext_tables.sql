#
# Table structure for table 'tx_hgontemplate_domain_model_didyouknow'
#
CREATE TABLE tx_hgontemplate_domain_model_didyouknow (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	description text,
	sys_category varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted smallint(5) unsigned DEFAULT '0' NOT NULL,
	hidden smallint(5) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,
	l10n_state text,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY language (l10n_parent,sys_language_uid)

);


#
# Table structure for table 'tx_hgontemplate_domain_model_eventculinary'
#
CREATE TABLE tx_hgontemplate_domain_model_eventculinary (

    uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,

    # Workaround: #1472074485: Unknown column 'tx_hgontemplate_domain_model_eventculinary.start' in 'order clause'
	start int(11) unsigned DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	description text,
	price varchar(255) DEFAULT '' NOT NULL,
	date int(11) unsigned DEFAULT '0' NOT NULL,

	event int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,
	l10n_state text,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_hgontemplate_domain_model_registrationaddon'
#
CREATE TABLE tx_hgontemplate_domain_model_registrationaddon (

    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,

    registration int(11) unsigned DEFAULT '0' NOT NULL,
    culinary int(11) unsigned DEFAULT '0' NOT NULL,
    quantity int(11) unsigned DEFAULT '0' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,
    description text,
    unit_price decimal(10,2) DEFAULT '0.00' NOT NULL,
    selected_date int(11) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY registration (registration),
    KEY culinary (culinary)

);

#
# Table structure for table 'tx_news_domain_model_news'
#
CREATE TABLE tx_news_domain_model_news (

	tx_hgontemplate_type int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_youtube_video_id varchar(255) DEFAULT '' NOT NULL,
	tx_hgontemplate_header_image int(11) unsigned NOT NULL default '0'
);


#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_hgontemplate_contactperson int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_article_image int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_teaser_text text
);


#
# Table structure for table 'tx_sfeventmgt_domain_model_event'
#
CREATE TABLE tx_sfeventmgt_domain_model_event (
    tx_hgontemplate_online_event tinyint(4) unsigned DEFAULT '0' NOT NULL,
    tx_hgontemplate_event_type varchar(32) DEFAULT 'standard' NOT NULL,
    tx_hgontemplate_eventculinary int(11) unsigned DEFAULT '0' NOT NULL,
    tx_hgontemplate_registration_mode varchar(32) DEFAULT 'native' NOT NULL,
    tx_hgontemplate_registration_form varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_mdnewsauthor_domain_model_newsauthor'
#
CREATE TABLE tx_mdnewsauthor_domain_model_newsauthor (
  tx_hgontemplate_short_description varchar(255) DEFAULT '' NOT NULL,
  tx_hgontemplate_longer_description text NOT NULL,
  phone2 varchar(255) DEFAULT '' NOT NULL
);
