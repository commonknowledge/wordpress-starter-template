import { formatTime } from "./time";

// Alpine itself is bundled once, in the theme's main script, which starts it
// on DOMContentLoaded. Registering via the alpine:init event (rather than
// importing Alpine here) keeps this bundle small and load-order independent.
document.addEventListener("alpine:init", () => {
  window.Alpine.data("digitalClock", () => ({
    time: formatTime(new Date()),
    init() {
      window.setInterval(() => {
        this.time = formatTime(new Date());
      }, 1000);
    },
  }));
});
