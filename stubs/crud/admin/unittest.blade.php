namespace Tests\Controllers\Admin;

use Tests\TestCase;

class {{ $modelName }}ControllerTest extends TestCase
{

    protected $useDatabase = true;

    public function testGetInstance()
    {
        /** @var  \App\Http\Controllers\Admin\{{ $modelName }}Controller $controller */
        $controller = \App::make(\App\Http\Controllers\Admin\{{ $modelName }}Controller::class);
        $this->assertNotNull($controller);
    }

    public function setUp()
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
        $response = $this->action('GET', 'Admin\{{ $modelName }}Controller@index');
        $this->assertResponseOk();
    }

    public function testCreateModel()
    {
        $this->action('GET', 'Admin\{{ $modelName }}Controller@create');
        $this->assertResponseOk();
    }

    public function testStoreModel()
    {
        ${{ $variableName }} = factory(\App\Models\{{ $modelName }}::class)->make();
        $this->action('POST', 'Admin\{{ $modelName }}Controller@store', [
                '_token' => csrf_token(),
            ] + ${{ $variableName }}->toArray());
        $this->assertResponseStatus(302);
    }

    public function testEditModel()
    {
        ${{ $variableName }} = factory(\App\Models\{{ $modelName }}::class)->create();
        $this->action('GET', 'Admin\{{ $modelName }}Controller@@show', [${{ $variableName }}->id]);
        $this->assertResponseOk();
    }

    public function testUpdateModel()
    {
        $faker = \Faker\Factory::create();

        ${{ $variableName }} = factory(\App\Models\{{ $modelName }}::class)->create();

        $testData = {{ $testData }};
        $id = ${{ $variableName }}->id;

        ${{ $variableName }}->{{ $testColumnName }} = $testData;

        $this->action('PUT', 'Admin\{{ $modelName }}Controller@update', [$id], [
                '_token' => csrf_token(),
            ] + ${{ $variableName }}->toArray());
        $this->assertResponseStatus(302);

        $new{{ $modelName }} = \App\Models\{{ $modelName }}::find($id);
        $this->assertEquals($testData, $new{{ $modelName }}->{{ $testColumnName }});
    }

    public function testDeleteModel()
    {
        ${{ $variableName }} = factory(\App\Models\{{ $modelName }}::class)->create();

        $id = ${{ $variableName }}->id;

        $this->action('DELETE', 'Admin\{{ $modelName }}Controller@destroy', [$id], [
            '_token' => csrf_token(),
        ]);
        $this->assertResponseStatus(302);

        $check{{ $modelName }} = \App\Models\{{ $modelName }}::find($id);
        $this->assertNull($check{{ $modelName }});
    }

}
