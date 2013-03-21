(function ($) {
	"use strict";
	$(function () {
		// Place your administration-specific JavaScript here
		if($('#media-meta').length > 0) {
			$('form').attr('enctype', 'multipart/form-data');
		} // end if
	});
}(jQuery));