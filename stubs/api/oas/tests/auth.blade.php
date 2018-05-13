@switch($action->getAction())
@case("postSignUp")
    public function testSignUp()
    {
        $headers = [];

        $email    = $this->faker->email;
        $password = $this->faker->password(8);

        list($clientId, $clientSecret) = $this->getClientIdAndSecret();

        $input = [
            'name'          => $this->faker->firstName,
            'email'         => $email,
            'password'      => $password,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ];

        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            [],
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );

        $data = json_decode($response->getContent(), true);
        $this->assertResponseStatus(201);
    }
@break;
@case("postSignIn")
    public function testSignIn()
    {
        $email    = $this->faker->email;
        $password = $this->faker->password(8);
        $user     = factory(\App\Models\User::class)->create([
            'email'    => $email,
            'password' => $password,
        ]);

        $headers = [];
        list($clientId, $clientSecret) = $this->getClientIdAndSecret();

        $input = [
            'email'         => $email,
            'password'      => $password,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ];

        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            [],
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $data = json_decode($response->getContent(), true);
        $this->assertResponseStatus(200);
    }
@break;
@case("postSignOut")
    public function testSignOut()
    {
        $email    = $this->faker->email;
        $password = $this->faker->password(8);
        $user     = factory(\App\Models\User::class)->create([
            'email'    => $email,
            'password' => $password,
        ]);

        $headers = [];

        list($clientId, $clientSecret) = $this->getClientIdAndSecret();

        $input = [
            'email'         => $email,
            'password'      => $password,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ];

        $response = $this->action('POST', 'Api\{{ $versionNamespace }}\AuthController＠postSignIn',
            [],
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $data = json_decode($response->getContent(), true);
        $this->assertResponseStatus(200);

        $type  = $data['tokenType'];
        $token = $data['accessToken'];

        $headers = [
            'Authorization' => $type.' '.$token,
        ];

        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            [],
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $data = json_decode($response->getContent(), true);
        $this->assertResponseStatus(200);
    }
@break;
@case("postRefreshToken")
    public function testPostRefreshToken()
    {
        $email    = $this->faker->email;
        $password = $this->faker->password(8);
        $user     = factory(\App\Models\User::class)->create([
            'email'    => $email,
            'password' => $password,
        ]);

        $headers = [];

        list($clientId, $clientSecret) = $this->getClientIdAndSecret();

        $input = [
            'email'         => $email,
            'password'      => $password,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ];

        $response = $this->action('POST', 'Api\{{ $versionNamespace }}\AuthController＠postSignIn',
            [],
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $data = json_decode($response->getContent(), true);
        $this->assertResponseStatus(200);
        $type         = $data['tokenType'];
        $accessToken  = $data['accessToken'];
        $refreshToken = $data['refreshToken'];

        $headers = [
            'Authorization' => $type.' '.$accessToken,
        ];

        $input = [
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ];

        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            [],
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $data = json_decode($response->getContent(), true);
        $this->assertResponseStatus(200);
    }
@break;
@default
@include('api.oas.tests.unknown')
@endswitch
