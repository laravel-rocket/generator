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

        $model = $this->{{ lcfirst($action->getTargetModel()) }}Repository->find($id);
        if (empty($model) ) {
            throw new APIErrorException('notFound', 'Not found');
        }
@if( $action->hasParent() )
@foreach( $action->getParentFilters() as $key => $value )
        if( $model->{{ $key }} != $parent->{!! $value !!} ) {
            throw new APIErrorException('notFound', 'Not found');
        }
@endforeach
@endif

        $this->{{ lcfirst($action->getTargetModel()) }}Repository->delete($model);

        return Status::ok()->response();
    }
