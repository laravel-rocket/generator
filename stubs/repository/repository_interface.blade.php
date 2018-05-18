namespace App\Repositories;

use LaravelRocket\Foundation\Repositories\{{ $baseClass }}Interface;
/**
 *
 * ＠method \App\Models\{{ $modelName }}[]|\Illuminate\Database\Eloquent\Collection getEmptyList()
 * ＠method \App\Models\{{ $modelName }}[]|\Traversable|array|\Illuminate\Database\Eloquent\Collection all($order = null, $direction = null)
 * ＠method \App\Models\{{ $modelName }}[]|\Traversable|array|\Illuminate\Database\Eloquent\Collection get($order, $direction, $offset, $limit, $before = 0)
 * ＠method \App\Models\{{ $modelName }} create($value)
 * ＠method \App\Models\{{ $modelName }} find($id)
 * ＠method \App\Models\{{ $modelName }}[]|\Traversable|array|\Illuminate\Database\Eloquent\Collection allByIds($ids, $order = null, $direction = null, $reorder = false)
 * ＠method \App\Models\{{ $modelName }}[]|\Traversable|array|\Illuminate\Database\Eloquent\Collection getByIds($ids, $order = null, $direction = null, $offset = null, $limit = null);
 * ＠method \App\Models\{{ $modelName }} update($model, $input)
 * ＠method \App\Models\{{ $modelName }} save($model);
 * ＠method \App\Models\{{ $modelName }} firstByFilter($filter);
 * ＠method \App\Models\{{ $modelName }}[]|\Traversable|array|\Illuminate\Database\Eloquent\Collection getByFilter($filter,$order = null, $direction = null, $offset = null, $limit = null, $before = 0);
 * ＠method \App\Models\{{ $modelName }}[]|\Traversable|array|\Illuminate\Database\Eloquent\Collection allByFilter($filter,$order = null, $direction = null);
 */

interface {{ $modelName }}RepositoryInterface extends {{ $baseClass }}Interface
{
    /**
     * @return \App\Models\{{ $modelName }}
     */
    public function getBlankModel();

@foreach( $existingMethods as $name => $method )
@if( !in_array($name, ['getBlankModel']))
{!! $method !!}
@endif
@endforeach

}
