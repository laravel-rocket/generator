return [
@foreach( $columns as $key => $name )
    '{{ $key }}' => '{{ $name }}',
@endforeach
@foreach( $booleans as $key => $name )
    '{{ $key }}' => '{{ $name }}',
@endforeach
@foreach( $options as $column => $option )
@if( count($option) > 0)
    '{{ $column }}_options' => [
@foreach( $option as $key => $name )
        '{{ $key }}' => '{{ $name }}',
@endforeach
    ],
@endif
@endforeach
];
