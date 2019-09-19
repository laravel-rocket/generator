return [
@foreach( $columns as $key => $info )
    '{{ $key }}' => [
    @if( count(\Illuminate\Support\Arr::get($info, 'options', [])) > 0 )
        'options' => [
        @foreach( \Illuminate\Support\Arr::get($info, 'options', []) as $optionKey => $optionName )
            '{{ $optionKey }}' => 'tables/{{ $tableName }}/columns.{{ $key }}.options.{{ $optionKey }}',
        @endforeach
        ],
    @endif
    ],
@endforeach
];
