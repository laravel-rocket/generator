    public function test{{ ucfirst($action->getMethod()) }}()
    {

@if( $action->hasParent() )
        $parent = factory(\App\Models\{{ array_get($action->getActionContext('parentModel'), 'model', 'Base') }}::class)->create();
        $variables = [
        @foreach( $action->getActionContext('parentFilter') as $index => $param )
            $model->{{ substr($param,1) }},
        @endforeach
        ];
@else
        $variables = [
        @foreach( $action->getParams() as $index => $param )
            $model->{{ substr($param,1) }},
        @endforeach
        ];
@endif
        $model= factory(\App\Models\{{ $action->getResponse()->getModelName() }}::class)->make($variables);
        $input = $model->toArray();
        $headers = $this->getAuthenticationHeaders();
        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getMethod() }}',
            $variables,
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($model->id, $data['id']);
    }
