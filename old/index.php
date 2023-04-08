<?php

if(!isset($_GET['lawDeleted']) || $_GET['lawDeleted'] != 1)
{
	mf_redirect(admin_url(str_replace("/wp-admin/", "", $_SERVER['REQUEST_URI']."&lawDeleted=1")));
}

$obj_law = new mf_law();
$obj_law->fetch_request();

echo $obj_law->save_data();

echo "<div class='wrap'>
	<h2>".__("Trash", 'lang_law')."</h2>"
	.get_notification();

	$tbl_group = new mf_law_table();

	$tbl_group->select_data(array(
		'select' => $wpdb->prefix."law.lawID, lawNo, lawValid, lawValidTo, lawName, lawDeleted, lawReleased, lawUpdatedTo, ".$wpdb->prefix."law.lawChapterID, ".$wpdb->prefix."law.lawTypeID, lawLink, ".$wpdb->prefix."law.userID",
		//'debug' => true,
	));

	$tbl_group->do_display();

echo "</div>";