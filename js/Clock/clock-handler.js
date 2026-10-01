import * as C from './config.js';

const MIN_MS = 60 * 1000;

function parseTime(value) {
  const m = /^(\d{1,2}):(\d{2})$/.exec(value || '');
  return m ? { h: Number(m[1]), m: Number(m[2]) } : null;
}

// Tijdstip (ms) van "HH:MM" op de dag van 'base' + dayOffset dagen
function dateAt(base, time, dayOffset) {
  return new Date(
    base.getFullYear(), base.getMonth(), base.getDate() + dayOffset,
    time.h, time.m, 0, 0
  ).getTime();
}

export class ClockHandler {
  constructor(esp, { sleepInput, wakeInput, phases = C.PHASES } = {}) {
    this.esp = esp;
    this.sleepInput = sleepInput;
    this.wakeInput = wakeInput;
    this.phases = phases;

    this.timeOffsetMs = C.DEBUG_TIME_OFFSET_MINUTES * MIN_MS;

    this.session = null;
    this.currentPhase = null;
    this.appliedKey = null;      // laatst succesvol toegepaste fase
    this.suppressedKey = null;   // fase die de gebruiker heeft weggetikt (wekker)
    this.lastFailKey = null;
    this.lastFailAt = 0;
    this.busy = false;
    this.enabled = true;         // false = powerswitch staat uit, klok stuurt de lamp niet aan
    this._timer = null;
  }

  // Powerswitch uit: lamp direct uit, planning gepauzeerd.
  // Powerswitch weer aan: huidige fase opnieuw toepassen.
  setEnabled(enabled) {
    this.enabled = enabled;
    if (enabled) return this.reset();
    console.info('[Klok] planning gepauzeerd (powerswitch uit)');
    this.appliedKey = null;
    this.lastFailKey = null;
  }

  start() {
    // 'change' i.p.v. 'input': wacht tot de gebruiker klaar is met de tijd kiezen
    [this.sleepInput, this.wakeInput].forEach((el) => {
      if (el) el.addEventListener('change', () => this.reset());
    });

    // ESP32 weer bereikbaar (of herstart)? Dan de huidige fase opnieuw toepassen.
    this.esp.subscribe('connected', () => this.reset());

    this._timer = setInterval(() => this.tick(), C.CLOCK_TICK_MS);
    this.tick();
  }

  stop() {
    clearInterval(this._timer);
    this._timer = null;
  }

  now() {
    return Date.now() + this.timeOffsetMs;
  }

  // Forceer dat de huidige fase opnieuw wordt toegepast
  reset() {
    this.appliedKey = null;
    this.lastFailKey = null;
    return this.tick();
  }

  // Alleen om te testen: klok X minuten vooruit (of achteruit) zetten
  setTimeOffset(minutes) {
    this.timeOffsetMs = minutes * MIN_MS;
    this.suppressedKey = null;
    return this.reset();
  }

  // ---------- planning opbouwen ----------
  _currentSession(now) {
    const sleep = parseTime(this.sleepInput && this.sleepInput.value);
    const wake = parseTime(this.wakeInput && this.wakeInput.value);
    if (!sleep || !wake) return null;

    const base = new Date(now);
    const sessions = [];

    // Gisteren-, vandaag- en morgenavond bekijken, zodat een sessie
    // die over middernacht loopt altijd gevonden wordt.
    for (const dayOffset of [-1, 0, 1]) {
      const sleepAt = dateAt(base, sleep, dayOffset);
      let wakeAt = dateAt(base, wake, dayOffset);
      if (wakeAt <= sleepAt) wakeAt = dateAt(base, wake, dayOffset + 1);

      const phases = this.phases
        .map((p) => ({
          ...p,
          at: (p.anchor === 'sleep' ? sleepAt : wakeAt) + p.offsetMin * MIN_MS,
        }))
        .sort((a, b) => a.at - b.at);

      const end = phases[phases.length - 1].at + C.SESSION_GRACE_MS;
      sessions.push({ key: sleepAt, sleepAt, wakeAt, phases, end });
    }

    // Eerste sessie die nog niet voorbij is (lopend, of de eerstvolgende)
    return sessions.find((s) => now < s.end) || null;
  }

