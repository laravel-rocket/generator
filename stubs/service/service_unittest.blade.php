namespace Tests\Services;

use Tests\TestCase;

class {!! $serviceName !!}Test extends TestCase
{

    /**
     * @return \App\Services\{!! $serviceName !!}Interface
     */
    protected function getInstance()
    {
        /** @var \App\Services\{!! $serviceName !!}eInterface $service */
        $service = \App::make(\App\Services\{!! $serviceName !!}Interface::class);

        return $service;
    }

    public function testGetInstance()
    {
        $service = $this->getInstance();
        $this->assertNotNull($service);
    }

}
