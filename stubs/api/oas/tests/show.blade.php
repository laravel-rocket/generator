    public function test{{ ucfirst($action->getAction()) }}()
    {
        $headers = $this->getAuthenticationHeaders();

@if( $action->hasParent() )
        $parent = factory(\App\Models\{{ $action->getParentTable()->getModelName() }}::class)->create();
        $variables = [
            '{{ snake_case($action->getParentTable()->getModelName()) }}_id' => $parent->id,
        ];
@else
        $variables = [];
@endif
        $models = factory(\App\Models\{{ $action->getResponse()->getModelName() }}::class, 3)->create($variables);

        $variables = [
@foreach( $action->getParams() as $parameter )
            '{!! $parameter->getName() !!}' => $models[0]->{!! $parameter->getName() !!},
@endforeach
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
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($models[0]->id, $data['id']);
    }
