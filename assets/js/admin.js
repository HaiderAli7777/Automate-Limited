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
    var defaultDialog = $(board.getAttribute("data-reason-dialog") || "");
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
        var dialog = $(board.getAttribute("data-dialog-" + kind) || "") || ((kind === "rejected" || kind === "lost") ? defaultDialog : null);
        if (!dialog) { resolve({}); return; }
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

  /* ------------------------------------------------------------ access rights form
     Picking a level ticks its permissions. Changing any tick switches to
     Custom access. The summary on the side follows along. */
  all("[data-access-form]").forEach(function (form) {
    var radios = all('input[name="role"]', form);
    var boxes = all('input[name="perms[]"]', form);
    var custom = form.querySelector('input[name="role"][value="custom"]');
    var adminNote = form.querySelector("[data-admin-note]");
    var summary = document.querySelector("[data-access-summary]");
    function has(p) { return boxes.some(function (b) { return b.value === p && b.checked; }); }
    function level(view, manage) { return manage ? "Full" : (view ? "View only" : "None"); }
    function render() {
      var chosen = radios.filter(function (r) { return r.checked; })[0];
      if (adminNote) adminNote.hidden = !chosen || chosen.value !== "admin";
      if (!summary) return;
      var sales = level(has("crm.view"), has("crm.manage"));
      if (sales !== "None") sales += has("crm.all") ? ", all leads" : ", own leads";
      var also = [];
      if (has("team.manage")) also.push("Team");
      if (has("settings.manage")) also.push("Settings");
      if (has("data.export")) also.push("Export");
      if (has("data.delete")) also.push("Delete");
      if (has("audit.view")) also.push("Activity log");
      var people = has("payroll.manage") ? "Full, with payroll" : level(has("hr.view"), has("hr.manage"));
      var rows = [["Recruitment", level(has("ats.view"), has("ats.manage"))], ["Interviews", has("interviews.own") || has("ats.view") ? "Yes" : "No"], ["Sales", sales], ["People", people]];
      if (also.length) rows.push(["Also", also.join(", ")]);
      summary.innerHTML = "";
      rows.forEach(function (r) {
        var dt = document.createElement("dt"); dt.textContent = r[0];
        var dd = document.createElement("dd"); dd.textContent = r[1];
        summary.appendChild(dt); summary.appendChild(dd);
      });
    }
    radios.forEach(function (r) {
      r.addEventListener("change", function () {
        var preset = null;
        try { preset = JSON.parse(r.getAttribute("data-preset") || "null"); } catch (e) {}
        if (preset) boxes.forEach(function (b) { if (!b.disabled) b.checked = preset.indexOf(b.value) > -1; });
        render();
      });
    });
    boxes.forEach(function (b) {
      b.addEventListener("change", function () {
        var needs = b.getAttribute("data-needs");
        if (b.checked && needs) boxes.forEach(function (o) { if (o.value === needs) o.checked = true; });
        if (!b.checked) boxes.forEach(function (o) { if (o.getAttribute("data-needs") === b.value) o.checked = false; });
        if (custom && !custom.disabled) custom.checked = true;
        render();
      });
    });
    render();
  });

  /* ------------------------------------------------------------ bulk selection on list tables */
  all("[data-bulk]").forEach(function (wrap) {
    var bar = wrap.querySelector("[data-bulk-bar]");
    var master = wrap.querySelector("[data-bulk-all]");
    var count = wrap.querySelector("[data-bulk-count]");
    function boxes() { return all("[data-bulk-item]", wrap); }
    function sync() {
      var n = boxes().filter(function (b) { return b.checked; }).length;
      if (bar) bar.hidden = n === 0;
      if (count) count.textContent = n + " selected";
      if (master) {
        master.checked = n > 0 && n === boxes().length;
        master.indeterminate = n > 0 && n < boxes().length;
      }
      boxes().forEach(function (b) { var tr = b.closest("tr"); if (tr) tr.classList.toggle("is-selected", b.checked); });
    }
    if (master) master.addEventListener("change", function () { boxes().forEach(function (b) { b.checked = master.checked; }); sync(); });
    wrap.addEventListener("change", function (e) { if (e.target.hasAttribute && e.target.hasAttribute("data-bulk-item")) sync(); });
    all("[data-bulk-clear]", wrap).forEach(function (btn) {
      btn.addEventListener("click", function () { boxes().forEach(function (b) { b.checked = false; }); sync(); });
    });
    // show only the inputs the chosen action needs
    var action = wrap.querySelector("[data-bulk-action]");
    function syncAction() {
      if (!action) return;
      all("[data-bulk-for]", wrap).forEach(function (el) {
        var on = el.getAttribute("data-bulk-for").split(" ").indexOf(action.value) > -1;
        el.hidden = !on;
        all("select, input", el).forEach(function (i) { i.disabled = !on; });
      });
      var go = wrap.querySelector("[data-bulk-go]");
      if (go) go.classList.toggle("btn--danger", action.value === "delete");
    }
    if (action) { action.addEventListener("change", syncAction); syncAction(); }
    wrap.addEventListener("submit", function (e) {
      var n = boxes().filter(function (b) { return b.checked; }).length;
      if (action && action.value === "delete" && !window.confirm("Delete " + n + (n === 1 ? " item" : " items") + " and their history? This can't be undone.")) e.preventDefault();
    });
    var stage = wrap.querySelector("[data-bulk-stage]");
    function syncStage() {
      if (!stage) return;
      var opt = stage.options[stage.selectedIndex];
      var kind = opt ? opt.getAttribute("data-kind") : "";
      all("[data-bulk-rejected]", wrap).forEach(function (el) {
        el.hidden = kind !== "rejected" && kind !== "lost";
        all("select, input", el).forEach(function (i) { i.disabled = el.hidden; });
      });
      all("[data-bulk-pool]", wrap).forEach(function (el) {
        el.hidden = kind !== "pool";
        all("select, input", el).forEach(function (i) { i.disabled = el.hidden; });
      });
    }
    if (stage) { stage.addEventListener("change", syncStage); syncStage(); }
    sync();
  });

  /* ------------------------------------------------------------ notifications: mark all read from the bell */
  all("[data-read-all]").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      post(form.getAttribute("action"), {}).then(function (res) {
        if (!res.ok) return;
        all(".notif.is-unread").forEach(function (n) { n.classList.remove("is-unread"); });
        all("[data-notif-count]").forEach(function (c) { c.remove(); });
        form.remove();
      });
    });
  });

  /* ------------------------------------------------------------ "/" focuses search */
  document.addEventListener("keydown", function (e) {
    if (e.key !== "/" || e.ctrlKey || e.metaKey || e.altKey) return;
    var t = e.target;
    if (t && (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName))) return;
    var box = document.querySelector('.search-box input');
    if (box && box.offsetParent !== null) { e.preventDefault(); box.focus(); box.select(); }
  });

  /* ------------------------------------------------------------ allowances and deductions editor */
  all("[data-lines]").forEach(function (wrap) {
    var basic = parseFloat(wrap.getAttribute("data-basic")) || 0;
    var tpl = wrap.querySelector("template[data-line-template]");
    var netEl = wrap.querySelector('[data-sum="net"]');
    var cur = netEl ? netEl.getAttribute("data-currency") : "";
    function fmt(v) { return v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function num(v) { var n = parseFloat(String(v).replace(/,/g, "")); return isNaN(n) ? 0 : n; }
    function sync() {
      var sums = { allowance: 0, deduction: 0 };
      all("[data-group]", wrap).forEach(function (g) {
        var type = g.getAttribute("data-group"), rows = all("[data-line]", g);
        rows.forEach(function (r) { sums[type] += num(r.querySelector("[data-line-amount]").value); });
        var empty = g.querySelector(".lines__empty");
        if (empty) empty.hidden = rows.length > 0;
        var out = g.querySelector('[data-sum="' + type + '"]');
        if (out) out.textContent = (type === "deduction" ? "- " : "+ ") + fmt(sums[type]);
      });
      var gross = wrap.querySelector('[data-sum="gross"]');
      if (gross) gross.textContent = cur + " " + fmt(basic + sums.allowance);
      if (netEl) {
        var net = basic + sums.allowance - sums.deduction;
        netEl.textContent = cur + " " + fmt(net);
        netEl.classList.toggle("is-negative", net < 0);
      }
    }
    wrap.addEventListener("input", function (e) { if (e.target.hasAttribute("data-line-amount")) sync(); });
    wrap.addEventListener("click", function (e) {
      var add = e.target.closest("[data-add-line]");
      if (add && tpl) {
        var type = add.getAttribute("data-add-line");
        var node = tpl.content.firstElementChild.cloneNode(true);
        node.querySelector('input[type="hidden"]').value = type;
        var label = node.querySelector('input:not([type="hidden"]):not([data-line-amount])');
        label.value = add.getAttribute("data-label") || "";
        wrap.querySelector('[data-group="' + type + '"] [data-line-list]').insertBefore(node, wrap.querySelector('[data-group="' + type + '"] .lines__empty'));
        (label.value ? node.querySelector("[data-line-amount]") : label).focus();
        sync();
      }
      var rm = e.target.closest("[data-remove-line]");
      if (rm) { rm.closest("[data-line]").remove(); sync(); }
    });
    sync();
  });

  /* ------------------------------------------------------------ employee status dialog */
  all("[data-status-to]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var d = $("statusDialog");
      if (!d) return;
      var to = btn.getAttribute("data-status-to");
      d.querySelector("[data-status-field]").value = to;
      d.querySelector("[data-status-heading]").textContent = btn.getAttribute("data-status-title") || "Change status";
      all("[data-status-only]", d).forEach(function (el) { el.hidden = el.getAttribute("data-status-only") !== to; });
      if (!d.open && d.showModal) d.showModal();
    });
  });
  all("[data-status-select]").forEach(function (sel) {
    var fields = document.querySelector("[data-exit-fields]");
    function sync() { if (fields) fields.hidden = sel.value !== "left"; }
    sel.addEventListener("change", sync);
    sync();
  });

  /* ------------------------------------------------------------ payroll: preview who will be paid */
  all("[data-payroll-form]").forEach(function (form) {
    function scope() { var c = form.querySelector('input[name="scope"]:checked'); return c ? c.value : "all"; }
    function syncScope() {
      all("[data-scope-only]", form).forEach(function (el) { el.hidden = el.getAttribute("data-scope-only") !== scope(); });
    }
    form.addEventListener("change", function (e) {
      if (!e.target.hasAttribute("data-preview-field")) return;
      syncScope();
      var q = new URLSearchParams();
      q.set("period", form.querySelector('[name="period"]').value);
      q.set("scope", scope());
      if (scope() === "department") q.set("department", form.querySelector('[name="department"]').value);
      if (scope() === "employee") q.set("employee", form.querySelector('[name="employee"]').value);
      if (scope() === "all" || q.get("department") || q.get("employee")) window.location.href = form.getAttribute("action") + "?" + q.toString();
    });
    syncScope();
  });

  /* ------------------------------------------------------------ copy buttons */
  all("[data-copy]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var text = btn.getAttribute("data-copy");
      if (navigator.clipboard) navigator.clipboard.writeText(text).then(function () { toast("Copied to the clipboard.", "success"); });
    });
  });
})();
