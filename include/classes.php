<?php

class mf_law
{
	function __construct($id = 0)
	{
		if($id > 0)
		{
			$this->id = $id;
		}

		else
		{
			$this->id = check_var('intLawID');
		}

		$this->is_updating = $this->id > 0;

		$this->lists = $this->lists_published = array();
	}

	function get_data_columns_for_select($data = array())
	{
		if(!isset($data['include_all'])){		$data['include_all'] = false;}

		$arr_data = array();
		$arr_data['created'] = __("Created", 'lang_law');

		if(isset($this->list_id) && $this->list_id > 0 || $data['include_all'] == true)
		{
			$arr_data[1] = __("What does the law say?", 'lang_law');
			$arr_data['effects_on_company'] = __("Effects on the company", 'lang_law'); // Previous key = 2
			$arr_data['requirements_met'] = __("Requirements met", 'lang_law');
			$arr_data['evaluation'] = __("Evaluation", 'lang_law'); // Previous key = 3
		}

		$arr_data[4] = __("Receipt", 'lang_law');
		$arr_data[5] = __("Comply with requirements", 'lang_law');

		if(IS_ADMIN)
		{
			$arr_data['replaced_by'] = __("Replaced by", 'lang_law');
			$arr_data['replaces'] = __("Is replacing these laws", 'lang_law');
		}

		return $arr_data;
	}

	function get_previous_and_next_laws($arr_groups)
	{
		foreach($arr_groups as $arr_group)
		{
			foreach($arr_group['laws'] as $arr_law)
			{
				if($this->law_id_next > 0)
				{
					break 2;
				}

				if($arr_law->lawID == $this->id)
				{
					$this->law_id_current = $arr_law->lawID;

					if($this->law_id_prev_temp > 0)
					{
						$this->law_id_prev = $this->law_id_prev_temp;
					}
				}

				else if($this->law_id_current > 0)
				{
					$this->law_id_next = $arr_law->lawID;
				}

				$this->law_id_prev_temp = $arr_law->lawID;
			}

			if(isset($arr_group['sub']))
			{
				$this->get_previous_and_next_laws($arr_group['sub']);
			}
		}
	}

	function get_base_urls($data = array())
	{
		if(!isset($data['is_admin_ui'])){	$data['is_admin_ui'] = is_admin();}
		if(!isset($data['invert'])){		$data['invert'] = false;}

		if($data['invert'] == true)
		{
			$data['is_admin_ui'] = !$data['is_admin_ui'];
		}

		if($data['is_admin_ui'])
		{
			$post_base_edit_url = admin_url("admin.php?page=mf_law/create/index.php");
			$post_list_url = admin_url("admin.php?page=mf_law/list/index.php");
		}

		else
		{
			$post_base_edit_url = "?view=edit";
			$post_list_url = "?view=list";
		}

		return array($post_base_edit_url, $post_list_url);
	}

	function get_law_navigation()
	{
		$out = "";

		if($this->id > 0)
		{
			list($post_base_edit_url, $post_list_url) = $this->get_base_urls();

			if($this->list_id > 0)
			{
				$post_base_edit_url .= "&intListID=".$this->list_id;
				$post_list_url .= "&intListID=".$this->list_id;
			}

			$out .= "<div".(is_admin() ? "" : " class='form_button'").">";

				$this->law_id_current = $this->law_id_prev = $this->law_id_prev_temp = $this->law_id_next = 0;

				//Comes from list specific laws (/wp-admin/admin.php?page=mf_law/list/index.php&intListID=x)
				if($this->list_id > 0)
				{
					$out .= "<a href='".$post_list_url."' class='".(is_admin() ? "add-new-h2" : "button")."'>&laquo; ".__("Back to list", 'lang_law')."</a>";

					/*if(IS_EDITOR)
					{
						$out .= "<a href='".$post_list_url."&display_mode=preview"."' class='".(is_admin() ? "add-new-h2" : "button")."'>&laquo; ".__("Back to preview", 'lang_law')."</a>";
					}*/

					$post_edit_url = $post_base_edit_url."&intLawID=";

					$this->arr_groups = $this->get_list_groups_2(array('hierarchical' => true));

					$this->get_previous_and_next_laws($this->arr_groups);
				}

				//Comes from all laws (/wp-admin/admin.php?page=mf_law/list/index.php)
				else
				{
					$out .= "<a href='".$post_list_url."' class='".(is_admin() ? "add-new-h2" : "button")."'>&laquo; ".__("Back to list", 'lang_law')."</a>";

					$prev_temp = $now_temp = 0;

					$tbl_group = new mf_law_table();

					$tbl_group->select_data(array(
						//'select' => "lawID", //This messes up the order
						'sort_data' => true,
					));

					foreach($tbl_group->data as $r)
					{
						if(!($this->law_id_next > 0) && $now_temp > 0)
						{
							$this->law_id_next = $r['lawID'];
						}

						if($this->id == $r['lawID'])
						{
							$now_temp = $r['lawID'];

							if(!($this->law_id_prev > 0) && $prev_temp > 0)
							{
								$this->law_id_prev = $prev_temp;
							}
						}

						$prev_temp = $r['lawID'];

						if($this->law_id_next > 0 && $this->law_id_prev > 0)
						{
							break;
						}
					}
				}

				if($this->law_id_prev > 0 && $this->law_id_prev != $this->id)
				{
					$out .= "<a href='".$post_base_edit_url."&intLawID=".$this->law_id_prev."' class='".(is_admin() ? "add-new-h2" : "button")."'>&laquo; ".__("Previous law", 'lang_law')."</a>";
				}

				if($this->law_id_next > 0 && $this->law_id_next != $this->id)
				{
					$out .= "<a href='".$post_base_edit_url."&intLawID=".$this->law_id_next."' class='".(is_admin() ? "add-new-h2" : "button")."'>".__("Next law", 'lang_law')." &raquo;</a>";
				}

				if(IS_ADMIN)
				{
					list($post_base_edit_url, $post_list_url) = $this->get_base_urls(array('invert' => true));

					if($this->list_id > 0)
					{
						$post_base_edit_url .= "&intListID=".$this->list_id;
					}

					$post_edit_url = $post_base_edit_url."&intLawID=".$this->id;

					if(is_admin())
					{
						$post_id = apply_filters('get_widget_search', 'law-list-widget');

						if(!is_plugin_active("mf_widget_logic_select/index.php") || $post_id > 0)
						{
							$out .= "<a href='".get_permalink($post_id).$post_edit_url."' class='add-new-h2'>".__("View on Front-End", 'lang_law')."</a>";
						}
					}

					else
					{
						$out .= "<a href='".$post_edit_url."' class='button'>".__("Edit in Admin", 'lang_law')."</a>";
					}
				}

			$out .= "</div>";
		}

		return $out;
	}

	function admin_init()
	{
		global $pagenow;

		$plugin_include_url = plugin_dir_url(__FILE__);
		$plugin_version = get_plugin_version(__FILE__);

		mf_enqueue_style('style_law_wp', $plugin_include_url."style_wp.css", $plugin_version);

		if($pagenow == 'admin.php')
		{
			switch(check_var('page'))
			{
				case "mf_law/list/index.php":
					$plugin_base_include_url = plugins_url()."/mf_base/include/";

					mf_enqueue_script('script_storage', $plugin_base_include_url."jquery.Storage.js", $plugin_version);
					mf_enqueue_script('script_law', $plugin_include_url."script.js", $plugin_version);
					mf_enqueue_script('script_law_wp', $plugin_include_url."script_wp.js", $plugin_version);
				break;

				case "mf_law/create/index.php":
					mf_enqueue_style('style_select2', "//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css", $plugin_version);
					mf_enqueue_script('script_select2', "//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js", $plugin_version);

					mf_enqueue_script('script_law', $plugin_include_url."script.js", $plugin_version);
					mf_enqueue_script('script_law_wp', $plugin_include_url."script_wp.js", array('choose_here_text' => __("Choose Here", 'lang_law')), $plugin_version);
				break;
			}
		}
	}

	function admin_menu()
	{
		$menu_root = 'mf_law/';
		$menu_start = $menu_root.'list/index.php';
		$menu_capability = 'edit_posts';

		$menu_title = __("Laws", 'lang_law');
		add_menu_page($menu_title, $menu_title, $menu_capability, $menu_start, '', 'dashicons-networking', 84);

		$menu_title = __("Add New", 'lang_law');
		add_submenu_page($menu_start, $menu_title, " - ".$menu_title, $menu_capability, $menu_root.'create/index.php');

		$menu_capability = 'edit_pages';

		$menu_title = __("Export", 'lang_law');
		add_submenu_page($menu_start, $menu_title, " - ".$menu_title, $menu_capability, $menu_root.'export/index.php');

		$menu_title = __("Import", 'lang_law');
		add_submenu_page($menu_start, $menu_title, " - ".$menu_title, $menu_capability, $menu_root.'import/index.php');

		$menu_title = __("Trash", 'lang_law');
		add_submenu_page($menu_start, $menu_title, " - ".$menu_title, $menu_capability, $menu_root.'old/index.php');
	}

