// ============================================================
//  ESP HANDLER
//  Praat met de ESP32 en biedt simpele functies voor de lamp:
//    turnOn(), turnOff(), setBrightness(%), flash(),
//    fade({from, to, durationMs}), pulse({min, max, periodMs})
//  Houdt ook in de gaten of de ESP32 bereikbaar is.
// ============================================================

import {
  ESP_HOST,
  ESP_LED_PIN,
  ESP_PING_INTERVAL_MS,
  ESP_REQUEST_TIMEOUT_MS,
  ESP_MAX_MISSED_PINGS,
} from './config.js';

const clamp = (v, lo, hi) => Math.min(hi, Math.max(lo, v));
const pct = (v) => Math.round(clamp(v, 0, 100) * 10) / 10;   // 1 decimaal

export class EspHandler {
  constructor({ host = ESP_HOST, pin = ESP_LED_PIN } = {}) {
    this.host = host.replace(/\/+$/, '');
    this.pin = pin;

    this.online = false;
    this.brightness = null;   // laatst bekende helderheid (%)
    this.lastError = '';

    this._missed = 0;
    this._lastUptime = null;
    this._timer = null;
    this._listeners = { status: [], connected: [] };
  }

  // ---------- events ----------
  // 'status'    -> online/offline of helderheid veranderd
  // 'connected' -> ESP32 is (weer) bereikbaar, of is opnieuw opgestart
  subscribe(event, callback) {
    if (!this._listeners[event]) this._listeners[event] = [];
    this._listeners[event].push(callback);
  }

  _emit(event) {
    (this._listeners[event] || []).forEach((cb) => {
      try { cb(this); } catch (e) { console.error(e); }
    });
  }

  // ---------- lamp-functies ----------
  turnOn()                { return this._led({ state: 'on' }); }
  turnOff()               { return this._led({ state: 'off' }); }
  flash()                 { return this._led({ state: 'flash' }); }
  setBrightness(percent)  { return this._led({ brightness: Math.round(clamp(percent, 0, 100)) }); }

  // Geleidelijk van 'from' naar 'to' (%) in durationMs. De ESP32 voert dit zelf uit.
  fade({ from, to, durationMs }) {
    return this._led({
      effect: 'fade',
      from: pct(from),
      to: pct(to),
      duration: Math.max(1, Math.round(durationMs)),
    });
  }

  // Zacht ademen tussen min en max (%), één cyclus per periodMs.
  pulse({ min, max, periodMs }) {
    return this._led({
      effect: 'pulse',
      min: pct(min),
      max: pct(max),
      period: Math.max(200, Math.round(periodMs)),
    });
  }

  // ---------- verbinding bewaken ----------
  start() {
    this._beat();
    this._timer = setInterval(() => this._beat(), ESP_PING_INTERVAL_MS);
  }

  stop() {
    clearInterval(this._timer);
    this._timer = null;
  }

  async _beat() {
    try {
      const data = await this._request('/ping');
      const i = data.pins.indexOf(this.pin);
      if (i === -1) throw new Error(`GPIO ${this.pin} staat niet in ledPins[] van de ESP32`);

      const wasOnline = this.online;
      const rebooted = this._lastUptime !== null && data.uptime < this._lastUptime;

      this._lastUptime = data.uptime;
      this._missed = 0;
      this.online = true;
      this.lastError = '';
      this.brightness = data.brightness[i];

      this._emit('status');
      if (!wasOnline || rebooted) this._emit('connected');
    } catch (err) {
      this._missed++;
      this.lastError = err.name === 'AbortError' ? 'timeout' : err.message;

      if (this._missed >= ESP_MAX_MISSED_PINGS && this.online) {
        this.online = false;
        this._emit('status');
      }
    }
  }

  // ---------- intern ----------
  async _led(params) {
    const data = await this._request('/api/led', { pin: this.pin, ...params });
    if (typeof data.brightness === 'number') {
      this.brightness = data.brightness;
      this._emit('status');
    }
    return data;
  }

  async _request(path, query = {}) {
    const qs = new URLSearchParams(query).toString();
    const url = `${this.host}${path}${qs ? '?' + qs : ''}`;

    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), ESP_REQUEST_TIMEOUT_MS);

    try {
      const res = await fetch(url, { signal: controller.signal, cache: 'no-store' });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return await res.json();
    } finally {
      clearTimeout(timer);
    }
  }
}