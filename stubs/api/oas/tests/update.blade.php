    public function test{{ ucfirst($action->getAction()) }}()
    {

        $model= factory(\App\Models\{{ $action->getTargetModel() }}::class)->create($variables);

        $headers = $this->getAuthenticationHeaders();
        $newData= factory(\App\Models\{{ $action->getResponse()->getModelName() }}::class)->make();
        $input = [
@foreach( $action->getBodyParameters() as $parameter)
            '{{ $parameter }}' => $newData->{{ $parameter }},
@endforeach
        ];
        $variables = [
@foreach( $action->getParams() as $parameter )
            '{!! $parameter->getName() !!}' => $model->{!! $parameter->getName() !!},
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
        $data = json_decode($response->getContent(), true);
    }
