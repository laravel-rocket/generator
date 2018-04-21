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

        $model = $this->{{ lcfirst(array_get($action->getActionContext('data'), 'model', '')) }}Repository->find($id);
        if (empty($model) ) {
            throw new APIErrorException('notFound', 'Not found');
        }

        $this->{{ lcfirst(array_get($action->getActionContext('data'), 'model', '')) }}Repository->delete($model);

        return Status::ok()->response();
    }
