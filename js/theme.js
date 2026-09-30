// Wisselt automatisch van kleurmodus op basis van de lokale tijd van het apparaat.

function timeToMinutes(time) {
    const [hours, minutes] = time.split(':').map(Number);

    return (hours * 60) + minutes;
}

function getThemeForCurrentTime() {
    const wakeInput = document.getElementById('wake-time');
    const sleepInput = document.getElementById('sleep-time');

    // Stop als de inputs niet op deze pagina bestaan
    if (!wakeInput || !sleepInput) {
        return;
    }

    const wakeTime = timeToMinutes(wakeInput.value);
    const sleepTime = timeToMinutes(sleepInput.value);

    // 2 uur vóór en 2 uur na wakker worden
    const wakeUpStart = wakeTime - (2 * 60);
    const focusStart = wakeTime + (2 * 60);

    // 2 uur vóór slapen begint night mode
    const nightStart = sleepTime - (2 * 60);

    const now = new Date();
    const currentTime = (now.getHours() * 60) + now.getMinutes();

    if (currentTime >= wakeUpStart && currentTime < focusStart) {
        return 'wake-up';
    }

    if (currentTime >= focusStart && currentTime < nightStart) {
        return 'focus';
    }

    return 'night';
}

function applyTimeTheme() {
    const theme = getThemeForCurrentTime();

    if (!theme) {
        return;
    }

    document.body.dataset.theme = theme;

    console.log(
        `Applied theme: ${theme} at ${new Date().toLocaleTimeString()}`
    );
}


// Meteen uitvoeren wanneer de pagina opent
applyTimeTheme();


// Elke minuut opnieuw controleren
setInterval(applyTimeTheme, 60 * 1000);


// Meteen opnieuw berekenen als WakeTime verandert
document.getElementById('wake-time')?.addEventListener('change', applyTimeTheme);


// Meteen opnieuw berekenen als SleepTime verandert
document.getElementById('sleep-time')?.addEventListener('change', applyTimeTheme);