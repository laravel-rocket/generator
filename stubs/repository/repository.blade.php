namespace App\Repositories\Eloquent;

use LaravelRocket\Foundation\Repositories\Eloquent\{{ $baseClass }};
use App\Repositories\{{ $modelName }}RepositoryInterface;
use App\Models\{{ $modelName }};

class {{ $modelName }}Repository extends {{ $baseClass }} implements {{ $modelName }}RepositoryInterface
{

    public function getBlankModel()
    {
        return new {{ $modelName }}();
    }

    public function rules()
    {
        return [
        ];
    }

    public function messages()
    {
        return [
        ];
    }

    protected function buildQueryByFilter($query, $filter)
    {
@if(count($keywordColumns)>0)
        if (array_key_exists('query', $filter)) {
            $searchWord = array_get($filter, 'query');
            if (!empty($searchWord)) {
                $query = $query->where(function ($q) use ($searchWord) {
                    $q->where('{{ $keywordColumns[0] }}', 'LIKE', '%'.$searchWord.'%')
@foreach( $keywordColumns as $index => $keywordColumn )
@if( $index > 0 )
                    ->orWhere('{{ $keywordColumn }}', 'LIKE', '%'.$searchWord.'%')
@endif
@endforeach
                ;
                });
                unset($filter['query']);
            }
        }
@endif

        return parent::buildQueryByFilter($query, $filter);
    }

}
