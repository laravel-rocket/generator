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
        $data = $request->only([
    @foreach($action->getResponse()->getProperties() as $property )
            '{{ $property['name'] }}',
    @endforeach
        ]);

    @foreach( $action->getActionContext('parentFilters') as $key => $value )
        $data['{!! $key !!}'] = {!! $value !!};
    @endforeach

        $model = $this->{{ lcfirst($action->getResponse()->getModelName()) }}Repository->create($data);
        if (empty($model) ) {
            throw new APIErrorException('unknown', 'Creation Failed');
        }

        return {{ $action->getResponse()->getName() }}::updateWithModel($model)->response();
    }
