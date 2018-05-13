    public function test{{ ucfirst($action->getAction()) }}()
    {

@if( $action->hasParent() )
        $parent = factory(\App\Models\{{ $action->getParentModel() }}::class)->create();
@if( $action->getParentRelation() && $action->getParentRelation()->getType() === \LaravelRocket\Generator\Objects\Relation::TYPE_BELONGS_TO_MANY)
        $variables = [];
@else
        $variables = [
            '{{ snake_case($action->getParentModel()) }}_id' => $parent->id,
        ];
@endif
@else
        $variables = [];
@endif

        $headers = $this->getAuthenticationHeaders();
        $models = factory(\App\Models\{{ $action->getResponse()->getListItem()->getModelName() }}::class, 3)->create($variables);

@if( $action->hasParent() && $action->getParentRelation() && $action->getParentRelation()->getType() === \LaravelRocket\Generator\Objects\Relation::TYPE_BELONGS_TO_MANY)
        foreach( $models as $index => $model ){
            factory(\App\Models\{{ $action->getParentRelation()->getIntermediateTableModel() }}::class, 3)->create([
                '{{ \ICanBoogie\singularize($action->getParentTable()->getName()) }}_id' => $parent->id,
                '{{ \ICanBoogie\singularize($action->getTargetTable()->getName()) }}_id' => $model->id,
            ]);
        }
@endif

        $variables = [
@if( $action->hasParent() )
@foreach( $action->getParams() as $parameter )
            '{!! $parameter->getName() !!}' => $models[0]->{!! $parameter->getName() !!},
@endforeach
@endif
        ];

        $response = $this->action('{{ strtoupper($action->getHttpMethod()) }}', 'Api\{{ $versionNamespace }}\{{ $className }}＠{{ $action->getAction() }}',
            $variables,
            [],
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );
        $this->assertResponseOk();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(3, count($data['items']));
    }
