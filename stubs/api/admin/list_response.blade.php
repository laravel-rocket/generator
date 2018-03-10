namespace App\Http\Responses\Api\Admin;

class {{ $className }} extends ListBase
{
    protected static $itemsResponseModel = {{ $modelName }}::class;
}
