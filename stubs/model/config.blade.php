return [
@foreach( $columns as $key => $info )
    '{{ $key }}' => [
    @if( count(array_get($info, 'options', [])) > 0 )
        'options' => [
        @foreach( array_get($info, 'options', []) as $optionKey => $optionName )
            '{{ $optionKey }}' => 'tables/{{ $tableName }}/columns.{{ $key }}.options.{{ $optionKey }}',
        @endforeach
        ],
    @endif
    ],
@endforeach
];
