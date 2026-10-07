@once
  @push('page-script')
    <script>
      (function () {
        var url = @json(route('dashboard.valuation-lookups.neighborhoods', ['geoCity' => '__ID__']));
        function load(city, selected) {
          var target = document.querySelector(city.dataset.neighborhoodTarget);
          if (!target) { return; }
          target.innerHTML = '<option value="">' + @json(__('Select')) + '</option>';
          if (!city.value) { return; }
          fetch(url.replace('__ID__', city.value), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (rows) {
              rows.forEach(function (row) {
                var opt = document.createElement('option');
                opt.value = row.id; opt.textContent = row.name;
                if (selected && String(row.id) === String(selected)) { opt.selected = true; }
                target.appendChild(opt);
              });
            });
        }
        document.addEventListener('change', function (e) {
          var city = e.target.closest('[data-neighborhood-target]');
          if (city) { load(city, null); }
        });
        document.querySelectorAll('[data-neighborhood-target]').forEach(function (city) {
          var target = document.querySelector(city.dataset.neighborhoodTarget);
          if (city.value && target && target.options.length <= 1) {
            load(city, target.dataset.selected);
          }
        });
      })();
    </script>
  @endpush
@endonce
