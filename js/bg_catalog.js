/**
 * Проверка на полетата при въвеждане (заглавие, екземпляр, КДБФ, акт за отчисляване).
 * Работи само за полета с атрибут data-bgval. Сървърът проверява всичко отново
 * (lib/Bg/Validator.php) – тук е само за бърза обратна връзка.
 */
(function (window, document) {
  'use strict';
  if (window.BgCat) return;

  var DEFAULT_I18N = {
    invno: 'Инвентарният номер трябва да е цяло число без водещи нули (напр. 1234)',
    invnoJump: 'Номерът е с повече от 100 над следващия свободен (%s). Да продължа ли?',
    isbnChars: 'Разрешени са само цифри, X, тирета и интервали',
    isbnLen: 'ISBN има 10 или 13 цифри, ISSN – 8',
    isbnCheck: 'Грешна контролна цифра – проверете номера. Ако в книгата наистина е отпечатан грешен номер, добавете „(грешен)“',
    year: 'Годината трябва да е във вида 2019, [2019], 2019?, [ок. 1900], 2010–2012 или [б. г.]',
    yearRange: 'Годината трябва да е между 1450 и %s',
    price: 'Цената трябва да е число с до 2 знака след десетичната запетая, напр. 12.50',
    priceZero: 'Цената е 0 – за дарения въведете оценъчна стойност',
    udc: 'Не прилича на УДК индекс (напр. 886.7-31)',
    authormark: 'Обикновено е главна буква и 1–3 цифри (напр. П47)',
    collation: 'Обикновено е във вида „208 с. : ил. ; 20 см“',
    datePast: 'Датата не може да е в бъдещето',
    date: 'Невалидна дата',
    posint: 'Трябва да е цяло число по-голямо от 0',
    ranges: 'Въведете номера и интервали, напр. 101-130, 135',
    required: 'Полето е задължително',
    title: 'Моля, поправете отбелязаните полета'
  };

  function t(key) {
    var src = window.BgCatI18n || (window.top && window.top.BgCatI18n) || {};
    return src[key] || DEFAULT_I18N[key] || key;
  }

  function fmt(s, v) { return String(s).replace('%s', v); }

  /* ---------------- чисти проверки ---------------- */

  function isbnDigits(s) { return String(s).replace(/[^0-9Xx]/g, '').toUpperCase(); }

  function isbn10(d) {
    if (!/^\d{9}[\dX]$/.test(d)) return false;
    var s = 0;
    for (var i = 0; i < 10; i++) s += (d[i] === 'X' ? 10 : +d[i]) * (10 - i);
    return s % 11 === 0;
  }

  function isbn13(d) {
    if (!/^97[89]\d{10}$/.test(d)) return false;
    var s = 0;
    for (var i = 0; i < 13; i++) s += +d[i] * (i % 2 === 0 ? 1 : 3);
    return s % 10 === 0;
  }

  function issn(d) {
    if (!/^\d{7}[\dX]$/.test(d)) return false;
    var s = 0;
    for (var i = 0; i < 7; i++) s += +d[i] * (8 - i);
    var c = (11 - s % 11) % 11;
    return c === (d[7] === 'X' ? 10 : +d[7]);
  }

  /** @returns {{level: string|null, msg: string}} */
  function ok() { return { level: null, msg: '' }; }
  function err(m) { return { level: 'error', msg: m }; }
  function warn(m) { return { level: 'warning', msg: m }; }

  var checks = {
    invno: function (v, el) {
      if (!/^[1-9]\d{0,8}$/.test(v)) return err(t('invno'));
      return ok();
    },
    isbn: function (v) {
      var parts = v.split(/\s*;\s*/), res = ok();
      for (var i = 0; i < parts.length; i++) {
        var p = parts[i], q = '';
        if (!p) continue;
        var m = p.match(/^(.*?)\s*(\([^()]{1,40}\))\s*$/);
        if (m) { p = m[1]; q = m[2]; }
        p = p.replace(/^\s*IS[BS]N\s*:?\s*/i, '');
        if (!/^[0-9Xx\- ]+$/.test(p)) return err(p + ': ' + t('isbnChars'));
        var d = isbnDigits(p), good;
        if (d.length === 13) good = isbn13(d);
        else if (d.length === 10) good = isbn10(d);
        else if (d.length === 8) good = issn(d);
        else return err(p + ': ' + t('isbnLen'));
        if (!good) {
          if (q.toLowerCase().indexOf('грешен') >= 0) res = warn(p + ': ' + t('isbnCheck'));
          else return err(p + ': ' + t('isbnCheck'));
        }
      }
      return res;
    },
    year: function (v, el) {
      var serial = el.getAttribute('data-bg-serial') === '1';
      var pats = [/^\d{4}$/, /^\d{4}\?$/, /^\[\d{4}\??\]$/, /^\[(?:ок\.|около|ca\.?|c\.)\s*\d{4}\??\]$/i, /^\d{4}\s*[-–]\s*\d{4}$/, /^\[б\.\s*г\.\]$/i];
      var matched = pats.some(function (p) { return p.test(v); });
      var bad = serial ? warn : err;
      if (!matched) return bad(t('year'));
      var ys = v.match(/\d{4}/g) || [], max = new Date().getFullYear() + 1, prev = 0;
      for (var i = 0; i < ys.length; i++) {
        var y = +ys[i];
        if (y < 1450 || y > max || y < prev) return bad(fmt(t('yearRange'), max));
        prev = y;
      }
      return ok();
    },
    price: function (v) {
      var n = v.replace(/[\s ]+/g, '').replace(',', '.');
      if (!/^\d{1,8}(\.\d{1,2})?$/.test(n)) return err(t('price'));
      if (parseFloat(n) === 0) return warn(t('priceZero'));
      return ok();
    },
    udc: function (v) {
      return /^[0-9](?:[0-9.\-:+\/'\s]|\([^()]*\)|"[^"]*"|=[0-9.\-]+|\[[^\[\]]*\])*$/.test(v) ? ok() : warn(t('udc'));
    },
    authormark: function (v) {
      return /^[А-ЯЁA-Z]\d{1,3}$/.test(v) ? ok() : warn(t('authormark'));
    },
    collation: function (v) {
      return (v.indexOf('с.') >= 0 && v.indexOf('см') >= 0) ? ok() : warn(t('collation'));
    },
    date: function (v) {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(v) || isNaN(new Date(v + 'T00:00:00').getTime())) return err(t('date'));
      return ok();
    },
    'date-past': function (v) {
      var r = checks.date(v);
      if (r.level) return r;
      var today = new Date(); today.setHours(0, 0, 0, 0);
      if (new Date(v + 'T00:00:00') > today) return err(t('datePast'));
      return ok();
    },
    posint: function (v) {
      return /^[1-9]\d{0,8}$/.test(v) ? ok() : err(t('posint'));
    },
    ranges: function (v) {
      return /^\s*\d{1,9}(\s*[-–]\s*\d{1,9})?([\s,;]+\d{1,9}(\s*[-–]\s*\d{1,9})?)*\s*$/.test(v) ? ok() : err(t('ranges'));
    }
  };

  /* ---------------- показване ---------------- */

  function hintFor(el) {
    var next = el.parentNode && el.parentNode.querySelector('small.bg-hint[data-for="' + (el.id || el.name) + '"]');
    if (!next) {
      next = document.createElement('small');
      next.className = 'bg-hint d-block';
      next.setAttribute('data-for', el.id || el.name);
      el.insertAdjacentElement('afterend', next);
    }
    return next;
  }

  function show(el, r) {
    el.classList.toggle('is-invalid', r.level === 'error');
    el.classList.toggle('bg-warn', r.level === 'warning');
    var h = hintFor(el);
    h.textContent = r.msg || '';
    h.className = 'bg-hint d-block ' + (r.level === 'error' ? 'text-danger' : (r.level === 'warning' ? 'text-warning' : ''));
  }

  function isActive(el) {
    if (el.disabled) return false;
    if (el.closest('[data-bg-skip]')) return false;
    // полета, които се проверяват само когато друго поле е попълнено (напр. брой копия > 0)
    var when = el.closest('[data-bg-when]');
    if (when) {
      var ref = document.getElementById(when.getAttribute('data-bg-when'));
      if (!ref || !(parseInt(ref.value, 10) > 0)) return el === ref;
    }
    return true;
  }

  function validate(el) {
    var kind = el.getAttribute('data-bgval');
    var v = (el.value || '').trim();
    var r = ok();
    if (!isActive(el)) {
      r = ok();
    } else if (v === '') {
      if (el.getAttribute('data-bg-required') === '1') r = err(t('required'));
    } else if (checks[kind]) {
      r = checks[kind](v, el);
    }
    show(el, r);
    return r;
  }

  function onBlur(e) {
    var el = e.target;
    if (el && el.getAttribute && el.getAttribute('data-bgval')) validate(el);
  }

  function onSubmit(e) {
    var form = e.target;
    if (!form || !form.querySelectorAll) return;
    var fields = form.querySelectorAll('[data-bgval]');
    if (!fields.length) return;
    var errors = [], first = null;
    for (var i = 0; i < fields.length; i++) {
      var el = fields[i];
      if (!isActive(el)) continue;
      var r = validate(el);
      if (r.level === 'error') {
        var label = el.getAttribute('data-bg-label') || el.name;
        errors.push(label + ': ' + r.msg);
        if (!first) first = el;
      }
    }
    // скок в инвентарната номерация (един грешен номер измества всички следващи предложения)
    if (!errors.length) {
      var inv = form.querySelectorAll('[data-bgval="invno"][data-bg-next]');
      for (var j = 0; j < inv.length; j++) {
        var nx = parseInt(inv[j].getAttribute('data-bg-next'), 10), val = parseInt(inv[j].value, 10);
        var flag = form.querySelector('input[name="confirmBigJump"]');
        if (nx && val > nx + 100 && isActive(inv[j])) {
          if (!window.confirm(fmt(t('invnoJump'), nx))) {
            e.preventDefault(); e.stopImmediatePropagation();
            return false;
          }
          if (!flag) {
            flag = document.createElement('input');
            flag.type = 'hidden'; flag.name = 'confirmBigJump';
            form.appendChild(flag);
          }
          flag.value = '1';
        }
      }
    }
    if (errors.length) {
      e.preventDefault();
      e.stopImmediatePropagation();
      var collapse = first.closest('.collapse');
      if (collapse) collapse.classList.add('show');
      try { first.focus(); } catch (x) { /* скрито поле */ }
      var toastr = (window.top && window.top.toastr) || window.toastr;
      if (toastr) toastr.error(errors.join('<br>'), t('title'));
      else window.alert(t('title') + '\n' + errors.join('\n'));
      var loaders = document.querySelectorAll('.loader');
      for (var k = 0; k < loaders.length; k++) loaders[k].style.display = 'none';
      return false;
    }
  }

  document.addEventListener('focusout', onBlur, true);
  document.addEventListener('submit', onSubmit, true);

  window.BgCat = {
    checks: checks,
    validate: validate,
    isbn10: isbn10, isbn13: isbn13, issn: issn,
    /** Сглобява сигнатура: УДК + авторски знак */
    composeCallNumber: function (udc, mark) {
      udc = (udc || '').trim(); mark = (mark || '').trim();
      return (udc + ' ' + mark).trim();
    }
  };
})(window, document);
