$(document).ready(function () {
	$('.btn-pass').click(function () {
		var el = $(this),
			elParent = el.closest('tr');

		//show password
		if (el.find('span').hasClass('glyphicon-eye-open')) {
			elParent.find('.fakePassword').hide();
			elParent.find('.passwordHidden').show();
			el.find('span').removeClass('glyphicon-eye-open');
			el.find('span').addClass('glyphicon-eye-close');
		}
		else {
			elParent.find('.passwordHidden').hide();
			elParent.find('.fakePassword').show();
			el.find('span').removeClass('glyphicon-eye-close');
			el.find('span').addClass('glyphicon-eye-open');
		}
	});
});