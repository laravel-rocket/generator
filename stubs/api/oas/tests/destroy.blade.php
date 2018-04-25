    public function test{{ ucfirst($action->getMethod()) }}()
    {
        $headers = $this->getAuthenticationHeaders();
        $models = factory(\App\Models\{{ $action->getTargetModel() }}::class, 3)->create();
        $variables = [
@foreach( $action->getParams() as $index => $param )
@if( $index === count($action->getParams()) - 1)
            $models[0]->{{ substr($param,1) }},
@else
@endif
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
    }
