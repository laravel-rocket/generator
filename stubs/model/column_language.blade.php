return [
@foreach( $columns as $key => $info )
    '{{ $key }}' => [
        'name' => '{{ array_get($info, 'name', $key) }}',
@if( count(array_get($info, 'booleans', [])) > 0 )
        'booleans' => [
@foreach( array_get($info, 'booleans', []) as $booleanKey => $booleanName )
            '{{ $booleanKey }}' => '{{ $booleanName }}',
@endforeach
        ],
@endif
@if( count(array_get($info, 'options', [])) > 0 )
        'options' => [
@foreach( array_get($info, 'options', []) as $optionKey => $optionName )
            '{{ $optionKey }}' => '{{ $optionName }}',
@endforeach
        ],
@endif
    ],
@endforeach
];
