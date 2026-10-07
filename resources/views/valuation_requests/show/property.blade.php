@php
  $opt = config('valuation_form');
  $land = $property->lands->first();
  $facade = $property->facades->first();
  $service = $property->services->first();
  $border = $property->borders->first();
  $building = $property->building;
  $question = $property->question;
  $ac = $property->ac;
  $flooring = $property->flooring;
  $yesNo = static fn ($v) => $v === null ? null : ((bool) $v ? __('Yes') : __('No'));
  $date = static fn ($v) => $v instanceof \DateTimeInterface ? $v->format('Y-m-d') : $v;
@endphp

@include('valuation_requests.show.fields', ['title' => __('Initial information'), 'fields' => [
    __('Reference') => $request->reference,
    __('Transaction number') => $request->number,
    __('Customer') => $property->customer_name,
    __('Owner') => $property->owner_name,
    __('Instrument number') => $property->instrument_no,
    __('Instrument date') => $property->instrument_date ?: $date($property->instrument_gregorian_date),
    __('As per the deed') => $yesNo($property->according_instrument),
    __('License number') => $property->license_no,
    __('License date') => $date($property->license_date),
    __('Issued by') => $property->issued_by,
    __('Commissioning date') => $date($property->commissioning_date),
    __('Commercial register number') => $property->retail_no,
    __('Reasons for valuation') => $property->undergo_reasons,
    __('Coordinator') => $request->coordinator?->name,
    __('Evaluator') => $request->evaluator?->name,
]])

@include('valuation_requests.show.fields', ['title' => __('Property information'), 'fields' => [
    __('City') => $property->location?->city?->name_ar,
    __('Neighborhood') => $property->location?->neighborhood?->name_ar,
    __('Street') => $property->location?->street,
    __('Plan number') => $property->location?->sketch_no,
    __('Block number') => $property->location?->part_no,
    __('Plot number') => $property->location?->piece_number,
    __('Property kind') => $property->property_kind,
    __('Property type') => $property->property_type,
    __('Ownership type') => $land?->ownership_type,
    __('Furnishing status') => $land?->furnishing_status,
    __('Building type') => $land?->building_type,
    __('Finishing status') => $land?->finishing_status,
    __('Coordinator notes') => $property->notes_real_estate,
    __('Evaluator notes') => $property->notes_property,
]])

@if ($land)
  @include('valuation_requests.show.fields', ['title' => __('Property specifications'), 'fields' => [
      __('Site') => $land->site,
      __('Land nature') => $land->land_nature,
      __('Facades') => $land->facade,
      __('External doors') => $land->door_ex,
      __('Internal doors') => $land->door_in,
      __('Streets') => $land->street,
      __('Neighbors') => $land->neighbor,
      __('Coordinates') => $property->location ? $property->location->x_axis.', '.$property->location->y_axis : null,
      __('Electricity meter number') => $land->electricity_num,
      __('Water meter number') => $land->water_num,
      __('Street width') => $land->street_width,
      __('Total building area') => $land->total_building_area,
      __('Additional features') => $land->additional_features,
      __('Surrounding facilities') => $land->surrounding_facilities,
      __('Approved floors') => $land->approved_floors,
      __('Property use') => $land->property_use,
      __('Approved building ratio') => $land->approved_building,
      __('Sector description') => $land->sector_description,
      __('Ownership description') => $land->ownership_description,
  ]])
@endif

@if ($property->notes)
  @include('valuation_requests.show.fields', ['title' => __('Property components description'), 'col' => 'col-12', 'fields' => [__('Description') => $property->notes]])
@endif

