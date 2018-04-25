    public function test{{ ucfirst($action->getMethod()) }}()
    {

@if( $action->hasParent() )
        $parent = factory(\App\Models\{{ $action->getActionContext('parentModel') }}::class)->create();
        $variables = [
        @foreach( $action->getActionContext('parentFilters', []) as $key => $param )
            '{{ $key }}' => $parent->{!! substr($param,1)  !!},
        @endforeach
        ];
@else
        $variables = [
        @foreach( $action->getParams() as $key => $param )
            '$key' => $model->{!! substr($param,1) !!},
        @endforeach
        ];
@endif
        $model= factory(\App\Models\{{ $action->getActionContext('targetModel') }}::class)->make($variables);
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
