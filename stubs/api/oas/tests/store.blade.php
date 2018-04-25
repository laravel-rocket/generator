    public function test{{ ucfirst($action->getMethod()) }}()
    {

@if( $action->hasParent() )
        $parent = factory(\App\Models\{{ $action->getParentModel() }}::class)->create();
        $variables = [
        '{{ snake_case($action->getParentModel()) }}_id' => $parent->id,
        ];
@else
        $variables = [];
@endif

        $model= factory(\App\Models\{{ $action->getTargetModel() }}::class)->make($variables);
        $input = [
@foreach( $action->getBodyParameters() as $parameter)
            '{{ $parameter }}' => $model->{{ $parameter }},
@endforeach
        ];
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
    }
