{{-- docs/livewire-example.blade.php --}}
<div>
    @foreach ($overview->hits() as $voter)
        <div>{{ $voter['_source']['name'] }}</div>
    @endforeach

    @foreach ($overview->aggregations['party_counts']['buckets'] ?? [] as $bucket)
        <span>{{ $bucket['key'] }}: {{ $bucket['doc_count'] }}</span>
    @endforeach
</div>
