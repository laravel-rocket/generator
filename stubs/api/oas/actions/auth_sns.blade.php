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
        $token = $request->get('{{ $action->getSnsName()  }}_token');

        $userId = $this->serviceAuthenticationService->getUserIdFromToken('{{ $action->getSnsName()  }}', $token);
        $user   = $this->userRepository->find($userId);

        if (empty($user)) {
            throw new APIErrorException('authFailed');
        }
        $password          = $user->password;
        $temporaryPassword = str_random(16);
        $this->userRepository->update($user, [
            'password' => $temporaryPassword,
        ]);
        $params = [
            'username'      => $user->email,
            'password'      => $temporaryPassword,
            'scope'         => '',
            'grant_type'    => 'password',
            'client_id'     => $request->get('client_id'),
            'client_secret' => $request->get('client_secret'),
        ];
        try {
            $serverRequest = PsrServerRequest::createFromRequest($request, $params);
            $response      = $this->server->respondToAccessTokenRequest($serverRequest, new Psr7Response);
            $this->userRepository->updateRawPassword($user, $password);
        } catch (\Exception $e) {
            $this->userRepository->updateRawPassword($user, $password);
            throw $e;
        }

        return AccessToken::updateWithResponse($response)->response();
    }
