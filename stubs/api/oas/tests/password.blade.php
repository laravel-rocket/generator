    public function test{{ ucfirst($action->getMethod()) }}()
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