	function deleted_user($user_id)
	{
		global $wpdb;

		$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."law SET userID = '%d' WHERE userID = '%d'", get_current_user_id(), $user_id));
	}

	function wp_head()
	{
		if(!is_plugin_active("mf_widget_logic_select/index.php") || apply_filters('get_widget_search', 'law-list-widget') > 0)
		{
			$plugin_base_include_url = plugins_url()."/mf_base/include/";
			$plugin_include_url = plugin_dir_url(__FILE__);
			$plugin_version = get_plugin_version(__FILE__);

			mf_enqueue_style('style_law', $plugin_include_url."style.css", $plugin_version);
			mf_enqueue_script('script_storage', $plugin_base_include_url."jquery.Storage.js", $plugin_version);
			mf_enqueue_script('script_law', $plugin_include_url."script.js", $plugin_version);
		}

		if(is_plugin_active("mf_front_end_admin/index.php"))
		{
			$plugin_base_include_url = plugins_url()."/mf_front_end_admin/include/";
			$plugin_version = get_plugin_version(__FILE__);

			mf_enqueue_style('style_base_admin', $plugin_base_include_url."style.php", $plugin_version);
		}
	}

	function get_base_url($type)
	{
		switch($type)
		{
			case 'edit':
				$out = admin_url("admin.php?page=mf_law/create/index.php");
			break;

			default:
			case 'list':
				$out = admin_url("admin.php?page=mf_law/list/index.php");
			break;
		}

		if(!is_admin())
		{
			$post_id = apply_filters('get_widget_search', 'law-list-widget');

			if(!is_plugin_active("mf_widget_logic_select/index.php") || $post_id > 0)
			{
				$out = get_permalink($post_id)."?view=".$type;
			}
		}

		return $out;
	}

	function filter_is_file_used($arr_used)
	{
		global $wpdb;

		$result = $wpdb->get_results($wpdb->prepare("SELECT lawID FROM ".$wpdb->prefix."law2file WHERE fileID = '%d'", $arr_used['id']));
		$rows = $wpdb->num_rows;

		if($rows > 0)
		{
			$arr_used['amount'] += $rows;

			foreach($result as $r)
			{
				if($arr_used['example'] != '')
				{
					break;
				}

				$arr_used['example'] = admin_url("admin.php?page=mf_law/create/index.php&intLawID=".$r->lawID);
			}
		}

		return $arr_used;
	}

	function widgets_init()
	{
		register_widget('widget_law_list');
	}

	function fetch_request()
	{
		$this->id_parents = check_var('arrLawID_parents');
		$this->area_ids = check_var('arrLawAreaID');
		$this->chapter_id = check_var('intLawChapterID');
		$this->group_ids = check_var('arrLawGroupID');
		$this->type_id = check_var('intLawTypeID');
		$this->no = check_var('strLawNo');
		$this->decided = check_var('dteLawDecided');
		$this->comes_in_effect = check_var('dteLawComesInEffect');
		$this->transitional_date = check_var('dteLawTransitionalDate');
		$this->updated_to = check_var('strLawUpdatedTo');
		$this->released = check_var('dteLawReleased');
		$this->valid = check_var('dteLawValid');
		$this->valid_to = check_var('dteLawValidTo');
		$this->name = check_var('strLawName');
		$this->text = (isset($_POST['strLawText']) ? $_POST['strLawText'] : "");
		$this->changes = (isset($_POST['strLawChanges']) ? $_POST['strLawChanges'] : "");
		$this->link = check_var('strLawLink');
		$this->attachment = check_var('strLawAttachment');

		if($this->valid_to < $this->valid)
		{
			$this->valid_to = '';
		}

		$this->lists = check_var('arrListID');
		$this->lists_published = check_var('arrListPublishedID');
		$this->list_id = check_var('intListID');

		if(isset($_GET['display_mode']) && $_GET['display_mode'] != '')
		{
			$this->display_mode = check_var('display_mode', 'char', true, 'published');

			update_user_meta(get_current_user_id(), 'search_display_mode', $this->display_mode);

			$current_url = ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') ? 'http' : 'https')."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$current_url = remove_query_arg(array('display_mode'), $current_url);

			mf_redirect($current_url);
		}

		if(isset($_POST['btnLawFilter']))
		{
			$this->column_ids = check_var('arrColumnID');
			$this->responsibility_ids = check_var('arrResponsibilityID');

			$this->show_receipt = check_var('strLawShowReceipt');
			$this->show_requirements = check_var('strLawShowRequirements');
			//$this->law_responsibility = check_var('strLawResponsibility');

			$this->search = check_var('strLawSearch');
			$this->display_mode = check_var('display_mode', 'char', true, 'published');

			$this->show_old = check_var('strLawShowOld');

			$this->set_search_form();
		}

		else
		{
			$this->get_search_form();
		}
	}

	function save_data()
	{
		global $wpdb, $error_text, $done_text;

		$out = "";

		if((isset($_POST['btnLawCreate']) || isset($_POST['btnLawRevision'])) && wp_verify_nonce($_POST['_wpnonce_law_create'], 'law_create_'.$this->id))
		{
			if($this->name == '' || (isset($_POST['btnLawRevision']) && IS_EDITOR && $this->changes == ''))
			{
				$error_text = __("Please, enter all required fields", 'lang_law');
			}

			else
			{
				if($this->id > 0)
				{
					if(isset($_POST['btnLawRevision']))
					{
						$this->id_parents[] = $parent_id = $this->id;

						$this->lists = array_merge($this->lists, $this->lists_published);
						$this->lists_published = array();

						$child_id = $this->create();

						$obj_law_info = new mf_law_info();
						$obj_law_info->copy_law_info(array('law_id_from' => $parent_id, 'law_id_to' => $child_id));
					}

					else
					{
						$this->update();
					}

					$this->set_parents();
				}

				else
				{
					/*if(IS_EDITOR)
					{*/
						$wpdb->get_results($wpdb->prepare("SELECT lawID FROM ".$wpdb->prefix."law WHERE lawNo = %s OR lawName = %s LIMIT 0, 1", $this->no, $this->name));
					/*}

					else
					{
						$wpdb->get_results($wpdb->prepare("SELECT lawID FROM ".$wpdb->prefix."law WHERE lawName = %s AND userID = '%d' LIMIT 0, 1", $this->name, get_current_user_id()));
					}*/

					if($wpdb->num_rows > 0)
					{
						$error_text = __("There is already a law with that number or name", 'lang_law');
					}

					else
					{
						$this->create();

						$this->set_parents();

						if($this->id > 0)
						{
							$obj_list = new mf_list();

							$obj_list->company_id = $wpdb->get_var($wpdb->prepare("SELECT companyID FROM ".$wpdb->prefix."company INNER JOIN ".$wpdb->prefix."list USING (companyID) INNER JOIN ".$wpdb->prefix."list2user USING (listID) WHERE ".$wpdb->prefix."list2user.userID = '%d' AND listRights = %s GROUP BY companyID", get_current_user_id(), 'editor'));
							$obj_list->name = __("Other requirements", 'lang_law');

							if($obj_list->company_id > 0)
							{
								$obj_list->already_exists();

								if(!($obj_list->id > 0))
								{
									$obj_list->create();
									$obj_list->update_rights(array('type' => "editor", 'users' => array(get_current_user_id())));
								}

								if($obj_list->has_permission(array('rights' => array('editor'))))
								{
									$this->lists_published[] = $obj_list->id;
								}
							}

							/*else
							{
								do_log(sprintf("%s had no company but tried to create a new law", get_user_info()));
							}*/
						}
					}
				}

				if($this->id > 0)
				{
					$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."law2file WHERE lawID = '%d'", $this->id));

					get_attachment_callback($this->attachment, array($this, 'set_attachment'));

					$this->add2lists();
				}

				if(!isset($error_text) || $error_text == '')
				{
					mf_redirect(admin_url("admin.php?page=mf_law/list/index.php&".($this->list_id > 0 ? "intListID=".$this->list_id."&" : "").($this->is_updating ? "updated" : "created")));
				}
			}
		}

		else if(isset($_REQUEST['btnLawDelete']) && $this->id > 0 && wp_verify_nonce($_REQUEST['_wpnonce_law_delete'], 'law_delete_'.$this->id))
		{
			if($this->trash())
			{
				$done_text = __("The law was deleted", 'lang_law');
			}
		}

		else if(isset($_REQUEST['btnLawRemoveFromList']) && $this->id > 0 && wp_verify_nonce($_REQUEST['_wpnonce_law_remove'], 'law_remove_'.$this->id))
		{
			$removed = $this->remove_from_list(array('list_id' => $this->list_id, 'published' => 1));

			if($removed == false)
			{
				$removed = $this->remove_from_list(array('list_id' => $this->list_id, 'published' => 0));
			}

			if($removed)
			{
				$done_text = __("The law was removed from the list", 'lang_law');
			}

			else
			{
				$error_text = __("I could not remove the law from the list", 'lang_law');
			}
		}

		else if(isset($_REQUEST['btnLawAddListsFromParent']) && $this->id > 0 && wp_verify_nonce($_REQUEST['_wpnonce_law_add_lists_from_parent'], 'law_add_lists_from_parent_'.$this->id))
		{
			$this->get_parents(array('limit' => 1));

			$parent_id = $this->id_parents[0];

			$this->get_lists(array('law_id' => $parent_id));

			$this->lists = array_merge($this->lists, $this->lists_published);
			$this->lists_published = array();

			if(count($this->lists) > 0)
			{
				$this->add2lists();

				$done_text = sprintf(__("The law now has the same lists as it's parent (%d -> %d)", 'lang_law'), $parent_id, $this->id);
			}

			else
			{
				$error_text = __("The parent does seam to have any list either", 'lang_law');
			}
		}

		else if(isset($_REQUEST['btnListArchive']) && $this->list_id > 0 && wp_verify_nonce($_REQUEST['_wpnonce_list_archive'], 'list_archive_'.$this->list_id))
		{
			$obj_law_info = new mf_law_info();

			$this->arr_groups = $this->get_list_groups();

			foreach($this->arr_groups as $arr_group)
			{
				$arr_laws = $arr_group['laws'];

				foreach($arr_laws as $arr_law)
				{
					$intLawID = $arr_law['lawID'];

					// Archive Evaluation
					$obj_law_info->archive_key(array('law_id' => $intLawID, 'list_id' => $this->list_id, 'key' => 'evaluation'));

					// Reset Requirements
					$obj_law_info->trash_key(array('law_id' => $intLawID, 'list_id' => $this->list_id, 'key' => 'accepted'));
				}
			}

			$this->clear_cache();

			$done_text = __("The list was archived and the requirements were reset", 'lang_law');
		}

		else if(isset($_REQUEST['btnListResetRequirements']) && $this->list_id > 0 && wp_verify_nonce($_REQUEST['_wpnonce_list_reset_requirements'], 'list_reset_requirements_'.$this->list_id))
		{
			$obj_law_info = new mf_law_info();

			$this->arr_groups = $this->get_list_groups();

			foreach($this->arr_groups as $arr_group)
			{
				$arr_laws = $arr_group['laws'];

				foreach($arr_laws as $arr_law)
				{
					$intLawID = $arr_law['lawID'];

					// Reset Requirements
					$obj_law_info->trash_key(array('law_id' => $intLawID, 'list_id' => $this->list_id, 'key' => 'accepted'));
				}
			}

			$this->clear_cache();

			$done_text = __("The requirements were reset on the list", 'lang_law');
		}

		else if(isset($_GET['created']))
		{
			$done_text = __("The law was created", 'lang_law');
		}

		else if(isset($_GET['updated']))
		{
			$done_text = __("The law was updated", 'lang_law');
		}

		return $out;
	}

	function get_user()
	{
		global $wpdb;

		return $wpdb->get_var($wpdb->prepare("SELECT userID FROM ".$wpdb->prefix."law WHERE lawID = '%d'", $this->id));
	}

	function set_attachment($file_id)
	{
		global $wpdb;

		$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."law2file SET lawID = '%d', fileID = '%d'", $this->id, $file_id));
	}

	function get_parents($data = array())
	{
		global $wpdb;

		if(!isset($data['id'])){		$data['id'] = $this->id;}
		if(!isset($data['limit'])){		$data['limit'] = 0;}

		$this->id_parents = array();

		$query_limit = "";

		if($data['limit'] > 0)
		{
			$query_limit .= " LIMIT 0, ".$data['limit'];
		}

		$result = $wpdb->get_results($wpdb->prepare("SELECT lawID_parent FROM ".$wpdb->prefix."law2law WHERE lawID = '%d' ORDER BY lawID_parent ASC".$query_limit, $data['id']));

		foreach($result as $r)
		{
			$this->id_parents[] = $r->lawID_parent;
		}
	}

	function get_parent_changes()
	{
		global $wpdb;

		$out = "";

		$result = $wpdb->get_results("SELECT lawID, lawNo, lawName, lawChanges, lawCreated FROM ".$wpdb->prefix."law WHERE lawID IN ('".implode("', '", $this->id_parents)."') AND lawChanges != '' GROUP BY lawID ORDER BY lawCreated DESC");

		if($wpdb->num_rows > 0)
		{
			$out .= "<div class='law_old_versions'>"
				.get_toggler_container(array('type' => 'start', 'text' => __("Old versions", 'lang_law')))
					."<ul>";

						$strLawChanges_old = "";

						foreach($result as $r)
						{
							$intLawID = $r->lawID;
							$strLawNo = $r->lawNo;
							$strLawName = $r->lawName;
							$strLawChanges = $r->lawChanges;
							$dteLawCreated = $r->lawCreated;

							if($strLawChanges != $strLawChanges_old)
							{
								$post_edit_url = IS_ADMIN ? admin_url("admin.php?page=mf_law/create/index.php&intListID=".$this->list_id."&intLawID=".$intLawID) : "#";

								$out .= "<li>"
									.apply_filters('the_content', $strLawChanges)
									."<span class='grey'><a href='".$post_edit_url."'>".$strLawNo." ".$strLawName."</a> | ".format_date($dteLawCreated)."</span>
								</li>";

								$strLawChanges_old = $strLawChanges;
							}
						}

					$out .= "</ul>"
				.get_toggler_container(array('type' => 'end'))
			."</div>";
		}

		return $out;
	}

	function has_been_revoked($data = array())
	{
		if(!isset($data['list_id'])){		$data['list_id'] = 0;}
		if(!isset($data['valid_to'])){		$data['valid_to'] = isset($this->valid_to) ? $this->valid_to : "";}
		if(!isset($data['updated_to'])){	$data['updated_to'] = isset($this->updated_to) ? $this->updated_to : "";}

		if($data['valid_to'] > DEFAULT_DATE)
		{
			return ($data['valid_to'] < date("Y-m-d"));
		}

		else if($data['updated_to'] == __("Revoked", 'lang_law'))
		{
			return true;
		}

		else if($this->has_children(array('id' => $data['id'], 'list_id' => $data['list_id']))) // && (!($data['valid_to'] > DEFAULT_DATE) || $data['valid_to'] < date("Y-m-d"))
		{
			return true;
		}

		else
		{
			return false;
		}
	}

	function has_children($data = array())
	{
		global $wpdb;

		if(!isset($data['list_id'])){	$data['list_id'] = 0;}

		$query_join = " INNER JOIN ".$wpdb->prefix."law2list USING (lawID)";
		$query_where = " AND lawPublished = '1'";

		if($data['list_id'] > 0)
		{
			$query_where .= " AND listID = '".esc_sql($data['list_id'])."'";
		}

		$wpdb->get_results($wpdb->prepare("SELECT lawID FROM ".$wpdb->prefix."law2law".$query_join." WHERE ".$wpdb->prefix."law2law.lawID_parent = '%d'".$query_where." LIMIT 0, 1", $data['id']));

		return ($wpdb->num_rows > 0);
	}

	function clear_cache()
	{
		do_action('clear_admin_cache', "admin.php?page=mf_law");
	}

	function set_parents()
	{
		global $wpdb;

		$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."law2law WHERE lawID = '%d'", $this->id));

		foreach($this->id_parents as $parent_id)
		{
			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."law2law SET lawID = '%d', lawID_parent = '%d'", $this->id, $parent_id));
		}
	}

	function get_areas()
	{
		global $wpdb;

		$this->area_ids = array();

		$result = $wpdb->get_results($wpdb->prepare("SELECT lawAreaID FROM ".$wpdb->prefix."law2area WHERE lawID = '%d'", $this->id));

		foreach($result as $r)
		{
			$this->area_ids[] = $r->lawAreaID;
		}
	}

	function set_areas()
	{
		global $wpdb;

		$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."law2area WHERE lawID = '%d'", $this->id));

		foreach($this->area_ids as $area_id)
		{
			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."law2area SET lawID = '%d', lawAreaID = '%d'", $this->id, $area_id));
		}
	}

	function get_groups()
	{
		global $wpdb;

		$this->group_ids = array();

		$result = $wpdb->get_results($wpdb->prepare("SELECT lawGroupID FROM ".$wpdb->prefix."law2group WHERE lawID = '%d'", $this->id));

		foreach($result as $r)
		{
			$this->group_ids[] = $r->lawGroupID;
		}
	}

	function set_groups()
	{
		global $wpdb;

		$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."law2group WHERE lawID = '%d'", $this->id));

		foreach($this->group_ids as $group_id)
		{
			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."law2group SET lawID = '%d', lawGroupID = '%d'", $this->id, $group_id));
		}

		$this->clear_cache();
	}

	function get_from_db()
	{
		global $wpdb;

		if($this->id > 0)
		{
			if(isset($_GET['recover']))
			{
				$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."law SET lawDeleted = '0' WHERE lawID = '%d'", $this->id));
			}

			$result = $wpdb->get_results($wpdb->prepare("SELECT lawChapterID, lawTypeID, lawNo, lawDecided, lawComesInEffect, lawTransitionalDate, lawUpdatedTo, lawReleased, lawValid, lawValidTo, lawName, lawText, lawChanges, lawLink, lawCreated, userID FROM ".$wpdb->prefix."law WHERE lawID = '%d'", $this->id));

			if($wpdb->num_rows > 0)
			{
				$r = $result[0];
				$this->created = $r->lawCreated;
				$this->user_id = $r->userID;

				if(!isset($_POST['btnLawCreate']) && !isset($_POST['btnLawRevision']))
				{
					$this->chapter_id = $r->lawChapterID;
					$this->type_id = $r->lawTypeID;
					$this->no = $r->lawNo;
					$this->decided = $r->lawDecided;
					$this->comes_in_effect = $r->lawComesInEffect;
					$this->transitional_date = $r->lawTransitionalDate;
					$this->updated_to = $r->lawUpdatedTo;
					$this->released = ($r->lawReleased > DEFAULT_DATE ? $r->lawReleased : $this->decided);
					$this->valid = $r->lawValid;
					$this->valid_to = $r->lawValidTo;
					$this->name = $r->lawName;
					$this->text = stripslashes($r->lawText);
					$this->changes = stripslashes($r->lawChanges);
					$this->link = $r->lawLink;

					$this->get_parents();
					$this->get_areas();
					$this->get_groups();

					$this->attachment = "";

					$result = $wpdb->get_results($wpdb->prepare("SELECT fileID FROM ".$wpdb->prefix."law2file WHERE lawID = '%d'", $this->id));

					foreach($result as $r)
					{
						list($file_name, $file_url) = get_attachment_data_by_id($r->fileID);

						$this->attachment .= ($this->attachment != '' ? "," : "").$file_name."|".$file_url;
					}

					$this->get_lists();
				}
			}
		}
	}

	function get_lists($data = array())
	{
		global $wpdb;

		if(!isset($data['law_id'])){	$data['law_id'] = $this->id;}

		$result = $wpdb->get_results($wpdb->prepare("SELECT listID, lawPublished FROM ".$wpdb->prefix."law2list WHERE lawID = '%d'", $data['law_id']));

		foreach($result as $r)
		{
			$obj_list = new mf_list(array('id' => $r->listID));

			if($obj_list->has_permission(array('rights' => array('editor', 'author', 'reader'))))
			{
				if($r->lawPublished == 1)
				{
					$this->lists_published[] = $r->listID;
				}

				else
				{
					$this->lists[] = $r->listID;
				}
			}
		}
	}

	function show_in_result($data)
	{
		global $wpdb;

		$show_in_result = true;

		if(is_array($this->responsibility_ids) && count($this->responsibility_ids) > 0)
		{
			$wpdb->get_results($wpdb->prepare("SELECT lawInfoID FROM ".$wpdb->prefix."law_info WHERE lawID = '%d' AND listID = '%d' AND lawInfoDeleted = '0' AND lawInfoArchived = '0' AND lawInfoKey = 'responsibility' AND lawInfoValue IN ('".implode("','", $this->responsibility_ids)."') LIMIT 0, 1", $data['law_id'], $this->list_id));

			if($wpdb->num_rows == 0)
			{
				$show_in_result = false;
			}
		}

		if($show_in_result == true && $this->show_receipt != '')
		{
			if(!isset($obj_law_info))
			{
				$obj_law_info = new mf_law_info();
			}

			$obj_law_info->law_id = $data['law_id'];
			$obj_law_info->list_id = $this->list_id;

			/*if(!isset($this->list_id) || !($this->list_id > 0))
			{
				do_log("No ListID in show_in_result(): (".var_export($data, true).")");
			}*/

			$acknowledged = $obj_law_info->get_key_value(array('key' => 'acknowledged', 'line3debug' => $data['line3debug'], 'line4debug' => __LINE__));

			switch($this->show_receipt)
			{
				case 'yes':
					if($acknowledged == '')
					{
						$show_in_result = false;
					}
				break;

				case 'no':
					if($acknowledged != '')
					{
						$show_in_result = false;
					}
				break;
			}
		}

		if($show_in_result == true && $this->show_requirements != '')
		{
			if(!isset($obj_law_info))
			{
				$obj_law_info = new mf_law_info();
			}

			$obj_law_info->law_id = $data['law_id'];
			$obj_law_info->list_id = $this->list_id;

			switch($this->show_requirements)
			{
				case 'yes':
					$is_accepted = $obj_law_info->get_key_value(array('key' => 'accepted'));

					if($is_accepted == '')
					{
						$show_in_result = false;
					}
				break;

				case 'no':
					$is_accepted = $obj_law_info->get_key_value(array('key' => 'accepted'));

					if($is_accepted != '')
					{
						$show_in_result = false;
					}
				break;

				case 'not_evaluated':
					$evaluation = $obj_law_info->get_key_value(array('key' => 'evaluation'));

					if($evaluation != '')
					{
						$show_in_result = false;
					}
				break;
			}
		}

		return $show_in_result;
	}

	function get_list_groups($data = array())
	{
		global $wpdb;

		if(!isset($data['parent'])){		$data['parent'] = 0;}
		if(!isset($data['hierarchical'])){	$data['hierarchical'] = false;}

		$arr_groups = $arr_laws_unknown = array();

		$tbl_group = new mf_law_group_table();
		$tbl_law = new mf_law_table();

		$tbl_group->select_data(array(
			'select' => "lawGroupID, lawGroupID2, lawGroupName",
			'where' => ($data['hierarchical'] == true ? "lawGroupID2 = '".$data['parent']."'" : ""),
			'sort_data' => ($data['parent'] > 0 ? false : true),
			//'debug' => true,
		));

		$i = 0;

		foreach($tbl_group->data as $r)
		{
			$intLawGroupID = $r['lawGroupID'];
			$strLawGroupName = $r['lawGroupName'];

			$arr_laws = array();

			$query_where = "";

			if($this->list_id > 0)
			{
				$query_where .= ($query_where != '' ? " AND " : "").$wpdb->prefix."law2list.listID = '".$this->list_id."'";
			}

			if($data['parent'] == 0 && $i == 0)
			{
				$query_where .= ($query_where != '' ? " AND " : "")."(".$wpdb->prefix."law2group.lawGroupID = '".$intLawGroupID."' OR ".$wpdb->prefix."law2group.lawGroupID IS null)";
			}

			else
			{
				$query_where .= ($query_where != '' ? " AND " : "")."".$wpdb->prefix."law2group.lawGroupID = '".$intLawGroupID."'";
			}

			if(IS_EDITOR && $this->list_id > 0)
			{
				switch($this->display_mode)
				{
					default:
					case 'published':
						$query_where .= ($query_where != '' ? " AND " : "")."lawPublished = '1'";
					break;

					case 'preview':
						// What to do?
					break;

					case 'difference':
						$query_where .= ($query_where != '' ? " AND " : "")."lawPublished = '0'";
					break;

					/*case 'new':
						global $obj_list;

						if(!isset($obj_list))
						{
							$obj_list = new mf_list();
						}

						$dteLawPublishedDate = $obj_list->last_published(array('id' => $this->list_id));

						$query_where .= ($query_where != '' ? " AND " : "")."lawPublished = '0' AND lawCreated > ".esc_sql($dteLawPublishedDate)."";
					break;*/
				}
			}

			$tbl_law->select_data(array(
				'select' => $wpdb->prefix."law.lawID AS lawID, lawNo, lawName, lawValid, lawValidTo, lawUpdatedTo, ".$wpdb->prefix."law2group.lawGroupID",
				'join' => " LEFT JOIN ".$wpdb->prefix."law2group ON ".$wpdb->prefix."law.lawID = ".$wpdb->prefix."law2group.lawID",
				'where' => $query_where,
				'sort_data' => true,
				//'debug' => true,
			));

			foreach($tbl_law->data as $r)
			{
				$intLawID = $r['lawID'];

				if($this->show_in_result(array('law_id' => $intLawID, 'line3debug' => __LINE__)))
				{
					if($r['lawGroupID'] > 0)
					{
						$arr_laws[] = $r;
					}

					else
					{
						$arr_laws_unknown[] = $r;
					}
				}
			}

			$arr_groups[$intLawGroupID] = array(
				'id' => $intLawGroupID,
				'name' => $strLawGroupName,
				'laws' => $arr_laws,
				'sub' => ($data['hierarchical'] == true ? $this->get_list_groups(array('parent' => $intLawGroupID, 'hierarchical' => $data['hierarchical'])) : array()),
			);

			$i++;
		}

		if(count($arr_laws_unknown) > 0)
		{
			$unknown_id = 0;

			$arr_groups[$unknown_id] = array(
				'id' => $unknown_id,
				'name' => __("Unknown", 'lang_law'),
				'laws' => $arr_laws_unknown,
				'sub' => array(),
			);
		}

		return $arr_groups;
	}

	function get_list_groups_2($data = array())
	{
		global $wpdb;

		if(!isset($data['parent'])){		$data['parent'] = 0;}
		if(!isset($data['hierarchical'])){	$data['hierarchical'] = false;}

		$arr_groups = $arr_laws_unknown = array();
		$query_where = $query_limit = "";

		$display_new_laws = (IS_EDITOR && $this->list_id > 0 && $this->display_mode == 'new');

		if($data['hierarchical'] == true)
		{
			$query_where .= " AND lawGroupID2 = '".esc_sql($data['parent'])."'";
		}

		$result = $wpdb->get_results("SELECT lawGroupID, lawGroupName FROM ".$wpdb->prefix."law_group WHERE lawGroupDeleted != '1'".$query_where." GROUP BY lawGroupID ORDER BY lawGroupOrder ASC, lawGroupName ASC".$query_limit);

		$i = 0;

		foreach($result as $r)
		{
			$intLawGroupID = $r->lawGroupID;
			$strLawGroupName = $r->lawGroupName;

			$arr_laws = array();

			$query_join = $query_where = "";

			// Needs to be here since it might be used if !IS_EDITOR below
			$query_join .= " LEFT JOIN ".$wpdb->prefix."law2list ON ".$wpdb->prefix."law.lawID = ".$wpdb->prefix."law2list.lawID";

			if($this->list_id > 0 && $display_new_laws == false)
			{
				$query_where .= " AND ".$wpdb->prefix."law2list.listID = '".$this->list_id."'";
			}

			$query_join .= " LEFT JOIN ".$wpdb->prefix."law2group ON ".$wpdb->prefix."law.lawID = ".$wpdb->prefix."law2group.lawID";

			if($data['parent'] == 0 && $i == 0)
			{
				$query_where .= " AND (".$wpdb->prefix."law2group.lawGroupID = '".$intLawGroupID."' OR ".$wpdb->prefix."law2group.lawGroupID IS null)";
			}

			else if($display_new_laws == false)
			{
				$query_where .= " AND ".$wpdb->prefix."law2group.lawGroupID = '".$intLawGroupID."'";
			}

			if(IS_EDITOR)
			{
				if($this->list_id > 0)
				{
					switch($this->display_mode)
					{
						default:
						case 'published':
							$query_where .= " AND lawPublished = '1'";
						break;

						case 'preview':
							// What to do?
						break;

						case 'difference':
							$query_where .= " AND lawPublished = '0'";
						break;

						case 'new':
							global $obj_list;

							if(!isset($obj_list))
							{
								$obj_list = new mf_list();
							}

							$dteLawPublishedDate = $obj_list->last_published(array('id' => $this->list_id));

							if(IS_SUPER_ADMIN)
							{
								$query_join .= " LEFT JOIN ".$wpdb->prefix."law2law ON ".$wpdb->prefix."law.lawID = ".$wpdb->prefix."law2law.lawID";
								$query_where .= " AND ".$wpdb->prefix."law2law.lawID_parent IS null";
							}

							$query_where .= " AND lawCreated > '".esc_sql($dteLawPublishedDate)."'";
						break;
					}
				}
			}

			else
			{
				$query_join .= " LEFT JOIN ".$wpdb->prefix."list2user ON ".$wpdb->prefix."law2list.listID = ".$wpdb->prefix."list2user.listID";
				$query_where .= " AND (".$wpdb->prefix."law.userID = '".get_current_user_id()."' OR ".$wpdb->prefix."list2user.userID = '".get_current_user_id()."' AND lawPublished = '1')";
			}

			if($this->search != '')
			{
				global $tbl_law;

				if(!isset($tbl_law))
				{
					$tbl_law = new mf_law_table();
				}

				$query_where .= " AND (".$wpdb->prefix."law.lawID = '".$this->search."' OR lawNo LIKE '".$tbl_law->filter_search_before_like($this->search)."' OR lawUpdatedTo LIKE '".$tbl_law->filter_search_before_like($this->search)."' OR lawName LIKE '".$tbl_law->filter_search_before_like($this->search)."' OR CONCAT(lawNo, ' ', lawName) LIKE '".$tbl_law->filter_search_before_like($this->search)."' OR SOUNDEX(lawName) = SOUNDEX('".$this->search."'))";
			}

			$query_join .= " LEFT JOIN ".$wpdb->prefix."law_chapter ON (".$wpdb->prefix."law.lawChapterID = ".$wpdb->prefix."law_chapter.lawChapterID)"
				." LEFT JOIN ".$wpdb->prefix."law_type ON (".$wpdb->prefix."law.lawTypeID = ".$wpdb->prefix."law_type.lawTypeID)";

			$result = $wpdb->get_results("SELECT ".$wpdb->prefix."law.lawID AS lawID, lawNo, lawName, lawComesInEffect, lawValid, lawValidTo, lawUpdatedTo, ".$wpdb->prefix."law2group.lawGroupID"
				." FROM ".$wpdb->prefix."law".$query_join
				." WHERE lawDeleted != '1'".$query_where
				." GROUP BY ".$wpdb->prefix."law.lawID"
				." ORDER BY lawTypeOrder ASC, lawChapterName ASC, lawTypeName ASC, lawNo ASC, lawName ASC"
			);

			foreach($result as $r)
			{
				$intLawID = $r->lawID;
				$dteLawValidTo = $r->lawValidTo;
				$strLawUpdatedTo = $r->lawUpdatedTo;

				if($this->show_in_result(array('law_id' => $intLawID, 'line3debug' => __LINE__)))
				{
					// If not revoked or displaying old and being editor/admin or the law has stopped being valid within the last three months
					if($this->has_been_revoked(array('id' => $intLawID, 'valid_to' => $dteLawValidTo, 'updated_to' => $strLawUpdatedTo)) == false || (isset($this->show_old) && $this->show_old == 'yes' && (IS_EDITOR || $dteLawValidTo >= date("Y-m-d", strtotime("-3 month"))))) //, 'list_id' => $this->list_id // This should not be used here. The connection to a list does not matter
					{
						if($r->lawGroupID > 0 && $display_new_laws == false)
						{
							$arr_laws[$intLawID] = $r;
						}

						else
						{
							$arr_laws_unknown[$intLawID] = $r;
						}
					}
				}
			}

			$arr_groups[$intLawGroupID] = array(
				'id' => $intLawGroupID,
				'name' => $strLawGroupName,
				'laws' => $arr_laws,
				'sub' => ($data['hierarchical'] == true && $display_new_laws == false ? $this->get_list_groups_2(array('parent' => $intLawGroupID, 'hierarchical' => $data['hierarchical'])) : array()),
			);

			$i++;
		}

		if(count($arr_laws_unknown) > 0)
		{
			$unknown_id = 0;

			$arr_groups[$unknown_id] = array(
				'id' => $unknown_id,
				'name' => __("Unknown", 'lang_law'),
				'laws' => $arr_laws_unknown,
				'sub' => array(),
			);
		}

		return $arr_groups;
	}

	function output_groups($data)
	{
		global $wpdb, $obj_law_info;

		if(!isset($obj_law_info))
		{
			$obj_law_info = new mf_law_info();
		}

		if(!isset($data['output_type'])){	$data['output_type'] = 'html';}
		if(!isset($data['parent_id'])){		$data['parent_id'] = 0;}
		if(!isset($data['arr_header'])){	$data['arr_header'] = array();}

		switch($data['output_type'])
		{
			case 'array':
				$out = $out_group = $out_law = array();
			break;

			case 'html':
				$out = "";
			break;
		}

		if(is_admin())
		{
			$post_edit_url = admin_url("admin.php?page=mf_law/create/index.php");
			$post_list_url = admin_url("admin.php?page=mf_law/list/index.php");
		}

		else
		{
			$post_edit_url = "?view=edit";
			$post_list_url = "?view=list";
		}

		if($this->list_id > 0)
		{
			$post_edit_url .= "&intListID=".$this->list_id;
		}

		$arr_data_columns = $this->get_data_columns_for_select();

		foreach($data['arr_groups'] as $arr_group)
		{
			$intLawGroupID = $arr_group['id'];
			$strLawGroupName = $arr_group['name'];

			$arr_laws = $arr_group['laws'];

			if(count($arr_laws) > 0 || count($arr_group['sub']) > 0)
			{
				$selector_group_id = "group_".$intLawGroupID;

				switch($data['output_type'])
				{
					case 'array':
						$out_group = $out_law = array();

						$out_group[] = array(
							'type' => 'group',
							'group_id' => $intLawGroupID,
							'class' => ($data['parent_id'] > 0 ? " toggle_item group_".$data['parent_id'] : ""),
							'name' => ($data['parent_id'] > 0 ? "&mdash; " : "").$strLawGroupName,
						);
					break;

					case 'html':
						$group_header = "<tr id='".$selector_group_id."' class='toggle_header".($data['parent_id'] > 0 ? " toggle_item group_".$data['parent_id'] : "")."'>
							<td colspan='".count($data['arr_header'])."'>
								<h3>"
									.($data['parent_id'] > 0 ? "&mdash; " : "")
									.$strLawGroupName
								."</h3>
							</td>
						</tr>";

						$group_items = "";
					break;
				}

				if(count($arr_laws) > 0)
				{
					foreach($arr_laws as $arr_law)
					{
						$intLawID = $arr_law->lawID;
						$strLawNo = $arr_law->lawNo;
						$strLawName = $arr_law->lawName;
						$dteLawComesInEffect = $arr_law->lawComesInEffect;
						$dteLawValid = $arr_law->lawValid;
						$dteLawValidTo = $arr_law->lawValidTo;
						$strLawUpdatedTo = $arr_law->lawUpdatedTo;

						$tr_class = $selector_group_id;

						if($this->has_been_revoked(array('id' => $intLawID, 'list_id' => $this->list_id, 'valid_to' => $dteLawValidTo, 'updated_to' => $strLawUpdatedTo)))
						{
							$tr_class .= " inactive";
						}

						switch($data['output_type'])
						{
							case 'array':
								$data_temp = array(
									'type' => 'law',
									'class' => $tr_class,
									'id' => $intLawID,
									'no' => $strLawNo,
									'name' => $strLawName,
								);

								if($this->list_id > 0)
								{
									$data_temp['law_info'] = $obj_law_info->get_existing_keys(array('law_id' => $intLawID, 'list_id' => $this->list_id));
								}

								if(is_array($this->column_ids))
								{
									foreach($this->column_ids as $key)
									{
										if(isset($arr_data_columns[$key]))
										{
											$data_temp['column_'.$key] = $obj_law_info->get_column_value(array('key' => $key, 'law_id' => $intLawID, 'list_id' => $this->list_id, 'law_valid' => $dteLawValid, 'edit_url' => $post_edit_url));
										}
									}
								}

								$out_law[] = $data_temp;
							break;

							case 'html':
								if($dteLawComesInEffect > DEFAULT_DATE && $dteLawComesInEffect > date("Y-m-d"))
								{
									$strLawName .= " <i class='fas fa-calendar-check' title='".__("Comes in Effect", 'lang_law')." ".$dteLawComesInEffect."'></i>";
								}

								else if($dteLawValid > DEFAULT_DATE && $dteLawValid > date("Y-m-d"))
								{
									$strLawName .= " <i class='fas fa-calendar-check' title='".__("Valid from", 'lang_law')." ".$dteLawValid."'></i>";
								}

								$row_actions = "";

								if(IS_ADMIN)
								{
									$row_actions .= (is_admin() && $row_actions != '' ? " | " : "")."<span class='edit'><a href='".$post_edit_url."&intLawID=".$intLawID."'>".__("Edit", 'lang_law')."</a></span>";

									$row_actions .= (is_admin() && $row_actions != '' ? " | " : "")."<span class='delete'><a href='".wp_nonce_url($post_list_url."&btnLawDelete&intLawID=".$intLawID.(isset($_GET['display_mode']) ? "&display_mode=".$_GET['display_mode'] : ""), 'law_delete_'.$intLawID, '_wpnonce_law_delete')."' rel='confirm'>".__("Delete", 'lang_law')."</a></span>";

									if($this->list_id > 0 && $this->display_mode != 'new')
									{
										$row_actions .= (is_admin() && $row_actions != '' ? " | " : "")."<span class='remove_from_list'><a href='".wp_nonce_url($post_list_url."&btnLawRemoveFromList&intListID=".$this->list_id."&intLawID=".$intLawID.(isset($_GET['display_mode']) ? "&display_mode=".$_GET['display_mode'] : ""), 'law_remove_'.$intLawID, '_wpnonce_law_remove')."' rel='confirm'>".__("Remove from List", 'lang_law')."</a><span>";
									}
								}

								if($this->list_id > 0 && $this->display_mode != 'new')
								{
									$row_actions .= (is_admin() && $row_actions != '' ? " | " : "")."<span class='info'>
										<a href='".$post_edit_url."&intLawID=".$intLawID."#law_info_edit'>".$obj_law_info->get_existing_keys(array('law_id' => $intLawID, 'list_id' => $this->list_id))."</a>
									</span>";
								}

								$group_items .= "<tr class='toggle_item ".$tr_class."'>";

									if(!isset($this->is_other_req) || $this->is_other_req == false)
									{
										$group_items .= "<td>".$strLawNo."</td>";
									}

									$group_items .= "<td>
										<a href='".$post_edit_url."&intLawID=".$intLawID."'>".$strLawName."</a>
										<div class='row-actions'>".$row_actions."</div>"
									."</td>";

									$obj_law_info->has_column_acknowledged = false;

									if(is_array($this->column_ids))
									{
										foreach($this->column_ids as $key)
										{
											if(isset($arr_data_columns[$key]))
											{
												$group_items .= "<td>".$obj_law_info->get_column_value(array('key' => $key, 'law_id' => $intLawID, 'list_id' => $this->list_id, 'law_valid' => $dteLawValid, 'edit_url' => $post_edit_url))."</td>";
											}
										}
									}

									// Just to make sure that each row has its set_tr_color loaded
									if($obj_law_info->has_column_acknowledged == false)
									{
										$group_items .= "<td class='hide'>".$obj_law_info->get_acknowledged(array('law_id' => $intLawID, 'list_id' => $this->list_id, 'law_valid' => $dteLawValid))."</td>";
									}

								$group_items .= "</tr>";
							break;
						}
					}
				}

				switch($data['output_type'])
				{
					case 'array':
						if(count($out_law) > 0)
						{
							$out = array_merge($out, $out_group);
							$out = array_merge($out, $out_law);
						}
					break;

					case 'html':
						if(count($arr_group['sub']) > 0)
						{
							$group_items .= $this->output_groups(array('parent_id' => $intLawGroupID, 'arr_groups' => $arr_group['sub'], 'arr_header' => $data['arr_header']));
						}

						if($group_items != '')
						{
							$out .= $group_header.$group_items;
						}
					break;
				}
			}
		}

		return $out;
	}

	function set_search_form()
	{
		$intUserID = get_current_user_id();

		update_user_meta($intUserID, 'search_column_ids', $this->column_ids);
		update_user_meta($intUserID, 'search_responsibility_ids', $this->responsibility_ids);
		update_user_meta($intUserID, 'search_show_receipt', $this->show_receipt);
		update_user_meta($intUserID, 'search_show_requirements', $this->show_requirements);

		update_user_meta($intUserID, 'search_search', $this->search);
		update_user_meta($intUserID, 'search_display_mode', $this->display_mode);
		update_user_meta($intUserID, 'search_show_old', $this->show_old);
	}

	function remove_filter_if_not_exists($intUserID)
	{
		if(is_array($this->responsibility_ids) && count($this->responsibility_ids) > 0)
		{
			$responsibility_exists = false;

			$arr_data = $this->get_responsibilities_for_select();

			foreach($this->responsibility_ids as $responsibility_id)
			{
				if(isset($arr_data[$responsibility_id]))
				{
					$responsibility_exists = true;

					break;
				}
			}

			if($responsibility_exists == false)
			{
				$this->responsibility_ids = array();

				update_user_meta($intUserID, 'search_responsibility_ids', $this->responsibility_ids);
			}
		}
	}

	function get_search_form()
	{
		$intUserID = get_current_user_id();

		$this->column_ids = get_user_meta($intUserID, 'search_column_ids', true);
		$this->responsibility_ids = get_user_meta($intUserID, 'search_responsibility_ids', true);
		$this->show_receipt = get_user_meta($intUserID, 'search_show_receipt', true);
		$this->show_requirements = get_user_meta($intUserID, 'search_show_requirements', true);

		$this->search = get_user_meta($intUserID, 'search_search', true);
		$this->display_mode = get_user_meta($intUserID, 'search_display_mode', true);

		if($this->display_mode == '') // Just in case someone has managed to save it empty
		{
			$this->display_mode = 'published';
		}

		$this->show_old = get_user_meta($intUserID, 'search_show_old', true);

		$this->remove_filter_if_not_exists($intUserID);
	}

	function get_responsibilities_for_select()
	{
		$arr_data = array();

		$tbl_group_resp = new mf_responsibility_table();

		$tbl_group_resp->select_data(array(
			'select' => "responsibilityID, responsibilityName",
			'where' => "listID = '".$this->list_id."'",
		));

		foreach($tbl_group_resp->data as $r)
		{
			$arr_data[$r['responsibilityID']] = $r['responsibilityName'];
		}

		return $arr_data;
	}

	function get_search_list_law()
	{
		global $obj_company, $obj_list;

		$out = "";

		if(!isset($obj_company))
		{
			$obj_company = new mf_company();
		}

		$obj_company->get_logo($this->list_id);

		if(!isset($obj_list))
		{
			$obj_list = new mf_list(array('id' => $this->list_id));
		}

		if($this->list_id > 0)
		{
			switch($this->display_mode)
			{
				case 'preview':
					$out .= "<h3 class='list_preview'>".__("Preview", 'lang_law')."</h3>";
				break;

				case 'difference':
					$out .= "<h3 class='list_preview'>".__("Differences", 'lang_law')."</h3>";
				break;

				case 'new':
					$out .= "<h3 class='list_preview'>".__("New Laws", 'lang_law')."</h3>";
				break;
			}
		}

		if($obj_company->file_url != '')
		{
			$out .= "<img src='".$obj_company->file_url."' alt='".$obj_company->name."' class='company_logo'>";
		}

		if($this->list_id > 0)
		{
			$out .= "<h2>".$obj_list->get_name()."</h2>";
		}

		// This won't show anyway, and it messes up with 'difference' on lists with only one 'responsibility'
		//$arr_data_responsibility = $this->get_responsibilities_for_select();

		if(is_admin())
		{
			$container_one = show_select(array('data' => $this->get_data_columns_for_select(), 'name' => 'arrColumnID[]', 'text' => __("Display Columns", 'lang_law'), 'value' => $this->column_ids, 'xtra' => "class='multiselect'"));

			/*if(count($arr_data_responsibility) > 0)
			{
				$container_one .= show_select(array('data' => $arr_data_responsibility, 'name' => 'arrResponsibilityID[]', 'text' => __("Responsibility", 'lang_law'), 'value' => $this->responsibility_ids, 'xtra' => "class='multiselect'"));
			}*/
		}

		else
		{
			$container_one = "<label for='arrColumnID'>".__("Display Columns", 'lang_law')."</label>".show_select(array('data' => $this->get_data_columns_for_select(), 'name' => 'arrColumnID[]', 'value' => $this->column_ids, 'xtra' => "class='multiselect'"));

			/*if(count($arr_data_responsibility) > 0)
			{
				$container_one .= "<label for='arrResponsibilityID'>".__("Responsibility", 'lang_law')."</label>".show_select(array('data' => $arr_data_responsibility, 'name' => 'arrResponsibilityID[]', 'value' => $this->responsibility_ids, 'xtra' => "class='multiselect'"));
			}*/
		}

		$container_two = "";

		$container_one .= show_textfield(array('name' => 'strLawSearch', 'value' => $this->search, 'placeholder' => __("Text", 'lang_law')));

		//if(IS_ADMIN)
		$container_one .= show_select(array('data' => get_yes_no_for_select(array('add_choose_here' => true, 'choose_here_text' => __("Show old", 'lang_law'))), 'name' => 'strLawShowOld', 'value' => $this->show_old));

		if($this->list_id > 0)
		{
			if(IS_EDITOR)
			{
				list($result_laws, $result_laws_published) = $obj_list->get_laws(array('id' => $this->list_id));

				if(count($result_laws) > 0 || count($result_laws_published) > 0 || $this->display_mode != 'published')
				{
					$arr_data = array();

					if($this->display_mode == 'published' || count($result_laws_published) > 0)
					{
						$arr_data['published'] = __("Published", 'lang_law');
					}

					if($this->display_mode == 'preview' || count($result_laws) > 0)
					{
						$arr_data['preview'] = __("Preview", 'lang_law');
					}

					if($this->display_mode == 'difference' || count($result_laws_published) > 0 && count($result_laws) > 0)
					{
						$arr_data['difference'] = __("Differences", 'lang_law');
					}

					if($this->display_mode == 'new' || $obj_list->does_new_laws_exist(array('id' => $this->list_id)))
					{
						$arr_data['new'] = __("New Laws", 'lang_law');
					}

					if(count($arr_data) > 1)
					{
						if(is_admin())
						{
							$container_two .= show_select(array('data' => $arr_data, 'name' => 'display_mode', 'text' => __("Display Mode", 'lang_law'), 'value' => $this->display_mode));
						}

						else
						{
							$container_two .= "<label for='display_mode'>".__("Display Mode", 'lang_law')."</label>".show_select(array('data' => $arr_data, 'name' => 'display_mode', 'value' => $this->display_mode));
						}
					}
				}
			}

			if(is_admin())
			{
				$container_two .= show_select(array('data' => get_yes_no_for_select(array('add_choose_here' => true, 'choose_here_text' => __("Choose Receipt Here", 'lang_law'))), 'name' => 'strLawShowReceipt', 'text' => __("Filter", 'lang_law'), 'value' => $this->show_receipt));
			}

			else
			{
				$container_two .= show_select(array('data' => get_yes_no_for_select(array('add_choose_here' => true, 'choose_here_text' => __("Choose Receipt Here", 'lang_law'))), 'name' => 'strLawShowReceipt', 'value' => $this->show_receipt));
			}

			$arr_data = get_yes_no_for_select(array('add_choose_here' => true, 'choose_here_text' => __("Choose Requirements Here", 'lang_law')));
			$arr_data['not_evaluated'] = __("Not Evaluated", 'lang_law');

			$container_two .= show_select(array('data' => $arr_data, 'name' => 'strLawShowRequirements', 'value' => $this->show_requirements));

			//$container_two .= show_select(array('data' => $obj_list->get_responisibility_for_select(array('list_id' => $this->list_id)), 'name' => 'strLawResponsibility', 'value' => $this->law_responsibility));
		}

		$out .= "<form method='post' class='mf_form mf_search postbox'>
			<div class='".(is_admin() ? "inside" : "flex_flow")."'>";

				if($container_one != '' && $container_two != '')
				{
					if(is_admin())
					{
						$out .= "<div class='flex_flow'>
							<div>".$container_one."</div>
							<div>".$container_two."</div>
						</div>";
					}

					else
					{
						$out .= $container_one
						."</div><div class='flex_flow'>"
						.$container_two;
					}
				}

				else
				{
					$out .= $container_one.$container_two;
				}

				$out .= "<div class='form_button'>"
					.show_submit(array('name' => 'btnLawFilter', 'text' => __("Filter", 'lang_law')))
				."</div>"
			."</div>";

			if(!is_admin())
			{
				$out .= "<div class='form_button'>
					<a href='#' class='button toggle_all'>".__("Toggle All", 'lang_law')."</a>";

					if(IS_EDITOR && $this->list_id > 0)
					{
						$out .= "<a href='".admin_url("admin.php?page=mf_list/compare/index.php&intListID=".$this->list_id)."' class='button'>".__("Add new law to the list", 'lang_law')."</a>";
					}

					$out .= "<a href='#' onclick='window.print()' class='button'>".__("Print", 'lang_law')."</a>";

					if($this->list_id > 0 && $obj_list->has_permission(array('rights' => array('editor'))))
					{
						switch($this->display_mode)
						{
							case 'published':
								$archive_url = wp_nonce_url(admin_url("admin.php?page=mf_law/list/index.php&btnListArchive&intListID=".$this->list_id), 'list_archive_'.$this->list_id, '_wpnonce_list_archive');
								$archive_attr = " class='button' rel='confirm' confirm_text='".__("Are you sure? This will archive all laws in this list and reset requiements.", 'lang_law')."'";

								$reset_requirements_url = wp_nonce_url(admin_url("admin.php?page=mf_law/list/index.php&btnListResetRequirements&intListID=".$this->list_id), 'list_reset_requirements_'.$this->list_id, '_wpnonce_list_reset_requirements');
								$reset_requirements_attr = " class='button' rel='confirm'";
							break;

							default:
								$archive_url = $reset_requirements_url = "#";
								$archive_attr = $reset_requirements_attr = " class='button is_disabled' title='".__("This is only active in public display mode", 'lang_law')."'";
							break;
						}

						$out .= "<a href='".$archive_url."'".$archive_attr.">".__("Archive Evaluation", 'lang_law')."</a>"
						."<a href='".$reset_requirements_url."'".$reset_requirements_attr.">".__("Reset Requirements", 'lang_law')."</a>";
					}

				$out .= "</div>";
			}

		$out .= "</form>";

		return $out;
	}

	function save_status($data)
	{
		global $wpdb;

		$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."law_status SET lawID = '%d', listID = '%d', statusType = %s, statusCreated = NOW(), userID = '%d'", $data['law_id'], $data['list_id'], $data['type'], get_current_user_id()));

		$this->clear_cache();
	}

	function add2lists()
	{
		global $wpdb;

		if(is_array($this->lists))
		{
			$this->remove_from_list(array('exclude' => $this->lists, 'published' => 0));

			foreach($this->lists as $intListID)
			{
				if(!in_array($intListID, $this->lists_published))
				{
					$this->add2list(array('list_id' => $intListID));
				}
			}
		}

		if(is_array($this->id_parents))
		{
			foreach($this->id_parents as $parent_id)
			{
				$result = $wpdb->get_results($wpdb->prepare("SELECT listID FROM ".$wpdb->prefix."law2list WHERE lawID = '%d' AND lawPublished = '1'", $parent_id));

				foreach($result as $r)
				{
					$this->add2list(array('list_id' => $r->listID, 'parent' => $parent_id));
				}
			}
		}

		if(is_array($this->lists_published))
		{
			$this->remove_from_list(array('exclude' => $this->lists_published, 'published' => 1));

			foreach($this->lists_published as $intListID)
			{
				$this->add2list(array('list_id' => $intListID, 'published' => 1, 'publish_type' => 'update'));
			}
		}
	}

	function add2list($data)
	{
		global $wpdb;

		if(!isset($data['published'])){		$data['published'] = 0;}
		if(!isset($data['publish_type'])){	$data['publish_type'] = ($data['published'] == 0 ? 'preview' : 'publish');}
		if(!isset($data['parent'])){		$data['parent'] = 0;}

		$wpdb->get_results($wpdb->prepare("SELECT lawID FROM ".$wpdb->prefix."law2list WHERE lawID = '%d' AND listID = '%d' LIMIT 0, 1", $this->id, $data['list_id']));

		if($wpdb->num_rows > 0)
		{
			$this->update_list($data);
		}

		else
		{
			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."law2list SET lawID = '%d', listID = '%d', lawPublished = '%d'", $this->id, $data['list_id'], $data['published'])); //, lawID_parent = '%d', $data['parent']

			if($wpdb->rows_affected > 0)
			{
				$this->save_status(array('law_id' => $this->id, 'list_id' => $data['list_id'], 'type' => $data['publish_type']));
			}
		}
	}

	function update_list($data)
	{
		global $wpdb;

		if(!isset($data['published'])){		$data['published'] = 0;}
		if(!isset($data['publish_type'])){	$data['publish_type'] = ($data['published'] == 0 ? 'preview' : 'publish');}
		if(!isset($data['parent'])){		$data['parent'] = 0;}

		$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."law2list SET lawPublished = '%d' WHERE lawID = '%d' AND listID = '%d'", $data['published'], $this->id, $data['list_id'])); //, lawID_parent = '%d', $data['parent']

		$rows_affected = $wpdb->rows_affected;

		if($rows_affected > 0)
		{
			$this->save_status(array('law_id' => $this->id, 'list_id' => $data['list_id'], 'type' => $data['publish_type']));
		}

		return $rows_affected;
	}

	function remove_from_list($data = array())
	{
		global $wpdb;

		if(!isset($data['law_id'])){		$data['law_id'] = $this->id;}
		if(!isset($data['list_id'])){		$data['list_id'] = "";}
		if(!isset($data['exclude'])){		$data['exclude'] = array();}
		if(!isset($data['published'])){		$data['published'] = 0;}

		$removed = false;

		$query_where = "";

		if(count($data['exclude']) > 0)
		{
			$query_where .= " AND listID NOT IN('".implode("','", $data['exclude'])."')";
		}

		if($data['list_id'] > 0)
		{
			$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."law2list WHERE lawID = '%d' AND listID = '%d' AND lawPublished = '%d'".$query_where, $data['law_id'], $data['list_id'], $data['published']));

			if($wpdb->rows_affected > 0)
			{
				$this->save_status(array('law_id' => $data['law_id'], 'list_id' => $data['list_id'], 'type' => 'trash'));

				$removed = true;
			}
		}

		else
		{
			$result = $wpdb->get_results($wpdb->prepare("SELECT listID FROM ".$wpdb->prefix."law2list WHERE lawID = '%d' AND lawPublished = '%d'".$query_where, $data['law_id'], $data['published']));

			if($wpdb->num_rows > 0)
			{
				foreach($result as $r)
				{
					$intListID = $r->listID;

					$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."law2list WHERE lawID = '%d' AND listID = '%d' AND lawPublished = '%d'".$query_where, $data['law_id'], $intListID, $data['published']));

					if($wpdb->rows_affected > 0)
					{
						$this->save_status(array('law_id' => $data['law_id'], 'list_id' => $intListID, 'type' => 'trash'));

						$removed = true;
					}
				}
			}
		}

		return $removed;
	}

	function add_or_remove_from_list($data)
	{
		global $wpdb;

		if(!isset($data['published'])){		$data['published'] = 0;}
		if(!isset($data['publish_type'])){	$data['publish_type'] = ($data['published'] == 0 ? 'preview' : 'publish');}

		$wpdb->get_results($wpdb->prepare("SELECT lawID FROM ".$wpdb->prefix."law2list WHERE lawID = '%d' AND listID = '%d' AND lawPublished = '%d' LIMIT 0, 1", $this->id, $data['list_id'], $data['published']));

		if($wpdb->num_rows > 0)
		{
			$this->remove_from_list($data);

			return false;
		}

		else
		{
			$this->add2list($data);

			return true;
		}
	}

	function create()
	{
		global $wpdb;

		$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."law SET lawChapterID = '%d', lawTypeID = '%d', lawNo = %s, lawDecided = %s, lawComesInEffect = %s, lawTransitionalDate = %s, lawUpdatedTo = %s, lawReleased = %s, lawValid = %s, lawValidTo = %s, lawName = %s, lawText = %s, lawChanges = %s, lawLink = %s, lawCreated = NOW(), userID = '%d'", $this->chapter_id, $this->type_id, $this->no, $this->decided, $this->comes_in_effect, $this->transitional_date, $this->updated_to, $this->released, $this->valid, $this->valid_to, $this->name, $this->text, $this->changes, $this->link, get_current_user_id()));

		$this->id = $wpdb->insert_id;

		$this->set_areas();
		$this->set_groups();

		return $this->id;
	}

	function update()
	{
		global $wpdb;

		$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."law SET lawChapterID = '%d', lawTypeID = '%d', lawNo = %s, lawDecided = %s, lawComesInEffect = %s, lawTransitionalDate = %s, lawUpdatedTo = %s, lawReleased = %s, lawValid = %s, lawValidTo = %s, lawName = %s, lawText = %s, lawChanges = %s, lawLink = %s WHERE lawID = '%d'", $this->chapter_id, $this->type_id, $this->no, $this->decided, $this->comes_in_effect, $this->transitional_date, $this->updated_to, $this->released, $this->valid, $this->valid_to, $this->name, $this->text, $this->changes, $this->link, $this->id));

		$this->set_areas();
		$this->set_groups();
	}

	function trash($id = 0)
	{
		global $wpdb;

		if($id > 0)
		{
			$this->id = $id;
		}

		$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."law SET lawDeleted = '1', lawDeletedID = '%d', lawDeletedDate = NOW() WHERE lawID = '%d' AND lawDeleted = '0'", get_current_user_id(), $this->id));

		if($wpdb->rows_affected > 0)
		{
			$this->clear_cache();

			return true;
		}

		else
		{
			return false;
		}
	}
}

