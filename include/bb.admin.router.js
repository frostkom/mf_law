var LawAdminApp = Backbone.Router.extend(
{
	routes:
	{
		"admin/law/:action": "handle_law",
		"admin/law/:action/:action": "handle_law"
	},

	handle_law: function(action1, action2)
	{
		this.handle('law', action1, action2);
	},

	handle: function(type, action1, action2)
	{
		var action = "admin/" + type + "/" + action1;

		if(action2 != null)
		{
			action += "/" + action2;
		}

		if(action1 == 'list')
		{
			switch(type)
			{
				case 'law':
					myLawAdminView.filter_law(action);
				break;
			}
		}

		else
		{
			myLawAdminView.loadPage(action);
		}
	}
});

new LawAdminApp();