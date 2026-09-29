// ============================================================
//  MAIN - verbindt alles met de PHP-pagina
// ============================================================

import * as C from './config.js';
import { EspHandler } from './esp-handler.js';
import { ClockHandler } from './clock-handler.js';

const esp = new EspHandler();

const clock = new ClockHandler(esp, {
  sleepInput: document.querySelector(C.SELECTOR_SLEEP_INPUT),
  wakeInput: document.querySelector(C.SELECTOR_WAKE_INPUT),
});

// Percentage-cirkel op de pagina laten meelopen met de echte lamp
const circle = document.querySelector(C.SELECTOR_PERCENT_CIRCLE);
if (C.UPDATE_PERCENTAGE_CIRCLE && circle) {
  esp.subscribe('status', () => {
    circle.textContent = esp.online && esp.brightness !== null ? `${esp.brightness}%` : '--%';
  });
}

// Tik ergens op de pagina = wekker uit (doet niets als er geen wekker loopt)
if (C.DISMISS_ALARM_ON_TAP) {
  document.addEventListener('pointerdown', () => clock.dismissAlarm());
}

// Optioneel: scherm aan houden, zodat de pagina de hele nacht actief blijft
async function keepAwake() {
  if (!C.KEEP_SCREEN_AWAKE || !('wakeLock' in navigator)) return;
  try { await navigator.wakeLock.request('screen'); } catch (e) { console.warn('Wake lock mislukt:', e.message); }
}
keepAwake();
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') keepAwake();
});

esp.start();
clock.start();

// Voor testen in de browserconsole: sleepLamp.clock.setTimeOffset(20)
window.sleepLamp = { esp, clock, config: C };