class mf_law_table extends mf_list_table
{
	function set_default()
	{
		global $wpdb, $pagenow, $obj_law;

		$this->arr_settings['query_from'] = $wpdb->prefix."law";
		$this->arr_settings['query_select_id'] = $wpdb->prefix."law.lawID";
		$this->arr_settings['query_all_id'] = "0";
		$this->arr_settings['query_trash_id'] = "1";
		$this->orderby_default = "lawTypeOrder ASC, lawChapterName ASC, lawTypeName ASC, lawName ASC, lawNo ASC, ".$wpdb->prefix."law.lawID";

		$this->arr_settings['has_autocomplete'] = true;
		$this->arr_settings['plugin_name'] = 'mf_law';

		//Search
		################
		if(!in_array($pagenow, array('users.php')) && (!isset($this->arr_settings['ignore_search']) || $this->arr_settings['ignore_search'] == false))
		{
			$obj_list = new mf_list();
			$obj_list->fetch_request();

			if($obj_list->area_id != '')
			{
				$this->query_join .= " INNER JOIN ".$wpdb->prefix."law2area ON ".$wpdb->prefix."law.lawID = ".$wpdb->prefix."law2area.lawID";
				$this->query_where .= ($this->query_where != '' ? " AND " : "").$wpdb->prefix."law2area.lawAreaID = '".$obj_list->area_id."'";
			}

			if($obj_list->chapter_id != '')
			{
				$this->query_where .= ($this->query_where != '' ? " AND " : "").$wpdb->prefix."law_chapter.lawChapterID = '".$obj_list->chapter_id."'";
			}

			if($obj_list->group_id != '')
			{
				$this->query_join .= " INNER JOIN ".$wpdb->prefix."law2group ON ".$wpdb->prefix."law.lawID = ".$wpdb->prefix."law2group.lawID";
				$this->query_where .= ($this->query_where != '' ? " AND " : "").$wpdb->prefix."law2group.lawGroupID = '".$obj_list->group_id."'";
			}

			if($obj_list->type_id != '')
			{
				$this->query_where .= ($this->query_where != '' ? " AND " : "").$wpdb->prefix."law_type.lawTypeID = '".$obj_list->type_id."'";
			}

			if($obj_list->search != '')
			{
				$this->search = $obj_list->search;
			}

			if($this->search != '')
			{
				$this->query_where .= ($this->query_where != '' ? " AND " : "")."(".$wpdb->prefix."law.lawID = '".$this->search."' OR lawNo LIKE '".$this->filter_search_before_like($this->search)."' OR lawUpdatedTo LIKE '".$this->filter_search_before_like($this->search)."' OR lawName LIKE '".$this->filter_search_before_like($this->search)."' OR SOUNDEX(lawName) = SOUNDEX('".$this->search."'))";
			}

			$this->arr_settings['show_old'] = $obj_list->show_old;
		}
		#################

		$this->query_join .= " LEFT JOIN ".$wpdb->prefix."law2list ON ".$wpdb->prefix."law.lawID = ".$wpdb->prefix."law2list.lawID LEFT JOIN ".$wpdb->prefix."list2user USING (listID)";

		if(!IS_EDITOR)
		{
			$this->query_where .= ($this->query_where != '' ? " AND " : "")."(".$wpdb->prefix."law.userID = '".get_current_user_id()."' OR ".$wpdb->prefix."list2user.userID = '".get_current_user_id()."' AND lawPublished = '1')";
		}

		$this->query_join .= " LEFT JOIN ".$wpdb->prefix."law_chapter ON (".$wpdb->prefix."law.lawChapterID = ".$wpdb->prefix."law_chapter.lawChapterID)"
			." LEFT JOIN ".$wpdb->prefix."law_type ON (".$wpdb->prefix."law.lawTypeID = ".$wpdb->prefix."law_type.lawTypeID)";

		$this->set_views(array(
			'db_field' => 'lawDeleted',
			'types' => array(
				'0' => __("All", 'lang_law'),
				'1' => __("Trash", 'lang_law')
			),
		));

		$arr_columns = array(
			'lawChapterID' => __("Chapter", 'lang_law'),
			'lawNo' => __("Law Number", 'lang_law'),
			'lawName' => __("Name", 'lang_law'),
		);

		if(IS_EDITOR)
		{
			$arr_columns['userID'] = shorten_text(array('string' => __("User", 'lang_law'), 'limit' => 4));
			$arr_columns['lawUpdatedTo'] = __("Updated to", 'lang_law');
			$arr_columns['lawReleased'] = __("Released", 'lang_law');
			$arr_columns['lawTypeID'] = __("Type", 'lang_law');
			$arr_columns['lawAreaID'] = __("Area", 'lang_law');
			$arr_columns['lawGroupID'] = __("Group", 'lang_law');
			$arr_columns['lawLink'] = __("Links", 'lang_law');
		}

		$arr_columns['lawAcknowledged'] = __("Receipt", 'lang_law');
		$arr_columns['lawAccepted'] = __("Comply with requirements", 'lang_law');

		$this->set_columns($arr_columns);

		$this->set_sortable_columns(array(
			'lawNo',
			'lawName',
			//'userID', //Does not work yet
			'lawReleased',
		));
	}

