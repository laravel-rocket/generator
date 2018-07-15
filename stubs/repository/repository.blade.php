namespace App\Repositories\Eloquent;

use LaravelRocket\Foundation\Repositories\Eloquent\{{ $baseClass }};
use App\Repositories\{{ $modelName }}RepositoryInterface;
use App\Models\{{ $modelName }};

class {{ $modelName }}Repository extends {{ $baseClass }} implements {{ $modelName }}RepositoryInterface
{

@if( $relationTable )
    protected $parentKey = '{{ $parentKey }}';

    protected $childKey = '{{ $childKey }}';

@endif
    protected $querySearchTargets = [
@foreach( $keywordColumns as $index => $keywordColumn )
        '{{ $keywordColumn  }}',
@endforeach
    ];

@if( array_key_exists('getBlankModel', $existingMethods))
    {!! $existingMethods['getBlankModel'] !!}
@php
unset($existingMethods['getBlankModel']);
@endphp
@else
    public function getBlankModel()
    {
        return new {{ $modelName }}();
    }
@endif

@if( array_key_exists('rules', $existingMethods))
    {!! $existingMethods['rules'] !!}
@php
    unset($existingMethods['rules']);
@endphp
@else
    public function rules()
    {
        return [
        ];
    }
@endif

@if( array_key_exists('messages', $existingMethods))
    {!! $existingMethods['messages'] !!}
@php
    unset($existingMethods['messages']);
@endphp
@else
    public function messages()
    {
        return [
        ];
    }
@endif

@if( array_key_exists('buildQueryByFilter', $existingMethods))
{!! $existingMethods['buildQueryByFilter'] !!}
@php
    unset($existingMethods['buildQueryByFilter']);
@endphp
@else
    protected function buildQueryByFilter($query, $filter)
    {

@foreach( $relations as $relation)
@if( $relation->getType() === \LaravelRocket\Generator\Objects\Relation::TYPE_BELONGS_TO_MANY)
    if (array_key_exists('{{ $relation->getReferenceColumn()->getName() }}', $filter)) {
        $value = $filter['{{ $relation->getReferenceColumn()->getName() }}'];
        $query     = $query->whereHas('{{ $relation->getName() }}', function ($q) use ($value) {
            $q->where('id', $value);
        });
        unset($filter['{{ $relation->getReferenceColumn()->getName() }}']);
    }

@endif
@endforeach
        return parent::buildQueryByFilter($query, $filter);
    }
@endif

@foreach( $existingMethods as $name => $method )
{!! $method !!}
@endforeach
}
