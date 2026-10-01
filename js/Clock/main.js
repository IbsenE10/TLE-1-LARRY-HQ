// ============================================================
//  MAIN - verbindt alles met de PHP-pagina
// ============================================================

import * as C from './config.js';
import { EspHandler } from './esp-handler.js';
import { ClockHandler } from './clock-handler.js';
import { DemoHandler } from './demo-handler.js';

const esp = new EspHandler();

const clock = new ClockHandler(esp, {
  sleepInput: document.querySelector(C.SELECTOR_SLEEP_INPUT),
  wakeInput: document.querySelector(C.SELECTOR_WAKE_INPUT),
});

const demo = new DemoHandler(esp, clock, { onUpdate: updateDemoUI });

// ---------- Percentage-cirkel ----------
const circle = document.querySelector(C.SELECTOR_PERCENT_CIRCLE);
if (C.UPDATE_PERCENTAGE_CIRCLE && circle) {
  esp.subscribe('status', () => {
    circle.textContent = esp.online && esp.brightness !== null ? `${esp.brightness}%` : '--%';
  });
}

// ---------- Lamp-afbeelding: dimt/verheldert mee met de echte helderheid ----------
const lampImage = document.querySelector(C.SELECTOR_LAMP_IMAGE);
if (lampImage) {
  esp.subscribe('status', () => {
    const b = esp.online && esp.brightness !== null ? esp.brightness : 0;
    const t = b / 100; // 0 - 1

    lampImage.style.opacity = (C.LAMP_MIN_OPACITY + (1 - C.LAMP_MIN_OPACITY) * t).toFixed(2);
    lampImage.style.filter = `brightness(${(C.LAMP_MIN_BRIGHTNESS_FILTER + (1 - C.LAMP_MIN_BRIGHTNESS_FILTER) * t).toFixed(2)})`;
    lampImage.classList.toggle('lamp-offline', !esp.online);
  });
}

// ---------- Powerswitch: lamp + planning in één keer aan/uit ----------
const powerSwitch = document.querySelector(C.SELECTOR_POWER_SWITCH);
if (powerSwitch) {
    console.log('Powerswitch:', powerSwitch.checked ? 'aan' : 'uit');
  powerSwitch.addEventListener('change', () => {
    if (demo.running) demo.stop();   // een lopende demo mag niet overschreven worden

    if (powerSwitch.checked) {
      clock.setEnabled(true);        // past de fase die nu hoort te lopen meteen toe
    } else {
      clock.setEnabled(false);
      esp.setBrightness(C.POWER_SWITCH_OFF_BRIGHTNESS).catch((e) => console.warn('Lamp uitzetten mislukt:', e.message));
    }
  });

  // Beginstand van de switch in het paneel overnemen
  clock.setEnabled(powerSwitch.checked);
}

// ---------- Demo-knop ----------
const demoButton = document.querySelector(C.SELECTOR_DEMO_BUTTON);
const demoStatus = document.querySelector(C.SELECTOR_DEMO_STATUS);

function updateDemoUI({ running, label, secondsLeft }) {
  if (demoButton) {
    demoButton.textContent = running ? 'Demo stoppen' : 'Demo (5 min)';
    demoButton.classList.toggle('demo-running', running);
  }
  if (demoStatus) {
    const mm = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
    const ss = String(secondsLeft % 60).padStart(2, '0');
    demoStatus.textContent = running ? `${label} — nog ${mm}:${ss}` : label;
  }
}

if (demoButton) {
  demoButton.addEventListener('click', () => {
    if (demo.running) {
      demo.stop();
    } else {
      if (powerSwitch && !powerSwitch.checked) powerSwitch.checked = true;
      demo.start();
    }
  });
}

// ---------- Tik op de pagina = wekker uit (echte planning of demo) ----------
if (C.DISMISS_ALARM_ON_TAP) {
  document.addEventListener('pointerdown', (e) => {
    if (demoButton && demoButton.contains(e.target)) return;   // klikken op de knop zelf niet meetellen
    if (demo.running) demo.dismissAlarm();
    else clock.dismissAlarm();
  });
}

// ---------- Optioneel: scherm aan houden ----------
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

// Voor testen in de browserconsole:
//   sleepLamp.clock.setTimeOffset(20)
//   sleepLamp.demo.start()
window.sleepLamp = { esp, clock, demo, config: C };