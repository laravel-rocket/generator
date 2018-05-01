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
        /** @var \App\Models\{{ $action->getParentTable()->getModelName() }} $parent */
        $parent = $this->{{ lcfirst($action->getParentTable()->getModelName()) }}Repository->find($id);
        if (empty($parent) ) {
            throw new APIErrorException('notFound', 'Not found');
        }
@endif

        $offset = $request->offset();
        $limit  = $request->limit();
        $filters = [
@if( $action->hasParent() )
@foreach( $action->getParentFilters() as $key => $value )
            '{!! $key !!}' => $parent->{!! $value !!},
@endforeach
@endif
        ];

        $models = $this->{{ lcfirst($action->getTargetTable()->getModelName()) }}Repository->getByFilter($filters, 'id', 'asc', $offset, $limit + 1);

        $hasNext = false;
        if (count($models) > $limit) {
            $hasNext   = true;
            $models = $models->slice(0, $limit);
        }

        return {{ $action->getResponse()->getName() }}::updateListWithModel($models, $offset, $limit, $hasNext)->response();
    }
