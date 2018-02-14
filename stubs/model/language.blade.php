return [
@foreach( $columns as $key => $info )
    '{{ $key }}' => [
        'name' => '{{ array_get($info, 'name' }}',
@if( count(array_get($info, 'booleans', [])) > 0 )
        'booleans' => [
@foreach( array_get($info, 'booleans', []) as $key => $name )
            '{{ $key }} => '{{ $name }}',
@endforeach
        ],
@endif
    @if( count(array_get($info, 'options', [])) > 0 )
        'options' => [
        @foreach( array_get($info, 'options', []) as $key => $name )
            '{{ $key }} => '{{ $name }}',
        @endforeach
        ],
    @endif
    ],
@endforeach
];
