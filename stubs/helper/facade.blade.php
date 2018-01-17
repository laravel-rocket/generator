namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class {!! $helperName !!} extends Facade
{

    protected static function getFacadeAccessor()
    {
        return \App\Helpers\{!! $helperName !!}Interface::class;
    }

}
