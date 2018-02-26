namespace App\Events;

@foreach( $models as $model)
use App\Models\{{ $model }};
@endforeach

class {{ $name }} extends Event
{
@foreach( $models as $model)
    /** @var {{ $model }} ${{ lcfirst($model) }} */
    public ${{ lcfirst($model) }};
@endforeach

    /**
     * Create a new event instance.
     *
@foreach( $models as $model)
     * {{ $model }} ${{ lcfirst($model) }}
@endforeach
     */
    public function __construct(
@foreach( $models as $model)
        {{ $model }} ${{ lcfirst($model) }}{{ ($loop->last ? '' : ',') }}
@endforeach
    )
    {
@foreach( $models as $model)
        $this->{{ lcfirst($model) }}   = ${{ lcfirst($model) }};
@endforeach
    }

}
