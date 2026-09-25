/* Applies the saved theme before first paint so the team area never flashes light. */
(function (r) {
  try { if (localStorage.getItem("automate-theme") === "dark") r.setAttribute("data-theme", "dark"); } catch (e) {}
})(document.documentElement);
