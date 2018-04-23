    public function test{{ ucfirst($action->getMethod()) }}()
    {
        $headers = $this->getAuthenticationHeaders();
        $variables = [
        @foreach( $action->getParams() as $index => $param )
            0,
        @endforeach
        ];
        $model= factory(\App\Models\{{ $action->getResponse()->getModelName() }}::class)->make($variables);
        $input = $model->toArray();
        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getMethod() }}',
            $variables,
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($models[0]->id, $data['id']);
    }
