return [
@foreach( $relations as $key => $relation )
    '{{ $relation->getName() }}' => [
        'name' => '{{ $relation->getDisplayName() }}',
    ],
@endforeach
];
