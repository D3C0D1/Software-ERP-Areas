/**
 * Banner ERP – Sistema de Sonidos Elegantes v2
 * Web Audio API pura. Sin dependencias externas.
 * Preferencias guardadas en localStorage: bannerSoundEnabled, bannerSoundTheme
 */
(function (global) {
    'use strict';

    /* ─── Contexto de audio ────────────────────────────────────────── */
    var _ctx = null;
    function ctx() {
        if (!_ctx) {
            try { _ctx = new (window.AudioContext || window.webkitAudioContext)(); }
            catch (e) { return null; }
        }
        if (_ctx.state === 'suspended') _ctx.resume();
        return _ctx;
    }

    /* ─── Constructor de nota ──────────────────────────────────────── */
    function nota(c, freq, start, dur, vol, type, fadeIn, fadeOut) {
        var o = c.createOscillator();
        var g = c.createGain();
        o.connect(g); g.connect(c.destination);
        o.type = type || 'sine';
        o.frequency.setValueAtTime(freq, c.currentTime + start);
        var v0 = 0.0001;
        g.gain.setValueAtTime(v0, c.currentTime + start);
        g.gain.exponentialRampToValueAtTime(vol, c.currentTime + start + (fadeIn || 0.018));
        g.gain.exponentialRampToValueAtTime(v0, c.currentTime + start + dur - (fadeOut || 0.01));
        o.start(c.currentTime + start);
        o.stop(c.currentTime + start + dur + 0.05);
    }

    /* ─── Reverb ligero (impulso de ruido corto) ───────────────────── */
    function mkReverb(c, dur, wet) {
        var rate = c.sampleRate;
        var len = rate * dur;
        var buf = c.createBuffer(2, len, rate);
        for (var ch = 0; ch < 2; ch++) {
            var d = buf.getChannelData(ch);
            for (var i = 0; i < len; i++)
                d[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / len, 2.5);
        }
        var conv = c.createConvolver();
        conv.buffer = buf;
        var gIn = c.createGain(); gIn.gain.value = 1 - wet;
        var gWet = c.createGain(); gWet.gain.value = wet;
        return { input: gIn, reverb: conv, wetGain: gWet };
    }

    /* ─── Definición de los 5 temas ───────────────────────────────── */
    var TEMAS = {

        /* 1 · Cristal — campanas de vidrio muy suaves */
        cristal: {
            label: '🔮 Cristal',
            desc: 'Campanas de vidrio, suave y etéreo',
            color: '#a5b4fc',
            login: function (c) { nota(c, 1046.5, 0, .18, .10, 'sine', .015, .06); nota(c, 1318.5, .14, .22, .08, 'sine', .015, .08); nota(c, 1568, .28, .30, .07, 'sine', .015, .10); nota(c, 2093, .42, .40, .05, 'sine', .015, .14); },
            crear: function (c) { nota(c, 880, 0, .12, .09, 'sine', .012, .05); nota(c, 1108, .10, .16, .07, 'sine', .012, .06); nota(c, 1397, .21, .24, .06, 'sine', .012, .09); },
            mover: function (c) { nota(c, 659, 0, .10, .08, 'sine', .010, .05); nota(c, 880, .09, .14, .07, 'sine', .010, .06); },
            finalizar: function (c) { nota(c, 783.9, 0, .12, .08, 'sine', .012, .05); nota(c, 1046, .10, .12, .08, 'sine', .012, .05); nota(c, 1318, .20, .18, .07, 'sine', .012, .07); nota(c, 2093, .36, .40, .06, 'sine', .015, .14); },
            eliminar: function (c) { nota(c, 415, 0, .16, .09, 'sine', .015, .08); nota(c, 349, .18, .20, .07, 'sine', .015, .10); }
        },

        /* 2 · Bambú — teclado marimba cálido */
        bambu: {
            label: '🎋 Bambú',
            desc: 'Marimba cálida, suave percusión',
            color: '#6ee7b7',
            login: function (c) { nota(c, 523.2, 0, .10, .12, 'triangle', .008, .05); nota(c, 659.2, .09, .12, .11, 'triangle', .008, .05); nota(c, 784, .18, .14, .10, 'triangle', .008, .06); nota(c, 1047, .28, .22, .09, 'triangle', .010, .09); },
            crear: function (c) { nota(c, 880, 0, .09, .11, 'triangle', .008, .04); nota(c, 1047, .08, .12, .09, 'triangle', .008, .05); nota(c, 1319, .17, .18, .08, 'triangle', .008, .07); },
            mover: function (c) { nota(c, 698, 0, .08, .10, 'triangle', .007, .04); nota(c, 880, .08, .12, .09, 'triangle', .007, .05); },
            finalizar: function (c) { nota(c, 698, 0, .09, .11, 'triangle', .008, .04); nota(c, 880, .08, .09, .10, 'triangle', .008, .04); nota(c, 1047, .16, .09, .10, 'triangle', .008, .04); nota(c, 1397, .24, .30, .09, 'triangle', .010, .12); },
            eliminar: function (c) { nota(c, 392, 0, .14, .11, 'triangle', .010, .07); nota(c, 349, .16, .18, .09, 'triangle', .010, .08); }
        },

        /* 3 · Jazz — notas suaves de piano redondo */
        jazz: {
            label: '🎷 Jazz',
            desc: 'Piano suave, notas redondeadas',
            color: '#fbbf24',
            login: function (c) { nota(c, 523.2, 0, .18, .11, 'sine', .020, .09); nota(c, 622.2, .15, .18, .10, 'sine', .020, .09); nota(c, 740, .28, .20, .09, 'sine', .020, .10); nota(c, 987.7, .42, .35, .08, 'sine', .022, .14); },
            crear: function (c) { nota(c, 740, 0, .14, .10, 'sine', .018, .07); nota(c, 987.7, .12, .18, .09, 'sine', .018, .08); },
            mover: function (c) { nota(c, 587.3, 0, .12, .10, 'sine', .018, .07); nota(c, 740, .10, .16, .09, 'sine', .018, .08); },
            finalizar: function (c) { nota(c, 587.3, 0, .10, .10, 'sine', .018, .05); nota(c, 740, .09, .10, .10, 'sine', .018, .05); nota(c, 880, .18, .10, .10, 'sine', .018, .05); nota(c, 1174, .27, .34, .09, 'sine', .020, .14); },
            eliminar: function (c) { nota(c, 370, 0, .18, .10, 'sine', .020, .09); nota(c, 311, .20, .22, .08, 'sine', .020, .10); }
        },

        /* 4 · Neo — synthwave suave y limpio */
        neo: {
            label: '🌐 Neo',
            desc: 'Synth electrónico suave',
            color: '#38bdf8',
            login: function (c) {
                var lfo = c.createOscillator(); var lfoG = c.createGain();
                lfo.frequency.value = 4; lfoG.gain.value = 6;
                lfo.connect(lfoG);
                var o = c.createOscillator(); var g = c.createGain();
                lfo.start(c.currentTime); lfo.stop(c.currentTime + .7);
                lfoG.connect(o.detune);
                o.type = 'sine'; o.frequency.value = 880;
                o.connect(g); g.connect(c.destination);
                g.gain.setValueAtTime(.0001, c.currentTime);
                g.gain.exponentialRampToValueAtTime(.09, c.currentTime + .04);
                g.gain.exponentialRampToValueAtTime(.0001, c.currentTime + .65);
                o.start(c.currentTime); o.stop(c.currentTime + .70);
                nota(c, 1320, .30, .30, .07, 'sine', .015, .12);
            },
            crear: function (c) { nota(c, 880, 0, .10, .09, 'sine', .014, .06); nota(c, 1108, .10, .14, .07, 'sine', .014, .08); },
            mover: function (c) {
                var o = c.createOscillator(); var g = c.createGain();
                o.type = 'sine'; o.connect(g); g.connect(c.destination);
                o.frequency.setValueAtTime(740, c.currentTime);
                o.frequency.linearRampToValueAtTime(440, c.currentTime + .28);
                g.gain.setValueAtTime(.0001, c.currentTime);
                g.gain.exponentialRampToValueAtTime(.09, c.currentTime + .02);
                g.gain.exponentialRampToValueAtTime(.0001, c.currentTime + .28);
                o.start(c.currentTime); o.stop(c.currentTime + .32);
            },
            finalizar: function (c) { nota(c, 659.2, 0, .10, .09, 'sine', .012, .05); nota(c, 880, .08, .10, .09, 'sine', .012, .05); nota(c, 1108, .16, .10, .09, 'sine', .012, .05); nota(c, 1320, .24, .38, .08, 'sine', .015, .16); },
            eliminar: function (c) {
                var o = c.createOscillator(); var g = c.createGain();
                o.type = 'sine'; o.connect(g); g.connect(c.destination);
                o.frequency.setValueAtTime(550, c.currentTime);
                o.frequency.linearRampToValueAtTime(300, c.currentTime + .30);
                g.gain.setValueAtTime(.0001, c.currentTime);
                g.gain.exponentialRampToValueAtTime(.10, c.currentTime + .02);
                g.gain.exponentialRampToValueAtTime(.0001, c.currentTime + .30);
                o.start(c.currentTime); o.stop(c.currentTime + .34);
            }
        },

        /* 5 · Minimal — apenas audible, discreto */
        minimal: {
            label: '🔇 Minimal',
            desc: 'Casi imperceptible, muy discreto',
            color: '#94a3b8',
            login: function (c) { nota(c, 880, 0, .22, .06, 'sine', .025, .15); nota(c, 1046, .20, .28, .05, 'sine', .025, .18); },
            crear: function (c) { nota(c, 1046.5, 0, .18, .06, 'sine', .020, .12); },
            mover: function (c) { nota(c, 784, 0, .14, .05, 'sine', .018, .10); },
            finalizar: function (c) { nota(c, 880, 0, .10, .05, 'sine', .020, .07); nota(c, 1046, .08, .26, .06, 'sine', .020, .18); },
            eliminar: function (c) { nota(c, 440, 0, .20, .06, 'sine', .020, .14); }
        }
    };

    /* ─── API pública ──────────────────────────────────────────────── */
    var BannerSounds = {

        _enabled: true,
        _theme: 'cristal',

        init: function () {
            // Prioridad: BD (inyectada por PHP via window.BANNER_SOUND_CFG) > localStorage
            if (window.BANNER_SOUND_CFG) {
                this._enabled = !!window.BANNER_SOUND_CFG.enabled;
                this._theme = window.BANNER_SOUND_CFG.theme || 'cristal';
                // Sincronizar localStorage con el valor de BD
                localStorage.setItem('bannerSoundEnabled', this._enabled ? '1' : '0');
                localStorage.setItem('bannerSoundTheme', this._theme);
            } else {
                this._enabled = localStorage.getItem('bannerSoundEnabled') !== '0';
                this._theme = localStorage.getItem('bannerSoundTheme') || 'cristal';
            }
        },

        setEnabled: function (v) {
            this._enabled = !!v;
            localStorage.setItem('bannerSoundEnabled', v ? '1' : '0');
        },

        setTheme: function (t) {
            if (TEMAS[t]) { this._theme = t; localStorage.setItem('bannerSoundTheme', t); }
        },

        getTemas: function () { return TEMAS; },

        _play: function (action) {
            if (!this._enabled) return;
            var c = ctx(); if (!c) return;
            var tema = TEMAS[this._theme] || TEMAS['cristal'];
            if (tema[action]) tema[action](c);
        },

        login: function () { this._play('login'); },
        crear: function () { this._play('crear'); },
        mover: function () { this._play('mover'); },
        finalizar: function () { this._play('finalizar'); },
        eliminar: function () { this._play('eliminar'); }
    };

    /* Auto-init al cargar */
    BannerSounds.init();
    global.BannerSounds = BannerSounds;

})(window);
