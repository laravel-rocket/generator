return [
@foreach( $columns as $key => $info )
    '{{ $key }}' => [
    @if( count(array_get($info, 'options', [])) > 0 )
        'options' => [
        @foreach( array_get($info, 'options', []) as $key => $name )
            '{{ $key }}' => 'tables/{{ $tableName }}/columns.{{ $key }}.name',
        @endforeach
        ],
    @endif
    ],
@endforeach
];
