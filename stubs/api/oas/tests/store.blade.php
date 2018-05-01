    public function test{{ ucfirst($action->getAction()) }}()
    {

@if( $action->hasParent() )
        $parent = factory(\App\Models\{{ $action->->getParentModel() }}::class)->create();
@if( $action->getParentRelation() && $action->getParentRelation()->getType() === \LaravelRocket\Generator\Objects\Relation::TYPE_BELONGS_TO_MANY)
@else
        $variables = [
            '{{ snake_case($action->->getParentModel()) }}_id' => $parent->id,
        ];
@endif
@else
        $variables = [];
@endif

        $model= factory(\App\Models\{{ $action->->getTargetModel() }}::class)->make($variables);

@if( $action->hasParent() && $action->getParentRelation() && $action->getParentRelation()->getType() === \LaravelRocket\Generator\Objects\Relation::TYPE_BELONGS_TO_MANY)
        $relation = factory(\App\Models\{{ $action->getParentRelation()->getIntermediateTableModel() }}::class)->make([
            '{{ \ICanBoogie\singularize($action->getParentTable()->getName() }}_id' => $parent->id,
            '{{ \ICanBoogie\singularize($action->getTargetTable()->getName() }}_id' => $model->id,
        ]);
@endif

        $input = [
@foreach( $action->getBodyParameters() as $parameter)
            '{{ $parameter }}' => $model->{{ $parameter }},
@endforeach
        ];
        $headers = $this->getAuthenticationHeaders();
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
