return [
@foreach( $columns as $key => $info )
    '{{ $key }}' => [
        'name' => '{{ \Illuminate\Support\Arr::get($info, 'name', $key) }}',
@if( count(\Illuminate\Support\Arr::get($info, 'booleans', [])) > 0 )
        'booleans' => [
@foreach( \Illuminate\Support\Arr::get($info, 'booleans', []) as $booleanKey => $booleanName )
            '{{ $booleanKey }}' => '{{ $booleanName }}',
@endforeach
        ],
@endif
@if( count(\Illuminate\Support\Arr::get($info, 'options', [])) > 0 )
        'options' => [
@foreach( \Illuminate\Support\Arr::get($info, 'options', []) as $optionKey => $optionName )
            '{{ $optionKey }}' => '{{ $optionName }}',
@endforeach
        ],
@endif
    ],
@endforeach
];
