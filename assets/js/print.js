/* Payslip page: the print button. (Inline handlers are blocked by the team area's CSP.) */
document.querySelectorAll("[data-print]").forEach(function (b) { b.addEventListener("click", function () { window.print(); }); });
