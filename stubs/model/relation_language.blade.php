return [
@foreach( $relations as $key => $info )
    '{{ $key }}' => [
        'name' => '{{ array_get($info, 'viewName', $key) }}',
    ],
@endforeach
];
