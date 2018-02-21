return [
@foreach( $columns as $key => $info )
    '{{ $key }}' => [
    @if( count(array_get($info, 'options', [])) > 0 )
        'options' => [
        @foreach( array_get($info, 'options', []) as $optionKey => $optionName )
            '{{ $key }}' => 'tables/{{ $tableName }}/columns.{{ $key }}.{{ $optionKey }}.name',
        @endforeach
        ],
    @endif
    ],
@endforeach
];
