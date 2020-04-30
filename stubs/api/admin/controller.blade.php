namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\Api\Admin\APIErrorException;
use App\Http\Controllers\Controller;

use App\Http\Requests\Api\Admin\{{ $requestNameSpace }}\IndexRequest;
use App\Http\Requests\Api\Admin\{{ $requestNameSpace }}\StoreRequest;
use App\Http\Requests\Api\Admin\{{ $requestNameSpace }}\UpdateRequest;
use App\Http\Responses\Api\Admin\{{ $modelName }};
use App\Http\Responses\Api\Admin\{{ \ICanBoogie\pluralize($modelName) }};
use App\Http\Responses\Api\Admin\Status;
use App\Repositories\{{ $modelName }}RepositoryInterface;
@foreach( $table->getRelations() as $relation )
@if( $relation->isRoles())
use \App\Repositories\{{ $relation->getReferenceModel() }}RepositoryInterface;
@endif
@endforeach
use App\Services\AdminUserServiceInterface;
use App\Services\FileServiceInterface;

class {{ $modelName }}Controller extends Controller
{
    /** @var \App\Repositories\{{ $modelName }}RepositoryInterface */
    protected ${{ $variableName }}Repository;

    /** @var \App\Services\AdminUserServiceInterface $adminUserService */
    protected $adminUserService;

    /** @var \App\Services\FileServiceInterface $fileService */
    protected $fileService;

@foreach( $table->getRelations() as $relation )
@if( $relation->isRoles())
    /** @var \App\Repositories\{{ $relation->getReferenceModel() }}RepositoryInterface */
    protected ${{ lcfirst($relation->getReferenceModel()) }}Repository;
@endif
@endforeach

    public function __construct(
        {{ $modelName }}RepositoryInterface ${{ $variableName }}Repository,
@foreach( $table->getRelations() as $relation )
@if( $relation->isRoles())
        {{ $relation->getReferenceModel() }}RepositoryInterface ${{ lcfirst($relation->getReferenceModel()) }}Repository,
@endif
@endforeach
        FileServiceInterface $fileService,
        AdminUserServiceInterface $adminUserService
    ) {
        $this->{{ $variableName }}Repository = ${{ $variableName }}Repository;
@foreach( $table->getRelations() as $relation )
    @if( $relation->isRoles())
        $this->{{ lcfirst($relation->getReferenceModel()) }}Repository = ${{ lcfirst($relation->getReferenceModel()) }}Repository;
    @endif
@endforeach
        $this->adminUserService    = $adminUserService;
        $this->fileService         = $fileService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndexRequest $request)
    {
        $offset    = $request->offset();
        $limit     = $request->limit();
        $order     = $request->order();
        $direction = $request->direction();

        $queryWord = $request->get('query');
        $filter    = [];
        if (!empty($queryWord)) {
            $filter['query'] = $queryWord;
        }

        $count      = $this->{{ $variableName }}Repository->countByFilter($filter);
        ${{ \ICanBoogie\pluralize($variableName) }} = $this->{{ $variableName }}Repository->getByFilter($filter, $order, $direction, $offset, $limit);

        return {{ \ICanBoogie\pluralize($modelName) }}::updateListWithModel(${{ \ICanBoogie\pluralize($variableName) }}, $offset, $limit, $count)->response();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest $request
     *
     * @throws APIErrorException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $input = $request->only([
@foreach( $table->getColumns() as $column )
@if( $column->isEditable() && !$column->hasRelation())
            '{{ $column->getName() }}',
@endif
@endforeach
@foreach( $table->getRelations() as $relation )
@if( $relation->shouldIncludeInAPI() && !$relation->isFile() && $relation->getType() == \LaravelRocket\Generator\Objects\Relation::TYPE_BELONGS_TO )
            '{{ $relation->getColumn()->getName() }}',
@endif
@endforeach
        ]);

@foreach( $table->getRelations() as $relation )
@if( $relation->shouldIncludeInAPI() && $relation->isFile())
        if ($request->hasFile('{{ $relation->getQueryName() }}')) {
            $file      = $request->file('{{ $relation->getQueryName() }}');
            $mediaType = $file->getClientMimeType();
            $path      = $file->getPathname();
@if( $relation->isImage() )
            $fileModel     = $this->fileService->upload('default-image', $path, $mediaType, []);
@else
            $fileModel     = $this->fileService->upload('default-file', $path, $mediaType, []);
@endif
            if (!empty($fileModel)) {
                $input['{{ $relation->getColumn()->getName() }}'] = $fileModel->id;
            }
        }
@endif
@endforeach

        ${{ $variableName }} = $this->{{ $variableName }}Repository->create($input);


        if (empty(${{ $variableName }})) {
            throw new APIErrorException('unknown', '{{ $modelName }} Creation Failed');
        }
@foreach( $table->getRelations() as $relation )
@if( $relation->isRoles())

        $roles = $request->get('{{ $relation->getQueryName() }}', []);
        $this->{{ lcfirst($relation->getReferenceModel()) }}Repository->updateMultipleEntries(${{ $variableName }}->id, '{{ $relation->getReferenceColumn()->getName() }}', 'role', $roles);
@endif
@endforeach

        return {{ $modelName }}::updateWithModel(${{ $variableName }})->withStatus(201)->response();
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @throws APIErrorException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        ${{ $variableName }} = $this->{{ $variableName }}Repository->find($id);
        if (empty(${{ $variableName }})) {
            throw new APIErrorException('notFound', '{{ $modelName }} not found');
        }

        return {{ $modelName }}::updateWithModel(${{ $variableName }})->response();
    }

