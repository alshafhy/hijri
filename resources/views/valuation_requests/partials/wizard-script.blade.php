{{-- Tab wizard: [data-wizard] form, nav-link tabs, [data-wizard-prev]/[data-wizard-next] buttons. --}}
@push('page-script')
  <script>
    (function () {
      var form = document.querySelector('[data-wizard]');
      if (!form) { return; }
      var tabs = Array.prototype.slice.call(form.querySelectorAll('[data-bs-toggle="tab"]'));
      var prev = form.querySelector('[data-wizard-prev]');
      var next = form.querySelector('[data-wizard-next]');
      var submit = form.querySelector('[data-wizard-submit]');
      var progress = form.querySelector('[data-wizard-progress]');

      function current() { return tabs.findIndex(function (t) { return t.classList.contains('active'); }); }
      function pane(i) { return document.querySelector(tabs[i].getAttribute('data-bs-target')); }
      function show(i) { bootstrap.Tab.getOrCreateInstance(tabs[i]).show(); }
      function sync() {
        var i = current();
        prev.disabled = i === 0;
        next.classList.toggle('d-none', i === tabs.length - 1);
        submit.classList.toggle('d-none', i !== tabs.length - 1);
        if (progress) { progress.style.width = ((i + 1) / tabs.length * 100) + '%'; }
      }
      function paneValid(i) {
        return window.FormUI.validate(pane(i));
      }
      function showPaneOf(el) {
        var p = el.closest('.tab-pane');
        var idx = tabs.findIndex(function (t) { return t.getAttribute('data-bs-target') === '#' + p.id; });
        if (idx >= 0) { show(idx); }
      }

      tabs.forEach(function (t) { t.addEventListener('shown.bs.tab', sync); });
      prev.addEventListener('click', function () { var i = current(); if (i > 0) { show(i - 1); } });
      next.addEventListener('click', function () { var i = current(); if (paneValid(i)) { show(i + 1); } });
      form.addEventListener('invalid', function (e) { showPaneOf(e.target); }, true);

      var errorKeys = @json(array_keys($errors->getMessages()));
      for (var e = 0; e < errorKeys.length; e++) {
        var parts = errorKeys[e].split('.');
        var name = parts[0] + parts.slice(1).map(function (p) { return '[' + p + ']'; }).join('');
        var field = form.querySelector('[name="' + name + '"]');
        if (field) { field.classList.add('is-invalid'); showPaneOf(field); break; }
      }
      sync();
    })();
  </script>
@endpush
