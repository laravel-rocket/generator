namespace App\Http\Responses\Api\Admin;

class {{ $className }} extends Response
{
    protected $columns = [
@foreach( $table->getColumns() as $column )
@if( !$column->hasRelation() && $column->isAPIReturnable())
        '{{ $column->getAPIName() }}' => {!! $column->getDefaultAPIResponse() !!},
@endif
@endforeach
@foreach( $table->getRelations() as $relation )
@if( $relation->shouldIncludeInAPI())
@if( $relation->isMultipleSelection())
        '{{ $relation->getAPIName() }}' => [],
@else
        '{{ $relation->getAPIName() }}' => null,
@endif
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
@if( !$column->hasRelation() && $column->isAPIReturnable())
@if( $column->isTimestamp())
                '{{ $column->getAPIName() }}' => $model->{{ $column->getName() }} ? $model->{{ $column->getName() }}->timestamp : null,
@else
                '{{ $column->getAPIName() }}' => $model->{{ $column->getName() }},
@endif
@endif
@endforeach
@foreach( $table->getRelations() as $relation )
@if( $relation->shouldIncludeInAPI())
@if( $relation->isMultipleSelection())
                '{{ $relation->getName() }}' => !empty($model->{{ $relation->getName() }}) ? {{ ucfirst(camel_case($relation->getName())) }}::updateWithModel($model->{{ camel_case($relation->getName()) }}) : null,
@elseif( $relation->isImage() )
                '{{ $relation->getName() }}' => Image::updateWithModel($model->{{ camel_case($relation->getName()) }}),
@else
                '{{ $relation->getName() }}' => {{ ucfirst(camel_case(\ICanBoogie\singularize($relation->getReferenceModel()))) }}::updateWithModel($model->{{ camel_case($relation->getName()) }}),
@endif
@endif
@endforeach
            ];
            $response   = new static($modelArray, 200);
        }

        return $response;
    }
}
