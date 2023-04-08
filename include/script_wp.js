jQuery(function($)
{
	if(typeof script_law_wp !== 'undefined' && typeof script_law_wp.choose_here_text !== 'undefined')
	{
		$("select.select2").select2(
		{
			placeholder: "-- " + script_law_wp.choose_here_text + " --"
		});
	}

	$(".form_editable .fa-edit").on('click', function()
	{
		$(this).parent(".form_editable").addClass('hide').next(".form_textfield.hide").removeClass('hide');
	});

	/* Look for changes in the form */
	$(document).on('submit', "#poststuff > .mf_form", function()
	{
		$(window).off('beforeunload.edit-law');
	});

	$(document).on('change', "#poststuff > .mf_form", function()
	{
		$(window).on('beforeunload.edit-law', function()
		{
			return "The changes you made will be lost if you navigate away from this page.";
		});
    });

	function has_tiny_changed(arr_ids)
	{
		$.each(arr_ids, function(key, value)
		{
			$(window).on('beforeunload.edit-law', function()
			{
				var editor = typeof tinymce !== 'undefined' && tinymce.get(value);

				if((editor && !editor.isHidden() && editor.isDirty()) || (wp.autosave && wp.autosave.server.postChanged()))
				{
					return "The changes you made will be lost if you navigate away from this page.";
				}
			});
		});
	}

	has_tiny_changed(['strLawText', 'strLawChanges']);
	/*###################*/
});