	function show_search_form()
	{
		global $obj_law;

		echo "<form method='post' class='mf_form'".($this->arr_settings['has_autocomplete'] == true ? " rel='".$this->arr_settings['plugin_name']."'" : "").">";

			if(IS_EDITOR)
			{
				global $obj_list;

				if(!isset($obj_list))
				{
					$obj_list = new mf_list();
				}

				$obj_list->fetch_request();

				echo "<div class='mf_search postbox'>
					<div class='inside'>"
						.$obj_list->show_filter_form()
					."</div>
				</div>";
			}

			else
			{
				$this->search_box(__("Search", 'lang_law'), 's');
			}

			echo input_hidden(array('name' => 'page', 'value' => $this->page))
		."</form>";
	}

	function sort_data()
	{
		global $obj_law, $obj_list;

		if(!IS_ADMIN || isset($this->arr_settings['show_old']) && $this->arr_settings['show_old'] == 'no')
		{
			if(!isset($obj_law))
			{
				$obj_law = new mf_law();
			}

			/*if(!isset($obj_list))
			{
				$obj_list = new mf_list();
			}*/

			foreach($this->data as $key => $value)
			{
				if($obj_law->has_been_revoked(array('id' => $value['lawID'], 'valid_to' => $value['lawValidTo'], 'updated_to' => $value['lawUpdatedTo']))) //, 'list_id' => $obj_list->id // This should not be used here. The connection to a list does not matter
				{
					unset($this->data[$key]);
				}
			}
		}
	}

