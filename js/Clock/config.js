// ============================================================
//  SLAAPLAMP INSTELLINGEN
//  Alles wat je wilt aanpassen staat in dit bestand.
// ============================================================

// ---------- ESP32 verbinding ----------
export const ESP_HOST = 'http://esp32haptic.local';
export const ESP_LED_PIN = 26;              // moet in ledPins[] van de sketch staan
export const ESP_PING_INTERVAL_MS = 5000;   // hoe vaak we checken of de ESP32 er nog is
export const ESP_REQUEST_TIMEOUT_MS = 3000;
export const ESP_MAX_MISSED_PINGS = 2;      // pas na zoveel gemiste pings "offline"

// ---------- Elementen op de PHP-pagina ----------
export const SELECTOR_SLEEP_INPUT = '#sleep-time';
export const SELECTOR_WAKE_INPUT = '#wake-time';
export const SELECTOR_PERCENT_CIRCLE = '.percentage-circle';
export const SELECTOR_LAMP_IMAGE = '#lamp-image';
export const SELECTOR_POWER_SWITCH = '#alarm-toggle';
export const SELECTOR_DEMO_BUTTON = '#demo-button';
export const SELECTOR_DEMO_STATUS = '#demo-status';
export const UPDATE_PERCENTAGE_CIRCLE = true;   // toont de echte lamphelderheid in de cirkel

// ---------- Lamp-afbeelding ----------
// De <img id="lamp-image"> wordt gedimd/verhelderd op basis van de
// werkelijke helderheid van de ESP32 - geen losse plaatjes per percentage nodig.
export const LAMP_MIN_OPACITY = 0.15;   // hoe "uit" de lamp eruitziet op 0%
export const LAMP_MIN_BRIGHTNESS_FILTER = 0.35;  // CSS brightness() op 0%

// ---------- Klok ----------
export const SCHEDULE_ENABLED = true;           // false = lamp wordt niet automatisch aangestuurd
export const CLOCK_TICK_MS = 1000;              // hoe vaak de klok kijkt of er een fase begint
export const RETRY_AFTER_FAIL_MS = 5000;        // opnieuw proberen als de ESP32 niet reageerde
export const SESSION_GRACE_MS = 5 * 60 * 1000;  // na de laatste fase nog zo lang blijven proberen
export const DEBUG_TIME_OFFSET_MINUTES = 0;     // om te testen: klok X minuten vooruit zetten

// ---------- Wekker ----------
export const DISMISS_ALARM_ON_TAP = true;       // tik/klik op de pagina = wekker uit
export const KEEP_SCREEN_AWAKE = false;         // scherm aan laten (pagina moet open blijven!)

// ---------- Powerswitch (#alarm-toggle) ----------
// Uit: lamp gaat direct uit en de planning wordt gepauzeerd (klok blijft meelopen,
//      er wordt alleen niets naar de lamp gestuurd totdat hij weer aan gaat).
// Aan: planning gaat weer actief de lamp aansturen.
export const POWER_SWITCH_OFF_BRIGHTNESS = 0;

// ============================================================
//  FASE 1 - VOOR HET SLAPEN: lamp wordt geleidelijk zachter
// ============================================================
export const WIND_DOWN_MINUTES = 30;            // zoveel minuten voor bedtijd begint het dimmen
export const WIND_DOWN_START_BRIGHTNESS = 100;  // % waarmee het dimmen begint
export const WIND_DOWN_END_BRIGHTNESS = 40;     // % waar je bij bedtijd op uitkomt

// ============================================================
//  FASE 2 - TIJDENS DE SLAAP: lamp "ademt" zacht
// ============================================================
export const SLEEP_PULSE_ENABLED = true;        // false = lamp blijft constant branden
export const SLEEP_PULSE_MIN = 0;              // % laagste punt van het ademen
export const SLEEP_PULSE_MAX = 0;              // % hoogste punt van het ademen
export const SLEEP_PULSE_PERIOD_SEC = 10;       // seconden per ademhaling
export const SLEEP_STEADY_BRIGHTNESS = 0;      // % als SLEEP_PULSE_ENABLED = false

// ============================================================
//  FASE 3 - WAKKER WORDEN
// ============================================================
// 3a. Zonsopgang: lamp wordt geleidelijk feller, klaar op wektijd
export const WAKE_SUNRISE_MINUTES = 15;
export const WAKE_SUNRISE_START_BRIGHTNESS = 10;
export const WAKE_SUNRISE_END_BRIGHTNESS = 100;

