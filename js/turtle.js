// Easter egg :)
const konamiCodeSequence = [
	"ArrowUp",
	"ArrowUp",
	"ArrowDown",
	"ArrowDown",
	"ArrowLeft",
	"ArrowRight",
	"ArrowLeft",
	"ArrowRight",
	"b",
	"a",
];
let konamiCodeIndex = 0;

document.addEventListener("keydown", function (event) {
	const keyPressed = event.key;
	const expectedKey = konamiCodeSequence[konamiCodeIndex];

	if (keyPressed.toLowerCase() === expectedKey.toLowerCase()) {
		konamiCodeIndex++;

		if (konamiCodeIndex === konamiCodeSequence.length) {
			activateEasterEgg();
			konamiCodeIndex = 0;
		}
	} else {
		konamiCodeIndex = 0;
	}
});

function activateEasterEgg() {
	const easterEggContainer = document.createElement("div");
	easterEggContainer.classList.add("easter-egg-container");

	const confettiColors = [
		"#f0d743",
		"#ed6c63",
		"#8ac926",
		"#6a4c93",
		"#00a896",
		"#f77f00",
		"#9d4edd",
		"#ff3838",
		"#5f0f40",
	];

	// Increase the number of confetti pieces
	for (let i = 0; i < 300; i++) {
		confetti({
			particleCount: 1,
			angle: Math.random() * 360,
			spread: Math.random() * 70 + 30,
			origin: {
				x: Math.random(),
				y: Math.random() - 0.2,
			},
			colors: [confettiColors[i % confettiColors.length]],
			shapes: ["square", "circle"],
		});
	}

	const turtleAsciiArt =
		"               _,.---.---.---.--.._ \n" +
		"            _.-' `--.`---.`---'-. _,`--.._\n" +
		"           /`--._ .'.     `.     `,`-.`-._\\\n" +
		"          ||   \\  `.`---.__`__..-`. ,'`-._/\n" +
		"     _  ,`\\ `-._\\   \\    `.    `_.-`-._,``-.\n" +
		"  ,`   `-_ \\/ `-.`--.\\    _\\_.-'__.-`-.`-._`.\n" +
		" (_.o> ,--. `._/'--.-`,--`  \\_.-'       \\`-._ \\\n" +
		"  `---'    `._ `---._/__,----`           `-. `-\\\n" +
		"            /_, ,  _..-'                    `-._\\\n" +
		"            \\_, \\/ ._( Sewe was here :)               \n" +
		"             \\_, \\/ ._\n" +
		"              `._,\\/ ._\n" +
		"                `._// ./`-._\n" +
		"                 `-._-_-_.-'";

	const asciiArtContainer = document.createElement("pre");
	asciiArtContainer.textContent = turtleAsciiArt;
	easterEggContainer.appendChild(asciiArtContainer);

	document.body.appendChild(easterEggContainer);

	setTimeout(() => {
		document.body.removeChild(easterEggContainer);
	}, 2600);
}
