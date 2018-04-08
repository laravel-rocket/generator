namespace App\Http\Controllers\Api\{{ $versionNamespace }};

use App\Exceptions\Api\{{ $versionNamespace }}\APIErrorException;
use App\Http\Controllers\Controller;
use App\Services\APIUserServiceInterface;
use App\Services\FileServiceInterface;
@foreach( $controller->getRequiredRepositoryNames() as $name )
use App\Repositories\{{ $name }}Interface;
@endforeach
@foreach( $controller->getRequiredResponseNames() as $name )
use App\Http\Responses\Api\{{ $versionNamespace }}\{{ $name }};
@endforeach
@foreach( $controller->getRequiredRequestNames() as $name )

use App\Http\Requests\Api\{{ $versionNamespace }}\{{ $name }};
@endforeach

class {{ $className }} extends Controller
{
    /** @var APIUserServiceInterface $userService */
    protected $userService;

    /** @var FileServiceInterface $fileService */
    protected $fileService;

@foreach( $controller->getRequiredRepositoryNames() as $name )
    /** @var {{ $name }}Interface ${{ lcfirst($name) }} */
    protected ${{ lcfirst($name) }};
@endforeach

    public function __construct(
@foreach( $controller->getRequiredRepositoryNames() as $name )
        {{ $name }}Interface ${{ lcfirst($name) }},
@endforeach
        APIUserServiceInterface $userService,
        FileServiceInterface $fileService
    ) {
@foreach( $controller->getRequiredRepositoryNames() as $name )
        $this->{{ lcfirst($name) }} = ${{ lcfirst($name) }};
@endforeach
        $this->userService        = $userService;
        $this->fileService        = $fileService;
    }

@foreach( $controller->getActions() as $action )
    /**
    * PATH: {{ $action->getHttpMethod() }} {{ $action->getPath() }}
@foreach( $action->getParams() as $param )
    * @param {{ $param }}
@endforeach
    * @param {{ $action->getRequest()->getName() }} $request
    *
    * @return \Illuminate\Http\JsonResponse
    * @throws \App\Exceptions\Api\{{ $versionNamespace }}\APIErrorException
    */
    public function {{ $action->getMethod() }}({{ implode(',', $action->getParams() ) }}{{ count($action->getParams()) > 0 ? ', ' : '' }}{{ $action->getRequest()->getName() }} $request)
    {
@if( $action->getResponse()->getType() === \LaravelRocket\Generator\Objects\OpenAPI\Definition::TYPE_LIST )
        $offset = $request->offset();
        $limit  = $request->limit();
        $filters = [];

        $models = $this->{{ lcfirst($action->getResponse()->getListItem()->getModelName()) }}Repository->getByFilter($filters, 'id', 'asc', $offset, $limit + 1);

        $hasNext = false;
        if (count($models) > $limit) {
            $hasNext   = true;
            $models = $models->slice(0, $limit);
        }

        return {{ $action->getResponse()->getName() }}::updateListWithModel($models, $offset, $limit, $hasNext)->response();
@elseif( $action->getHttpMethod() === 'post' && $action->getResponse()->getType() === \LaravelRocket\Generator\Objects\OpenAPI\Definition::TYPE_MODEL )
        $data = $request->only([
@foreach($action->getResponse()->getProperties() as $property )
            '{{ $property['name'] }}',
@endforeach
        ]);
        $model = $this->{{ lcfirst($action->getResponse()->getModelName()) }}Repository->create($data);
        if (empty($model) ) {
            throw new APIErrorException('unknown', 'Creation Failed');
        }

        return {{ $action->getResponse()->getName() }}::updateWithModel($model)->response();
@elseif( $action->getResponse()->getType() === \LaravelRocket\Generator\Objects\OpenAPI\Definition::TYPE_MODEL )
        $model = $this->{{ lcfirst($action->getResponse()->getModelName()) }}Repository->find($id);
        if (empty($model) ) {
            throw new APIErrorException('notFound', 'Not found');
        }

@if( $action->getHttpMethod() === 'put' || $action->getHttpMethod() === 'patch' )
        $data = $request->only([
        @foreach($action->getResponse()->getProperties() as $property )
            '{{ $property['name'] }}',
        @endforeach
        ]);
        $model = $this->{{ lcfirst($action->getResponse()->getModelName()) }}Repository->update($model, $data);

        return {{ $action->getResponse()->getName() }}::updateWithModel($model)->response();
@elseif( $action->getHttpMethod() === 'delete')
        $model = $this->{{ lcfirst($action->getResponse()->getModelName()) }}Repository->delete($model);

        return Status::ok()->response();
@else
        return {{ $action->getResponse()->getName() }}::updateWithModel($model)->response();
@endif
@else
        $modelArray = [
@foreach($action->getResponse()->getProperties() as $property )
            '{{ $property['name'] }}' => $request->get('{{ $property['name'] }}',{!! $property['default'] !!}),
@endforeach
        ];
        $response = new static($modelArray, 200);

        return $response->response();
@endif
    }
@endforeach

}
