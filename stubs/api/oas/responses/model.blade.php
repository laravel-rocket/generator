namespace App\Http\Responses\Api\{{ $versionNamespace }};

class {{ $className }} extends Response
{
    protected $columns = [
@foreach( $properties as $property)
        '{{ $property['name'] }}' => {!! $property['default'] !!},
@endforeach
    ];

    /**
     * @param \App\Models\{{ $modelName }} $model
     *
     * @return static
     */
    public static function updateWithModel($model)
    {
        $response = new static([], 400);
        if (!empty($model)) {
            $modelArray = [
@foreach( $properties as $property)
@if( array_key_exists('columnName', $property))
                '{{ $property['name'] }}' => $model->{{ $property['columnName'] }},
@else
                '{{ $property['name'] }}' => {!! $property['default'] !!},
@endif
@endforeach
            ];
            $response = new static($modelArray, 200);
        }

        return $response;
    }
}
