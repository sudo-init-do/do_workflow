@extends('layouts.app')

@section('content')
<div class="container py-4">
  <a href="{{ route('workflows.index') }}" class="btn btn-link">&larr; Back</a>
  <h1 class="mb-3">{{ $workflow->name }}</h1>

  <div class="mb-4">
    <h5>Trigger URL (secret)</h5>
    <code>{{ url('/api/trigger/'.$workflow->trigger_secret) }}</code>
  </div>

  <div class="mb-4">
    <h5>Actions</h5>
    <ul class="list-group">
      @forelse($workflow->actions as $a)
        <li class="list-group-item">
          <strong>#{{ $a->order }} {{ $a->type }}</strong>
          <pre class="mb-0 small">{{ json_encode($a->config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
        </li>
      @empty
        <li class="list-group-item">No actions defined.</li>
      @endforelse
    </ul>
  </div>

  <div>
    <h5>Recent Runs</h5>
    <ul class="list-group">
      @forelse($workflow->runs as $run)
        <li class="list-group-item">
          <div class="d-flex justify-content-between">
            <span>#{{ $run->id }} • {{ $run->created_at->format('Y-m-d H:i:s') }}</span>
            <span class="badge text-bg-{{ $run->status === 'succeeded' ? 'success' : ($run->status === 'failed' ? 'danger' : 'secondary') }}">
              {{ $run->status }}
            </span>
          </div>
          @if($run->error)
            <pre class="text-danger small mt-2">{{ $run->error }}</pre>
          @endif
          @if($run->result)
            <pre class="small mt-2">{{ json_encode($run->result, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
          @endif
        </li>
      @empty
        <li class="list-group-item">No runs yet.</li>
      @endforelse
    </ul>
  </div>
</div>
@endsection
