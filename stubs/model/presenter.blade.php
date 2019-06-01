namespace App\Presenters;

use LaravelRocket\Foundation\Presenters\BasePresenter;

/**
 *
 * @property \App\Models\{{ $modelName }} $entity
@foreach($columns as $name => $type)
 * @property {{ $type }} ${{ $name }}
@endforeach
 */

class {{ $modelName }}Presenter extends BasePresenter
{

    protected $multilingualFields = [
@foreach( $multilingualFields as $multilingualField )
    '{{ $multilingualField }}',
@endforeach
    ];

    protected $imageFields = [
@foreach( $imageColumns as $imageColumn )
    '{{ $imageColumn }}',
@endforeach
    ];

@foreach( $relations as $relation )
@if( array_key_exists($relation->getName() , $existingMethods))
    {!! $existingMethods[$relation->getName()] !!}
@php
    unset($existingMethods[$relation->getName()]);
@endphp
@else
@if( $relation->getType() === 'belongsTo' || $relation->getType() === 'hasOne' )
    public function {{ $relation->getName() }}()
    {
        $model = $this->entity->{{ $relation->getName() }};
        if (!$model) {
            $model      = new \App\Models\{{ $relation->getReferenceModel() }}();
@if( \Illuminate\Support\Str::endsWith(strtolower($relation->getName()), 'image'))
@if( $authenticatable )
            $model->url = \URLHelper::asset('images/user.png', 'common');
@else
            $model->url = \URLHelper::asset('images/local.png', 'common');
@endif
@endif
        }
        return $model;
    }
@endif
@endif
@endforeach

@foreach( $editableColumns as $editableColumn)
@if( array_key_exists($editableColumn['name'] , $existingMethods))
    {!! $existingMethods[$editableColumn['name']] !!}
@php
unset($existingMethods[$editableColumn['name']]);
@endphp
@else
@if( $editableColumn['type'] == 'select')
    public function {{ $editableColumn['name'] }}()
    {
        return trans('tables/{{ $tableName }}/columns.{{ $editableColumn['name'] }}.options.'. $this->entity->{{ $editableColumn['name'] }});
    }
@elseif( $editableColumn['type'] == 'country')
    public function {{ $editableColumn['name'] }}()
    {
        return \DataHelper::getCountryName($this->entity->{{ $editableColumn['name'] }}, $this->entity->{{ $editableColumn['name'] }});
    }
@elseif( $editableColumn['type'] == 'currency')
    public function {{ $editableColumn['name'] }}()
    {
    return \DataHelper::getCurrencyName($this->entity->{{ $editableColumn['name'] }}, $this->entity->{{ $editableColumn['name'] }});
    }
@elseif( $editableColumn['type'] == 'boolean')
    public function {{ $editableColumn['name'] }}()
    {
        $key = $this->entity->{{ $editableColumn['name'] }} ? 'true' : 'false';
        return trans('tables/{{ $viewName }}/columns.{{ $editableColumn['name'] }}.booleans.'. $key);
    }
@endif
@endif
@endforeach

@if( array_key_exists('toString' , $existingMethods))
    {!! $existingMethods['toString'] !!}
@php
    unset($existingMethods['toString']);
@endphp
@else
    public function toString()
    {
        return $this->entity->present()->{{ $representativeColumn }};
    }
@endif

@foreach( $existingMethods as $name => $method )
    {!! $method !!}
@endforeach

}
