    public function {{ $action->getMethod() }}({{ implode(',', $action->getParams() ) }}{{ count($action->getParams()) > 0 ? ', ' : '' }}{{ $action->getRequest()->getName() }} $request)
    {
        $token = $request->get('{{ array_get($action->getActionContext('data'), 'sns')  }}_token');

        $userId = $this->serviceAuthenticationService->getUserIdFromToken('{{ array_get($action->getActionContext('data'), 'sns')  }}', $token);
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
