$(document).ready(function () {
	setTimeout(function () {
		const flashAlert = $(".flash-alert");
		flashAlert.fadeOut(500, function () {
			flashAlert.remove();
		});
	}, 2000);
});
