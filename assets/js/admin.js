/* Automate Limited: team area behaviour. Plain JS, no dependencies. */
(function () {
  "use strict";

  var root = document.documentElement;
  var csrf = document.body.getAttribute("data-csrf") || "";
  function all(sel, ctx) { return [].slice.call((ctx || document).querySelectorAll(sel)); }
  function $(id) { return document.getElementById(id); }

  /* ------------------------------------------------------------ toasts */
  function toast(message, kind) {
    var wrap = document.querySelector(".flashes");
    if (!wrap) {
      wrap = document.createElement("div");
      wrap.className = "flashes";
      wrap.setAttribute("role", "status");
      wrap.setAttribute("aria-live", "polite");
      document.body.appendChild(wrap);
    }
    var el = document.createElement("div");
    el.className = "flash flash--" + (kind || "info");
    var span = document.createElement("span");
    span.textContent = message;
    var btn = document.createElement("button");
    btn.type = "button";
    btn.setAttribute("aria-label", "Dismiss");
    btn.textContent = "×";
    btn.addEventListener("click", function () { el.remove(); });
    el.appendChild(span);
    el.appendChild(btn);
    wrap.appendChild(el);
    setTimeout(function () { el.remove(); }, 6000);
  }
  all(".flash").forEach(function (el) {
    var b = el.querySelector("[data-dismiss]");
    if (b) b.addEventListener("click", function () { el.remove(); });
    if (!el.classList.contains("flash--error")) setTimeout(function () { el.remove(); }, 6000);
  });

  /* ------------------------------------------------------------ theme */
  all("[data-theme-toggle]").forEach(function (btn) {
    function sync() { btn.setAttribute("aria-pressed", root.getAttribute("data-theme") === "dark" ? "true" : "false"); }
    btn.addEventListener("click", function () {
      var dark = root.getAttribute("data-theme") !== "dark";
      if (dark) root.setAttribute("data-theme", "dark"); else root.removeAttribute("data-theme");
      try { localStorage.setItem("automate-theme", dark ? "dark" : "light"); } catch (e) {}
      sync();
    });
    sync();
  });

  /* ------------------------------------------------------------ mobile navigation */
  var app = $("app");
  all("[data-nav-toggle]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var open = !app.classList.contains("is-nav-open");
      app.classList.toggle("is-nav-open", open);
      btn.setAttribute("aria-expanded", open ? "true" : "false");
    });
  });
  all("[data-nav-close]").forEach(function (el) {
    el.addEventListener("click", function () { app.classList.remove("is-nav-open"); });
  });

  /* ------------------------------------------------------------ dropdowns close on outside click / Escape */
  document.addEventListener("click", function (e) {
    all("details.dropdown[open]").forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute("open"); });
  });
  document.addEventListener("keydown", function (e) {
    if (e.key !== "Escape") return;
    all("details.dropdown[open]").forEach(function (d) { d.removeAttribute("open"); });
    if (app) app.classList.remove("is-nav-open");
  });

  /* ------------------------------------------------------------ confirm before destructive submits */
  document.addEventListener("submit", function (e) {
    var form = e.target;
    var msg = form.getAttribute && form.getAttribute("data-confirm");
    if (msg && !window.confirm(msg)) { e.preventDefault(); return; }
    var btn = form.querySelector('button[type="submit"][data-busy]');
    if (btn) setTimeout(function () { btn.disabled = true; btn.textContent = btn.getAttribute("data-busy"); }, 0);
  }, true);

  /* ------------------------------------------------------------ dialogs */
  all("[data-open-dialog]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var d = $(btn.getAttribute("data-open-dialog"));
      if (d && d.showModal) d.showModal();
    });
  });
  all("[data-close-dialog]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var d = btn.closest("dialog");
      if (d) d.close();
    });
  });

  /* ------------------------------------------------------------ filters submit on change */
  all("form[data-autosubmit]").forEach(function (form) {
    all("select, input[type=checkbox], input[type=date]", form).forEach(function (el) {
      el.addEventListener("change", function () { form.submit(); });
    });
  });

  /* ------------------------------------------------------------ POST helper */
  function post(url, data) {
    var fd = new FormData();
    fd.append("_csrf", csrf);
    Object.keys(data || {}).forEach(function (k) { fd.append(k, data[k]); });
    return fetch(url, {
      method: "POST",
      body: fd,
      credentials: "same-origin",
      headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest", "X-CSRF-Token": csrf }
    }).then(function (r) {
      return r.json().catch(function () { return { ok: false, error: "Unexpected response from the server." }; }).then(function (j) {
        if (!r.ok && j.ok !== false) j.ok = false;
        return j;
      });
    });
  }

  /* ------------------------------------------------------------ kanban boards
     Drag a card to another column, or use the select on touch screens. Moves
     into a "Rejected" or "Lost" column ask for a reason first. */
  all("[data-board]").forEach(function (board) {
    var url = board.getAttribute("data-move-url");
    var dialog = $(board.getAttribute("data-reason-dialog") || "");
    var dragged = null, fromList = null, nextSibling = null;
    var money = board.getAttribute("data-currency") || "";

    function listFor(stageId) { return board.querySelector('.bcol__list[data-stage="' + stageId + '"]'); }
    function refreshCounts() {
      all(".bcol", board).forEach(function (col) {
        var cards = all(".kcard", col);
        var count = col.querySelector(".bcol__count");
        if (count) count.textContent = cards.length;
        var sum = col.querySelector("[data-sum]");
        if (sum) {
          var total = cards.reduce(function (acc, c) { return acc + (parseFloat(c.getAttribute("data-value")) || 0); }, 0);
          sum.textContent = total > 0 ? money + " " + Math.round(total).toLocaleString() : "";
        }
        var empty = col.querySelector("[data-empty]");
        if (empty) empty.hidden = cards.length > 0;
      });
    }
    function syncSelect(card, stageId) {
      var sel = card.querySelector(".kcard__move select");
      if (sel) sel.value = stageId;
    }
    function askReason(kind) {
      return new Promise(function (resolve) {
        if (!dialog || (kind !== "rejected" && kind !== "lost")) { resolve({}); return; }
        var form = dialog.querySelector("form");
        form.reset();
        dialog.showModal();
        function done(val) {
          form.removeEventListener("submit", onSubmit);
          dialog.removeEventListener("close", onClose);
          resolve(val);
        }
        function onSubmit(e) {
          e.preventDefault();
          var fd = new FormData(form);
          var data = {};
          fd.forEach(function (v, k) { data[k] = v; });
          dialog.close("ok");
          done(data);
        }
        function onClose() { if (dialog.returnValue !== "ok") done(null); }
        dialog.returnValue = "";
        form.addEventListener("submit", onSubmit);
        dialog.addEventListener("close", onClose);
      });
    }
    function move(card, toList, before, revert) {
      var stageId = toList.getAttribute("data-stage");
      var kind = toList.getAttribute("data-kind");
      askReason(kind).then(function (extra) {
        if (extra === null) { revert(); refreshCounts(); return; }
        card.classList.add("is-saving");
        var data = { stage_id: stageId };
        Object.keys(extra).forEach(function (k) { data[k] = extra[k]; });
        post(url.replace("{id}", card.getAttribute("data-id")), data).then(function (res) {
          card.classList.remove("is-saving");
          if (!res.ok) { revert(); toast(res.error || "That move didn't save.", "error"); }
          else {
            syncSelect(card, stageId);
            if (res.message) toast(res.message, "success");
          }
          refreshCounts();
        }).catch(function () {
          card.classList.remove("is-saving");
          revert();
          refreshCounts();
          toast("Couldn't reach the server. Check your connection.", "error");
        });
      });
    }

    all(".kcard", board).forEach(function (card) {
      card.setAttribute("draggable", "true");
      card.addEventListener("dragstart", function (e) {
        dragged = card;
        fromList = card.parentNode;
        nextSibling = card.nextSibling;
        card.classList.add("is-dragging");
        e.dataTransfer.effectAllowed = "move";
        try { e.dataTransfer.setData("text/plain", card.getAttribute("data-id")); } catch (err) {}
      });
      card.addEventListener("dragend", function () {
        card.classList.remove("is-dragging");
        all(".bcol__list.is-over", board).forEach(function (l) { l.classList.remove("is-over"); });
      });
      var sel = card.querySelector(".kcard__move select");
      if (sel) {
        sel.addEventListener("change", function () {
          var target = listFor(sel.value);
          if (!target) return;
          var from = card.parentNode, next = card.nextSibling;
          target.insertBefore(card, target.querySelector(".kcard"));
          refreshCounts();
          move(card, target, null, function () { from.insertBefore(card, next); sel.value = from.getAttribute("data-stage"); });
        });
      }
    });

    all(".bcol__list", board).forEach(function (list) {
      list.addEventListener("dragover", function (e) {
        if (!dragged) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = "move";
        list.classList.add("is-over");
        var after = null;
        all(".kcard:not(.is-dragging)", list).some(function (c) {
          var r = c.getBoundingClientRect();
          if (e.clientY < r.top + r.height / 2) { after = c; return true; }
          return false;
        });
        if (after) list.insertBefore(dragged, after); else list.appendChild(dragged);
      });
      list.addEventListener("dragleave", function (e) {
        if (!list.contains(e.relatedTarget)) list.classList.remove("is-over");
      });
      list.addEventListener("drop", function (e) {
        e.preventDefault();
        list.classList.remove("is-over");
        if (!dragged) return;
        var card = dragged, origin = fromList, next = nextSibling;
        dragged = null;
        refreshCounts();
        if (origin === list) return;
        move(card, list, null, function () { origin.insertBefore(card, next); });
      });
    });
    refreshCounts();
  });

  /* ------------------------------------------------------------ email template picker */
  all("[data-template-picker]").forEach(function (sel) {
    var form = sel.closest("form");
    var url = sel.getAttribute("data-preview-url");
    sel.addEventListener("change", function () {
      if (!sel.value) return;
      var subject = form.querySelector('[name="subject"]'), body = form.querySelector('[name="body"]');
      if (body && body.value.trim() && body.getAttribute("data-dirty") === "1" && !window.confirm("Replace what you've written with this template?")) return;
      fetch(url + (url.indexOf("?") > -1 ? "&" : "?") + "template=" + encodeURIComponent(sel.value), {
        credentials: "same-origin", headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }
      }).then(function (r) { return r.json(); }).then(function (res) {
        if (!res.ok) { toast(res.error || "Couldn't load that template.", "error"); return; }
        if (subject) subject.value = res.subject;
        if (body) { body.value = res.body; body.setAttribute("data-dirty", "0"); }
      }).catch(function () { toast("Couldn't load that template.", "error"); });
    });
    var body = form.querySelector('[name="body"]');
    if (body) body.addEventListener("input", function () { body.setAttribute("data-dirty", "1"); });
  });

  /* ------------------------------------------------------------ pipeline stage editor: reorder rows */
  all("[data-stage-list]").forEach(function (list) {
    list.addEventListener("click", function (e) {
      var btn = e.target.closest("[data-move]");
      if (!btn) return;
      var row = btn.closest("[data-stage-row]");
      if (btn.getAttribute("data-move") === "up" && row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
      else if (btn.getAttribute("data-move") === "down" && row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
      btn.focus();
    });
  });

  /* ------------------------------------------------------------ screening question builder */
  all("[data-questions]").forEach(function (wrap) {
    var list = wrap.querySelector("[data-question-list]");
    var tpl = wrap.querySelector("template");
    function syncRow(row) {
      var type = row.querySelector('[name="q_type[]"]');
      var opts = row.querySelector("[data-q-options]");
      if (type && opts) opts.hidden = type.value !== "select";
    }
    all("[data-question-row]", list).forEach(syncRow);
    wrap.addEventListener("change", function (e) {
      if (e.target.name === "q_type[]") syncRow(e.target.closest("[data-question-row]"));
    });
    wrap.addEventListener("click", function (e) {
      if (e.target.closest("[data-add-question]")) {
        var node = tpl.content.firstElementChild.cloneNode(true);
        list.appendChild(node);
        syncRow(node);
        var input = node.querySelector("input, textarea");
        if (input) input.focus();
      }
      var rm = e.target.closest("[data-remove-question]");
      if (rm) rm.closest("[data-question-row]").remove();
    });
  });

  /* ------------------------------------------------------------ chart tooltips
     Every value is also in the table under each chart; this is a convenience. */
  var tip = $("tip");
  if (tip) {
    var show = function (el, x, y) {
      tip.innerHTML = "";
      var b = document.createElement("b");
      b.textContent = el.getAttribute("data-tip-value");
      var s = document.createElement("span");
      s.textContent = el.getAttribute("data-tip-label");
      tip.appendChild(b);
      tip.appendChild(s);
      tip.classList.add("is-on");
      var w = tip.offsetWidth, h = tip.offsetHeight;
      var left = Math.min(window.innerWidth - w - 8, Math.max(8, x - w / 2));
      var top = y - h - 12;
      if (top < 8) top = y + 16;
      tip.style.left = left + "px";
      tip.style.top = top + "px";
    };
    var hide = function () { tip.classList.remove("is-on"); };
    all("[data-tip-value]").forEach(function (el) {
      el.addEventListener("pointermove", function (e) { show(el, e.clientX, e.clientY); });
      el.addEventListener("pointerleave", hide);
      el.addEventListener("focus", function () {
        var r = el.getBoundingClientRect();
        show(el, r.left + r.width / 2, r.top);
      });
      el.addEventListener("blur", hide);
    });
    window.addEventListener("scroll", hide, { passive: true });
  }

  /* ------------------------------------------------------------ copy buttons */
  all("[data-copy]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var text = btn.getAttribute("data-copy");
      if (navigator.clipboard) navigator.clipboard.writeText(text).then(function () { toast("Copied to the clipboard.", "success"); });
    });
  });
})();
