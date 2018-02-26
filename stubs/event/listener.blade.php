namespace App\Listeners;

use App\Events\{{ $eventName }};
@foreach( $models as $model)
use App\Models\{{ $model }};
@endforeach
@foreach( $models as $model)
use App\Repositories\{{ $model }}RepositoryInterface;
@endforeach
@foreach( $services as $service)
use App\Services\{{ $service }}Interface;
@endforeach

class {{ $listenerName }}
{
@foreach( $models as $model)
    /** @var {{ $model }}RepositoryInterface ${{ lcfirst($model) }}Repository */
    protected ${{ lcfirst($model) }}Repository;
@endforeach

@foreach( $services as $service)
    /** @var {{ $service }}Interface ${{ lcfirst($service) }} */
    protected ${{ lcfirst($service) }};
@endforeach

    /**
     * Create the event listener.
     *
@foreach( $models as $model)
     * @param {{ $model }}RepositoryInterface ${{ lcfirst($model) }}Repository
@endforeach
@foreach( $services as $service)
    * @param {{ $service }}Interface ${{ lcfirst($service) }}
@endforeach
     */
    public function __construct(
@foreach( $models as $model)
        {{ $model }}RepositoryInterface ${{ lcfirst($model) }}Repository{{ ($loop->last && count($service) == 0 ? '' : ',') }}
@endforeach
@foreach( $services as $service)
        {{ $service }}Interface ${{ lcfirst($service) }}{{ ($loop->last ? '' : ',') }}
@endforeach
    ) {
@foreach( $models as $model)
        $this->{{ lcfirst($model) }} = ${{ lcfirst($model) }};
@endforeach
@foreach( $services as $service)
        $this->{{ lcfirst($service) }} = ${{ lcfirst($service) }};
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
