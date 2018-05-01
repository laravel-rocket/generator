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

@if( $action->getHttpMethod() === 'get' )
        return {{ $action->getResponse()->getName() }}::updateWithModel($user)->response();
@elseif( $action->getHttpMethod() === 'put' || $action->getHttpMethod() === 'patch' )
        $data = $request->only([
@foreach($action->getBodyParameters() as $parameter )
            '{{ $parameter }}',
@endforeach
        ]);
        $user = $this->userRepository->update($user, $data);

        return {{ $action->getResponse()->getName() }}::updateWithModel($user)->response();
@endif
    }
