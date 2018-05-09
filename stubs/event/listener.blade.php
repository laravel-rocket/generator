namespace App\Listeners;

use App\Events\{{ $eventName }};
@foreach( $models as $model)
use App\Models\{{ $model }};
@endforeach
@foreach( $models as $model)
use App\Repositories\{{ $model }}RepositoryInterface;
@endforeach
@foreach( $services as $service)
use App\Services\{{ $service }}ServiceInterface;
@endforeach

class {{ $listenerName }} extends Listener
{
@foreach( $models as $model)
    /** @var {{ $model }}RepositoryInterface ${{ lcfirst($model) }}Repository */
    protected ${{ lcfirst($model) }}Repository;
@endforeach

@foreach( $services as $service)
    /** @var {{ $service }}ServiceInterface ${{ lcfirst($service) }}Service */
    protected ${{ lcfirst($service) }}Service;
@endforeach

    /**
     * Create the event listener.
     *
@foreach( $models as $model)
     * @param {{ $model }}RepositoryInterface ${{ lcfirst($model) }}Repository
@endforeach
@foreach( $services as $service)
    * @param {{ $service }}ServiceInterface ${{ lcfirst($service) }}Service
@endforeach
     */
    public function __construct(
@foreach( $models as $model)
        {{ $model }}RepositoryInterface ${{ lcfirst($model) }}Repository{{ ($loop->last && count($services) == 0 ? '' : ',') }}
@endforeach
@foreach( $services as $service)
        {{ $service }}ServiceInterface ${{ lcfirst($service) }}Service{{ ($loop->last ? '' : ',') }}
@endforeach
    ) {
@foreach( $models as $model)
        $this->{{ lcfirst($model) }}Repository = ${{ lcfirst($model) }}Repository;
@endforeach
@foreach( $services as $service)
        $this->{{ lcfirst($service) }}Service = ${{ lcfirst($service) }}Service;
@endforeach
    }

    /**
     * Handle the event.
     *
     * @param {{ $eventName }} $event
     *
     * @return void
     */
    public function handle({{ $eventName }} $event)
    {
    }

}
