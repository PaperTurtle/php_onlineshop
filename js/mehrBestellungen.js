$(document).ready(function () {
	$("#load_more").click(function () {
		const start = parseInt($("#start").val());

		// Show the spinner while loading
		$("#accordionFlushExample").append(
			'<div class="d-flex pt-3 justify-content-center"> <div class = "spinner-border" role = "status" ><span class = "visually-hidden" > Loading... < /span> </div> </div>'
		);

		$.ajax({
			url: `bestellungen_laden.php?start=${start}`,
			method: "GET",
			success: function (data) {
				setTimeout(function () {
					// Remove the entire spinner div and its content
					$(".spinner-border").parent().remove();

					// Append the new orders
					$("#accordionFlushExample").append(data);

					// Increment the offset
					$("#start").val(start + 5);
				}, 500); // Set the delay in milliseconds
			},
		});
	});
});
