    /**
    * PATH: {{ $action->getHttpMethod() }} {{ $action->getPath() }}
@foreach( $action->getParams() as $param )
    * @param {{ $param->getVariableType() }} {{ $param->getName() }}
@endforeach
    * @param {{ $action->getRequest()->getName() }} $request
    *
    * @return \Illuminate\Http\JsonResponse
    * @throws \App\Exceptions\Api\{{ $versionNamespace }}\APIErrorException
    */
    public function {{ $action->getAction() }}({{ implode(',', $action->getParamNames() ) }}{{ count($action->getParams()) > 0 ? ', ' : '' }}{{ $action->getRequest()->getName() }} $request)
    {
        /** @var \App\Models\User $user */
        $user = $this->userService->getUser();

@if( $action->hasParent() )
        /** @var \App\Models\{{ $action->getParentModel() }} $parent */
        $parent = $this->{{ lcfirst($action->getParentModel()) }}Repository->find($id);
        if (empty($parent) ) {
            throw new APIErrorException('notFound', 'Not found');
        }
@endif

        $data = $request->only([
@foreach($action->getBodyParameters() as $parameter )
            '{{ $parameter }}',
@endforeach
        ]);

@if( $action->hasParent() )
@if( $action->getParentRelation() && $action->getParentRelation()->getType() === \LaravelRocket\Generator\Objects\Relation::TYPE_BELONGS_TO_MANY)
@else
@foreach( $action->getParentFilters() as $key => $value )
        $data['{!! $key !!}'] = $parent->{!! $value !!};
@endforeach
@endif
@endif

        /** @var \App\Models\{{ $action->getTargetModel() }} $model */
        $model = $this->{{ lcfirst($action->getTargetModel()) }}Repository->create($data);
        if (empty($model) ) {
            throw new APIErrorException('unknown', 'Creation Failed');
        }

@if( $action->hasParent() && $action->getParentRelation() && $action->getParentRelation()->getType() === \LaravelRocket\Generator\Objects\Relation::TYPE_BELONGS_TO_MANY)
        $relation = $this->{{ lcfirst($action->getParentRelation()->getIntermediateTableModel()) }}Repository->create([
            '{{ \ICanBoogie\singularize($action->getParentTable()->getName()) }}_id' => $parent->id,
            '{{ \ICanBoogie\singularize($action->getTargetTable()->getName()) }}_id' => $model->id,
        ]);
@endif

@if( $action->getTargetModel() != $action->getResponse()->getModelName() )
        /** @var \App\Models\{{ $action->getResponse()->getModelName() }} $model */
        $model = $this->{{ lcfirst($action->getResponse()->getModelName()) }}Repository->find($id);
@endif

        return {{ $action->getResponse()->getName() }}::updateWithModel($model)->response();
    }
