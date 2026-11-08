/* Corner Bites — flicker-free refresh helper (SWR: keep stale, update silently).
 * Load once in admin/index.html before wpos.js.
 * - First paint: skeleton shimmer (no blank flash, no layout shift)
 * - Refreshes: keep old rows, only write DOM when the payload hash changes
 * - Polling: one scheduler per widget, pauses when tab hidden, aborts in-flight,
 *   backs off on error, cleaned up on SPA navigation (loadPageContent).
 */
(function (global) {
    "use strict";
    if (global.CBRefresh) return;

    // --- Skeleton CSS (first paint only) ---
    try {
        var st = document.createElement("style");
        st.id = "cb-skel-style";
        st.textContent = [
            ".cb-skel-row{height:18px;border-radius:4px;background:linear-gradient(90deg,#eee 25%,#f7f7f7 50%,#eee 75%);background-size:200% 100%;animation:cbshim 1.2s infinite;}",
            "@keyframes cbshim{0%{background-position:200% 0}100%{background-position:-200% 0}}",
            "@media (prefers-reduced-motion:reduce){.cb-skel-row{animation:none}}"
        ].join("\n");
        (document.head || document.getElementsByTagName("head")[0] || document.documentElement).appendChild(st);
    } catch (e) {}

    function skeletonRows(cols, n) {
        var out = "";
        for (var i = 0; i < (n || 5); i++) {
            out += '<tr><td colspan="' + cols + '"><div class="cb-skel-row"></div></td></tr>';
        }
        return out;
    }

    // --- Poll registry: every dashboard interval registers here, cleared on nav ---
    var registry = [];
    function registerPoll(id) { if (id) registry.push(id); }
    function clearAllPolls() {
        registry.forEach(function (api) {
            try { if (api && typeof api.stop === 'function') api.stop(); else clearInterval(api); } catch (e) {}
        });
        registry.length = 0;
    }
    global.CBClearPolls = clearAllPolls;

    // --- Visibility-aware scheduler (pauses in background, backs off on error) ---
    function schedule(fn, baseMs, opts) {
        opts = opts || {};
        var alive = true, consecutiveFails = 0, inflight = null, timer = null;
        function run() {
            if (!alive) return;
            if (document.hidden) { scheduleNext(baseMs); return; } // skip work while hidden
            if (inflight) { scheduleNext(2000); return; }
            inflight = fn(function ok() {
                inflight = null; consecutiveFails = 0; scheduleNext(baseMs);
            }, function fail() {
                inflight = null; consecutiveFails++;
                // exponential backoff: base * 2^n capped at 60s
                var back = Math.min(60000, baseMs * Math.pow(2, Math.min(consecutiveFails, 4)));
                scheduleNext(back);
            });
        }
        function scheduleNext(ms) {
            clearTimeout(timer);
            timer = setTimeout(run, ms);
        }
        // visible again → nudge (don’t wait a full interval)
        function onVis() { if (!document.hidden && alive) run(); }
        document.addEventListener("visibilitychange", onVis);
        var api = {
            stop: function () {
                alive = false;
                clearTimeout(timer);
                document.removeEventListener("visibilitychange", onVis);
                try { if (inflight && inflight.abort) inflight.abort(); } catch (e) {}
            }
        };
        registerPoll(api);
        return api;
    }

    // --- Signature-diffed render: skip DOM write when nothing changed; preserve scroll ---
    function patchTable(tbodySel, containerSel, html) {
        var tbody = document.querySelector(tbodySel);
        if (!tbody) return false;
        var sig = html.length + ":" + hashStr(html);
        if (tbody.getAttribute("data-cb-sig") === sig) return false; // unchanged → no flash
        var scroller = containerSel ? document.querySelector(containerSel) : null;
        var st = scroller ? scroller.scrollTop : 0;
        tbody.setAttribute("data-cb-sig", sig);
        // Write inside rAF so paint is one frame, not two
        try {
            requestAnimationFrame(function () {
                tbody.innerHTML = html;
                if (scroller && scroller.scrollTop !== st) scroller.scrollTop = st;
                tbody.setAttribute("aria-busy", "false");
            });
        } catch (e) { tbody.innerHTML = html; }
        return true;
    }
    function hashStr(s) {
        var h = 0;
        for (var i = 0; i < s.length; i++) { h = (h * 31 + s.charCodeAt(i)) | 0; }
        return String(h);
    }

    global.CBRefresh = { schedule: schedule, patchTable: patchTable, skeletonRows: skeletonRows, clearAll: clearAllPolls };
})(window);
