return [
@foreach( $relations as $key => $info )
    '{{ array_get($info, 'name', $key) }}' => [
        'name' => '{{ array_get($info, 'viewName', array_get($info, 'name', $key)) }}',
    ],
@endforeach
];