    /**
     * @param int                                                   $id
     * @param UpdateRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \App\Exceptions\Api\Admin\APIErrorException
     */
    public function update($id, UpdateRequest $request)
    {
        ${{ $variableName }} = $this->{{ $variableName }}Repository->find($id);
        if (empty(${{ $variableName }})) {
            throw new APIErrorException('notFound', '{{ $modelName }} not found');
        }

        $input = $request->only([
@foreach( $table->getColumns() as $column )
@if( $column->isEditable() && !$column->hasRelation())
            '{{ $column->getName() }}',
@endif
@endforeach
@foreach( $table->getRelations() as $relation )
@if( $relation->shouldIncludeInAPI() && !$relation->isFile() && $relation->getType() == \LaravelRocket\Generator\Objects\Relation::TYPE_BELONGS_TO )
            '{{ $relation->getColumn()->getName() }}',
@endif
@endforeach
        ]);


@foreach( $table->getRelations() as $relation )
@if( $relation->shouldIncludeInAPI() && $relation->isFile())
        if ($request->hasFile('{{ $relation->getQueryName() }}')) {
            $file      = $request->file('{{ $relation->getQueryName() }}');
            $mediaType = $file->getClientMimeType();
            $path      = $file->getPathname();
@if( $relation->isImage() )
            $fileModel     = $this->fileService->upload('default-image', $path, $mediaType, []);
@else
            $fileModel     = $this->fileService->upload('default-file', $path, $mediaType, []);
@endif
            if (!empty($fileModel)) {
                if (!empty(${{ $variableName }}->{{ $relation->getName() }})) {
                    $this->fileService->delete(${{ $variableName }}->{{ $relation->getName() }});
                }
                $input['{{ $relation->getColumn()->getName() }}'] = $fileModel->id;
            }
        }
@endif
@endforeach

        ${{ $variableName }} = $this->{{ $variableName }}Repository->update(${{ $variableName }}, $input);

@foreach( $table->getRelations() as $relation )
    @if( $relation->isRoles())
        $roles = $request->get('{{ $relation->getQueryName() }}', []);
        $this->{{ lcfirst($relation->getReferenceModel()) }}Repository->updateMultipleEntries(${{ $variableName }}->id, '{{ $relation->getReferenceColumn()->getName() }}', 'role', $roles);
    @endif
@endforeach

        return {{ $modelName }}::updateWithModel(${{ $variableName }})->response();
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \App\Exceptions\Api\Admin\APIErrorException
     */
    public function destroy($id)
    {
        ${{ $variableName }} = $this->{{ $variableName }}Repository->find($id);
        if (empty(${{ $variableName }})) {
            throw new APIErrorException('notFound', '{{ $modelName }} not found');
        }

        $this->{{ $variableName }}Repository->delete(${{ $variableName }});

        return Status::ok()->response();
    }
}
