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
@include('api.oas.actions.' . $action->getActionContext('type'))

@endforeach
}
