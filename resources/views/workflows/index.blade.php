@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h1 class="mb-3">Workflows</h1>
  <div class="row">
    @forelse($workflows as $wf)
      <div class="col-md-6 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">{{ $wf->name }}</h5>
            <p class="card-text"><code>/api/trigger/{{ $wf->trigger_secret }}</code></p>
            <a href="{{ route('workflows.show', $wf) }}" class="btn btn-primary btn-sm">View</a>
          </div>
          <ul class="list-group list-group-flush">
            @foreach($wf->runs->take(5) as $run)
              <li class="list-group-item d-flex justify-content-between">
                <span>#{{ $run->id }} • {{ $run->created_at->format('Y-m-d H:i') }}</span>
                <span class="badge text-bg-{{ $run->status === 'succeeded' ? 'success' : ($run->status === 'failed' ? 'danger' : 'secondary') }}">
                  {{ $run->status }}
                </span>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    @empty
      <p>No workflows yet.</p>
    @endforelse
  </div>
</div>
@endsection