// 3b. Wekker: snel pulseren tot je opstaat (of tikt op de pagina)
export const WAKE_ALARM_MIN = 40;
export const WAKE_ALARM_MAX = 100;
export const WAKE_ALARM_PERIOD_SEC = 1.2;
export const WAKE_ALARM_MINUTES = 10;           // zo lang duurt de wekker maximaal

// 3c. Daarna: 0 = lamp uit, anders blijft de lamp op dit % branden
export const AFTER_ALARM_BRIGHTNESS = 0;

// ============================================================
//  DE PLANNING
//  anchor:    'sleep' (bedtijd) of 'wake' (wektijd)
//  offsetMin: minuten t.o.v. het anker (negatief = ervoor)
//  action:    fade | pulse | set | off
//  Wil je een extra fase? Voeg gewoon een regel toe (id moet uniek zijn).
// ============================================================
export const PHASES = [
  {
    id: 'wind-down',
    anchor: 'sleep',
    offsetMin: -WIND_DOWN_MINUTES,
    action: {
      type: 'fade',
      from: WIND_DOWN_START_BRIGHTNESS,
      to: WIND_DOWN_END_BRIGHTNESS,
      durationMin: WIND_DOWN_MINUTES,
    },
  },
  {
    id: 'sleep',
    anchor: 'sleep',
    offsetMin: 0,
    action: SLEEP_PULSE_ENABLED
      ? { type: 'pulse', min: SLEEP_PULSE_MIN, max: SLEEP_PULSE_MAX, periodSec: SLEEP_PULSE_PERIOD_SEC }
      : { type: 'set', brightness: SLEEP_STEADY_BRIGHTNESS },
  },
  {
    id: 'sunrise',
    anchor: 'wake',
    offsetMin: -WAKE_SUNRISE_MINUTES,
    action: {
      type: 'fade',
      from: WAKE_SUNRISE_START_BRIGHTNESS,
      to: WAKE_SUNRISE_END_BRIGHTNESS,
      durationMin: WAKE_SUNRISE_MINUTES,
    },
  },
  {
    id: 'alarm',
    anchor: 'wake',
    offsetMin: 0,
    dismissTo: 'after-alarm',   // tik op de pagina = spring naar deze fase
    action: {
      type: 'pulse',
      min: WAKE_ALARM_MIN,
      max: WAKE_ALARM_MAX,
      periodSec: WAKE_ALARM_PERIOD_SEC,
    },
  },
  {
    id: 'after-alarm',
    anchor: 'wake',
    offsetMin: WAKE_ALARM_MINUTES,
    action: AFTER_ALARM_BRIGHTNESS > 0
      ? { type: 'set', brightness: AFTER_ALARM_BRIGHTNESS }
      : { type: 'off' },
  },
];

// ============================================================
//  DEMO - dezelfde vijf fases, maar samengeperst in 5 minuten
//  zodat je alles in één keer kunt laten zien.
//  offsetSec: seconden na het indrukken van de Demo-knop
//  (i.p.v. offsetMin/anchor, want er is geen bed-/wektijd nodig)
// ============================================================
export const DEMO_TOTAL_SECONDS = 5 * 60;

export const DEMO_PHASES = [
  {
    id: 'demo-wind-down',
    offsetSec: 0,
    label: 'Dimmen voor het slapen',
    action: { type: 'fade', from: WIND_DOWN_START_BRIGHTNESS, to: WIND_DOWN_END_BRIGHTNESS, durationSec: 40 },
  },
  {
    id: 'demo-sleep',
    offsetSec: 40,
    label: 'Slaapstand (zacht ademen)',
    action: SLEEP_PULSE_ENABLED
      ? { type: 'pulse', min: SLEEP_PULSE_MIN, max: SLEEP_PULSE_MAX, periodSec: 2 }
      : { type: 'set', brightness: SLEEP_STEADY_BRIGHTNESS },
  },
  {
    id: 'demo-sunrise',
    offsetSec: 190,
    label: 'Zonsopgang',
    action: { type: 'fade', from: WAKE_SUNRISE_START_BRIGHTNESS, to: WAKE_SUNRISE_END_BRIGHTNESS, durationSec: 60 },
  },
  {
    id: 'demo-alarm',
    offsetSec: 250,
    label: 'Wekker!',
    dismissTo: 'demo-after-alarm',
    action: { type: 'pulse', min: WAKE_ALARM_MIN, max: WAKE_ALARM_MAX, periodSec: 0.6 },
  },
  {
    id: 'demo-after-alarm',
    offsetSec: DEMO_TOTAL_SECONDS,
    label: 'Klaar',
    action: AFTER_ALARM_BRIGHTNESS > 0
      ? { type: 'set', brightness: AFTER_ALARM_BRIGHTNESS }
      : { type: 'off' },
  },
];