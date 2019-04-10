namespace Tests\Controllers\Api\Admin;

use Tests\TestCase;

class {{ $modelName }}ControllerTest extends TestCase
{

    protected $useDatabase = true;

    public function testGetInstance()
    {
        /** @var  \App\Http\Controllers\Api\Admin\{{ $modelName }}Controller $controller */
        $controller = \App::make(\App\Http\Controllers\Api\Admin\{{ $modelName }}Controller::class);
        $this->assertNotNull($controller);
    }

    public function setUp(): void
    {
        parent::setUp();
        $authUser = factory(\App\Models\AdminUser::class)->create();
        $authUserRole = factory(\App\Models\AdminUserRole::class)->create([
            'admin_user_id' => $authUser->id,
            'role' => \App\Models\AdminUserRole::ROLE_SUPER_USER,
        ]);
        $this->be($authUser, 'admins');
    }

    public function testGetList()
    {
        $response = $this->action('GET', 'Api\Admin\{{ $modelName }}Controller@index');
        $this->assertResponseOk();
    }

    public function testStoreModel()
    {
        ${{ $variableName }} = factory(\App\Models\{{ $modelName }}::class)->make();
        $this->action('POST', 'Api\Admin\{{ $modelName }}Controller@store', ${{ $variableName }}->toArray());
        $this->assertResponseStatus(201);
    }

@if( !empty($testColumnName))
    public function testUpdateModel()
    {
        $faker = \Faker\Factory::create();

        ${{ $variableName }} = factory(\App\Models\{{ $modelName }}::class)->create();

        $testData = {!! $testData !!};
        $id = ${{ $variableName }}->id;

        ${{ $variableName }}->{{ $testColumnName }} = $testData;

        $this->action('PUT', 'Api\Admin\{{ $modelName }}Controller@update', [$id], ${{ $variableName }}->toArray());
        $this->assertResponseStatus(200);

        $new{{ $modelName }} = \App\Models\{{ $modelName }}::find($id);
        $this->assertEquals($testData, $new{{ $modelName }}->{{ $testColumnName }});
    }

@endif
    public function testDeleteModel()
    {
        ${{ $variableName }} = factory(\App\Models\{{ $modelName }}::class)->create();

        $id = ${{ $variableName }}->id;

        $this->action('DELETE', 'Api\Admin\{{ $modelName }}Controller@destroy', [$id]);
        $this->assertResponseStatus(200);

        $check{{ $modelName }} = \App\Models\{{ $modelName }}::find($id);
        $this->assertNull($check{{ $modelName }});
    }

}
