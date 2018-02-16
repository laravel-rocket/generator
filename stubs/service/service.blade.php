namespace App\Services\Production;

@foreach( $repositories as $repository)
use App\Repositories\{!! $repository !!}Interface;
@endforeach
use App\Services\{!! $serviceName !!}Interface;
@if($isAuthService)
use LaravelRocket\Foundation\Services\Production\AuthenticatableService;
@else
use LaravelRocket\Foundation\Services\Production\BaseService;
@endif

class {!! $serviceName !!} extends @if($isAuthService) AuthenticatableService @else BaseService @endif implements {!! $serviceName !!}Interface
{
@if($isAuthService)

    /** @var string $resetEmailTitle */
    protected $resetEmailTitle = 'Reset Password';

    /** @var string $resetEmailTemplate */
    protected $resetEmailTemplate = 'emails.common.reset_password';

@endif
@foreach( $repositories as $repository)
    /** @var \App\Repositories\{!! $repository !!}Interface */
    protected ${!! lcfirst($repository) !!};
@endforeach

    public function __construct(
@foreach( $repositories as $index => $repository)
    {!! $repository !!}Interface ${!! lcfirst($repository) !!}{{ ($loop->last ? '' : ',') }}
@endforeach
    ) {
@foreach( $repositories as $repository)
        $this->{!! lcfirst($repository) !!} = ${!! lcfirst($repository) !!};
@endforeach
    }

}
