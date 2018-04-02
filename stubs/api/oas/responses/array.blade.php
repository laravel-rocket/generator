namespace App\Http\Responses\Api\{{ $versionNamespace }};

class {{ $className }} extends Response
{
    protected $columns = [
@foreach( $properties as $property)
        '{{ $property['name'] }}' => {{ $property['default'] }},
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
        if (!empty($body)) {
            $modelArray = [
@foreach( $properties as $property)
                '{{ $property['name'] }}' => array_get($array, '{{ $property['name'] }}', {{ $property['default'] }}),
@endforeach
            ];
            $response = new static($modelArray, 200);
        }

        return $response;
    }
}
