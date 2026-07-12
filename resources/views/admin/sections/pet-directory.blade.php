<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
    </div>

    <div class="tabs">
        <button class="tab active" onclick="filterPets('all')" id="tab-all">All Pets <span class="muted" style="margin-left:6px">({{ count($pets_by_status['vaccinated']) + count($pets_by_status['not_vaccinated']) + count($pets_by_status['unknown']) }})</span></button>
        <button class="tab" onclick="filterPets('vaccinated')" id="tab-vaccinated">Vaccinated <span class="muted" style="margin-left:6px">({{ count($pets_by_status['vaccinated']) }})</span></button>
        <button class="tab" onclick="filterPets('not_vaccinated')" id="tab-not-vaccinated">Unvaccinated <span class="muted" style="margin-left:6px">({{ count($pets_by_status['not_vaccinated']) }})</span></button>
        <button class="tab" onclick="filterPets('unknown')" id="tab-unknown">Unknown Status <span class="muted" style="margin-left:6px">({{ count($pets_by_status['unknown']) }})</span></button>
    </div>

    <div id="pets-all" class="tab-panel">
        @php
            $allPets = collect($pets_by_status['vaccinated'])->merge($pets_by_status['not_vaccinated'])->merge($pets_by_status['unknown']);
        @endphp
        @if ($allPets->count() > 0)
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">
                @foreach ($allPets as $pet)
                    @include('admin.pet-card', ['pet' => $pet])
                @endforeach
            </div>
        @else
            <div class="muted">No pets registered yet.</div>
        @endif
    </div>

    <div id="pets-vaccinated" class="tab-panel hidden">
        @if ($pets_by_status['vaccinated']->count() > 0)
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">
                @foreach ($pets_by_status['vaccinated'] as $pet)
                    @include('admin.pet-card', ['pet' => $pet])
                @endforeach
            </div>
        @else
            <div class="muted">No vaccinated pets.</div>
        @endif
    </div>

    <div id="pets-not_vaccinated" class="tab-panel hidden">
        @if ($pets_by_status['not_vaccinated']->count() > 0)
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">
                @foreach ($pets_by_status['not_vaccinated'] as $pet)
                    @include('admin.pet-card', ['pet' => $pet])
                @endforeach
            </div>
        @else
            <div class="muted">No unvaccinated pets.</div>
        @endif
    </div>

    <div id="pets-unknown" class="tab-panel hidden">
        @if ($pets_by_status['unknown']->count() > 0)
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">
                @foreach ($pets_by_status['unknown'] as $pet)
                    @include('admin.pet-card', ['pet' => $pet])
                @endforeach
            </div>
        @else
            <div class="muted">No pets with unknown vaccination status.</div>
        @endif
    </div>
</div>
