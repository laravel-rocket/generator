    public function test{{ ucfirst($action->getMethod()) }}()
    {
        $headers = $this->getAuthenticationHeaders();
        $model= factory(\App\Models\{{ $action->getResponse()->getModelName() }}::class)->create();
        $newData= factory(\App\Models\{{ $action->getResponse()->getModelName() }}::class)->make();
        $input = $newData->toArray();
        $variables = [
@foreach( $action->getParams() as $index => $param )
            $model->{{ substr($param,1) }},
@endforeach
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
    }
