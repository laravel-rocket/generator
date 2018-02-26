namespace App\Repositories\Eloquent;

use LaravelRocket\Foundation\Repositories\Eloquent\{{ $baseClass }};
use App\Repositories\{{ $modelName }}RepositoryInterface;
use App\Models\{{ $modelName }};

class {{ $modelName }}Repository extends {{ $baseClass }} implements {{ $modelName }}RepositoryInterface
{

    protected $querySearchTargets = [
@foreach( $keywordColumns as $index => $keywordColumn )
        '{{ $keywordColumn  }}',
@endforeach
    ];

@if( array_key_exists('getBlankModel', $existingMethods))
    {!! $existingMethods['getBlankModel'] !!}
@php
unset($existingMethods['getBlankModel']);
@endphp
@else
    public function getBlankModel()
    {
        return new {{ $modelName }}();
    }
@endif

@if( array_key_exists('rules', $existingMethods))
    {!! $existingMethods['rules'] !!}
@php
    unset($existingMethods['rules']);
@endphp
@else
    public function rules()
    {
        return [
        ];
    }
@endif

@if( array_key_exists('messages', $existingMethods))
    {!! $existingMethods['messages'] !!}
@php
    unset($existingMethods['messages']);
@endphp
@else
    public function messages()
    {
        return [
        ];
    }
@endif

@if( array_key_exists('buildQueryByFilter', $existingMethods))
{!! $existingMethods['buildQueryByFilter'] !!}
@php
    unset($existingMethods['buildQueryByFilter']);
@endphp
@else
    protected function buildQueryByFilter($query, $filter)
    {
        return parent::buildQueryByFilter($query, $filter);
    }
@endif

@foreach( $existingMethods as $name => $method )
{!! $method !!}
@endforeach
}
