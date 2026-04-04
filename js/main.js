document.addEventListener("DOMContentLoaded", () => {
  const yearSpans = document.querySelectorAll(".current-year");
  const year = new Date().getFullYear();
  yearSpans.forEach((span) => (span.textContent = year));
});