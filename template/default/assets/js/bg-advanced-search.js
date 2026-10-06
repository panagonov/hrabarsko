/**
 * Проверки в разширеното търсене (OPAC): години, ISBN, празна форма.
 * Празните редове и полета не се изпращат, за да е адресът кратък и да може да се запази.
 * Сървърът (BgQueryParser) проверява всичко отново.
 */
(function () {
  'use strict';
  var form = document.getElementById('bg-adv-form');
  if (!form || form.dataset.bgReady) return;
  form.dataset.bgReady = '1';

  var msg = function (k) { return form.getAttribute('data-' + k) || ''; };
  var rows = Array.prototype.slice.call(form.querySelectorAll('.bg-adv-row'));

  function setPlaceholder(row) {
    var f = row.querySelector('.bg-adv-field').value;
    var input = row.querySelector('.bg-adv-term');
    input.placeholder = f === 'PY' ? msg('ph-year') : f === 'BN' ? msg('ph-isbn') : f === 'UC' ? msg('ph-udc') : msg('ph-text');
    input.inputMode = (f === 'PY' || f === 'BN') ? 'numeric' : 'text';
  }

  function invalid(input, text) {
    input.classList.add('is-invalid');
    var fb = input.parentNode.querySelector('.invalid-feedback');
    if (fb) fb.textContent = text;
    return false;
  }

  function clear(input) {
    input.classList.remove('is-invalid');
    var fb = input.parentNode.querySelector('.invalid-feedback');
    if (fb) fb.textContent = '';
  }

  rows.forEach(function (row) {
    setPlaceholder(row);
    row.querySelector('.bg-adv-field').addEventListener('change', function () { setPlaceholder(row); });
  });

  form.addEventListener('submit', function (e) {
    var ok = true, any = false;
    var err = document.getElementById('bg-adv-error');
    err.classList.add('d-none');

    rows.forEach(function (row) {
      var field = row.querySelector('.bg-adv-field');
      var input = row.querySelector('.bg-adv-term');
      var op = row.querySelector('select[name^="op"]');
      clear(input);
      var v = input.value.trim();
      // празен ред – не се изпраща
      [field, input, op].forEach(function (el) { if (el) el.disabled = (v === ''); });
      if (v === '') return;
      any = true;
      if (field.value === 'PY' && !/^\d{4}(\s*[-–:]\s*\d{4})?$|^\d{1,3}\*$/.test(v)) ok = invalid(input, msg('msg-year'));
      if (field.value === 'PY') {
        var m = v.match(/^(\d{4})\s*[-–:]\s*(\d{4})$/);
        if (m && +m[1] > +m[2]) ok = invalid(input, msg('msg-range'));
      }
      if (field.value === 'BN') {
        var d = v.replace(/[\s-]/g, '').toUpperCase();
        if (!/^\d{1,13}X?$/.test(d)) ok = invalid(input, msg('msg-isbn'));
        else input.value = d;
      }
    });

    // години от–до → years=ГГГГ;ГГГГ
    var yf = document.getElementById('bg-year-from'), yt = document.getElementById('bg-year-to');
    var years = document.getElementById('bg-years'), ymsg = document.getElementById('bg-year-msg');
    [yf, yt].forEach(function (el) { el.classList.remove('is-invalid'); });
    ymsg.textContent = '';
    var a = yf.value.trim(), b = yt.value.trim();
    years.disabled = true;
    if (a !== '' || b !== '') {
      if ((a !== '' && !/^\d{4}$/.test(a)) || (b !== '' && !/^\d{4}$/.test(b))) {
        ok = false; ymsg.textContent = msg('msg-year');
        if (a !== '' && !/^\d{4}$/.test(a)) yf.classList.add('is-invalid');
        if (b !== '' && !/^\d{4}$/.test(b)) yt.classList.add('is-invalid');
      } else {
        a = a || '1000'; b = b || String(new Date().getFullYear() + 1);
        if (+a > +b) { ok = false; ymsg.textContent = msg('msg-range'); yf.classList.add('is-invalid'); }
        else { years.value = a + ';' + b; years.disabled = false; any = true; }
      }
    }
    ['bg-lang', 'bg-gmd'].forEach(function (id) {
      var s = document.getElementById(id);
      s.disabled = (s.value === '');
      if (s.value !== '') any = true;
    });

    if (ok && !any) { ok = false; err.textContent = msg('msg-empty'); err.classList.remove('d-none'); }
    if (!ok) {
      e.preventDefault();
      // връщаме полетата активни, за да може да се поправят
      form.querySelectorAll('[disabled]').forEach(function (el) { el.disabled = false; });
    }
  });

  document.getElementById('bg-adv-clear').addEventListener('click', function () {
    rows.forEach(function (row) { var i = row.querySelector('.bg-adv-term'); i.value = ''; clear(i); });
    ['bg-year-from', 'bg-year-to'].forEach(function (id) { document.getElementById(id).value = ''; });
    document.getElementById('bg-lang').value = '';
    document.getElementById('bg-gmd').value = '';
  });
})();
