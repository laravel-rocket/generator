namespace App\Http\Responses\Api\{{ $versionNamespace }};

class {{ $className }} extends Response
{
    protected $columns = [
@foreach( $properties as $property)
        '{{ $property['name'] }}' => {!! $property['default'] !!},
@endforeach
    ];

    /**
     * @param array $array
     *
     * @return static
     */
    public static function updateWithResponse($array)
    {
        $response = new static([], 400);
        if (!empty($array)) {
            $modelArray = [
@foreach( $properties as $property)
                '{{ $property['name'] }}' => {!! $property['cast'] !!} \Illuminate\Support\Arr::get($array, '{!! $property['name'] !!}', {!! $property['default'] !!}),
@endforeach
            ];
            $response = new static($modelArray, 200);
        }

        return $response;
    }
}
