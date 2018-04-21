    /**
    * PATH: {{ $action->getHttpMethod() }} {{ $action->getPath() }}
    @foreach( $action->getParams() as $param )
        * @param {{ $param }}
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

        $offset = $request->offset();
        $limit  = $request->limit();
        $filters = [
    @foreach( $action->getActionContext('parentFilters') as $key => $value )
            '{!! $key !!}' => {!! $value !!},
    @endforeach
        ];

        $models = $this->{{ lcfirst($action->getRepositoryName()) }}->getByFilter($filters, 'id', 'asc', $offset, $limit + 1);

        $hasNext = false;
        if (count($models) > $limit) {
            $hasNext   = true;
            $models = $models->slice(0, $limit);
        }

        return {{ $action->getResponse()->getName() }}::updateListWithModel($models, $offset, $limit, $hasNext)->response();
    }
