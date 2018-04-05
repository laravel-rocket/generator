namespace App\Http\Controllers\Api\V1;

use App\Exceptions\APIErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PaginationRequest;
use App\Services\APIUserServiceInterface;
use App\Services\FileServiceInterface;

class {{ $className }} extends Controller
{
    /** @var APIUserServiceInterface $userService */
    protected $userService;

    /** @var FileServiceInterface $fileService */
    protected $fileService;

    public function __construct(
        APIUserServiceInterface $userService,
        FileServiceInterface $fileService
    ) {
        $this->userService        = $userService;
        $this->fileService        = $fileService;
    }

@foreach( $actions as $action )
    /**
    * @return \Illuminate\Http\JsonResponse
    */
    public function {{ $action->getName() }}({{ implode(',', $action->getParams() ) }})
    {
    }
@endforeach

}
