namespace Tests\Controllers\Api\{{ $versionNamespace }};

use Tests\TestCase;
use Illuminate\Support\Str;

class {{ $className }}Test extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $user = factory(\App\Models\User::class)->create();
        $this->be($user, 'web');
    }

@foreach( $controller->getActions() as $action )
@include('api.oas.tests.' . $action->getType())
@endforeach
}
