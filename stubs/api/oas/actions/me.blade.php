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

@if( $action->getHttpMethod() === 'get' )
        return {{ $action->getResponse()->getName() }}::updateWithModel($user)->response();
@elseif( $action->getHttpMethod() === 'put' || $action->getHttpMethod() === 'patch' )
        $data = $request->only([
@foreach($action->getResponse()->getProperties() as $property )
            '{{ $property['name'] }}',
@endforeach
        ]);
        $model = $this->userRepository->update($model, $data);

        return {{ $action->getResponse()->getName() }}::updateWithModel($model)->response();
@endif
    }
