'use strict'

/**
 * This callback replaces enable links with disable links and vice versa.
 * It does this by replacing the text, and replacing all instances of "enable"
 * in the href with "disable", and vice versa.
 */
phpbb.addAjaxCallback('toggle_enable', function(res) {
	var $this = $(this),
		newHref = $this.attr('href');

	$this.text(res.text);
	$this.attr('title', res.title);

	if (newHref.indexOf('disable') !== -1) {
		newHref = newHref.replace('disable', 'enable');
	} else {
		newHref = newHref.replace('enable', 'disable');
	}

	$this.attr('href', newHref);
});
