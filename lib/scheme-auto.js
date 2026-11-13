/**
 * @file
 * Match the device: sets the `dark` class on <html> before anything paints
 * (no flash of the light page) and follows a change of the device's setting
 * while the page is open. With JavaScript off the page stays light.
 */

(function () {
  var query = window.matchMedia("(prefers-color-scheme: dark)");
  function apply() {
    document.documentElement.classList.toggle("dark", query.matches);
  }
  apply();
  query.addEventListener("change", apply);
})();
