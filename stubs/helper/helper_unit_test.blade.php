namespace Tests\Helpers;

use Tests\TestCase;

class {!! $helperName !!}Test extends TestCase
{
    public function testGetInstance()
    {
        /** @var  \App\Helpers\{!! $helperName !!}Interface $helper */
        $helper = \App::make(\App\Helpers\{!! $helperName !!}Interface::class);
        $this->assertNotNull($helper);
    }
}
