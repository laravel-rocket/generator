@switch($action->getMethod())
@case("getMe")
    public function testGetMe()
    {
        $headers = $this->getAuthenticationHeaders();

        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getMethod() }}',
            [],
            [],
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
    }
@break;
@case("putMe")
    public function testPutMe()
    {
        $headers = $this->getAuthenticationHeaders();
        $email = $this->faker->email;

        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getMethod() }}',
            [],
            [
                'email' => $email;
            ],
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
    }
@break;
@default
@include('api.oas.tests.unknown')
@endswitch
