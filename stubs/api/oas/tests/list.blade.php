    public function test{{ ucfirst($action->getMethod()) }}()
    {
        $headers = $this->getAuthenticationHeaders();
        $models[] = factory(\App\Models\{{ $action->getResponse()->getListItem()->getModelName() }}::class, 3)->create();
        $variables = [
        @foreach( $action->getParams() as $index => $param )
            0,
        @endforeach
        ];
        $input = [
        ];

        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getMethod() }}',
            $variables,
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(3, count($data['items']));
    }
