@once
  @push('page-script')
    <script>
      document.addEventListener('change', function (e) {
        var city = e.target.closest('[data-neighborhood-target]');
        if (!city) { return; }
        var target = document.querySelector(city.dataset.neighborhoodTarget);
        if (!target) { return; }
        target.innerHTML = '<option value="">' + @json(__('Select')) + '</option>';
        if (!city.value) { return; }
        var url = @json(route('dashboard.valuation-lookups.neighborhoods', ['geoCity' => '__ID__'])).replace('__ID__', city.value);
        fetch(url, { headers: { 'Accept': 'application/json' } })
          .then(function (r) { return r.json(); })
          .then(function (rows) {
            rows.forEach(function (row) {
              var opt = document.createElement('option');
              opt.value = row.id; opt.textContent = row.name;
              target.appendChild(opt);
            });
          });
      });
    </script>
  @endpush
@endonce
