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

@if( $action->getResponse()->getType() === \LaravelRocket\Generator\Objects\OpenAPI\Definition::TYPE_MODEL )
        $model = $this->{{ lcfirst($action->->getTargetModel()) }}Repository->find($id);
        if (empty($model) ) {
            throw new APIErrorException('notFound', 'Not found');
        }

        $data = $request->only([
@foreach($action->getBodyParameters() as $parameter )
            '{{ $parameter }}',
@endforeach
        ]);
        /** @var \App\Models\{{ $action->->getTargetModel() }} $model */
        $model = $this->{{ lcfirst($action->->getTargetModel()) }}Repository->update($model, $data);

        return {{ $action->getResponse()->getName() }}::updateWithModel($model)->response();
@else
        $dataArray = [
@foreach($action->getResponse()->getProperties() as $property )
            '{{ $property['name'] }}' => $request->get('{{ $property['name'] }}',{!! $property['default'] !!}),
@endforeach
        ];
        $response = new {!! $action->getResponse()->getName() !!}($dataArray, 200);

        return $response->response();
@endif
    }
