$(document).ready(function () {
	$('.btn-pass').click(function () {
		var elBtn = $(this).find('span'),
			elPassword = $(this).closest('tr').find('.passwordHidden'),
			elFakePass = $(this).closest('tr').find('.fakePassword');

		//show password
		if (elBtn.hasClass('glyphicon-eye-open')) {
			if (elPassword.data('decrypt') === 0) {
				getPassword(elPassword);
			}

			showPassword(elFakePass, elPassword, elBtn);
		}
		else {
			elPassword.hide();
			elFakePass.show();
			elBtn.removeClass('glyphicon-eye-close');
			elBtn.addClass('glyphicon-eye-open');
		}
	});

	/*new Clipboard('.btn-copy', {
		text: function(trigger) {
			console.warn('copy!');

			var elBtn = $(trigger).find('span'),
				elPassword = $(trigger).closest('tr').find('.passwordHidden'),
				elFakePass = $(trigger).closest('tr').find('.fakePassword');

			if (elPassword.data('decrypt') == 0) {
				getPassword(elFakePass, elPassword, elBtn, true);
			}

			return elPassword.html();
		}
	});*/
});

function showPassword(elFakePass, elPassword, elBtn){
	elFakePass.hide();
	elPassword.show();
	elBtn.removeClass('glyphicon-eye-open');
	elBtn.addClass('glyphicon-eye-close');
}

function getPassword(elPassword) {
	$('#layoutLoading').attr('style', 'display:block;');

	$.ajax({
		method: "GET",
		async: true,
		url: Routing.generate(
			'scilone_encryption_xhr_decrypt',
			{'salt': elPassword.data('salt'), 'text':elPassword.data('password')}
		),
		success: function (msg) {
			elPassword.data('decrypt', 1);
			elPassword.html(msg);
		}
	}).done(function (data) {
		$('#layoutLoading').hide();
	});
}