	function column_default($item, $column_name)
	{
		global $wpdb, $obj_law, $obj_law_info, $obj_list;

		if(!isset($obj_law))
		{
			$obj_law = new mf_law();
		}

		if(!isset($obj_law_info))
		{
			$obj_law_info = new mf_law_info();
		}

		if(!isset($obj_list))
		{
			$obj_list = new mf_list();
		}

		$out = "";

		$obj_law->id = $item['lawID'];
		$dteLawValid = $item['lawValid'];
		$dteLawValidTo = $item['lawValidTo'];

		/*if(!($obj_law->id > 0))
		{
			do_log("No ID from (".var_export($item, true).")");
		}*/

		$post_edit_url = admin_url("admin.php?page=mf_law/create/index.php&intLawID=".$obj_law->id);

		switch($column_name)
		{
			case 'lawName':
				$item_value = $item['lawName'];
				$dteLawComesInEffect = $item['lawComesInEffect'];
				$intLawDeleted = $item['lawDeleted'];

				if($dteLawComesInEffect > DEFAULT_DATE && $dteLawComesInEffect > date("Y-m-d"))
				{
					$strLawName .= " <i class='fas fa-calendar-check' title='".__("Comes in Effect", 'lang_law')." ".$dteLawComesInEffect."'></i>";
				}

				else if($dteLawValid > DEFAULT_DATE && $dteLawValid > date("Y-m-d"))
				{
					$item_value .= " <i class='fas fa-calendar-check' title='".__("Valid from", 'lang_law')." ".$dteLawValid."'></i>";
				}

				$actions = array();

				if($intLawDeleted == 0)
				{
					$intUserID = $obj_law->get_user();

					if(IS_ADMIN || get_current_user_id() == $intUserID)
					{
						$actions['edit'] = "<a href='".$post_edit_url."'>".__("Edit", 'lang_law')."</a>";
						$actions['delete'] = "<a href='".wp_nonce_url($post_edit_url."&btnLawDelete", 'law_delete_'.$obj_law->id, '_wpnonce_law_delete')."'>".__("Delete", 'lang_law')."</a>";
					}
				}

				else
				{
					$actions['recover'] = "<a href='".$post_edit_url."&recover'>".__("Recover", 'lang_law')."</a>";
				}

				$out .= "<a href='".$post_edit_url."'>"
					.$item_value
				."</a>"
				.$this->row_actions($actions);
			break;

			case 'userID':
				$intUserID = $obj_law->get_user();

				if($intUserID > 0 && $intUserID != get_current_user_id())
				{
					$user_data = get_userdata($intUserID);

					if(in_array('author', $user_data->roles))
					{
						$actions = array();

						$actions['user'] = get_user_info(array('id' => $intUserID));

						$obj_company = new mf_company();

						$out .= $obj_company->get_user_company($intUserID)
						.$this->row_actions($actions);
					}
				}
			break;

			case 'lawReleased':
				$item_value = $item['lawReleased'];

				if($item_value > DEFAULT_DATE)
				{
					$out .= $item_value;
				}

				if($obj_law->has_been_revoked(array('id' => $obj_law->id, 'list_id' => $obj_list->id, 'valid_to' => $dteLawValidTo, 'updated_to' => $item['lawUpdatedTo'])))
				{
					$out .= "<i class='set_tr_color' rel='inactive'></i>";
				}
			break;

			case 'lawAreaID':
				$obj_law->get_areas();

				$str_areas = "";

				foreach($obj_law->area_ids as $area_id)
				{
					$obj_law_area = new mf_law_area($area_id);

					$str_areas .= ($str_areas != '' ? ", " : "").$obj_law_area->get_name();
				}

				$out .= $str_areas;
			break;

			case 'lawChapterID':
				$obj_law_chapter = new mf_law_chapter($item['lawChapterID']);

				$out .= $obj_law_chapter->get_name();
			break;

			case 'lawGroupID':
				$obj_law->get_groups();

				$str_groups = "";

				foreach($obj_law->group_ids as $group_id)
				{
					$obj_law_group = new mf_law_group($group_id);

					$str_groups .= ($str_groups != '' ? ", " : "").$obj_law_group->get_name();
				}

				$out .= $str_groups;
			break;

			case 'lawTypeID':
				$obj_law_type = new mf_law_type($item['lawTypeID']);

				$out .= $obj_law_type->get_name();
			break;

			case 'lawLink':
				$strLawLink = $item['lawLink'];
				$strLawAttachment = "";

				$result = $wpdb->get_results($wpdb->prepare("SELECT fileID FROM ".$wpdb->prefix."law2file WHERE lawID = '%d'", $obj_law->id));

				if($wpdb->num_rows > 2)
				{
					$strLawAttachment .= "<a href='".$post_edit_url."'>".$wpdb->num_rows."</a>";
				}

				else
				{
					foreach($result as $r)
					{
						list($file_name, $file_url) = get_attachment_data_by_id($r->fileID);

						$strLawAttachment .= ($strLawAttachment != '' ? " | " : "")."<a href='".$file_url."'>".$file_name."</a>";
					}
				}

				if($strLawLink != '')
				{
					$out .= "<a href='".validate_url($strLawLink)."'>".__("Link", 'lang_law')."</a>";
				}

				if($strLawAttachment != '')
				{
					$out .= ($strLawLink != '' ? " | " : "").$strLawAttachment;
				}
			break;

			case 'lawAcknowledged':
				$out .= $obj_law_info->get_acknowledged(array('law_id' => $obj_law->id, 'law_valid' => $dteLawValid));
			break;

			case 'lawAccepted':
				$out .= $obj_law_info->get_accepted(array('law_id' => $obj_law->id, 'law_valid' => $dteLawValid));
			break;

			default:
				if(isset($item[$column_name]))
				{
					$out .= $item[$column_name];
				}
			break;
		}

		return $out;
	}
}

