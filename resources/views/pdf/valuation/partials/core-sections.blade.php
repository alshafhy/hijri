{{-- Shared property / valuation summary sections --}}
@php
    $dash = '—';
@endphp

<h2>{{ __('Basic evaluation data') }}</h2>
<table class="report">
    <tr>
        <td class="label">{{ __('Request number') }}</td>
        <td>{{ $request->number ?: $reference }}</td>
        <td class="label">{{ __('Issue date') }}</td>
        <td>{{ $issue_date }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Customer') }}</td>
        <td>{{ $property?->customer_name ?: $dash }}</td>
        <td class="label">{{ __('Owner') }}</td>
        <td>{{ $property?->owner_name ?: $dash }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Instrument number') }}</td>
        <td>{{ $property?->instrument_no ?: $dash }}</td>
        <td class="label">{{ __('Issued by') }}</td>
        <td>{{ $property?->issued_by ?: $dash }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Property kind') }}</td>
        <td>{{ $property?->property_kind ?: $dash }}</td>
        <td class="label">{{ __('Property type') }}</td>
        <td>{{ $property?->property_type ?: $dash }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('City') }}</td>
        <td>{{ $city_name ?: $dash }}</td>
        <td class="label">{{ __('Neighborhood') }}</td>
        <td>{{ $neighborhood_name ?: $dash }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Street') }}</td>
        <td>{{ $location?->street ?: $dash }}</td>
        <td class="label">{{ __('Street count') }}</td>
        <td>{{ $street_count }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Coordinates') }}</td>
        <td colspan="3">
            {{ $location?->x_axis ?: $dash }} E /
            {{ $location?->y_axis ?: $dash }} N
        </td>
    </tr>
    <tr>
        <td class="label">{{ __('Evaluator') }}</td>
        <td>{{ $evaluator?->name ?: $dash }}</td>
        <td class="label">{{ __('Coordinator') }}</td>
        <td>{{ $coordinator?->name ?: $dash }}</td>
    </tr>
</table>

@if ($border)
<h2>{{ __('Borders and lengths') }}</h2>
<table class="report">
    <thead>
        <tr>
            <th>{{ __('Direction') }}</th>
            <th>{{ __('Description') }}</th>
            <th>{{ __('Length') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="label">{{ __('North') }}</td>
            <td>{{ $border->north ?: $dash }}</td>
            <td>{{ $border->north_length !== null ? $border->north_length.' م' : $dash }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('South') }}</td>
            <td>{{ $border->south ?: $dash }}</td>
            <td>{{ $border->south_length !== null ? $border->south_length.' م' : $dash }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('East') }}</td>
            <td>{{ $border->east ?: $dash }}</td>
            <td>{{ $border->east_length !== null ? $border->east_length.' م' : $dash }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('West') }}</td>
            <td>{{ $border->west ?: $dash }}</td>
            <td>{{ $border->west_length !== null ? $border->west_length.' م' : $dash }}</td>
        </tr>
    </tbody>
</table>
@endif

@if ($service)
<h2>{{ __('Available services') }}</h2>
<table class="report">
    <tr>
        <td class="label">{{ __('Electricity') }}</td><td>{{ $service->electricity ?: $dash }}</td>
        <td class="label">{{ __('Water') }}</td><td>{{ $service->water ?: $dash }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Sanitation') }}</td><td>{{ $service->sanitation ?: $dash }}</td>
        <td class="label">{{ __('Telephone') }}</td><td>{{ $service->telephone ?: $dash }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Internet') }}</td><td colspan="3">{{ $service->internet ?: $dash }}</td>
    </tr>
</table>
@endif

@if (! $hide_evaluation)
<h2>{{ __('Property evaluation') }}</h2>
@if ($components->isNotEmpty())
<table class="report">
    <thead>
        <tr>
            <th>{{ __('Component') }}</th>
            <th>{{ __('Area') }}</th>
            <th>{{ __('Meter price') }}</th>
            <th>{{ __('Total') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($components as $component)
            @php
                $area = (float) ($component->area_value ?? 0);
                $price = (float) ($component->price_value ?? 0);
            @endphp
            <tr>
                <td>{{ $component->component_key }}</td>
                <td style="text-align:center;">{{ number_format($area, 2) }}</td>
                <td style="text-align:center;">{{ number_format($price, 2) }}</td>
                <td style="text-align:center;">{{ number_format($area * $price, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@else
<p class="muted">{{ __('No valuation components recorded.') }}</p>
@endif

@if ($propertyTotal)
<table class="report">
    @if (($propertyTotal->profit_amount ?? 0) > 0)
    <tr>
        <td class="label">{{ __('Developer earnings') }} (+)</td>
        <td colspan="3" style="text-align:center;">{{ number_format((float) $propertyTotal->profit_amount, 2) }}</td>
    </tr>
    @endif
    @if (($propertyTotal->depreciation_amount ?? 0) > 0)
    <tr>
        <td class="label">{{ __('Depreciation') }} (-)</td>
        <td colspan="3" style="text-align:center;">{{ number_format((float) $propertyTotal->depreciation_amount, 2) }}</td>
    </tr>
    @endif
    @if ($forced_sale_percentage)
    <tr>
        <td class="label">{{ __('Forced sale') }} ({{ $forced_sale_percentage }}%) (-)</td>
        <td style="text-align:center;">%</td>
        <td style="text-align:center;">{{ $forced_sale_percentage }}</td>
        <td style="text-align:center;">{{ $forced_sale_amount !== null ? number_format((float) $forced_sale_amount, 2) : $dash }}</td>
    </tr>
    @endif
    @if (($propertyTotal->movables ?? 0) > 0)
    <tr>
        <td class="label">{{ __('Movables') }}</td>
        <td colspan="3" style="text-align:center;">{{ number_format((float) $propertyTotal->movables, 2) }}</td>
    </tr>
    @endif
    @if ($total_area)
    <tr>
        <td class="label">{{ __('Total area') }}</td>
        <td colspan="3" style="text-align:center;">{{ number_format((float) $total_area, 2) }}</td>
    </tr>
    @endif
</table>
@endif
@endif

<div class="final-box">
    <div><strong>{{ __('Final valuation amount') }}</strong>
        @if ($is_manual_amount)
            <span class="badge-manual">{{ __('Manual override') }}</span>
        @endif
    </div>
    <div class="amount">{{ $final_amount }} {{ __('Saudi riyal') }}</div>
    @if ($value_in_words)
        <div>{{ $value_in_words }} {{ __('only') }}</div>
    @endif
</div>
