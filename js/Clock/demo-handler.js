// ============================================================
//  DEMO HANDLER
//  Speelt de volledige cyclus (dimmen -> slapen -> zonsopgang ->
//  wekker) in 5 minuten af, voor als je het moet voordoen.
//  Pauzeert de echte planning zolang de demo loopt, en zet die
//  daarna terug in de stand van vóór de demo.
// ============================================================

import * as C from './config.js';

export class DemoHandler {
  constructor(esp, clock, { onUpdate = () => {} } = {}) {
    this.esp = esp;
    this.clock = clock;
    this.onUpdate = onUpdate;

    this.running = false;
    this._timers = [];
    this._ticker = null;
    this._startedAt = 0;
    this._currentPhase = null;
    this._wasClockEnabled = true;
  }

  start() {
    if (this.running) return;
    this.running = true;
    this._startedAt = Date.now();
    this._currentPhase = null;

    // Echte planning even aan de kant zetten, zodat hij niet tegelijk
    // aan de lamp zit te trekken. Na de demo krijgt hij zijn oude stand terug.
    this._wasClockEnabled = this.clock.enabled;
    this.clock.setEnabled(false);

    this._report('Demo gestart...');

    C.DEMO_PHASES.forEach((phase) => {
      const t = setTimeout(() => this._runPhase(phase), phase.offsetSec * 1000);
      this._timers.push(t);
    });

    this._ticker = setInterval(() => {
      const elapsed = (Date.now() - this._startedAt) / 1000;
      const left = Math.max(0, Math.ceil(C.DEMO_TOTAL_SECONDS - elapsed));
      this._report(undefined, left);
      if (left <= 0) this.stop(true);
    }, 1000);
  }

  // Tik op de pagina tijdens de wekker-fase = direct naar het einde
  dismissAlarm() {
    if (!this.running) return;
    const current = this._currentPhase;
    if (!current || !current.dismissTo) return;

    const target = C.DEMO_PHASES.find((p) => p.id === current.dismissTo);
    if (!target) return;

    this._timers.forEach(clearTimeout);
    this._timers = [];

    this._runPhase(target).finally(() => this.stop(true));
  }

  stop(finished = false) {
    if (!this.running) return;
    this.running = false;

    this._timers.forEach(clearTimeout);
    this._timers = [];
    clearInterval(this._ticker);
    this._ticker = null;

    // Planning terugzetten zoals hij was vóór de demo begon
    if (this._wasClockEnabled) this.clock.setEnabled(true);

    this._report(finished ? 'Demo klaar' : 'Demo gestopt', 0);
  }

  async _runPhase(phase) {
    this._currentPhase = phase;
    this._report(phase.label || phase.id);

    try {
      await this._apply(phase.action);
      console.info(`[Demo] fase "${phase.id}" toegepast`);
    } catch (err) {
      console.warn(`[Demo] fase "${phase.id}" mislukt: ${err.message}`);
    }
  }

  async _apply(a) {
    switch (a.type) {
      case 'fade':
        return this.esp.fade({ from: a.from, to: a.to, durationMs: a.durationSec * 1000 });
      case 'pulse':
        return this.esp.pulse({ min: a.min, max: a.max, periodMs: a.periodSec * 1000 });
      case 'set':
        return this.esp.setBrightness(a.brightness);
      case 'off':
        return this.esp.turnOff();
      default:
        throw new Error(`Onbekend actietype: ${a.type}`);
    }
  }

  _report(label, secondsLeft) {
    this.onUpdate({
      running: this.running,
      label: label !== undefined ? label : (this._currentPhase ? (this._currentPhase.label || this._currentPhase.id) : ''),
      secondsLeft: secondsLeft !== undefined ? secondsLeft : Math.max(0, Math.ceil(C.DEMO_TOTAL_SECONDS - (Date.now() - this._startedAt) / 1000)),
    });
  }
}