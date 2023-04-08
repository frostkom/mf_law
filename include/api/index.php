<?php

if(!defined('ABSPATH'))
{
	header('Content-Type: application/json');

	$folder = str_replace("/wp-content/plugins/mf_law/include/api", "/", dirname(__FILE__));

	require_once($folder."wp-load.php");
}

do_action('run_cache', array('suffix' => 'json'));

$obj_law = new mf_law();

$json_output = array(
	'success' => false,
);

$type = check_var('type');
$arr_type = explode("/", $type);

switch($arr_type[0])
{
	case 'admin':
		if(is_user_logged_in())
		{
			switch($arr_type[1])
			{
				case 'law':
					switch($arr_type[2])
					{
						case 'list':
							$obj_law->list_id = isset($arr_type[3]) && is_numeric($arr_type[3]) ? $arr_type[3] : 1;
							$current_page = isset($arr_type[4]) && is_numeric($arr_type[4]) ? $arr_type[4] : 1;
							$edit_page_per_page = get_the_author_meta_or_default('edit_page_per_page', get_current_user_id(), 20);

							$tbl_group = new mf_law_table();

							$tbl_group->select_data(array(
								'select' => $wpdb->prefix."law.lawID, lawValid, lawNo, lawName",
								'where' => "listID = '".esc_sql($obj_law->list_id)."'",
								'sort_data' => true,
								//'debug' => true,
							));

							$arr_pages = $arr_list = array();
							$list_amount = 0;

							$obj_law->get_search_form();

							$obj_law->arr_groups = $obj_law->get_list_groups_2(array('hierarchical' => true));

							if(count($obj_law->arr_groups) > 0)
							{
								$arr_list = $obj_law->output_groups(array('output_type' => 'array', 'arr_groups' => $obj_law->arr_groups)); //, 'arr_header' => $arr_header
							}

							$filters = show_textfield(array('name' => $tbl_group->search_key, 'value' => $tbl_group->search, 'placeholder' => __("Search", 'lang_law')));

							$obj_list = new mf_list();

							$json_output['success'] = true;
							$json_output['admin_law_response'] = array(
								'type' => $arr_type[0]."_".$arr_type[1]."_".$arr_type[2],
								'filters' => $filters,
								/*'pagination' => array(
									'list_amount' => $list_amount,
									'current_page' => $current_page,
									'pages' => $arr_pages,
								),*/
								'list_id' => $obj_law->list_id,
								'list_name' => $obj_list->get_name(array('id' => $obj_law->list_id)),
								'list' => $arr_list,
							);
						break;
					}
				break;
			}
		}

		else
		{
			$json_output['redirect'] = wp_login_url();
		}
	break;

	case 'table_search':
		if(is_user_logged_in())
		{
			$tbl_group = new mf_law_table();

			$tbl_group->select_data(array(
				'select' => "lawNo, lawName",
				'limit' => 0, 'amount' => 10,
				'sort_data' => true,
			));

			foreach($tbl_group->data as $r)
			{
				$json_output[] = ($r['lawNo'] != '' ? $r['lawNo']." " : "").$r['lawName'];
			}
		}
	break;
}

echo json_encode($json_output);