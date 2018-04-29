    public function test{{ ucfirst($action->getAction()) }}()
    {
        $headers = $this->getAuthenticationHeaders();

        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            [],
            [],
            [],
            [],
            $this->transformHeadersToServerVars($headers)
            );
        $this->assertResponseOk();
    }

