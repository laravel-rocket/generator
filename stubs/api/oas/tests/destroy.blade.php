    public function test{{ ucfirst($action->getAction()) }}()
    {
        $headers = $this->getAuthenticationHeaders();
        $models = factory(\App\Models\{{ $action->->getTargetModel() }}::class, 3)->create();
        $variables = [
@foreach( $action->getParams() as $index => $param )
@if( $index === count($action->getParams()) - 1)
            $models[0]->{{ $param->getName() }},
@else
@endif
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
    }
