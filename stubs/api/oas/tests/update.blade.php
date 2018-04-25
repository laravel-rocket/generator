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
        $model= factory(\App\Models\{{ $action->getActionContext('targetModel') }}::class)->create($variables);

        $headers = $this->getAuthenticationHeaders();
        $newData= factory(\App\Models\{{ $action->getResponse()->getModelName() }}::class)->make();
        $input = $newData->toArray();
        $variables = [
@foreach( $action->getParams() as $key => $param )
            '{{ $key }}' => $model->{!! substr($param,1) !!},
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