<div class="row match-height">
  @if ($border)
    <div class="col-md-6">
      <div class="card mb-2">
        <div class="card-header"><h5 class="card-title mb-0">{{ __('Borders and lengths') }}</h5></div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>{{ __('Side') }}</th><th>{{ __('Border') }}</th><th>{{ __('Length (m)') }}</th></tr></thead>
            <tbody>
              @foreach (['north' => __('North'), 'south' => __('South'), 'east' => __('East'), 'west' => __('West')] as $side => $label)
                <tr><td>{{ $label }}</td><td>{{ $border->{$side} ?: '—' }}</td><td>{{ $border->{"{$side}_length"} ?? '—' }}</td></tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endif

  @if ($service)
    <div class="col-md-6">
      <div class="card mb-2">
        <div class="card-header"><h5 class="card-title mb-0">{{ __('Services') }}</h5></div>
        <div class="card-body d-flex flex-wrap gap-50">
          @foreach ($opt['services'] as $key => $label)
            <span class="badge {{ $service->{$key} === 'نعم' ? 'bg-light-success' : 'bg-light-secondary' }}">{{ $label }}: {{ $service->{$key} ?: '—' }}</span>
          @endforeach
        </div>
      </div>
    </div>
  @endif
</div>

@if ($isBuilding)
  <div class="row match-height">
    @if ($facade)
      <div class="col-md-6">
        @include('valuation_requests.show.fields', ['title' => __('Facades'), 'col' => 'col-6', 'fields' => collect($opt['facade_sides'])->mapWithKeys(fn ($label, $side) => [$label => $facade->{"facade_type_$side"}])->all()])
      </div>
    @endif
    @if ($flooring)
      <div class="col-md-6">
        @include('valuation_requests.show.fields', ['title' => __('Flooring'), 'col' => 'col-6', 'fields' => collect($opt['flooring_rooms'])->mapWithKeys(fn ($label, $room) => [
            $label => trim(($flooring->{"flooring_$room"} ?? '').' '.($flooring->{"{$room}_description"} ?? '')),
        ])->all()])
      </div>
    @endif
  </div>

  @if ($building)
    @include('valuation_requests.show.fields', ['title' => __('Building details'), 'fields' => [
        __('Architectural style') => $building->architectural,
        __('Insulation quality') => $building->quality_insulation,
        __('Structure') => $building->structure_construction,
        __('Roof type') => $building->bishop_type,
        __('Building condition') => $building->build_state,
        __('Building age') => $building->build_age,
        __('Default building life') => $building->default_build_age,
        __('Remaining building life') => $building->remaining_build_age,
        __('Air conditioning') => $ac?->types,
        __('AC units count') => $ac?->ac_count,
        __('Heaters') => $land?->heater_no,
        __('Lifts') => $land?->lift_no,
        __('Arabic bathrooms') => $land?->bathroom_a_no,
        __('Western bathrooms') => $land?->bathroom_e_no,
    ]])
  @endif

  @if ($land?->include)
    @include('valuation_requests.show.fields', ['title' => __('Includes'), 'col' => 'col-12', 'fields' => [__('Includes') => $land->include]])
  @endif
@endif

@if ($question)
  <div class="card mb-2">
    <div class="card-header"><h5 class="card-title mb-0">{{ __('General notes') }}</h5></div>
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <tbody>
          @if ($isBuilding)
            @foreach ($opt['building_questions'] as $field => $label)
              <tr><td>{{ $label }}</td><td class="fw-bold">{{ $question->{$field} ?: '—' }}</td></tr>
            @endforeach
          @endif
          <tr><td>{{ $opt['question_labels']['need_cost'] }}</td><td class="fw-bold">{{ $question->need_cost ?: '—' }} @if ($question->need_cost === 'نعم' && $question->cost) ({{ $question->cost }} {{ __('SAR') }}) @endif</td></tr>
          <tr><td>{{ $opt['question_labels']['best_use'] }}</td><td class="fw-bold">{{ $question->best_use === null ? '—' : ((int) $question->best_use === 1 ? 'نعم' : 'لا') }}</td></tr>
          <tr><td>{{ __('Inspection type') }}</td><td class="fw-bold">
            @if ($question->inside_inspection === 'نعم') {{ $opt['question_labels']['inside_inspection'] }}
            @elseif ($question->outside_inspection === 'نعم') {{ $opt['question_labels']['outside_inspection'] }}
            @else — @endif
          </td></tr>
        </tbody>
      </table>
    </div>
  </div>
@endif
