<?php

$error_text = "";

$obj_law = new mf_law();
$obj_law->fetch_request();
echo $obj_law->save_data();
$obj_law->get_from_db();

$is_allowed_to_edit = IS_ADMIN || !($obj_law->id > 0) || get_current_user_id() == $obj_law->user_id;

$base_url = admin_url("admin.php?page=mf_law/create/index.php".($obj_law->id > 0 ? "&intLawID=".$obj_law->id : ""));

if($error_text == '' && count($obj_law->id_parents) > 0 && count($obj_law->lists) == 0 && count($obj_law->lists_published) == 0)
{
	$error_text = sprintf(__("This law is not connected to any lists even though it's parent is. %sWould you like to add the same lists as preview on this law?%s", 'lang_law'), "<a href='".wp_nonce_url($base_url."&btnLawAddListsFromParent", 'law_add_lists_from_parent_'.$obj_law->id, '_wpnonce_law_add_lists_from_parent')."' rel='confirm'>", "</a>");
}

echo "<div class='wrap'>
	<h2>";

		if($is_allowed_to_edit)
		{
			echo ($obj_law->id > 0 ? __("Update", 'lang_law') : __("Add New", 'lang_law'));
		}

		else
		{
			echo ($obj_law->no != '' ? $obj_law->no." " : "").$obj_law->name;
		}

	echo "</h2>"
	.get_notification()
	."<div id='poststuff'>
		<form method='post' action='".$base_url."' class='mf_form mf_settings'>
			<div id='post-body'".($is_allowed_to_edit ? " class='columns-2'" : "").">
				<div id='post-body-content'>"
					.$obj_law->get_law_navigation();

					if($is_allowed_to_edit)
					{
						echo "<div class='postbox'>
							<h3 class='hndle'><span>".__("Information", 'lang_law')."</span></h3>
							<div class='inside'>"
								.show_textfield(array('name' => 'strLawName', 'text' => __("Name", 'lang_law'), 'value' => $obj_law->name, 'maxlength' => 255, 'required' => true, 'xtra' => "autofocus"))
								.show_wp_editor(array(
									'name' => 'strLawText',
									'value' => $obj_law->text,
									'class' => "hide_media_button hide_tabs",
									'mini_toolbar' => true,
									'textarea_rows' => 10,
									//'statusbar' => false,
								));

								if(IS_EDITOR)
								{
									echo show_wp_editor(array(
										'name' => 'strLawChanges',
										'value' => $obj_law->changes,
										'class' => "hide_media_button hide_tabs",
										'text' => __("What does the change mean?", 'lang_law'),
										'required' => true,
										'mini_toolbar' => true,
										'textarea_rows' => 5,
										//'statusbar' => false,
									))
									.$obj_law->get_parent_changes();
								}

							echo "</div>
						</div>";

						if(IS_EDITOR)
						{
							echo "<div class='postbox'>
								<h3 class='hndle'><span>".__("Links", 'lang_law')."</span></h3>
								<div class='inside'>";

									if($obj_law->link != '')
									{
										echo "<p class='form_editable'>
											<a href='".$obj_law->link."'>".__("Link to original law", 'lang_law')."</a>&nbsp;
											<i class='far fa-edit fa-lg'></i>
										</p>";
									}

									echo show_textfield(array('name' => 'strLawLink', 'text' => __("Link to original law", 'lang_law'), 'value' => $obj_law->link, 'maxlength' => 400, 'xtra_class' => ($obj_law->link != '' ? " hide" : "")))
									.get_media_button(array('name' => 'strLawAttachment', 'value' => $obj_law->attachment))
								."</div>
							</div>";
						}
					}

					else
					{
						echo "<div class='postbox'>
							<h3 class='hndle'>
								<span>"
									.__("What does the law say?", 'lang_law')
								."</span>
							</h3>
							<div class='inside'>";

								if($obj_law->text == '')
								{
									echo "<p><em>".__("There is no info yet", 'lang_law')."</em></p>";
								}

								else
								{
									echo apply_filters('the_content', $obj_law->text);

									if($obj_law->changes != '')
									{
										echo "<h4>".__("What does the change mean?", 'lang_law')."</h4>
										<div class='color_sunday'>".apply_filters('the_content', $obj_law->changes)."</div>";

										if(IS_EDITOR)
										{
											echo $obj_law->get_parent_changes();
										}
									}

									if($obj_law->updated_to != '')
									{
										echo "<p><strong>".__("Updated to", 'lang_law')."</strong> ".$obj_law->updated_to."</p>";
									}

									if($obj_law->released > DEFAULT_DATE)
									{
										echo "<p><strong>".__("Released", 'lang_law')."</strong> ".$obj_law->released."</p>";
									}

									if($obj_law->valid > DEFAULT_DATE)
									{
										echo "<p><strong>".__("Valid from", 'lang_law')."</strong> ".$obj_law->valid."</p>";
									}

									if($obj_law->valid_to > DEFAULT_DATE)
									{
										echo "<p><strong>".__("Valid to", 'lang_law')."</strong> ".$obj_law->valid_to."</p>";
									}

									if($obj_law->link != '')
									{
										echo "<p><a href='".validate_url($obj_law->link)."'>".__("Link to original law", 'lang_law')."</a></p>";
									}

									echo get_media_button(array('name' => 'strLawAttachment', 'value' => $obj_law->attachment, 'show_add_button' => IS_EDITOR));
								}

							echo "</div>
						</div>";
					}

					if(is_plugin_active("mf_law_info/index.php"))
					{
						$obj_law_info = new mf_law_info();

						echo $obj_law_info->show_form($obj_law);
					}

				echo "</div>";

				if($is_allowed_to_edit)
				{
					echo "<div id='postbox-container-1'>
						<div class='postbox'>
							<h3 class='hndle'><span>".__("Save", 'lang_law')."</span></h3>
							<div class='inside'>";

								if($obj_law->id > 0)
								{
									$has_been_revoked = $obj_law->has_been_revoked(array('id' => $obj_law->id, 'list_id' => $obj_law->list_id));

									if($has_been_revoked)
									{
										if(IS_ADMIN)
										{
											echo show_submit(array(
												'name' => 'btnLawCreate',
												'text' => __("Update", 'lang_law'),
												'xtra' => "rel='confirm' confirm_text='".__("Are you sure that you want to update this? It has already been revoked and it will be updated in those lists where it's already published", 'lang_law')."'",
											));
										}

										else
										{
											echo "<i class='fa fa-exclamation-triangle fa-lg yellow' title='".__("The law has been revoked. Contact an admin if this law needs to be updated", 'lang_law')."'></i>";
										}
									}

									else if(count($obj_law->lists_published) > 0)
									{
										if(IS_ADMIN)
										{
											echo show_submit(array(
												'name' => 'btnLawCreate',
												'text' => __("Update", 'lang_law'),
												'xtra' => "rel='confirm' confirm_text='".__("Are you sure that you want to update this? It will be updated in those lists where it's already published", 'lang_law')."'",
											));
										}

										else
										{
											echo "<i class='fa fa-exclamation-triangle fa-lg yellow' title='".__("The law is already published. Contact an admin if this law needs to be updated", 'lang_law')."'></i>";
										}
									}

									else
									{
										echo show_submit(array(
											'name' => 'btnLawCreate',
											'text' => __("Update", 'lang_law'),
											'xtra' => "rel='confirm'",
										));
									}

									if(IS_EDITOR)
									{
										if($has_been_revoked)
										{
											echo "<i class='fa fa-question-circle fa-lg' title='".__("The law has been revoked so you can't create a new version", 'lang_law')."'></i>";
										}

										else if(count($obj_law->lists_published) == 0)
										{
											echo "<i class='fa fa-question-circle fa-lg' title='".__("The law has not been published yet so you can't create a new version", 'lang_law')."'></i>";
										}

										else
										{
											echo show_submit(array(
												'name' => 'btnLawRevision',
												'text' => __("Save new version", 'lang_law'),
												'class' => "button",
												'xtra' => "rel='confirm' confirm_text='".__("Are you sure that you want to save a new version? This will replace the old one as soon as you've published new changes to a list", 'lang_law')."'",
											))
											.input_hidden(array('name' => 'intLawID', 'value' => $obj_law->id));
										}
									}

									echo "<br><em>".sprintf(__("Created %s by %s", 'lang_law'), format_date($obj_law->created), get_user_info(array('id' => $obj_law->user_id)))."</em>";
								}

								else
								{
									echo show_submit(array(
										'name' => 'btnLawCreate',
										'text' => __("Add", 'lang_law'),
									));
								}

								echo wp_nonce_field('law_create_'.$obj_law->id, '_wpnonce_law_create', true, false)
							."</div>
						</div>";

						if(IS_EDITOR)
						{
							echo "<div class='postbox'>
								<h3 class='hndle'><span>".__("Settings", 'lang_law')."</span></h3>
								<div class='inside'>"
									.show_textfield(array('name' => 'strLawNo', 'text' => __("Law Number", 'lang_law'), 'value' => $obj_law->no, 'maxlength' => 30))
									."<div class='flex_flow'>"
										.show_textfield(array('type' => 'date', 'name' => 'dteLawDecided', 'text' => __("Decided", 'lang_law'), 'value' => $obj_law->decided))
										.show_textfield(array('type' => 'date', 'name' => 'dteLawComesInEffect', 'text' => __("Comes in Effect", 'lang_law'), 'value' => $obj_law->comes_in_effect))
									."</div>";

									if($obj_law->comes_in_effect > DEFAULT_DATE && count($obj_law->id_parents) > 0)
									{
										echo show_textfield(array('type' => 'date', 'name' => 'dteLawTransitionalDate', 'text' => __("Transitional Rules Exist", 'lang_law'), 'value' => $obj_law->transitional_date, 'xtra' => "min='".date("Y-m-d", strtotime($obj_law->comes_in_effect." +1 day"))."'"));
									}

									echo show_textfield(array('name' => 'strLawUpdatedTo', 'text' => __("Updated to", 'lang_law'), 'value' => $obj_law->updated_to, 'maxlength' => 20))
									.show_textfield(array('type' => 'date', 'name' => 'dteLawReleased', 'text' => __("Released", 'lang_law'), 'value' => $obj_law->released))
									."<div class='flex_flow'>"
										.show_textfield(array('type' => 'date', 'name' => 'dteLawValid', 'text' => __("Valid from", 'lang_law'), 'value' => $obj_law->valid))
										.show_textfield(array('type' => 'date', 'name' => 'dteLawValidTo', 'text' => __("Valid to", 'lang_law'), 'value' => $obj_law->valid_to))
									."</div>
								</div>
							</div>
							<div class='postbox'>
								<h3 class='hndle'><span>".__("Filters", 'lang_law')."</span></h3>
								<div class='inside'>";

									$obj_law_area = new mf_law_area();
									$arr_data = $obj_law_area->get_for_select(array('add_choose_here' => false));

									if(count($arr_data) > 0)
									{
										echo show_form_alternatives(array('data' => $arr_data, 'name' => 'arrLawAreaID[]', 'text' => __("Area", 'lang_law'), 'value' => $obj_law->area_ids, 'maxsize' => 5));
									}

									else if(IS_EDITOR)
									{
										echo "<p><a href='".admin_url("admin.php?page=mf_area/create/index.php")."'>".__("Add new area", 'lang_law')."</a></p>";
									}

									$tbl_group = new mf_law_chapter_table();

									$tbl_group->select_data(array(
										'select' => "lawChapterID, lawChapterName",
									));

									if($tbl_group->num_rows > 0)
									{
										$arr_data = array(
											'' => "-- ".__("Choose Here", 'lang_law')." --",
										);

										foreach($tbl_group->data as $r)
										{
											$arr_data[$r['lawChapterID']] = $r['lawChapterName'];
										}

										echo show_select(array('data' => $arr_data, 'name' => 'intLawChapterID', 'text' => __("Chapter", 'lang_law'), 'value' => $obj_law->chapter_id));
									}

									else if(IS_EDITOR)
									{
										echo "<p><a href='".admin_url("admin.php?page=mf_chapter/create/index.php")."'>".__("Add new chapter", 'lang_law')."</a></p>";
									}

									$obj_law_group = new mf_law_group();
									$arr_data = $obj_law_group->get_group_select(array('multiple' => true));

									if($arr_data > 0)
									{
										echo show_select(array('data' => $arr_data, 'name' => 'arrLawGroupID[]', 'text' => __("Group", 'lang_law'), 'value' => $obj_law->group_ids, 'xtra' => "class='multiselect'"));
									}

									else if(IS_EDITOR)
									{
										echo "<p><a href='".admin_url("admin.php?page=mf_group/create/index.php")."'>".__("Add new group", 'lang_law')."</a></p>";
									}

									$tbl_group = new mf_law_type_table();

									$tbl_group->select_data(array(
										'select' => "lawTypeID, lawTypeName",
									));

									if($tbl_group->num_rows > 0)
									{
										$arr_data = array(
											'' => "-- ".__("Choose Here", 'lang_law')." --",
										);

										foreach($tbl_group->data as $r)
										{
											$arr_data[$r['lawTypeID']] = $r['lawTypeName'];
										}

										echo show_select(array('data' => $arr_data, 'name' => 'intLawTypeID', 'text' => __("Type", 'lang_law'), 'value' => $obj_law->type_id));
									}

									else if(IS_EDITOR)
									{
										echo "<p><a href='".admin_url("admin.php?page=mf_law_type/create/index.php")."'>".__("Add new law type", 'lang_law')."</a></p>";
									}

								echo "</div>
							</div>
							<div class='postbox'>
								<h3 class='hndle'><span>".__("Advanced", 'lang_law')."</span></h3>
								<div class='inside'>";

									$query_join = $query_where = "";

									$query_join .= " INNER JOIN ".$wpdb->prefix."law2list USING (lawID)";
									$query_where .= " AND lawPublished = '1'";

									/* This ties the law to this specific list which is not what we want */
									/*if($obj_law->list_id > 0)
									{
										$query_where .= " AND listID = '".esc_sql($obj_law->list_id)."'";
									}*/

									$result = $wpdb->get_results($wpdb->prepare("SELECT lawID, lawNo, lawName, lawUpdatedTo FROM ".$wpdb->prefix."law".$query_join." WHERE lawDeleted = '0' AND lawID != '%d'".$query_where." ORDER BY lawName ASC, lawUpdatedTo ASC", $obj_law->id));

									if($wpdb->num_rows > 0)
									{
										$arr_data = array();

										foreach($result as $r)
										{
											$arr_data[$r->lawID] = ($r->lawNo != '' ? $r->lawNo." " : "").$r->lawName.($r->lawUpdatedTo != '' ? " (".$r->lawUpdatedTo.")" : "");
										}

										echo show_select(array('data' => $arr_data, 'name' => 'arrLawID_parents[]', 'text' => __("Is replacing these laws", 'lang_law'), 'value' => $obj_law->id_parents, 'xtra' => "class='select2'")); //multiselect //__("Is replaced upon next publish", 'lang_law')
									}

									$tbl_group = new mf_this_table();

									$data = array(
										'select' => "listID, companyName, listName",
									);

									$tbl_group->select_data($data);

									if(count($tbl_group->data) > 0)
									{
										$arr_data = array();

										if(!isset($obj_list))
										{
											$obj_list = new mf_list();
										}

										foreach($tbl_group->data as $r)
										{
											$arr_data[$r['listID']] = $obj_list->get_name(array('id' => $r['listID']));
										}

										echo show_select(array('data' => $arr_data, 'name' => 'arrListID[]', 'text' => __("List", 'lang_law')." (".__("Preview", 'lang_law').")", 'value' => $obj_law->lists, 'xtra' => "class='multiselect'"))
										.show_select(array('data' => $arr_data, 'name' => 'arrListPublishedID[]', 'text' => __("List", 'lang_law')." (".__("Published", 'lang_law').")", 'value' => $obj_law->lists_published, 'xtra' => "class='multiselect'", 'description' => __("This has permanent effect on the customers public laws", 'lang_law')));
									}

									else if(IS_EDITOR)
									{
										echo "<p><a href='".admin_url("admin.php?page=mf_list/create/index.php")."'>".__("Add new list", 'lang_law')."</a></p>";
									}

								echo "</div>
							</div>";
						}

					echo "</div>";
				}

			echo "</div>
		</form>
	</div>
</div>";