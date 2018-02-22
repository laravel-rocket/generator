namespace App\Repositories\Eloquent;

use LaravelRocket\Foundation\Repositories\Eloquent\{{ $baseClass }};
use App\Repositories\{{ $modelName }}RepositoryInterface;
use App\Models\{{ $modelName }};

class {{ $modelName }}Repository extends {{ $baseClass }} implements {{ $modelName }}RepositoryInterface
{

    protected $searchTargetColumns = [
@foreach( $keywordColumns as $index => $keywordColumn )
        '{{ $keywordColumn  }}',
@endforeach
    ];

@if( array_key_exists('getBlankModel', $existingMethods))
    {!! $existingMethods['getBlankModel'] !!}
@else
    public function getBlankModel()
    {
        return new {{ $modelName }}();
    }
@endif

@if( array_key_exists('rules', $existingMethods))
    {!! $existingMethods['rules'] !!}
@else
    public function rules()
    {
        return [
        ];
    }
@endif

@if( array_key_exists('messages', $existingMethods))
    {!! $existingMethods['messages'] !!}
@else
    public function messages()
    {
        return [
        ];
    }
@endif

@if( array_key_exists('buildQueryByFilter', $existingMethods))
{!! $existingMethods['buildQueryByFilter'] !!}
@else
    protected function buildQueryByFilter($query, $filter)
    {
        if( count($this->searchTargetColumns) > 0 ){
            if (array_key_exists('query', $filter)) {
                $searchWord = array_get($filter, 'query');
                if (!empty($searchWord)) {
                    $query = $query->where(function ($q) use ($searchWord) {
                        $q = $q->where($this->searchKeys[0], 'LIKE', '%'.$searchWord.'%');
                        foreach( $this->searchTargetColumns as $index => $column ){
                            if( $index > 0 ){
                                $q =$q->orWhere($column, 'LIKE', '%'.$searchWord.'%');
                            }
                        }
                    });
                }
                unset($filter['query']);
            }
        }

        return parent::buildQueryByFilter($query, $filter);
    }
@endif

@foreach( $existingMethods as $name => $method )
@if( !in_array($name, ['buildQueryByFilter','messages','rules','getBlankModel']))
{!! $method !!}
@endif
@endforeach
}
