const backToTopButton = document.querySelector("#back-to-top-btn");
const scrollOffset = 300;
const animationDuration = 750;

window.addEventListener("scroll", scrollFunction);
backToTopButton.addEventListener("click", smoothScrollBackToTop);

/**
 * Wenn der Benutzer nach unten scrollt, wird der Button angezeigt.
 */
function scrollFunction() {
	if (window.pageYOffset > scrollOffset) {
		showBackToTopButton();
	} else {
		hideBackToTopButton();
	}
}

/**
 * Der Button wird angezeigt.
 */
function showBackToTopButton() {
	if (!backToTopButton.classList.contains("btnEntrance")) {
		backToTopButton.classList.remove("btnExit");
		backToTopButton.classList.add("btnEntrance");
		backToTopButton.style.display = "block";
	}
}

/**
 * Der Button wird ausgeblendet.
 */
function hideBackToTopButton() {
	if (backToTopButton.classList.contains("btnEntrance")) {
		backToTopButton.classList.remove("btnEntrance");
		backToTopButton.classList.add("btnExit");
		setTimeout(() => {
			backToTopButton.style.display = "none";
		}, animationDuration);
	}
}

/**
 * Der Button wird angeklickt und die Seite scrollt nach oben.
 */
function smoothScrollBackToTop() {
	const startPosition = window.pageYOffset;
	const targetPosition = 0;
	const distance = targetPosition - startPosition;
	let start = null;

	window.requestAnimationFrame(step);

	function step(timestamp) {
		if (!start) start = timestamp;
		const progress = timestamp - start;
		window.scrollTo(0, easeInOutCubic(progress, startPosition, distance, animationDuration));
		if (progress < animationDuration) window.requestAnimationFrame(step);
	}
}

/**
 * Berechnet die Position der Seite.
 *
 * @param {number} t Zeit
 * @param {number} b Startposition
 * @param {number} c Distanz
 * @param {number} d Dauer
 * @returns {number} Position
 */
function easeInOutCubic(t, b, c, d) {
	t /= d / 2;
	if (t < 1) return (c / 2) * t * t * t + b;
	t -= 2;
	return (c / 2) * (t * t * t + 2) + b;
}
