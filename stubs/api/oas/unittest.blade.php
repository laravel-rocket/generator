namespace Tests\Controllers\Api\{{ $versionNamespace }};

use Tests\TestCase;

class {{ $className }}Test extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $user     = factory(\App\Models\User::class)->create();
        $this->be($user, 'users');
    }

@foreach( $controller->getActions() as $action )
@include('api.oas.tests.' . $action->getActionContext('type'))
@endforeach
}
