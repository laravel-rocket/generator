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
    {!! \ArrayHelper::popWithKey($existingMethods, 'getBlankModel') !!}
@else
    public function getBlankModel()
    {
        return new {{ $modelName }}();
    }
@endif

@if( array_key_exists('rules', $existingMethods))
    {!! \ArrayHelper::popWithKey($existingMethods, 'rules') !!}
@else
    public function rules()
    {
        return [
        ];
    }
@endif

@if( array_key_exists('messages', $existingMethods))
    {!! \ArrayHelper::popWithKey('messages', $existingMethods) !!}
@else
    public function messages()
    {
        return [
        ];
    }
@endif

@if( array_key_exists('buildQueryByFilter', $existingMethods))
    {!! \ArrayHelper::popWithKey('buildQueryByFilter', $existingMethods) !!}
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
{!! $method !!}
@endforeach
}
