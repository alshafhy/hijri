@if ($breaks['property_cards'] ?? true)
    <pagebreak />
@endif
<div class="rpt-property-cards">
    @foreach ($property_cards as $card)
        @include('pdf.report.partials.card', $card)
    @endforeach
</div>