class mf_law_import extends mf_import
{
	function get_defaults()
	{
		global $obj_law;

		$this->table = "law";
		$this->actions = array('import');
		$this->columns = array(
			'lawAreaID' => __("Area", 'lang_law'),
			'lawChapterID' => __("Chapter", 'lang_law'),
			'lawGroupID' => __("Group", 'lang_law'),
			'lawTypeID' => __("Type", 'lang_law'),
			'lawNo' => __("Law Number", 'lang_law'),
			'lawUpdatedTo' => __("Updated to", 'lang_law'),
			'lawReleased' => __("Released", 'lang_law'),
			'lawValid' => __("Valid from", 'lang_law'),
			'lawValidTo' => __("Valid to", 'lang_law'),
			'lawName' => __("Name", 'lang_law'),
			'lawText' => __("Summary", 'lang_law'),
			'lawLink' => __("Link", 'lang_law'),
		);
		$this->unique_columns = array(
			'lawNo',
			'lawName',
		);
		$this->unique_check = "AND";
	}

	function get_external_value(&$strRowField, &$value)
	{
		switch($strRowField)
		{
			case 'lawAreaID':
				$obj_law_area = new mf_law_area();

				$id = $obj_law_area->find($value);

				if(!($id > 0))
				{
					$obj_law_area->name = $value;
					$id = $obj_law_area->create();
				}

				if($id > 0)
				{
					$value = $id;
				}
			break;

			case 'lawChapterID':
				$obj_law_chapter = new mf_law_chapter();

				$id = $obj_law_chapter->find($value);

				if(!($id > 0))
				{
					$obj_law_chapter->name = $value;
					$id = $obj_law_chapter->create();
				}

				if($id > 0)
				{
					$value = $id;
				}
			break;

			case 'lawGroupID':
				$obj_law_group = new mf_law_group();

				$id = $obj_law_group->find($value);

				if(!($id > 0))
				{
					$obj_law_group->name = $value;
					$id = $obj_law_group->create();
				}

				if($id > 0)
				{
					$value = $id;
				}
			break;

			case 'lawTypeID':
				$obj_law_type = new mf_law_type();

				$id = $obj_law_type->find($value);

				if(!($id > 0))
				{
					$obj_law_type->name = $value;
					$id = $obj_law_type->create();
				}

				if($id > 0)
				{
					$value = $id;
				}
			break;
		}
	}
}

