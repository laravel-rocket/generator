namespace Tests\Events;

use App\Events\{{ $eventName}};
@foreach( $models as $model )
use App\Models\{{ $model }};
@endforeach
use Tests\TestCase;

class {{ $eventName}}Test extends TestCase
{
    protected $useDatabase = true;

    public function testGetInstance()
    {
        /** @var \App\Events\{{ $eventName}} $event */
@foreach( $models as $model )
        ${{ lcfirst($model) }} = factory({{ $model }}::class)->create();
@endforeach
        $event = new {{ $eventName }}(
@foreach( $models as $model )
            ${{ lcfirst($model) }}{{ ($loop->last ? '' : ',') }}
@endforeach
        );
        $this->assertNotNull($event);
    }
}
