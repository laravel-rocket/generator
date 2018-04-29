    public function test{{ ucfirst($action->getAction()) }}()
    {
        $headers = $this->getAuthenticationHeaders();
        $variables = [
        ];
        $input = [
        ];

        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            $variables,
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
    }
