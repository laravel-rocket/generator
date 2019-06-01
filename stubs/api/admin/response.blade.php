namespace App\Http\Responses\Api\Admin;

class {{ $className }} extends Response
{
    protected $columns = [
@foreach( $table->getColumns() as $column )
@if( $column->isAPIReturnable())
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
@if( $column->isAPIReturnable())
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
                '{{ $relation->getName() }}' => !empty($model->{{ $relation->getName() }}) ? {{ ucfirst(\Illuminate\Support\Str::camel($relation->getName())) }}::updateWithModels($model->{{ \Illuminate\Support\Str::camel($relation->getName()) }}) : null,
@elseif( $relation->isImage() )
                '{{ $relation->getName() }}' => empty($model->{{ \Illuminate\Support\Str::camel($relation->getName()) }}) ? null : Image::updateWithModel($model->{{ \Illuminate\Support\Str::camel($relation->getName()) }}),
@else
                '{{ $relation->getName() }}' => empty($model->{{ \Illuminate\Support\Str::camel($relation->getName()) }}) ? null : {{ ucfirst(\Illuminate\Support\Str::camel(\ICanBoogie\singularize($relation->getReferenceModel()))) }}::updateWithModel($model->{{ \Illuminate\Support\Str::camel($relation->getName()) }}),
@endif
@endif
@endforeach
            ];
            $response   = new static($modelArray, 200);
        }

        return $response;
    }
}
