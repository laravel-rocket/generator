namespace App\Http\Controllers\Api\V1;

use App\Exceptions\APIErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PaginationRequest;
use App\Services\APIUserServiceInterface;
use App\Services\FileServiceInterface;
@foreach( $controller->getRequiredRepositoryNames() as $name )
use App\Repositories\{{ $name }}Interface;
@endforeach
@foreach( $controller->getRequiredResponseNames() as $name )
use App\Http\Response\Api\V1\{{ $name }};
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

@foreach( $controller->actions as $action )
    /**
    * PATH: {{ $action->getMethod() }} {{ $action->getPath() }}
    * @return \Illuminate\Http\JsonResponse
    */
    public function {{ $action->getMethod() }}({{ implode(',', $action->getParams() ) }}, {{ $action->getRequest()->getName() }} $request)
    {
    }
@endforeach

}
