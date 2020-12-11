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
# Table structure for table 'tx_hgonpayment_domain_model_article'
#
CREATE TABLE tx_hgonpayment_domain_model_article (
	tx_hgontemplate_subtitle varchar(255) DEFAULT '' NOT NULL,
	tx_hgontemplate_image int(11) unsigned NOT NULL default '0',
	tx_hgontemplate_link varchar(255) DEFAULT '' NOT NULL,
);


#
# Table structure for table 'tx_news_domain_model_news'
#
CREATE TABLE tx_news_domain_model_news (

    tx_rkwproject_project int(11) unsigned DEFAULT '0',
	tx_hgontemplate_type int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_youtube_video_id varchar(255) DEFAULT '' NOT NULL,
	tx_hgontemplate_header_image int(11) unsigned NOT NULL default '0',

);


#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_hgontemplate_contactperson int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_article int(11) unsigned DEFAULT '0' NOT NULL,
);


#
# Table structure for table 'tx_rkwevents_domain_model_event'
#
CREATE TABLE tx_rkwevents_domain_model_event (
	tx_hgontemplate_eventculinary int(11) unsigned DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tx_rkwevents_domain_model_eventreservation'
#
CREATE TABLE tx_rkwevents_domain_model_eventreservation (
	tx_hgontemplate_eventculinary int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_eventcosts varchar(255) DEFAULT '' NOT NULL,
	tx_hgontemplate_paymenttype int(11) unsigned DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tx_rkwnewsletter_domain_model_newsletter'
#
CREATE TABLE tx_rkwnewsletter_domain_model_newsletter (
    tx_hgontemplate_is_internal int(11) unsigned DEFAULT '0' NOT NULL,

	tx_hgontemplate_news_select int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_news_count int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_news_list int(11) DEFAULT '0' NOT NULL,

	tx_hgontemplate_article_select int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_article_count int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_article_list int(11) DEFAULT '0' NOT NULL,

	tx_hgontemplate_event_select int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_event_count int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_event_list int(11) DEFAULT '0' NOT NULL,

	tx_hgontemplate_donation_select int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_donation_count int(11) unsigned DEFAULT '0' NOT NULL,
	tx_hgontemplate_donation_list int(11) DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tx_rkwnewsletter_domain_model_newsletter_news_mm'
#
CREATE TABLE tx_rkwnewsletter_domain_model_newsletter_news_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_rkwnewsletter_domain_model_newsletter_article_mm'
#
CREATE TABLE tx_rkwnewsletter_domain_model_newsletter_article_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_rkwnewsletter_domain_model_newsletter_event_mm'
#
CREATE TABLE tx_rkwnewsletter_domain_model_newsletter_event_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_rkwnewsletter_domain_model_newsletter_donation_mm'
#
CREATE TABLE tx_rkwnewsletter_domain_model_newsletter_donation_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_rkwprojects_domain_model_projects'
#
CREATE TABLE tx_rkwprojects_domain_model_projects (
    tx_hgontemplate_bank_header varchar(255) DEFAULT '' NOT NULL,
    tx_hgontemplate_bank_institute varchar(255) DEFAULT '' NOT NULL,
	tx_hgontemplate_bank_iban varchar(255) DEFAULT '' NOT NULL,
	tx_hgontemplate_bank_bic varchar(255) DEFAULT '' NOT NULL,
);



#
# Table structure for table 'tx_rkwauthors_domain_model_authors'
#
CREATE TABLE tx_rkwauthors_domain_model_authors (
  tx_hgontemplate_short_description varchar(255) DEFAULT '' NOT NULL,
  tx_hgontemplate_longer_description text NOT NULL,
);