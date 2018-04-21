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
@if( $action->getMethod() === 'forgotPassword' )
        $email = $request->get('email');
        $this->apiUserService->sendPasswordResetEmail($email);

        return Status::ok()->response();
@endif
    }
