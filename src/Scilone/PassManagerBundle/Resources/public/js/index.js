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

	$('.btn-rights').click(function () {
		getModalRights($(this).data('account'));
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

function getModalRights(idAccount) {
	showOverlay();

	$.ajax({
		method: "GET",
		async: true,
		url: Routing.generate(
			'scilone_pass_manager_account_xhr_modal_right',
			{'idAccount': idAccount}
		),
		success: function (msg) {
			$('#modalRights .modal-body').append(msg);
		}
	}).done(function() {
		hideOverlay();
		showModalRights();
	});
}

function showModalRights() {
	$('#modalRights').modal('show');
}

function showPassword(elFakePass, elPassword, elBtn){
	elFakePass.hide();
	elPassword.show();
	elBtn.removeClass('glyphicon-eye-open');
	elBtn.addClass('glyphicon-eye-close');
}

function showOverlay(){
	$('#layoutLoading').attr('style', 'display:block;');
}

function hideOverlay(){
	$('#layoutLoading').hide();
}

function getPassword(elPassword) {
	showOverlay();

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
	}).done(function() {
		hideOverlay();
	});
}