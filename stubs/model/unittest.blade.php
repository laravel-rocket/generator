namespace Tests\Models;

use App\Models\{{ $modelName }};
use Tests\TestCase;
use Illuminate\Support\Str;

class {{ $modelName }}Test extends TestCase
{

    protected $useDatabase = true;

    public function testGetInstance()
    {
        /** @var  \App\Models\{{ $modelName }} ${{ $variableName }} */
        ${{ $variableName }} = new {{ $modelName }}();
        $this->assertNotNull(${{ $variableName }});
    }

    public function testStoreNew()
    {
        /** @var  \App\Models\{{ $modelName }} ${{ $variableName }} */
        ${{ $variableName }}Model = new {{ $modelName }}();

        ${{ $variableName }}Data = factory({{ $modelName }}::class)->make();
        foreach( ${{ $variableName }}Data->toFillableArray() as $key => $value ) {
            ${{ $variableName }}Model->$key = $value;
        }
        ${{ $variableName }}Model->save();

        $this->assertNotNull({{ $modelName }}::find(${{ $variableName }}Model->id));
    }

}
