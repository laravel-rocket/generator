namespace Tests\Events;

use App\Events\{{ $eventName}};
use Tests\TestCase;

class EventTest extends TestCase
{
    protected $useDatabase = true;

    public function testGetInstance()
    {
        /** @var \App\Events\{{ $eventName}} $event */
@foreach( $models as $model )
        ${{ lcfirst($model) }} = factory({{ $modelName }}::class)->create();
@endforeach
        $event = new {{ $eventName }}(
@foreach( $models as $model )
            ${{ lcfirst($model) }}{{ ($loop->last ? '' : ',') }}
@endforeach
        );
        $this->assertNotNull($event);
    }
}
