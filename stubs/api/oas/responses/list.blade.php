namespace App\Http\Responses\Api\{{ $versionNamespace }};

class {{ $className }} extends ListBase
{
    protected static $itemsResponseModel = {{ $modelName }}::class;
}
