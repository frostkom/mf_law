jQuery(function($)
{
	var has_storage = false;

	function toggle_groups(dom_obj, save)
	{
		var group_class = dom_obj.attr('id'),
			group_id = group_class.replace("group_", "");

		dom_obj.toggleClass('open').find(".fa").toggleClass('fa-plus-square').toggleClass('fa-minus-square');
		dom_obj.siblings(".toggle_item." + group_class).toggleClass('hide');

		if(save == true && has_storage == true)
		{
			set_groups_open(group_id, dom_obj.hasClass('open'));
		}
	}

	function set_groups_open(group_id, is_open)
	{
		var index = groups_open.indexOf(group_id);

		if(is_open)
		{
			if(typeof index != 'undefined')
			{
				groups_open.push(group_id);
			}
		}

		else
		{
			groups_open.splice(index, 1);
		}
	}

	function save_groups_open()
	{
		if(has_storage == true)
		{
			$.Storage.set('groups_open', JSON.stringify(groups_open));
		}
	}

	if($(".toggle_header").length > 1)
	{
		$(".toggle_header h3").append(" <i class='fa fa-plus-square'></i>");
		$(".toggle_item").addClass('hide');

		$(".toggle_header").on('click', function()
		{
			toggle_groups($(this), true);

			save_groups_open();
		});

		$(".toggle_all").on('click', function()
		{
			var dom_open = $(".toggle_header.open"),
				groups_open = dom_open.length,
				dom_closed = $(".toggle_header:not(.open)"),
				groups_closed = dom_closed.length;

			if(groups_open > groups_closed)
			{
				dom_open.each(function()
				{
					toggle_groups($(this), true);
				});
			}

			else
			{
				dom_closed.each(function()
				{
					toggle_groups($(this), true);
				});
			}

			save_groups_open();

			return false;
		});

		if(typeof $.Storage != 'undefined')
		{
			has_storage = true;

			if(typeof $.Storage.get('groups_open') != 'undefined')
			{
				var groups_open = JSON.parse($.Storage.get('groups_open')),
					count_temp = groups_open.length,
					del_amount = 0;

				for(var i = 0; i < count_temp; i++)
				{
					var group_id = groups_open[i],
						dom_obj = $("#group_" + groups_open[i]);

					if(dom_obj.length > 0)
					{
						toggle_groups(dom_obj, false);
					}

					else
					{
						set_groups_open(group_id, dom_obj.hasClass('open'));

						del_amount++;
					}
				}

				if(del_amount > 0)
				{
					save_groups_open();
				}
			}

			else
			{
				var groups_open = [];
			}
		}
	}

	else
	{
		$(".toggle_header, .toggle_all").hide();
	}

	/* Same code as in mf_base/include/script_wp.js */
	var dom_tables = $(".wp-list-table");

	if(dom_tables.length > 0)
	{
		dom_tables.removeClass('fixed').find("tr").each(function()
		{
			var self = $(this);

			if(self.find(".set_tr_color").length > 0)
			{
				self.find(".set_tr_color").each(function()
				{
					var add_class = $(this).attr('rel');

					if(add_class != '')
					{
						self.addClass(add_class);
					}
				});
			}
		});
	}
});