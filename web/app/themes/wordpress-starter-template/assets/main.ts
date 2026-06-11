import Alpine from "alpinejs";

import "./main.css";

window.Alpine = Alpine;

// Block view scripts register their components on the alpine:init event.
// Starting Alpine on DOMContentLoaded guarantees every footer script has run
// (and registered its listeners) first, whatever order they load in.
document.addEventListener("DOMContentLoaded", () => {
  Alpine.start();
});