class mf_law_export extends mf_export
{
	function get_defaults()
	{
		global $obj_law, $obj_list;

		$this->type_name = __("List", 'lang_law');

		$this->plugin = "mf_law";

		if(IS_ADMIN)
		{
			$this->types[''] = "-- ".__("Export All Laws", 'lang_law')." --";
		}

		else
		{
			$this->types[''] = "-- ".__("Choose Here", 'lang_law')." --";
		}

		$tbl_group = new mf_this_table();

		$tbl_group->select_data(array(
			'select' => "listID",
			//'debug' => true,
		));

		if(count($tbl_group->data) > 0)
		{
			if(!isset($obj_list))
			{
				$obj_list = new mf_list();
			}

			foreach($tbl_group->data as $r)
			{
				$this->types[$r['listID']] = $obj_list->get_name(array('id' => $r['listID']));
			}
		}
	}

	function get_columns_for_select($data = array())
	{
		if(!isset($data['include'])){	$data['include'] = 'all';}

		switch($data['include'])
		{
			default:
			case 'all':
				$arr_data = array(
					'number' => __("Law Number", 'lang_law'),
					'name' => __("Name", 'lang_law'),
					'decided' => __("Decided", 'lang_law'),
					'updated_to' => __("Updated to", 'lang_law'),
					'area' => __("Area", 'lang_law'),
					'released' => __("Released", 'lang_law'),
					'valid_from' => __("Valid from", 'lang_law'),
					'valid_to' => __("Valid to", 'lang_law'),
					'summary' => __("Summary", 'lang_law'),
					'links' => __("Links", 'lang_law'),
					'opt_start_lists' => __("Lists", 'lang_law'),
						'effects_on_company' => __("Effects on the company", 'lang_law'),
						'requirements_met' => __("Requirements met", 'lang_law'),
						'responsibility' => __("Responsibility", 'lang_law'),
						'evaluation' => __("Evaluation", 'lang_law'),
						'link' => __("Link", 'lang_law'),
						'acknowledged' => __("Receipt", 'lang_law'),
						'accepted' => __("Check Evaluation", 'lang_law'),
					'opt_end_lists' => "",
				);
			break;

			case 'order_by':
				$arr_data = array(
					'' => "-- ".__("Default", 'lang_law')." --",
					'number' => __("Law Number", 'lang_law'),
					'name' => __("Name", 'lang_law'),
					'decided' => __("Decided", 'lang_law'),
					'released' => __("Released", 'lang_law'),
					'valid_from' => __("Valid from", 'lang_law'),
					'valid_to' => __("Valid to", 'lang_law'),
				);
			break;
		}

		return $arr_data;
	}

	function fetch_request_xtra()
	{
		$this->arr_columns = check_var('arrColumns');
		$this->order_by = check_var('strOrderBy');
	}

	function get_form_xtra()
	{
		return show_select(array('data' => $this->get_columns_for_select(), 'name' => 'arrColumns[]', 'text' => __("Columns", 'lang_law'), 'value' => $this->arr_columns))
		.show_select(array('data' => $this->get_columns_for_select(array('include' => 'order_by')), 'name' => 'strOrderBy', 'text' => __("Order", 'lang_law'), 'value' => $this->order_by));
	}

	function get_export_data()
	{
		global $wpdb, $obj_law, $error_text;

		$data_temp = array();

		switch($this->order_by)
		{
			case 'number':
				$data_temp['order_by'] = "lawNo";
			break;

			case 'name':
				$data_temp['order_by'] = "lawName";
			break;

			case 'decided':
				$data_temp['order_by'] = "lawDecided";
			break;

			case 'released':
				$data_temp['order_by'] = "lawReleased";
			break;

			case 'valid_from':
				$data_temp['order_by'] = "lawValid";
			break;

			case 'valid_to':
				$data_temp['order_by'] = "lawValidTo";
			break;
		}

		if($this->type > 0)
		{
			$obj_list = new mf_list(array('id' => $this->type));

			$this->name = $obj_list->get_name();

			list($result_laws, $result_laws_published) = $obj_list->get_laws();

			$tbl_group = new mf_law_table();

			$data_temp['select'] = $wpdb->prefix."law.lawID, lawNo, lawName, lawDecided, lawValid, lawValidTo, lawText, lawUpdatedTo, lawReleased, lawLink";
			$data_temp['where'] = $wpdb->prefix."law.lawID IN ('".implode("','", $result_laws_published)."')";
			$data_temp['sort_data'] = true;
			//$data_temp['debug'] = true;

			$tbl_group->select_data($data_temp);

			if(count($tbl_group->data) > 0)
			{
				$this_row = array();

				foreach($this->get_columns_for_select() as $key => $value)
				{
					if(!in_array($key, array('decided', 'opt_start_lists', 'opt_end_lists')) && count($this->arr_columns) == 0 || in_array($key, $this->arr_columns))
					{
						$this_row[] = $value;
					}
				}

				$this->data[] = $this_row;

				foreach($tbl_group->data as $r)
				{
					$intLawID = $r['lawID'];

					if(!isset($obj_law_info))
					{
						$obj_law_info = new mf_law_info();
					}

					$obj_law_info->law_id = $intLawID;
					$obj_law_info->list_id = $obj_list->id;

					$this_row = array();

					foreach($this->get_columns_for_select() as $key => $value)
					{
						if(!in_array($key, array('decided', 'opt_start_lists', 'opt_end_lists')) && count($this->arr_columns) == 0 || in_array($key, $this->arr_columns))
						{
							switch($key)
							{
								case 'number':
									$this_row[] = $r['lawNo'];
								break;

								case 'updated_to':
									$this_row[] = $r['lawUpdatedTo'];
								break;

								case 'released':
									$this_row[] = $r['lawReleased'];
								break;

								case 'valid_from':
									$this_row[] = $r['lawValid'];
								break;

								case 'valid_to':
									$this_row[] = $r['lawValidTo'];
								break;

								case 'name':
									$this_row[] = $r['lawName'];
								break;

								case 'decided':
									$this_row[] = $r['lawDecided'];
								break;

								case 'summary':
									$this_row[] = $r['lawText'];
								break;

								case 'links':
									$this_row[] = $r['lawLink'];
								break;

								case 'area':
									$obj_law->id = $intLawID;
									$obj_law->get_areas();

									$str_areas = "";

									foreach($obj_law->area_ids as $area_id)
									{
										$obj_law_area = new mf_law_area($area_id);

										$str_areas .= ($str_areas != '' ? ", " : "").$obj_law_area->get_name();
									}

									$this_row[] = $str_areas;
								break;

								case 'effects_on_company':
									$this_row[] = $obj_law_info->get_key_value(array('key' => 'effects_on_company'));
								break;

								case 'requirements_met':
									$this_row[] = $obj_law_info->get_key_value(array('key' => 'requirements_met'));
								break;

								case 'responsibility':
									$this_row[] = $obj_law_info->get_key_value(array('key' => 'responsibility'));
								break;

								//$this_row[] = $obj_law_info->get_key_value(array('key' => 'responsibility_v2'));
								//$this_row[] = $obj_law_info->get_key_value(array('key' => 'title'));

								case 'evaluation':
									$this_row[] = $obj_law_info->get_key_value(array('key' => 'evaluation'));
								break;

								case 'link':
									$this_row[] = $obj_law_info->get_key_value(array('key' => 'link'));
								break;

								case 'acknowledged':
									$this_row[] = $obj_law_info->get_key_value(array('key' => 'acknowledged', 'line4debug' => __LINE__));
								break;

								case 'accepted':
									$this_row[] = $obj_law_info->get_key_value(array('key' => 'accepted'));
								break;
							}
						}
					}

					$this->data[] = $this_row;
				}
			}
		}

		else
		{
			if(IS_ADMIN)
			{
				$this->name = __("All Laws", 'lang_law');

				$tbl_group = new mf_law_table();

				$data_temp['select'] = $wpdb->prefix."law.lawID, lawNo, lawName, lawDecided, lawValid, lawValidTo, lawText, lawUpdatedTo, lawReleased, lawLink";
				//$data_temp['sort_data'] = true;
				//$data_temp['debug'] = true;

				$tbl_group->select_data($data_temp);

				if(count($tbl_group->data) > 0)
				{
					$this_row = array();

					foreach($this->get_columns_for_select() as $key => $value)
					{
						if(!in_array($key, array('opt_start_lists', 'opt_end_lists', 'effects_on_company', 'requirements_met', 'responsibility', 'evaluation', 'link', 'acknowledged', 'accepted')) && count($this->arr_columns) == 0 || in_array($key, $this->arr_columns))
						{
							$this_row[] = $value;
						}
					}

					$this->data[] = $this_row;

					foreach($tbl_group->data as $r)
					{
						$intLawID = $r['lawID'];
						$strLawNo = $r['lawNo'];
						$strLawName = $r['lawName'];
						$dteLawDecided = $r['lawDecided'];
						$dteLawValidTo = $r['lawValidTo'];
						$strLawUpdatedTo = $r['lawUpdatedTo'];

						if($obj_law->has_been_revoked(array('id' => $intLawID, 'valid_to' => $dteLawValidTo, 'updated_to' => $strLawUpdatedTo)))
						{
							// Do not export it...
						}

						else
						{
							$this_row = array();

							foreach($this->get_columns_for_select() as $key => $value)
							{
								if(!in_array($key, array('opt_start_lists', 'opt_end_lists', 'effects_on_company', 'requirements_met', 'responsibility', 'evaluation', 'link', 'acknowledged', 'accepted')) && count($this->arr_columns) == 0 || in_array($key, $this->arr_columns))
								{
									switch($key)
									{
										case 'number':
											$this_row[] = $strLawNo;
										break;

										case 'name':
											$this_row[] = $strLawName;
										break;

										case 'decided':
											$this_row[] = $dteLawDecided;
										break;

										case 'updated_to':
											$this_row[] = $strLawUpdatedTo;
										break;

										case 'released':
											$this_row[] = $r['lawReleased'];
										break;

										case 'valid_from':
											$this_row[] = $r['lawValid'];
										break;

										case 'valid_to':
											$this_row[] = $dteLawValidTo;
										break;

										case 'summary':
											$this_row[] = $r['lawText'];
										break;

										case 'links':
											$this_row[] = $r['lawLink'];
										break;

										case 'area':
											$obj_law->id = $intLawID;
											$obj_law->get_areas();

											$str_areas = "";

											foreach($obj_law->area_ids as $area_id)
											{
												$obj_law_area = new mf_law_area($area_id);

												$str_areas .= ($str_areas != '' ? ", " : "").$obj_law_area->get_name();
											}

											$this_row[] = $str_areas;
										break;
									}
								}
							}

							$this->data[] = $this_row;
						}
					}
				}
			}

			else
			{
				$error_text = __("You have to choose a list to export", 'lang_law');
			}
		}
	}
}

