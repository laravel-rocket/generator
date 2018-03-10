namespace App\Http\Responses\Api\Admin;

class {{ $className }} extends Response
{
    protected $columns = [
@foreach( $table->getColumns() as $column )
@if( !$column->hasRelation() )
        '{{ $column->getAPIName() }}' => {{ $column->getDefaultValue() }},
@else
        '{{ $column->getAPIName() }}' => null,
@endif
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
        if(!empty($model)) {
            $modelArray = [
@foreach( $table->getColumns() as $column )
@if( !$column->hasRelation() )
                '{{ $column->getAPIName() }}' => $model->{{ $column->getName() }},

@endif
@endforeach
@foreach( $table->getRelations() as $relation )
@if( $relation->shouldIncludeInAPI())
@if( $relation->isMultipleSelection())
                '{{ $relation->getName() }}' => !empty($model->{{ $relation->getName() }}) ? {{ ucfirst(camel_case($relation->getName())) }}::updateWithModel($model->{{ camel_case($relation->getName()) }} : null,
@else
                '{{ $relation->getName() }}' => {{ ucfirst(camel_case(\ICanBoogie\singularize($relation->getName()))) }}::updateWithModels($model->{{ camel_case($relation->getName()) }},
@endif
@endif
@endforeach
            ];
            $response   = new static($modelArray, 200);
        }

        return $response;
    }
}
