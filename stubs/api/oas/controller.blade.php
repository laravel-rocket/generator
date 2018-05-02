namespace App\Http\Controllers\Api\{{ $versionNamespace }};

use App\Exceptions\Api\{{ $versionNamespace }}\APIErrorException;
use App\Http\Controllers\Controller;
use App\Services\UserServiceInterface;
use App\Services\FileServiceInterface;
@foreach( $controller->getRequiredRepositoryNames() as $name )
use App\Repositories\{{ $name }}Interface;
@endforeach
@foreach( $controller->getRequiredResponseNames() as $name )
use App\Http\Responses\Api\{{ $versionNamespace }}\{{ $name }};
@endforeach
@foreach( $controller->getRequiredRequests() as $request )
use App\Http\Requests\Api\{{ $versionNamespace }}{{ $request->getNamespace() }}\{{ $request->getName() }};
@endforeach
@if( ends_with($className, 'AuthController'))
use App\Http\Requests\Api\V1\PsrServerRequest;
use App\Repositories\UserRepositoryInterface;
use App\Services\UserServiceAuthenticationServiceInterface;
use League\OAuth2\Server\AuthorizationServer;
use Zend\Diactoros\Response as Psr7Response;
@endif

class {{ $className }} extends Controller
{
    /** @var UserServiceInterface $userService */
    protected $userService;

    /** @var FileServiceInterface $fileService */
    protected $fileService;

@if( ends_with($className, 'AuthController'))
    /** @var UserServiceInterface $authenticatableService */
    protected $authenticatableService;

    /** @var UserServiceAuthenticationServiceInterface $serviceAuthenticationService */
    protected $serviceAuthenticationService;

    /** @var UserRepositoryInterface $userRepository */
    protected $userRepository;

    /** @var \League\OAuth2\Server\AuthorizationServer  */
    protected $server;
@endif

@foreach( $controller->getRequiredRepositoryNames() as $name )
    /** @var {{ $name }}Interface ${{ lcfirst($name) }} */
    protected ${{ lcfirst($name) }};
@endforeach

    public function __construct(
@foreach( $controller->getRequiredRepositoryNames() as $name )
        {{ $name }}Interface ${{ lcfirst($name) }},
@endforeach
@if( ends_with($className, 'AuthController'))
        UserServiceAuthenticationServiceInterface $serviceAuthenticationService,
        AuthorizationServer $server,
        UserRepositoryInterface $userRepository,
@endif
        UserServiceInterface $userService,
        FileServiceInterface $fileService
    ) {
@foreach( $controller->getRequiredRepositoryNames() as $name )
        $this->{{ lcfirst($name) }} = ${{ lcfirst($name) }};
@endforeach
        $this->userService        = $userService;
        $this->fileService        = $fileService;
@if( ends_with($className, 'AuthController'))
        $this->authenticatableService       = $userService;
        $this->serviceAuthenticationService = $serviceAuthenticationService;
        $this->server                       = $server;
        $this->userRepository               = $userRepository;
@endif
    }

@foreach( $controller->getActions() as $action )
@include('api.oas.actions.' . $action->getType())

@endforeach
}
