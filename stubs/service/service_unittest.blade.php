namespace Tests\Services;

use Tests\TestCase;

class {!! $serviceName !!}Test extends TestCase
{

    public function testGetInstance()
    {
        /** @var  \App\Services\{!! $serviceName !!}Interface $service */
        $service = \App::make(\App\Services\{!! $serviceName !!}Interface::class);
        $this->assertNotNull($service);
    }

}