  // ---------- elke seconde ----------
  async tick() {
    if (!C.SCHEDULE_ENABLED || !this.enabled || this.busy) return;

    const now = this.now();
    const session = this._currentSession(now);
    this.session = session;
    if (!session) return;

    // Laatste fase waarvan de starttijd al voorbij is
    let idx = -1;
    session.phases.forEach((p, i) => { if (p.at <= now) idx = i; });
    if (idx === -1) { this.currentPhase = null; return; }   // sessie is nog niet begonnen

    const phase = session.phases[idx];
    this.currentPhase = phase;
    const key = `${session.key}:${phase.id}`;

    if (key === this.appliedKey || key === this.suppressedKey) return;
    if (key === this.lastFailKey && now - this.lastFailAt < C.RETRY_AFTER_FAIL_MS) return;

    this.busy = true;
    try {
      await this._apply(phase, now - phase.at);
      this.appliedKey = key;
      this.lastFailKey = null;
      console.info(`[Klok] fase "${phase.id}" toegepast`);
    } catch (err) {
      this.lastFailKey = key;
      this.lastFailAt = now;
      console.warn(`[Klok] fase "${phase.id}" mislukt (${err.message}), opnieuw proberen...`);
    } finally {
      this.busy = false;
    }
  }

  // ---------- fase uitvoeren ----------
  // elapsedMs = hoe lang de fase al bezig zou moeten zijn (voor als de pagina later opent)
  async _apply(phase, elapsedMs) {
    const a = phase.action;

    switch (a.type) {
      case 'fade': {
        const total = a.durationMin * MIN_MS;
        if (elapsedMs >= total) return this.esp.setBrightness(a.to);
        const from = a.from + (a.to - a.from) * (elapsedMs / total);
        return this.esp.fade({ from, to: a.to, durationMs: total - elapsedMs });
      }
      case 'pulse':
        return this.esp.pulse({ min: a.min, max: a.max, periodMs: a.periodSec * 1000 });
      case 'set':
        return this.esp.setBrightness(a.brightness);
      case 'off':
        return this.esp.turnOff();
      case 'flash':
        return this.esp.flash();
      default:
        throw new Error(`Onbekend actietype: ${a.type}`);
    }
  }

  // Wekker wegtikken: springt naar de fase die bij 'dismissTo' staat
  async dismissAlarm() {
    const current = this.currentPhase;
    if (!this.enabled || !this.session || !current || !current.dismissTo) return;

    const target = this.session.phases.find((p) => p.id === current.dismissTo);
    if (!target) return;

    this.suppressedKey = `${this.session.key}:${current.id}`;
    try {
      await this._apply(target, 0);
      this.appliedKey = `${this.session.key}:${target.id}`;
      console.info('[Klok] wekker weggetikt');
    } catch (err) {
      this.suppressedKey = null;   // niet gelukt: wekker blijft doorgaan
      console.warn(`[Klok] wekker wegtikken mislukt (${err.message})`);
    }
  }

  // Handig om in de console te bekijken: sleepLamp.clock.getStatus()
  getStatus() {
    const s = this.session;
    if (!s) return { session: null };
    const now = this.now();
    const next = s.phases.find((p) => p.at > now);
    return {
      bedtijd: new Date(s.sleepAt).toLocaleString(),
      wektijd: new Date(s.wakeAt).toLocaleString(),
      huidigeFase: this.currentPhase ? this.currentPhase.id : '(nog niet begonnen)',
      volgendeFase: next ? `${next.id} om ${new Date(next.at).toLocaleTimeString()}` : '(geen)',
    };
  }
}