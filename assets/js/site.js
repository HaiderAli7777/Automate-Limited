/* Automate Limited: public site behaviour. Every block checks that its elements exist,
   so the same file serves the homepage, service pages, careers and simple pages. */
(function () {
  "use strict";
  window.AUTOMATE_READY = true;

  var root = document.documentElement;
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var finePointer = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
  var page = document.body.getAttribute("data-page") || "";
  var NAV_H = 68;

  function clamp(v, a, b) { return v < a ? a : v > b ? b : v; }
  function map(v, a, b) { return clamp((v - a) / (b - a), 0, 1); }
  function easeOut(t) { return 1 - Math.pow(1 - t, 3); }
  function easeInOut(t) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2; }
  function $(id) { return document.getElementById(id); }
  function all(sel, ctx) { return [].slice.call((ctx || document).querySelectorAll(sel)); }

  /* ------------------------------------------------------------ theme */
  var themeBtn = $("themeBtn");
  function syncThemeButton() {
    var dark = root.getAttribute("data-theme") === "dark";
    if (themeBtn) themeBtn.setAttribute("aria-pressed", dark ? "true" : "false");
    var meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute("content", dark ? "#08131F" : "#F4F7FB");
  }
  if (themeBtn) {
    themeBtn.addEventListener("click", function () {
      var dark = root.getAttribute("data-theme") !== "dark";
      if (dark) root.setAttribute("data-theme", "dark"); else root.removeAttribute("data-theme");
      try { localStorage.setItem("automate-theme", dark ? "dark" : "light"); } catch (e) {}
      syncThemeButton();
    });
  }
  syncThemeButton();

  /* ------------------------------------------------------------ header */
  var hdr = $("hdr"), sentinel = $("topSentinel");
  if (hdr && sentinel && "IntersectionObserver" in window) {
    new IntersectionObserver(function (entries) {
      hdr.classList.toggle("is-stuck", !entries[0].isIntersecting);
    }).observe(sentinel);
  } else if (hdr) {
    hdr.classList.add("is-stuck");
  }

  /* ------------------------------------------------------------ mobile menu */
  var menu = $("menu"), menuBtn = $("menuBtn"), mainEl = $("main"), footEl = $("ftr");
  function menuOpen() { return !!(menu && menu.classList.contains("is-open")); }
  function setMenu(open, refocus) {
    if (!menu || !menuBtn) return;
    menu.classList.toggle("is-open", open);
    menu.setAttribute("aria-hidden", open ? "false" : "true");
    menuBtn.setAttribute("aria-expanded", open ? "true" : "false");
    menuBtn.firstElementChild.textContent = open ? "Close" : "Menu";
    document.body.classList.toggle("is-locked", open);
    if (mainEl) mainEl.inert = open;
    if (footEl) footEl.inert = open;
    if (open) { var first = menu.querySelector("a"); if (first) first.focus(); }
    else if (refocus) menuBtn.focus();
  }
  if (menuBtn) menuBtn.addEventListener("click", function () { setMenu(!menuOpen(), true); });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && menuOpen()) setMenu(false, true);
  });
  if (menu) {
    menu.addEventListener("click", function (e) {
      var a = e.target.closest ? e.target.closest("a") : null;
      if (a && a.getAttribute("href").charAt(0) !== "#") setMenu(false, false);
    });
  }

  /* ------------------------------------------------------------ services dropdown
     Opens on hover with a mouse, and on the caret button for keyboard and touch. */
  all("[data-dd]").forEach(function (dd) {
    var btn = dd.querySelector(".dd__toggle"), closeTimer = null;
    function set(open) {
      clearTimeout(closeTimer);
      dd.classList.toggle("is-open", open);
      if (btn) btn.setAttribute("aria-expanded", open ? "true" : "false");
    }
    if (btn) btn.addEventListener("click", function () { set(!dd.classList.contains("is-open")); });
    if (finePointer) {
      dd.addEventListener("mouseenter", function () { set(true); });
      dd.addEventListener("mouseleave", function () { closeTimer = setTimeout(function () { set(false); }, 160); });
    }
    dd.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && dd.classList.contains("is-open")) { set(false); if (btn) btn.focus(); }
    });
    dd.addEventListener("focusout", function (e) { if (!dd.contains(e.relatedTarget)) set(false); });
    document.addEventListener("click", function (e) { if (!dd.contains(e.target)) set(false); });
  });

  /* ------------------------------------------------------------ services explorer */
  var tabs = all('.svx [role="tab"]');
  var panels = tabs.map(function (t) { return $(t.getAttribute("aria-controls")); });
  function selectTab(i, focus) {
    tabs.forEach(function (t, j) {
      var on = j === i;
      t.setAttribute("aria-selected", on ? "true" : "false");
      t.tabIndex = on ? 0 : -1;
      panels[j].classList.toggle("is-active", on);
      panels[j].inert = !on;
    });
    if (focus) {
      tabs[i].focus({ preventScroll: true });
      tabs[i].scrollIntoView({ block: "nearest", inline: "nearest" });
    }
  }
  tabs.forEach(function (tab, i) {
    tab.addEventListener("click", function () { selectTab(i, false); });
    tab.addEventListener("keydown", function (e) {
      var n = tabs.length, j = null;
      if (e.key === "ArrowRight" || e.key === "ArrowDown") j = (i + 1) % n;
      else if (e.key === "ArrowLeft" || e.key === "ArrowUp") j = (i - 1 + n) % n;
      else if (e.key === "Home") j = 0;
      else if (e.key === "End") j = n - 1;
      if (j !== null) { e.preventDefault(); selectTab(j, true); }
    });
  });
  if (tabs.length) {
    var fromHash = panels.map(function (p) { return "#" + p.id; }).indexOf(location.hash);
    selectTab(fromHash > -1 ? fromHash : 0, false);
  }

  /* ------------------------------------------------------------ today vs one system */
  var morph = $("morph"), whyCap = $("whyCap");
  if (morph && whyCap) {
    var segButtons = all(".seg button");
    var CAPTIONS = {
      today: "Every area keeps its own record, so someone reconciles them by hand, usually at month end.",
      after: "The same six areas write to one system. The figures agree because there is only one set of them."
    };
    var morphTouched = false;
    var setMorph = function (state) {
      morph.setAttribute("data-state", state);
      segButtons.forEach(function (b) { b.setAttribute("aria-pressed", b.getAttribute("data-state") === state ? "true" : "false"); });
      whyCap.textContent = CAPTIONS[state];
    };
    segButtons.forEach(function (b) {
      b.addEventListener("click", function () { morphTouched = true; setMorph(b.getAttribute("data-state")); });
    });
    if (!reduce && "IntersectionObserver" in window) {
      var morphIO = new IntersectionObserver(function (entries) {
        if (!entries[0].isIntersecting) return;
        morphIO.disconnect();
        setTimeout(function () { if (!morphTouched) setMorph("after"); }, 700);
      }, { threshold: 0.55 });
      morphIO.observe(morph);
    }
  }

  /* ------------------------------------------------------------ eased wheel scrolling
     Homepage, desktop pointers only. The document genuinely scrolls, so sticky
     positioning, find-in-page and the scrollbar all behave. Touch keeps native
     momentum, and anything that scrolls on its own (a textarea, a select) is left alone. */
  var smooth = page === "home" && finePointer && !reduce;
  var sTarget = window.scrollY, sRunning = false, sWritten = null;
  function sMax() { return Math.max(0, root.scrollHeight - window.innerHeight); }
  function sStep() {
    if (sWritten !== null && Math.abs(window.scrollY - sWritten) > 3) { sRunning = false; sWritten = null; return; }
    var d = sTarget - window.scrollY;
    if (Math.abs(d) < 1) { window.scrollTo(0, sTarget); sRunning = false; sWritten = null; return; }
    /* browsers round scroll positions, so a sub-pixel step would never land and
       the loop would spin forever just short of the target */
    var step = d * 0.12;
    if (Math.abs(step) < 1) step = d > 0 ? 1 : -1;
    sWritten = window.scrollY + step;
    window.scrollTo(0, sWritten);
    requestAnimationFrame(sStep);
  }
  function glideTo(y) {
    sTarget = clamp(y, 0, sMax());
    if (!sRunning) { sRunning = true; sWritten = null; requestAnimationFrame(sStep); }
  }
  if (smooth) {
    root.style.scrollBehavior = "auto";
    window.addEventListener("wheel", function (e) {
      if (e.ctrlKey || e.defaultPrevented || menuOpen()) return;
      if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;
      if (e.target.closest && e.target.closest("textarea, select, input, [data-native-scroll]")) return;
      e.preventDefault();
      if (!sRunning) sTarget = window.scrollY;
      var unit = e.deltaMode === 1 ? 32 : e.deltaMode === 2 ? window.innerHeight : 1;
      glideTo(sTarget + e.deltaY * unit);
    }, { passive: false });
  }

  /* in-page links: service links open their tab, everything clears the fixed bar */
  document.addEventListener("click", function (e) {
    var a = e.target.closest ? e.target.closest('a[href^="#"]') : null;
    if (!a) return;
    var id = a.getAttribute("href").slice(1);
    if (!id) return;
    var target = $(id);
    if (!target) return;
    e.preventDefault();
    if (menuOpen()) setMenu(false, false);
    var pi = panels.indexOf(target);
    if (pi > -1) { selectTab(pi, false); target = $("services"); }
    var top = id === "top" ? 0 : target.getBoundingClientRect().top + window.scrollY - (NAV_H + 12);
    if (smooth) glideTo(top);
    else window.scrollTo({ top: top, behavior: reduce ? "auto" : "smooth" });
    if (history.replaceState) history.replaceState(null, "", id === "top" ? location.pathname : "#" + id);
    if (id !== "top") {
      if (!target.hasAttribute("tabindex")) target.setAttribute("tabindex", "-1");
      target.focus({ preventScroll: true });
    }
  });

  /* ------------------------------------------------------------ scroll-linked pieces (homepage)
     One rAF loop, running only while the hero or the timeline is on screen. */
  var stage = $("top"), pin = stage ? stage.querySelector(".stage__pin") : null;
  var proc = $("proc");
  if (!(stage && pin)) { stage = null; pin = null; }
  if (stage || proc) {
    var heroCopy = $("heroCopy"), hub = $("hub"), lines = $("orbitLines");
    var chips = all(".chip");
    var panel = $("panel"), panelMedia = $("panelMedia"), panelCopy = $("panelCopy");
    var steps = proc ? all(".step", proc) : [];
    var motionStage = false;
    var geo = { top: 0, range: 1, inset: [0, 0, 0, 0], vec: [], dots: [] };
    var visible = { stage: false, proc: false }, running = false, lastY = -1, dirty = true;

    var measure = function () {
      motionStage = !!stage && !reduce && getComputedStyle(pin).position === "sticky";
      if (stage) {
        geo.top = stage.getBoundingClientRect().top + window.scrollY;
        geo.range = Math.max(1, stage.offsetHeight - window.innerHeight);
      }
      if (motionStage) {
        hub.style.transform = "";
        chips.forEach(function (c) { c.style.transform = ""; });
        var pr = pin.getBoundingClientRect(), hr = hub.getBoundingClientRect();
        geo.inset = [hr.top - pr.top, pr.right - hr.right, pr.bottom - hr.bottom, hr.left - pr.left];
        var hx = hr.left + hr.width / 2, hy = hr.top + hr.height / 2;
        geo.vec = chips.map(function (c) {
          var r = c.getBoundingClientRect();
          return { x: hx - (r.left + r.width / 2), y: hy - (r.top + r.height / 2) };
        });
      }
      var vertical = window.matchMedia("(max-width: 799px)").matches;
      var ph = proc ? proc.offsetHeight : 1;
      geo.dots = steps.map(function (s, i) {
        return vertical ? (s.offsetTop + 20) / Math.max(1, ph) : i / Math.max(1, steps.length - 1);
      });
      geo.vertical = vertical;
      dirty = true;
    };

    var renderStage = function (y) {
      var p = clamp((y - geo.top) / geo.range, 0, 1);
      var c = map(p, 0, 0.24);
      heroCopy.style.opacity = (1 - c).toFixed(3);
      heroCopy.style.transform = "translate3d(0," + (-c * 44).toFixed(1) + "px,0)";
      heroCopy.style.visibility = c > 0.98 ? "hidden" : "visible";
      if (lines) lines.style.opacity = (1 - map(p, 0, 0.12)).toFixed(3);

      var k = easeInOut(map(p, 0, 0.32)), fade = 1 - map(p, 0.16, 0.32);
      chips.forEach(function (ch, i) {
        var v = geo.vec[i] || { x: 0, y: 0 };
        ch.style.transform = "translate3d(" + (v.x * k).toFixed(1) + "px," + (v.y * k).toFixed(1) + "px,0) scale(" + (1 - k * 0.5).toFixed(3) + ")";
        ch.style.opacity = fade.toFixed(3);
      });
      hub.style.transform = "scale(" + (1 + map(p, 0.2, 0.32) * 0.06).toFixed(3) + ")";
      hub.style.opacity = (1 - map(p, 0.33, 0.37)).toFixed(3);

      panel.style.opacity = map(p, 0.28, 0.33).toFixed(3);
      var ee = easeOut(map(p, 0.3, 0.74)), s = 1 - ee;
      panel.style.clipPath = "inset(" + (geo.inset[0] * s).toFixed(1) + "px " + (geo.inset[1] * s).toFixed(1) + "px " +
        (geo.inset[2] * s).toFixed(1) + "px " + (geo.inset[3] * s).toFixed(1) + "px round " + (16 * s).toFixed(1) + "px)";
      panelMedia.style.transform = "scale(" + (1.2 - 0.2 * ee).toFixed(4) + ")";
      var q = easeOut(map(p, 0.64, 0.9));
      panelCopy.style.opacity = q.toFixed(3);
      panelCopy.style.transform = "translate3d(0," + ((1 - q) * 34).toFixed(1) + "px,0)";
      panelCopy.style.visibility = q < 0.02 ? "hidden" : "visible";
    };

    var renderProc = function () {
      var r = proc.getBoundingClientRect(), vh = window.innerHeight, p;
      if (geo.vertical) p = clamp((vh * 0.72 - r.top) / r.height, 0, 1);
      else p = clamp((vh * 0.8 - r.top) / (vh * 0.42), 0, 1);
      proc.style.setProperty("--p", p.toFixed(4));
      steps.forEach(function (s, i) { s.classList.toggle("is-lit", p >= geo.dots[i] - 0.002 && p > 0); });
    };

    var tick = function () {
      if (!visible.stage && !visible.proc) { running = false; return; }
      var y = window.scrollY;
      if (y !== lastY || dirty) {
        if (visible.stage && motionStage) renderStage(y);
        if (visible.proc && !reduce) renderProc();
        lastY = y; dirty = false;
      }
      requestAnimationFrame(tick);
    };
    var wake = function () { if (!running) { running = true; requestAnimationFrame(tick); } };

    measure();
    if (reduce) steps.forEach(function (s) { s.classList.add("is-lit"); });
    if ("IntersectionObserver" in window) {
      if (stage) new IntersectionObserver(function (en) { visible.stage = en[0].isIntersecting; dirty = true; wake(); }).observe(stage);
      if (proc) new IntersectionObserver(function (en) { visible.proc = en[0].isIntersecting; dirty = true; wake(); }).observe(proc);
    }
    var resizeTimer;
    window.addEventListener("resize", function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () { measure(); wake(); }, 120);
    }, { passive: true });
    window.addEventListener("load", function () { measure(); wake(); });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { measure(); wake(); });
  }

  /* ------------------------------------------------------------ reveal on scroll */
  var reveals = all(".rv");
  if (reveals.length) {
    if (reduce || !("IntersectionObserver" in window)) {
      reveals.forEach(function (el) { el.classList.add("is-in"); });
    } else {
      var rio = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { en.target.classList.add("is-in"); rio.unobserve(en.target); }
        });
      }, { rootMargin: "0px 0px -8% 0px", threshold: 0.08 });
      reveals.forEach(function (el) { rio.observe(el); });
    }
  }

  /* ------------------------------------------------------------ campaign attribution
     Remember how the visitor arrived (UTM tags, referrer, landing page) for the
     length of the visit, and pass it along with the enquiry form. */
  var ATTR_KEY = "automate-attr";
  var attr = null;
  try { attr = JSON.parse(sessionStorage.getItem(ATTR_KEY) || "null"); } catch (e) { attr = null; }
  if (!attr) {
    var params = new URLSearchParams(location.search);
    attr = {
      utm_source: params.get("utm_source") || "",
      utm_medium: params.get("utm_medium") || "",
      utm_campaign: params.get("utm_campaign") || "",
      landing: location.pathname + location.search,
      referrer: document.referrer && document.referrer.indexOf(location.host) === -1 ? document.referrer : ""
    };
    try { sessionStorage.setItem(ATTR_KEY, JSON.stringify(attr)); } catch (e) {}
  }

  /* ------------------------------------------------------------ enquiry form (submits in place) */
  all("form[data-ajax]").forEach(function (form) {
    Object.keys(attr).forEach(function (k) {
      var input = form.querySelector('input[name="' + k + '"]');
      if (input && !input.value) input.value = attr[k];
    });
    var status = form.querySelector(".status");
    var btn = form.querySelector('button[type="submit"]');
    var label = btn ? btn.textContent : "";

    function clearErrors() {
      all(".fld.has-error", form).forEach(function (f) { f.classList.remove("has-error"); });
      all(".fld__err", form).forEach(function (el) { el.remove(); });
      all("[aria-invalid]", form).forEach(function (el) { el.removeAttribute("aria-invalid"); el.removeAttribute("aria-describedby"); });
    }
    function showErrors(errors) {
      var first = null;
      Object.keys(errors || {}).forEach(function (k) {
        var fld = form.querySelector('[data-field="' + k + '"]');
        if (!fld) return;
        var control = fld.querySelector("input, textarea, select");
        var p = document.createElement("p");
        p.className = "fld__err";
        p.id = "err-" + k;
        p.textContent = errors[k];
        fld.classList.add("has-error");
        fld.appendChild(p);
        if (control) {
          control.setAttribute("aria-invalid", "true");
          control.setAttribute("aria-describedby", p.id);
          if (!first) first = control;
        }
      });
      if (first) first.focus();
    }
    function setStatus(kind, text) {
      if (!status) return;
      status.className = "status" + (kind ? " is-" + kind : "");
      status.textContent = text || "";
    }

    form.addEventListener("submit", function (e) {
      if (!window.fetch || !window.FormData) return;
      e.preventDefault();
      clearErrors();
      var local = {};
      var name = form.querySelector('[name="name"]'), email = form.querySelector('[name="email"]'), msg = form.querySelector('[name="message"]');
      if (name && !name.value.trim()) local.name = "Please tell us your name.";
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) local.email = "Please enter an email address we can reply to.";
      if (msg && msg.value.trim().length < 5) local.message = "Please tell us a little about what you need.";
      if (Object.keys(local).length) { setStatus("", ""); showErrors(local); return; }

      if (btn) { btn.disabled = true; btn.textContent = (btn.getAttribute("data-busy") || "Sending") + "…"; }
      setStatus("", "");
      fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin"
      }).then(function (r) {
        return r.json().catch(function () { return { ok: false, error: "Something went wrong. Please email info@automateltd.com." }; });
      }).then(function (data) {
        if (data && data.ok) {
          form.reset();
          Object.keys(attr).forEach(function (k) {
            var input = form.querySelector('input[name="' + k + '"]');
            if (input) input.value = attr[k];
          });
          setStatus("ok", data.message);
          status.setAttribute("tabindex", "-1");
          status.focus();
        } else {
          setStatus("bad", (data && data.error) || "Please check the highlighted fields.");
          showErrors(data && data.errors);
        }
      }).catch(function () {
        setStatus("bad", "We couldn't send that. Check your connection and try again, or email info@automateltd.com.");
      }).then(function () {
        if (btn) { btn.disabled = false; btn.textContent = label; }
      });
    });
  });

  /* ------------------------------------------------------------ enquiry form: the Odoo module field */
  all("[data-service-select]").forEach(function (sel) {
    var form = sel.closest("form"), fld = form ? form.querySelector("[data-module-field]") : null;
    if (!fld) return;
    var sync = function () {
      var on = sel.value === "Odoo ERP";
      fld.hidden = !on;
      fld.parentNode.classList.toggle("form__row--one", !on);
    };
    sel.addEventListener("change", sync);
    sync();
  });

  /* ------------------------------------------------------------ application form */
  all("form[data-apply]").forEach(function (form) {
    form.addEventListener("submit", function () {
      var btn = form.querySelector('button[type="submit"]');
      if (btn) {
        setTimeout(function () { btn.disabled = true; btn.textContent = (btn.getAttribute("data-busy") || "Sending") + "…"; }, 0);
      }
    });
  });
  var errsum = $("errsum");
  if (errsum) errsum.focus();

  all("[data-drop]").forEach(function (drop) {
    var input = drop.querySelector('input[type="file"]');
    var name = drop.querySelector("[data-drop-name]");
    if (!input || !name) return;
    var initial = name.textContent;
    function update() {
      var f = input.files && input.files[0];
      name.textContent = f ? f.name : initial;
      drop.classList.toggle("has-file", !!f);
    }
    input.addEventListener("change", update);
    ["dragenter", "dragover"].forEach(function (t) {
      drop.addEventListener(t, function () { drop.classList.add("is-over"); });
    });
    ["dragleave", "drop"].forEach(function (t) {
      drop.addEventListener(t, function () { drop.classList.remove("is-over"); setTimeout(update, 0); });
    });
  });

  /* ------------------------------------------------------------ careers filters */
  var filterControls = all("[data-job-filter]");
  if (filterControls.length) {
    var rows = all(".jrow");
    var empty = $("jobEmpty"), count = $("jobCount");
    var applyFilters = function () {
      var f = {};
      filterControls.forEach(function (c) { f[c.getAttribute("data-job-filter")] = c.value.trim().toLowerCase(); });
      var shown = 0;
      rows.forEach(function (row) {
        var ok = (!f.q || row.getAttribute("data-q").indexOf(f.q) > -1) &&
          (!f.department || row.getAttribute("data-department").toLowerCase() === f.department) &&
          (!f.location || row.getAttribute("data-location").toLowerCase() === f.location) &&
          (!f.type || row.getAttribute("data-type") === f.type);
        row.hidden = !ok;
        if (ok) shown++;
      });
      if (empty) empty.hidden = shown > 0;
      if (count) count.textContent = shown + (shown === 1 ? " open role" : " open roles");
    };
    filterControls.forEach(function (c) { c.addEventListener("input", applyFilters); c.addEventListener("change", applyFilters); });
  }

  /* ------------------------------------------------------------ copy link */
  all("[data-copy]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var text = btn.getAttribute("data-copy");
      var label = btn.querySelector("span");
      var done = function () {
        if (!label) return;
        var old = label.textContent;
        label.textContent = "Link copied";
        setTimeout(function () { label.textContent = old; }, 2000);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(text).then(done, function () {});
      else { window.prompt("Copy this link", text); }
    });
  });

  var yr = $("yr");
  if (yr) yr.textContent = String(new Date().getFullYear());
})();
