<?php

$obj_law = new mf_law();
$obj_law->fetch_request();

echo $obj_law->save_data();

if($obj_law->list_id > 0)
{
	$obj_list = new mf_list(array('id' => $obj_law->list_id));
	$strListName = $obj_list->get_name();

	$obj_law->is_other_req = $obj_list->is_other_req();

	if($strListName == '')
	{
		$obj_law->list_id = 0;
	}
}

if(!isset($obj_law->is_other_req) || $obj_law->is_other_req == false)
{
	$arr_header[] = __("Law Number", 'lang_law');
}

$arr_header[] = __("Name", 'lang_law');

if(is_array($obj_law->column_ids))
{
	$arr_data_columns = $obj_law->get_data_columns_for_select();

	foreach($obj_law->column_ids as $key)
	{
		if(isset($arr_data_columns[$key]))
		{
			$arr_header[] = $arr_data_columns[$key];
		}
	}
}

$table_body = "";

$obj_law->arr_groups = $obj_law->get_list_groups_2(array('hierarchical' => true));

if(count($obj_law->arr_groups) > 0)
{
	$table_body = $obj_law->output_groups(array(
		'arr_groups' => $obj_law->arr_groups,
		'arr_header' => $arr_header,
	));
}

echo "<div class='wrap'>
	<h2>"
		.($obj_law->list_id > 0 ? $strListName : __("Laws", 'lang_law'));

		if($table_body != '')
		{
			echo "<a href='#' class='add-new-h2 toggle_all'>".__("Toggle All", 'lang_law')."</a>";

			if(IS_EDITOR && $obj_list->id > 0)
			{
				echo "<a href='".admin_url("admin.php?page=mf_list/compare/index.php&intListID=".$obj_list->id)."' class='add-new-h2'>".__("Add new law to the list", 'lang_law')."</a>";
			}

			echo "<a href='#' onclick='window.print()' class='add-new-h2'>".__("Print", 'lang_law')."</a>";

			if($obj_list->id > 0 && $obj_list->has_permission(array('rights' => array('editor'))))
			{
				switch($obj_law->display_mode)
				{
					case 'published':
						$archive_url = wp_nonce_url(admin_url("admin.php?page=mf_law/list/index.php&btnListArchive&intListID=".$obj_list->id), 'list_archive_'.$obj_list->id, '_wpnonce_list_archive');
						$archive_attr = " class='add-new-h2' rel='confirm' confirm_text='".__("Are you sure? This will archive all laws in this list and reset requiements.", 'lang_law')."'";

						$reset_requirements_url = wp_nonce_url(admin_url("admin.php?page=mf_law/list/index.php&btnListResetRequirements&intListID=".$obj_list->id), 'list_reset_requirements_'.$obj_list->id, '_wpnonce_list_reset_requirements');
						$reset_requirements_attr = " class='add-new-h2' rel='confirm'";
					break;

					default:
						$archive_url = $reset_requirements_url = "#";
						$archive_attr = $reset_requirements_attr = " class='add-new-h2 is_disabled' title='".__("This is only active in public display mode", 'lang_law')."'";
					break;
				}

				echo "<a href='".$archive_url."'".$archive_attr.">".__("Archive Evaluation", 'lang_law')."</a>"
				."<a href='".$reset_requirements_url."'".$archive_attr.">".__("Reset Requirements", 'lang_law')."</a>";
			}
		}

	echo "</h2>"
	.get_notification();

	if($table_body == '')
	{
		$table_body = "<tr><td colspan='".count($arr_header)."'>".__("There are no laws in this list", 'lang_law')."</td></tr>";
	}

	$table_header = show_table_header($arr_header, false);

	echo $obj_law->get_search_list_law()
	."<table class='wp-list-table widefat striped'>"
		.$table_header
		."<tbody>".$table_body."</tbody>"
		.$table_header
	."</table>
</div>";