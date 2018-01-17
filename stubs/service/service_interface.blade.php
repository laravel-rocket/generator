namespace App\Services;

@if($isAuthService)
use LaravelRocket\Foundation\Services\AuthenticatableServiceInterface;
@else
use LaravelRocket\Foundation\Services\BaseServiceInterface;
@endif

interface {!! $serviceName !!}Interface extends BaseServiceInterface
{
}
