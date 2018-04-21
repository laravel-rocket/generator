@switch($action->getMethod())
@case("postSignUp")
    /**
    * @param \App\Http\Requests\Api\V1\Auth\SignUpRequest $request
    *
    * @return \Illuminate\Http\JsonResponse
    *
    * @throws \App\Exceptions\Api\V1\APIErrorException
    * @throws \League\OAuth2\Server\Exception\OAuthServerException
    */
    public function postSignUp(SignUpRequest $request)
    {
        $input = $request->only([
            'name',
            'email',
            'gender',
            'phone_number',
            'password',
        ]);

        /** @var \App\Models\User $user */
        $user = $this->userService->signUp($input);
        if (empty($user)) {
        throw new APIErrorException('authFailed', 'Register failed', []);
        }

        $params               = $request->all();
        $params['username']   = $params['email'];
        $params['grant_type'] = 'password';
        $params['scope']      = '';

        $serverRequest = PsrServerRequest::createFromRequest($request, $params);

        $response = $this->server->respondToAccessTokenRequest($serverRequest, new Psr7Response);

        return AccessToken::updateWithResponse($response)->withStatus(201)->response();
    }

@break;
@case("postSignIn")
    /**
    * @param \App\Http\Requests\Api\V1\Auth\SignInRequest $request
    *
    * @return \Illuminate\Http\JsonResponse
    *
    * @throws \App\Exceptions\Api\V1\APIErrorException
    * @throws \League\OAuth2\Server\Exception\OAuthServerException
    */
    public function postSignIn(SignInRequest $request)
    {
        $inputCheckUser             = $request->only('email', 'password');
        $user                       = $this->userService->signIn($inputCheckUser);
        if (empty($user)) {
            throw new APIErrorException('signInFailed', '', []);
        }
        $serverRequest = PsrServerRequest::createFromRequest($request, $request->all() + [
            'username'   => $request->get('email', ''),
            'grant_type' => 'password',
            'scope'      => '',
        ]);
        $response = $this->server->respondToAccessTokenRequest($serverRequest, new Psr7Response);

        return AccessToken::updateWithResponse($response)->response();
    }
@break;
@case("postSignOut")
    public function postSignOut()
    {
        $this->userService->signOut();

        return Status::ok()->response();
    }
@break;
@case("postRefreshToken")
    /**
    * @param \App\Http\Requests\API\V1\Auth\RefreshTokenRequest $request
    *
    * @return \Psr\Http\Message\ResponseInterface
    * @throws \League\OAuth2\Server\Exception\OAuthServerException
    */
    public function postRefreshToken(RefreshTokenRequest $request)
    {
        $serverRequest = PsrServerRequest::createFromRequest($request);

        return $this->server->respondToAccessTokenRequest($serverRequest, new Psr7Response);
    }
@break;
@endswitch
