namespace Tests\Repositories;

use App\Models\{{ $modelName }};
use Tests\TestCase;

class {{ $modelName }}RepositoryTest extends TestCase
{
    protected $useDatabase = true;

    public function testGetInstance()
    {
        /** @var  \App\Repositories\{{ $modelName }}RepositoryInterface $repository */
        $repository = \App::make(\App\Repositories\{{ $modelName }}RepositoryInterface::class);
        $this->assertNotNull($repository);
    }

    public function testGetList()
    {
        $models = factory({{ $modelName }}::class, 3)->create();
        ${{ $variableName }}Ids = $models->pluck('id')->toArray();

        /** @var  \App\Repositories\{{ $modelName }}RepositoryInterface $repository */
        $repository = \App::make(\App\Repositories\{{ $modelName }}RepositoryInterface::class);
        $this->assertNotNull($repository);

        $modelsCheck = $repository->get('id', 'asc', 0, 1);
        $this->assertInstanceOf({{ $modelName }}::class, $modelsCheck[0]);

        $modelsCheck = $repository->getByIds(${{ $variableName }}Ids);
        $this->assertEquals(3, count($modelsCheck));
    }

    public function testFind()
    {
        $models = factory({{ $modelName }}::class, 3)->create();
        ${{ $variableName }}Ids = $models->pluck('id')->toArray();

        /** @var  \App\Repositories\{{ $modelName }}RepositoryInterface $repository */
        $repository = \App::make(\App\Repositories\{{ $modelName }}RepositoryInterface::class);
        $this->assertNotNull($repository);

        ${{ $variableName }}Check = $repository->find(${{ $variableName }}Ids[0]);
        $this->assertEquals(${{ $variableName }}Ids[0], ${{ $variableName }}Check->id);
    }

    public function testCreate()
    {
        ${{ $variableName }}Data = factory({{ $modelName }}::class)->make();

        /** @var  \App\Repositories\{{ $modelName }}RepositoryInterface $repository */
        $repository = \App::make(\App\Repositories\{{ $modelName }}RepositoryInterface::class);
        $this->assertNotNull($repository);

        ${{ $variableName }}Check = $repository->create(${{ $variableName }}Data->toFillableArray());
        $this->assertNotNull(${{ $variableName }}Check);
    }

    public function testUpdate()
    {
        ${{ $variableName }}Data = factory({{ $modelName }}::class)->create();

        /** @var  \App\Repositories\{{ $modelName }}RepositoryInterface $repository */
        $repository = \App::make(\App\Repositories\{{ $modelName }}RepositoryInterface::class);
        $this->assertNotNull($repository);

        ${{ $variableName }}Data = factory({{ $modelName }}::class)->make();

        ${{ $variableName }}Check = $repository->update(${{ $variableName }}Data, ${{ $variableName }}Data->toFillableArray());
        $this->assertNotNull(${{ $variableName }}Check);
    }

    public function testDelete()
    {
        ${{ $variableName }}Data = factory({{ $modelName }}::class)->create();

        /** @var  \App\Repositories\{{ $modelName }}RepositoryInterface $repository */
        $repository = \App::make(\App\Repositories\{{ $modelName }}RepositoryInterface::class);
        $this->assertNotNull($repository);

        $repository->delete(${{ $variableName }}Data);

        ${{ $variableName }}Check = $repository->find(${{ $variableName }}Data->id);
        $this->assertNull(${{ $variableName }}Check);
    }

}
