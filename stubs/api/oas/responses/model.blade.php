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
@if( array_key_exists('column', $property))
                '{{ $property['name'] }}' => {!! $property['cast'] !!} $model->{{ $property['column']->getName() }},
@elseif( $property['type'] === 'object' && array_key_exists('relation', $property ))
                '{{ $property['name'] }}' => !empty($model->{{ $property['relation']->getName() }}) ? {{ $property['definition'] }}::updateWithModel($model->{{ $property['relation']->getName() }})->toArray() : null,
@elseif( $property['type'] === 'array' && array_key_exists('relation', $property ))
                '{{ $property['name'] }}' => {!! $property['default'] !!},
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
