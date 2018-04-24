    /**
    * PATH: {{ $action->getHttpMethod() }} {{ $action->getPath() }}
    @foreach( $action->getParams() as $param )
        * @param mixed {{ $param }}
    @endforeach
    * @param {{ $action->getRequest()->getName() }} $request
    *
    * @return \Illuminate\Http\JsonResponse
    * @throws \App\Exceptions\Api\{{ $versionNamespace }}\APIErrorException
    */
    public function {{ $action->getMethod() }}({{ implode(',', $action->getParams() ) }}{{ count($action->getParams()) > 0 ? ', ' : '' }}{{ $action->getRequest()->getName() }} $request)
    {
        /** @var \App\Models\User $user */
        $user = $this->userService->getUser();

        $data = $request->only([
    @foreach($action->getBodyParameters() as $parameter )
            '{{ $parameter }}',
    @endforeach
        ]);

    @foreach( $action->getActionContext('parentFilters', []) as $key => $value )
        $data['{!! $key !!}'] = {!! $value !!};
    @endforeach

        /** @var \App\Models\{{ $action->getResponse()->getModelName() }} $model */
        $model = $this->{{ lcfirst($action->getResponse()->getModelName()) }}Repository->create($data);
        if (empty($model) ) {
            throw new APIErrorException('unknown', 'Creation Failed');
        }

        return {{ $action->getResponse()->getName() }}::updateWithModel($model)->response();
    }
