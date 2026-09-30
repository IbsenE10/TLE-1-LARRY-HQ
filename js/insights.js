// js/insights.js  –  makes the Insights page interactive
// Loaded in Insights.php with: <script src="../js/insights.js" defer></script>

// Helper: 468 → "7h 48m" (same as formatMinutes in PHP)
function formatMinutes(minutes) {
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  return h > 0 ? `${h}h ${String(m).padStart(2, "0")}m` : `${m} min`;
}

// IDEA 4: greeting based on the time of day
const greeting = document.getElementById("greeting");

if (greeting) {
  const hour = new Date().getHours(); // 0 – 23, from the user's own clock
  let text;

  if (hour < 5) text = "Good night";
  else if (hour < 12) text = "Good morning";
  else if (hour < 18) text = "Good afternoon";
  else text = "Good evening";

  greeting.textContent = `${text}, ${greeting.dataset.name}!`;
}

// IDEA 1: hover or tap a night in the chart → show its numbers
const nights = document.querySelectorAll(".night");
const legendTitle = document.querySelector(".legend-title");
const stageDialog = document.getElementById("stage-dialog");
const closeStageDialog = document.querySelector(".stage-dialog-close");

// Summary button
const stageSummaryButton = document.querySelector(
  ".chevron[aria-label='More about your sleep stages']",
);
const stageNames = ["deep", "light", "rem", "awake"];

function openStageDialog(title, minutesByStage) {
  const timeInBed = stageNames.reduce(
    (sum, stage) => sum + minutesByStage[stage],
    0,
  );
  const sleepMinutes = timeInBed - minutesByStage.awake;

  document.getElementById("stage-dialog-date").textContent = title;
  document.getElementById("stage-dialog-total").textContent =
    formatMinutes(sleepMinutes);
  document.getElementById("stage-dialog-time-in-bed").textContent =
    `of ${formatMinutes(timeInBed)} in bed`;

  stageNames.forEach((stage) => {
    const minutes = minutesByStage[stage];
    const percent = timeInBed ? Math.round((minutes / timeInBed) * 100) : 0;
    document.querySelector(`[data-detail-value="${stage}"]`).textContent =
      formatMinutes(minutes);
    document.querySelector(`[data-detail-percent="${stage}"]`).textContent =
      `${percent}%`;
    document.querySelector(`[data-detail-bar="${stage}"]`).style.width =
      `${percent}%`;
  });

  stageDialog.showModal();
}

nights.forEach((night) => {
  // Everything that should happen for THIS night, in one function
  function showNight() {
    // highlight this night, un-highlight the others
    nights.forEach((n) => n.classList.remove("selected"));
    night.classList.add("selected");

    // read the numbers PHP put in the data- attributes
    const stages = ["awake", "rem", "light", "deep"];
    const total = stages.reduce((sum, s) => sum + Number(night.dataset[s]), 0);

    stages.forEach((stage) => {
      const minutes = Number(night.dataset[stage]);
      const percent = Math.round((minutes / total) * 100);
      document.querySelector(`[data-stage="${stage}"]`).textContent =
        `${formatMinutes(minutes)} (${percent}%)`;
    });

    legendTitle.textContent = night.dataset.day; // e.g. "Monday"
  }

  night.addEventListener("mouseenter", showNight); // computer: hover
  night.addEventListener("click", () => {
    showNight();
    const minutesByStage = Object.fromEntries(
      stageNames.map((stage) => [stage, Number(night.dataset[stage])]),
    );
    openStageDialog(night.dataset.date, minutesByStage);
  });
});

stageSummaryButton?.addEventListener("click", () => {
  const stageTotals = Object.fromEntries(stageNames.map((stage) => [stage, 0]));
  nights.forEach((night) => {
    stageNames.forEach((stage) => {
      stageTotals[stage] += Number(night.dataset[stage]);
    });
  });

  const averageMinutes = Object.fromEntries(
    stageNames.map((stage) => [
      stage,
      Math.round(stageTotals[stage] / nights.length),
    ]),
  );
  openStageDialog(`Average across ${nights.length} nights`, averageMinutes);
});

closeStageDialog?.addEventListener("click", () => stageDialog.close());
stageDialog?.addEventListener("click", (event) => {
  if (event.target === stageDialog) stageDialog.close();
});

// IDEA 2: ring fills and number counts up when the page opens ═════
const ring = document.querySelector(".ring");
const bigNum = document.querySelector(".big-number");

if (ring && bigNum) {
  // the final values PHP already wrote into the page
  const targetPercent = parseFloat(ring.style.getPropertyValue("--percent"));
  const numbers = bigNum.textContent.match(/\d+/g).map(Number); // "7h 48m" → [7, 48]
  const targetMinutes =
    numbers.length === 2 ? numbers[0] * 60 + numbers[1] : numbers[0];

  const duration = 1200; // 1.2 seconds
  const start = performance.now();

  function step(now) {
    const progress = Math.min((now - start) / duration, 1); // 0 → 1
    const eased = 1 - Math.pow(1 - progress, 3); // fast start, slow end

    ring.style.setProperty("--percent", targetPercent * eased + "%");
    bigNum.textContent = formatMinutes(Math.round(targetMinutes * eased));

    if (progress < 1) requestAnimationFrame(step); // next frame
  }
  requestAnimationFrame(step);
}
