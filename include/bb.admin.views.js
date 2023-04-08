var filter_has_changed = false,
	has_storage = false,
	groups_open = [];

var LawAdminView = Backbone.View.extend(
{
	el: jQuery(".admin_container"),

	initialize: function()
	{
		this.model.on("change:redirect", myAdminView.do_redirect, this);
		this.model.on('change:message', myAdminView.display_message, this);
		this.model.on("change:next_request", this.next_request, this);
		this.model.on("change:admin_law_response", this.admin_law_response, this);
	},

	events:
	{
		"submit form": "submit_form",
		"click .toggle_header": "toggle_header",
	},

	next_request: function()
	{
		var response = this.model.get("next_request");

		if(response != '')
		{
			//this.model.getPage(response);
			location.hash = response;

			this.model.set({"next_request" : ""});
		}
	},

	loadPage: function(action)
	{
		myAdminView.hide_message();

		var dom_container = jQuery("#" + action.replace(/\//g, '_'));

		if(dom_container.length > 0)
		{
			myAdminView.display_container(dom_container);
		}

		else
		{
			jQuery(".admin_container .loading").removeClass('hide').siblings("div").addClass('hide');
		}

		this.model.getPage(action);
	},

	toggle_groups: function(dom_obj, save)
	{
		var group_class = dom_obj.attr('id'),
			group_id = group_class.replace("group_", "");

		dom_obj.toggleClass('open').find(".fa").toggleClass('fa-plus-square fa-minus-square');
		dom_obj.siblings(".toggle_item." + group_class).toggleClass('hide');

		if(save == true && has_storage == true)
		{
			this.set_groups_open(group_id, dom_obj.hasClass('open'));
		}
	},

	set_groups_open: function(group_id, is_open)
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
	},

	save_groups_open: function()
	{
		if(has_storage == true)
		{
			jQuery.Storage.set('groups_open', JSON.stringify(groups_open));
		}
	},

	toggle_header: function(e)
	{
		this.toggle_groups(jQuery(e.currentTarget), true);

		this.save_groups_open();
	},

	submit_form: function(e)
	{
		var dom_obj = jQuery(e.currentTarget),
			dom_action = dom_obj.data('action'),
			api_url = (dom_obj.data('api-url') || '');

		if(api_url == '')
		{
			this.model.submitForm(dom_action, dom_obj.serialize());

			dom_obj.find("button[type='submit']").addClass('loading is_disabled')/*.attr('disabled', true)*/;

			return false;
		}
	},

	admin_law_response: function()
	{
		var response = this.model.get('admin_law_response'),
			type = response.type,
			html = '';

		switch(type)
		{
			case 'admin_law_list':
				var dom_template = jQuery("#template_" + type),
					dom_container = jQuery("#" + type);

				html = _.template(dom_template.html())(response);

				dom_container.children("div").html(html);

				myAdminView.display_container(dom_container);

				if(response.list_id > 0)
				{
					dom_container.children("h1").text(response.list_name);
				}

				if(jQuery(".toggle_header").length > 1)
				{
					jQuery(".toggle_header strong").append(" <i class='fa fa-plus-square'></i>");
					jQuery(".toggle_item").addClass('hide');

					/*jQuery(".toggle_all").on('click', function()
					{
						var dom_open = jQuery(".toggle_header.open"),
							dom_closed = jQuery(".toggle_header:not(.open)"),
							groups_closed = dom_closed.length;

						groups_open = dom_open.length;

						if(groups_open > groups_closed)
						{
							dom_open.each(function()
							{
								this.toggle_groups(jQuery(this), true);
							});
						}

						else
						{
							dom_closed.each(function()
							{
								this.toggle_groups(jQuery(this), true);
							});
						}

						this.save_groups_open();

						return false;
					});*/

					if(typeof jQuery.Storage != 'undefined')
					{
						has_storage = true;

						if(typeof jQuery.Storage.get('groups_open') != 'undefined')
						{
							groups_open = JSON.parse(jQuery.Storage.get('groups_open'));

							var count_temp = groups_open.length,
								del_amount = 0;

							for(var i = 0; i < count_temp; i++)
							{
								var group_id = groups_open[i],
									dom_obj = jQuery("#group_" + groups_open[i]);

								if(dom_obj.length > 0)
								{
									this.toggle_groups(dom_obj, false);
								}

								else
								{
									this.set_groups_open(group_id, dom_obj.hasClass('open'));

									del_amount++;
								}
							}

							if(del_amount > 0)
							{
								this.save_groups_open();
							}
						}
					}
				}

				else
				{
					jQuery(".toggle_header").hide(); /*, .toggle_all*/
				}
			break;

			default:
				console.log("I got an unexpected type: " + type);
			break;
		}
	},

	filter_law: function(action)
	{
		var dom_obj = jQuery("#admin_law_list .mf_form"),
			api_url = dom_obj.data('api-url') || '';

		if(typeof action !== 'undefined' && typeof action !== 'object')
		{
			var dom_action = action;

			filter_has_changed = true;
		}

		else
		{
			var dom_action = dom_obj.data('action');
		}

		if(api_url == '' && filter_has_changed == true)
		{
			filter_has_changed = false;

			this.model.submitForm(dom_action, dom_obj.serialize());
		}
	}
});

var myLawAdminView = new LawAdminView({model: new LawAdminModel()});