<div class="row g-3 mb-4">
  @foreach($cards as $card)
    <div class="col-sm-6 col-xl-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <p class="text-muted mb-1">{{ $card['label'] }}</p>
              <h4 class="mb-0">{{ $card['value'] }}</h4>
              @if(!empty($card['sub']))<small class="text-muted">{{ $card['sub'] }}</small>@endif
            </div>
            <div class="align-self-center">
              {!! $card['icon'] ?? '<i class="ri-file-list-3-line fs-3 text-muted"></i>' !!}
            </div>
          </div>
        </div>
      </div>
    </div>
  @endforeach
</div>
