/**
 * Corner Bites — New order alert (soft ding).
 *
 * Shared by Admin dashboard (dashboard.php / websiteorders.php / realtime.php)
 * and Staff POS (Online Sales). Plays a short two-tone WebAudio ding when a
 * genuinely new order ref is seen, and shows a floating toast.
 *
 * Autoplay policy: the AudioContext is created lazily on the first user
 * gesture (login/Process click) via `initOrderSound()` / `unlockOrderSound()`.
 *
 * Usage:
 *   initOrderSound();                      // call on app init / login
 *   checkNewOrders(salesArray, 'Online Sales'); // call on every poll render
 *   setOrderSoundMuted(true/false);        // toggle
 */
(function (global) {
    "use strict";

    var audioCtx = null;
    var seenRefs = Object.create(null);
    var seeded = false;
    var MUTED_KEY = "cb_order_sound_muted";

    function isMuted() {
        try { return localStorage.getItem(MUTED_KEY) === "1"; } catch (e) { return false; }
    }

    function ensureCtx() {
        if (audioCtx) {
            if (audioCtx.state === "suspended") { try { audioCtx.resume(); } catch (e) {} }
            return audioCtx;
        }
        var AC = global.AudioContext || global.webkitAudioContext;
        if (!AC) return null;
        try {
            audioCtx = new AC();
            if (audioCtx.state === "suspended") { try { audioCtx.resume(); } catch (e) {} }
        } catch (e) { audioCtx = null; }
        return audioCtx;
    }

    function unlockOrderSound() { ensureCtx(); }
    global.unlockOrderSound = unlockOrderSound;

    function playOrderDing() {
        if (isMuted()) return;
        var ctx = ensureCtx();
        if (!ctx) return;
        try {
            var t0 = ctx.currentTime;
            // Soft two-tone ding: 880Hz then 1318Hz, quick decay, low gain.
            [
                { freq: 880, at: 0, dur: 0.16, gain: 0.06 },
                { freq: 1318, at: 0.14, dur: 0.28, gain: 0.05 }
            ].forEach(function (n) {
                var osc = ctx.createOscillator();
                var g = ctx.createGain();
                osc.type = "sine";
                osc.frequency.value = n.freq;
                g.gain.setValueAtTime(0, t0 + n.at);
                g.gain.linearRampToValueAtTime(n.gain, t0 + n.at + 0.02);
                g.gain.exponentialRampToValueAtTime(0.0001, t0 + n.at + n.dur);
                osc.connect(g); g.connect(ctx.destination);
                osc.start(t0 + n.at);
                osc.stop(t0 + n.at + n.dur + 0.02);
            });
        } catch (e) { /* never throw from an alert path */ }
    }
    global.playOrderDing = playOrderDing;

    function toastNewOrder(ref, source) {
        try {
            var el = document.getElementById("cb-order-toast");
            if (!el) {
                el = document.createElement("div");
                el.id = "cb-order-toast";
                el.style.cssText = "position:fixed;top:16px;right:16px;z-index:99999;background:#0a0a0a;color:#fff;border:2px solid #84cc16;border-radius:10px;padding:12px 16px;font-family:Arial,sans-serif;font-size:13px;font-weight:700;box-shadow:0 8px 30px rgba(0,0,0,.35);display:none;align-items:center;gap:10px;";
                document.body.appendChild(el);
                el.addEventListener("click", function () { el.style.display = "none"; });
            }
            el.innerHTML = "🔔 New " + (source || "website") + " order: <span style='color:#84cc16'>" + String(ref || "").replace(/</g, "") + "</span>";
            el.style.display = "flex";
            setTimeout(function () { el.style.display = "none"; }, 6000);
        } catch (e) {}
    }

    /**
     * @param {Array} sales - order objects with .ref (and optional .source)
     * @param {string} sourceLabel - e.g. 'website' (sound+toast on the first new ref)
     */
    function checkNewOrders(sales, sourceLabel) {
        if (!Array.isArray(sales) || !sales.length) return;
        var fresh = [];
        for (var i = 0; i < sales.length; i++) {
            var s = sales[i];
            if (!s || !s.ref) continue;
            var key = String(s.ref);
            if (!seenRefs[key]) { seenRefs[key] = 1; fresh.push(s); }
        }
        // First poll seeds silently so page load doesn't "ding" for old orders.
        if (!seeded) { seeded = true; return; }
        if (fresh.length) {
            playOrderDing();
            var ref = fresh[0].ref;
            // Prefer a website/external ref for the message when present.
            for (var j = 0; j < fresh.length; j++) {
                if (fresh[j].source === "website") { ref = fresh[j].ref; break; }
            }
            toastNewOrder(ref, sourceLabel || "website");
        }
    }
    global.checkNewOrders = checkNewOrders;

    function setOrderSoundMuted(m) {
        try { localStorage.setItem(MUTED_KEY, m ? "1" : "0"); } catch (e) {}
        updateMuteButtons();
    }
    global.setOrderSoundMuted = setOrderSoundMuted;
    global.isOrderSoundMuted = isMuted;

    function updateMuteButtons() {
        var els = document.querySelectorAll("[data-order-sound-toggle]");
        for (var i = 0; i < els.length; i++) {
            els[i].textContent = isMuted() ? "🔇 Sound: Off" : "🔊 Sound: On";
        }
    }

    function initOrderSound() {
        // Unlock on first interaction (login / any click) so WebAudio is allowed.
        var unlock = function () { unlockOrderSound(); };
        ["click", "touchstart", "keydown"].forEach(function (ev) {
            try { document.addEventListener(ev, unlock, { once: true, passive: true }); } catch (e) {
                document.addEventListener(ev, unlock);
            }
        });
        updateMuteButtons();
    }
    global.initOrderSound = initOrderSound;
})(typeof window !== "undefined" ? window : this);
