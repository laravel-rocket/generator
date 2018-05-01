    public function test{{ ucfirst($action->getAction()) }}()
    {
        $headers = $this->getAuthenticationHeaders();
        $response = $this->action('get', 'Api\{{ $versionNamespace }}\MeController＠getMe',
            [],
            [],
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $data = json_decode($response->getContent(), true);
        $userId = $data['id'];

@if( $action->getResponse()->getType() === \LaravelRocket\Generator\Objects\OpenAPI\Definition::TYPE_LIST )
        $models = factory(\App\Models\{{ $action->getResponse()->getListItem()->getModelName() }}::class, 3)->create([
            'user_id' => $userId,
        ]);
        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            [],
            [],
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(3, count($data['items']));
@elseif( $action->getHttpMethod() === 'post' && $action->getResponse()->getType() === \LaravelRocket\Generator\Objects\OpenAPI\Definition::TYPE_MODEL )
        $model= factory(\App\Models\{{ $action->getTargetModel() }}::class)->make([
            'user_id' => $userId,
        ]);
        $input = [
@foreach( $action->getBodyParameters() as $parameter)
            '{{ $parameter }}' => $model->{{ $parameter }},
@endforeach
        ];
        $headers = $this->getAuthenticationHeaders();
        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            [],
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
        $data = json_decode($response->getContent(), true);
@elseif( $action->getResponse()->getType() === \LaravelRocket\Generator\Objects\OpenAPI\Definition::TYPE_MODEL )
        $model = factory(\App\Models\{{ $action->getResponse()->getListItem()->getModelName() }}::class)->create([
            'user_id' => $userId,
        ]);
        $variables = [
            $model->id,
        ];
@if( $action->getHttpMethod() === 'put' || $action->getHttpMethod() === 'patch' )
        $modelData= factory(\App\Models\{{ $action->getTargetModel() }}::class)->make([
            'user_id' => $userId,
        ]);
        $input = [
        @foreach( $action->getBodyParameters() as $parameter)
            '{{ $parameter }}' => $modelData->{{ $parameter }},
        @endforeach
        ];
        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            $variables,
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
@else
        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            $variables,
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
@endif
@else
        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            $variables,
            $input,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
@endif
    }
