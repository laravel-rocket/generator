return [
@foreach( $columns as $key => $name )
    '{{ $key }}' => '{{ $name }}',
@endforeach
];
