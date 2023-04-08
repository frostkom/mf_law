<?php
/*
Plugin Name: MF Law
Plugin URI: 
Description: 
Version: 7.6.11
Author: Martin Fors
Author URI: http://martinfors.se
Text Domain: lang_law
Domain Path: /lang

Depends: MF Base
*/

if(is_plugin_active("mf_base/index.php"))
{
	include_once("include/classes.php");

	load_plugin_textdomain('lang_law', false, dirname(plugin_basename(__FILE__)).'/lang/');

	$obj_law = new mf_law();

	add_action('cron_base', 'activate_law', mt_rand(1, 10));

	if(is_admin())
	{
		register_activation_hook(__FILE__, 'activate_law');
		register_uninstall_hook(__FILE__, 'uninstall_law');

		add_action('admin_init', array($obj_law, 'admin_init'), 0);
		add_action('admin_menu', array($obj_law, 'admin_menu'));

		add_action('deleted_user', array($obj_law, 'deleted_user'));
	}

	else
	{
		add_action('wp_head', array($obj_law, 'wp_head'), 0);
	}

	add_filter('filter_is_file_used', array($obj_law, 'filter_is_file_used'));

	add_action('widgets_init', array($obj_law, 'widgets_init'));

	function activate_law()
	{
		global $wpdb;

		$default_charset = (DB_CHARSET != '' ? DB_CHARSET : 'utf8');

		$arr_add_column = $arr_update_column = $arr_add_index = array();

		$wpdb->query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."law (
			lawID INT UNSIGNED NOT NULL AUTO_INCREMENT,
			lawChapterID INT UNSIGNED NOT NULL DEFAULT '0',
			lawTypeID INT UNSIGNED NOT NULL DEFAULT '0',
			lawNo VARCHAR(30),
			lawDecided DATE,
			lawComesInEffect DATE,
			lawTransitionalDate DATE,
			lawUpdatedTo VARCHAR(20),
			lawReleased DATE,
			lawValid DATE,
			lawValidTo DATE,
			lawName VARCHAR(255),
			lawText TEXT,
			lawChanges TEXT,
			lawLink VARCHAR(400),
			lawCreated DATETIME,
			userID INT UNSIGNED NOT NULL DEFAULT '0',
			lawDeleted ENUM('0', '1') NOT NULL DEFAULT '0',
			lawDeletedDate DATETIME DEFAULT NULL,
			lawDeletedID INT UNSIGNED DEFAULT '0',
			PRIMARY KEY (lawID),
			KEY lawNo (lawNo),
			KEY lawName (lawName),
			KEY lawTypeID (lawTypeID),
			KEY lawChapterID (lawChapterID),
			KEY lawCreated (lawCreated),
			KEY userID (userID),
			KEY lawDeleted (lawDeleted)
		) DEFAULT CHARSET=".$default_charset);

		$arr_add_column[$wpdb->prefix."law"] = array(
			//'lawTransitionalDate' => "ALTER TABLE [table] ADD [column] DATE AFTER lawComesInEffect",
		);

		$arr_update_column[$wpdb->prefix."law"] = array(
			//'' => "ALTER TABLE [table] DROP [column]",
			//'lawNo' => "ALTER TABLE [table] CHANGE [column] [column] VARCHAR(30)",
		);

		$arr_add_index[$wpdb->prefix."law"] = array(
			//'userID' => "ALTER TABLE [table] ADD INDEX [column] ([column])",
		);

		$wpdb->query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."law2law (
			lawID INT UNSIGNED,
			lawID_parent INT UNSIGNED,
			KEY lawID (lawID),
			KEY lawID_parent (lawID_parent)
		) DEFAULT CHARSET=".$default_charset);

		$wpdb->query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."law2list (
			lawID INT UNSIGNED,
			listID INT UNSIGNED,
			lawPublished ENUM('0', '1') NOT NULL DEFAULT '0',
			lawPublishedDate DATETIME,
			KEY lawID (lawID),
			KEY listID (listID),
			KEY lawPublished (lawPublished),
			KEY lawPublishedDate (lawPublishedDate)
		) DEFAULT CHARSET=".$default_charset);

		$arr_add_index[$wpdb->prefix."law2list"] = array(
			//'lawPublished' => "ALTER TABLE [table] ADD INDEX [column] ([column])",
		);

		$wpdb->query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."law_status (
			statusID INT UNSIGNED NOT NULL AUTO_INCREMENT,
			lawID INT UNSIGNED,
			listID INT UNSIGNED,
			statusType VARCHAR(10) NOT NULL DEFAULT '',
			statusCreated DATETIME,
			userID INT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (statusID),
			KEY lawID (lawID),
			KEY listID (listID),
			KEY statusType (statusType),
			KEY statusCreated (statusCreated)
		) DEFAULT CHARSET=".$default_charset);

		$arr_add_index[$wpdb->prefix."law_status"] = array(
			//'lawID' => "ALTER TABLE [table] ADD INDEX [column] ([column])",
		);

		$wpdb->query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."law2file (
			lawID INT UNSIGNED,
			fileID INT UNSIGNED,
			KEY lawID (lawID)
		) DEFAULT CHARSET=".$default_charset);

		add_columns($arr_add_column);
		update_columns($arr_update_column);
		add_index($arr_add_index);

		delete_base(array(
			'table' => "law",
			'field_prefix' => "law",
			'child_tables' => array(
				'law2law' => array(
					'action' => "delete",
					'field_prefix' => "law",
				),
				'law2list' => array(
					'action' => "delete",
					'field_prefix' => "law",
				),
				'law2file' => array(
					'action' => "delete",
					'field_prefix' => "law",
				),
				'law_info' => array(
					'action' => "trash",
					'field_prefix' => "lawInfo",
				),
			),
		));
	}

	function uninstall_law()
	{
		mf_uninstall_plugin(array(
			'uploads' => "mf_law",
			'tables' => array('law', 'law2law', 'law2list', 'law2file'),
		));
	}
}