// Wisselt automatisch van kleurmodus op basis van de lokale tijd van het apparaat.
// Pas alleen deze tijden aan als jullie later andere grenzen willen gebruiken.
const THEME_TIMES = {
    wakeUpStart: 6,  // 06:00
    focusStart: 12,  // 12:00
    nightStart: 20   // 20:00
};

function getThemeForCurrentTime() {
    const hour = new Date().getHours();

    if (hour >= THEME_TIMES.wakeUpStart && hour < THEME_TIMES.focusStart) {
        return 'wake-up';
    }

    if (hour >= THEME_TIMES.focusStart && hour < THEME_TIMES.nightStart) {
        return 'focus';
    }

    return 'night';
}

function applyTimeTheme() {
    document.body.dataset.theme = getThemeForCurrentTime();
}

applyTimeTheme();

// Controleer elke minuut opnieuw, zodat de modus ook wisselt als de pagina open blijft.
setInterval(applyTimeTheme, 60 * 1000);
