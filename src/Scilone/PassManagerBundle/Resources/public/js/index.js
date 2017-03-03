$(document).ready(function () {
	$('.btn-pass').click(function () {
		var elBtn = $(this).find('span'),
			elPassword = $(this).closest('tr').find('.passwordHidden'),
			elFakePass = $(this).closest('tr').find('.fakePassword');

		//show password
		if (elBtn.hasClass('glyphicon-eye-open')) {
			if (elPassword.data('decrypt') == 0) {
				$('#layoutLoading').attr('style', 'display:block;');

				$.ajax({
					method: "GET",
					async: true,
					url: Routing.generate(
						'scilone_encryption_xhr_decrypt',
						{'text': elPassword.data('password'), 'salt': elPassword.data('salt')}
					),
					success: function (msg) {
						elPassword.data('decrypt', 1);
						elPassword.html(msg);
					}
				}).done(function( data ) {
					$('#layoutLoading').hide();

					showPassword(elFakePass, elPassword, elBtn);
				});
			}
			else{
				showPassword(elFakePass, elPassword, elBtn);
			}
		}
		else {
			elPassword.hide();
			elFakePass.show();
			elBtn.removeClass('glyphicon-eye-close');
			elBtn.addClass('glyphicon-eye-open');
		}
	});

	$('.btn-copy').click(function () {
		$(this).closest('tr').find('td:eq(0)').select();
		document.execCommand( 'copy' );
		return false;
	});
});

function showPassword(elFakePass, elPassword, elBtn){
	elFakePass.hide();
	elPassword.show();
	elBtn.removeClass('glyphicon-eye-open');
	elBtn.addClass('glyphicon-eye-close');
}