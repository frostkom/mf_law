var LawAdminModel = Backbone.Model.extend(
{
	getPage: function(dom_action)
	{
		var self = this,
			url = '';

		if(dom_action)
		{
			url += '?type=' + dom_action;
		}

		jQuery().callAPI(
		{
			base_url: script_law_admin_models.plugin_url + 'api/',
			url: url + "&timestamp=" + Date.now(),
			send_type: 'get',
			onSuccess: function(data)
			{
				self.set(data);
			}
		});
	},

	submitForm: function(dom_action, form_data)
	{
		var self = this,
			url = '';

		if(dom_action)
		{
			/* If GET vars are added to search etc. we need to force this */
			jQuery.each(dom_action.split("&"), function(index, value)
			{
				if(index == 0)
				{
					dom_action = value;
				}

				else
				{
					form_data += (form_data != '' ? "&" : "") + value;
				}
			});

			url += '?type=' + dom_action;
		}

		jQuery().callAPI(
		{
			base_url: script_law_admin_models.plugin_url + 'api/',
			url: url,
			data: form_data,
			onSuccess: function(data)
			{
				self.set(data);
			}
		});
	}
});