class widget_law_list extends WP_Widget
{
	function __construct()
	{
		$this->obj_law = new mf_law();

		$this->widget_ops = array(
			'classname' => 'law_list',
			'description' => __("Display Law List", 'lang_law')
		);

		$this->arr_default = array(
			'list_heading' => '',
			'list_display' => 'all',
		);

		parent::__construct(str_replace("_", "-", $this->widget_ops['classname']).'-widget', __("Law List", 'lang_law'), $this->widget_ops);
	}

	function get_display_for_select()
	{
		return array(
			'all' => "-- ".__("All", 'lang_law')." --",
			'filter' => __("Filter", 'lang_law'),
			'table' => __("Table", 'lang_law'),
		);
	}

	function get_list($data = array())
	{
		if(!isset($data['display'])){	$data['display'] = 'all';}

		$out = "";

		$this->obj_law->fetch_request();

		if($this->obj_law->list_id > 0)
		{
			$obj_list = new mf_list(array('id' => $this->obj_law->list_id));
			$strListName = $obj_list->get_name();

			$this->obj_law->is_other_req = $obj_list->is_other_req();

			if($strListName == '')
			{
				$this->obj_law->list_id = 0;
			}
		}

		if(in_array($data['display'], array('all', 'filter')))
		{
			$out .= $this->obj_law->save_data()
			.get_notification()
			.$this->obj_law->get_search_list_law();
		}

		if(in_array($data['display'], array('all', 'table')))
		{
			$arr_header = array();
			$table_body = "";

			if(!isset($this->obj_law->is_other_req) || $this->obj_law->is_other_req == false)
			{
				$arr_header[] = __("Law Number", 'lang_law');
			}

			$arr_header[] = __("Name", 'lang_law');

			if(is_array($this->obj_law->column_ids))
			{
				$arr_data_columns = $this->obj_law->get_data_columns_for_select();

				foreach($this->obj_law->column_ids as $key)
				{
					if(isset($arr_data_columns[$key]))
					{
						$arr_header[] = $arr_data_columns[$key];
					}
				}
			}

			$this->obj_law->arr_groups = $this->obj_law->get_list_groups_2(array('hierarchical' => true));

			if(count($this->obj_law->arr_groups) > 0)
			{
				$table_body = $this->obj_law->output_groups(array('arr_groups' => $this->obj_law->arr_groups, 'arr_header' => $arr_header));
			}

			$table_header = show_table_header($arr_header, false);

			$out .= "<table class='wp-list-table widefat striped'>"
				.$table_header
				."<tbody>";

					if($table_body != '')
					{
						$out .= $table_body;
					}

					else
					{
						$out .= "<tr><td colspan='".count($arr_header)."'>".__("There are no laws in this list", 'lang_law')."</td></tr>";
					}

				$out .= "</tbody>"
				.$table_header
			."</table>";
		}

		return $out;
	}

	function get_form()
	{
		global $wpdb, $obj_list, $error_text;

		$out = "";

		$this->obj_law->fetch_request();
		$out .= $this->obj_law->save_data();
		$this->obj_law->get_from_db();

		$post_edit_url = "?view=edit";

		if($this->obj_law->id > 0)
		{
			$post_edit_url .= "&intLawID=".$this->obj_law->id;
		}

		if($error_text == '' && count($this->obj_law->id_parents) > 0 && count($this->obj_law->lists) == 0 && count($this->obj_law->lists_published) == 0)
		{
			$error_text = sprintf(__("This law is not connected to any lists even though it's parent is. %sWould you like to add the same lists as preview on this law?%s", 'lang_law'), "<a href='".wp_nonce_url($post_edit_url."&btnLawAddListsFromParent", 'law_add_lists_from_parent_'.$this->obj_law->id, '_wpnonce_law_add_lists_from_parent')."' rel='confirm'>", "</a>");
		}

		$context_normal = $context_side = "";

		$context_normal .= "<div class='meta_box'>
			<h2>".($this->obj_law->no != '' ? $this->obj_law->no." " : "").$this->obj_law->name."</h2>";

			$valid_to_temp = $this->obj_law->valid_to;

			// If no valid_to date has been set, look for a law that will replace this
			// This will display expired watermark on previous laws even though they haven't really expired
			/*if($valid_to_temp < DEFAULT_DATE)
			{
				$valid_to_temp = $wpdb->get_var($wpdb->prepare("SELECT lawComesInEffect FROM ".$wpdb->prefix."law INNER JOIN ".$wpdb->prefix."law2law USING (lawID) WHERE lawID_parent = '%d' AND lawComesInEffect IS NOT NULL ORDER BY lawComesInEffect ASC LIMIT 0, 1", $this->obj_law->id));
			}*/

			$parent_transitional_date = $wpdb->get_var($wpdb->prepare("SELECT lawTransitionalDate FROM ".$wpdb->prefix."law INNER JOIN ".$wpdb->prefix."law2law USING (lawID) WHERE lawID_parent = '%d' AND lawTransitionalDate IS NOT NULL ORDER BY lawTransitionalDate ASC LIMIT 0, 1", $this->obj_law->id));

			if($parent_transitional_date > DEFAULT_DATE && $parent_transitional_date > date("Y-m-d"))
			{
				if($valid_to_temp > DEFAULT_DATE)
				{
					$context_normal .= "<span class='watermark'>".sprintf(__("Expired %s, but transitional rules apply to %s", 'lang_law'), $valid_to_temp, $parent_transitional_date)."</span>";
				}

				else
				{
					$context_normal .= "<span class='watermark'>".sprintf(__("Expired, but transitional rules apply to %s", 'lang_law'), $parent_transitional_date)."</span>";
				}
			}

			else if($valid_to_temp > DEFAULT_DATE)
			{
				$context_normal .= "<span class='watermark'>".($valid_to_temp > date("Y-m-d") ? __("Expires", 'lang_law') : __("Expired", 'lang_law'))." ".$valid_to_temp."</span>";
			}

			else if($this->obj_law->comes_in_effect != '' && $this->obj_law->comes_in_effect > date("Y-m-d"))
			{
				$context_normal .= "<span class='watermark'>".__("Comes in Effect", 'lang_law')." ".$this->obj_law->comes_in_effect."</span>";
			}

			$context_normal .= "<div>
				<h3>".__("What does the law say?", 'lang_law')."</h3>";

				if($this->obj_law->text == '')
				{
					$context_normal .= "<p><em>".__("There is no info yet", 'lang_law')."</em></p>";
				}

				else
				{
					$context_normal .= apply_filters('the_content', $this->obj_law->text);
				}

				if($this->obj_law->changes != '')
				{
					$context_normal .= "<h4>".__("What does the change mean?", 'lang_law')."</h4>
					<div class='color_sunday'>".apply_filters('the_content', $this->obj_law->changes)."</div>";

					if(IS_EDITOR)
					{
						$context_normal .= $this->obj_law->get_parent_changes();
					}
				}

				if($this->obj_law->updated_to != '')
				{
					$context_normal .= "<p><strong>".__("Updated to", 'lang_law')."</strong> ".$this->obj_law->updated_to."</p>";
				}

				if($this->obj_law->released > DEFAULT_DATE)
				{
					$context_normal .= "<p><strong>".__("Released", 'lang_law')."</strong> ".$this->obj_law->released."</p>";
				}

				if($this->obj_law->valid > DEFAULT_DATE)
				{
					$context_normal .= "<p><strong>".__("Valid from", 'lang_law')."</strong> ".$this->obj_law->valid."</p>";
				}

				if($this->obj_law->valid_to > DEFAULT_DATE)
				{
					$context_normal .= "<p><strong>".__("Valid to", 'lang_law')."</strong> ".$this->obj_law->valid_to."</p>";
				}

				if($this->obj_law->link != '')
				{
					$context_normal .= "<p><a href='".validate_url($this->obj_law->link)."'>".__("Link to original law", 'lang_law')."</a></p>";
				}

				$context_normal .= get_media_button(array('name' => 'strLawAttachment', 'value' => $this->obj_law->attachment, 'show_add_button' => IS_EDITOR));

			$context_normal .= "</div>
		</div>";

		if(is_plugin_active("mf_law_info/index.php"))
		{
			$this->obj_law_info = new mf_law_info();

			$context_normal .= $this->obj_law_info->show_form($this->obj_law);
		}

		$out .= get_notification()
		."<form method='post' action='".$post_edit_url."' class='mf_form'>"
			.$this->obj_law->get_law_navigation()
			."<div class='admin_container'>
				<div".($context_side != '' ? " class='context_normal'" : "").">".$context_normal."</div>";

				if($context_side != '')
				{
					$out .= "<div class='context_side'>".$context_side."</div>";
				}

			$out .= "</div>
		</form>";

		return $out;
	}

	function widget($args, $instance)
	{
		extract($args);
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo $before_widget;

			if($instance['list_heading'] != '')
			{
				$instance['list_heading'] = apply_filters('widget_title', $instance['list_heading'], $instance, $this->id_base);

				echo $before_title
					.$instance['list_heading']
				.$after_title;
			}

			echo "<div class='section'>";

				$view = check_var('view');

				if($this->obj_law->id > 0 && $view != 'list')
				{
					if(in_array($instance['list_display'], array('all', 'filter')))
					{
						echo $this->get_form();
					}
				}

				else
				{
					echo $this->get_list(array('display' => $instance['list_display']));
				}

			echo "</div>"
		.$after_widget;
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args((array)$new_instance, $this->arr_default);

		$instance['list_heading'] = sanitize_text_field($new_instance['list_heading']);
		$instance['list_display'] = sanitize_text_field($new_instance['list_display']);

		return $instance;
	}

	function form($instance)
	{
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			.show_textfield(array('name' => $this->get_field_name('list_heading'), 'text' => __("Heading", 'lang_law'), 'value' => $instance['list_heading'], 'xtra' => " id='".$this->widget_ops['classname']."-title'"))
			.show_select(array('data' => $this->get_display_for_select(), 'name' => $this->get_field_name('list_display'), 'text' => __("Display", 'lang_law'), 'value' => $instance['list_display']))
		."</div>";
	}
}