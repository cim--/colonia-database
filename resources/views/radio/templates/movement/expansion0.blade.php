<p>{{$expanding->faction->name}} has announced its intent to bid for additional partner organisation contracts. Currently, they operate in
  @if ($expanding->faction->systemCount() > 1)
  {{$expanding->faction->systemCount()}} systems and are keen to open further branch offices.
  @else
  just their home system of {{$expanding->faction->system->name}}, and this change marks an increase in their ambitions.
  @endif
</p>

<p>Materials for construction and outfitting of their new offices are being accumulated at {{$picker->pickFrom($expanding->faction->stations()->tradable()->orderBy('id')->get())->name}}, with temporary shortages and high prices for a range of goods.</p>
