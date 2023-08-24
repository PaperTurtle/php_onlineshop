$(document).ready(function () {
	$("#load_more").click(function () {
		const start = parseInt($("#start").val());
		$("#accordionFlushExample").append(
			'<div class="d-flex pt-3 justify-content-center"> <div class = "spinner-border" role = "status" ><span class = "visually-hidden" > Loading... < /span> </div> </div>'
		);

		$.ajax({
			url: `bestellungen_laden.php?start=${start}`,
			method: "GET",
			success: function (data) {
				setTimeout(function () {
					$(".spinner-border").parent().remove();

					$("#accordionFlushExample").append(data);

					$("#start").val(start + 5);
				}, 500);
			},
		});
	});
});
