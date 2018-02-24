＠extends('layouts.admin.application', ['menu' => '{{ $viewName }}'] )

＠section('metadata')
＠stop

＠section('styles')
＠stop

＠section('scripts')
＠stop

＠section('title')
＠stop

＠section('header')
{{ $modelName }}
＠stop

＠section('breadcrumb')
<li><a href="｛!! action('Admin\{{ $modelName }}Controller＠index') !!｝"><i class="fa fa-files-o"></i> {{ $modelName }}</a></li>
<li class="active">｛｛ ${{ $variableName }}->id ｝｝</li>
＠stop

＠section('content')
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">｛｛ ${{ $variableName }}->present()->toString() ｝｝</h3>
        <div class="box-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <a href="｛!! action('Admin\{{ $modelName }}Controller＠edit', ${{ $variableName }}->id) !!｝" class="btn btn-block btn-primary btn-sm"><i class="fas fa-edit"></i> ＠lang('admin.pages.common.buttons.edit')</a>
            </div>
        </div>
    </div>
    <div class="box-body">
        <table class="table">
@foreach( $showableColumns as $column)
        <tr data-column-name="{{ $column['name'] }}">
            <th>＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</th>
                <td>
@if( array_key_exists($column['name'], $belongsToRelations) )
@if( $column['type'] === 'image' )
＠if( $model->{{ $belongsToRelations[$column['name']]['name'] }} )
                <img src="｛｛ ${{ $variableName }}->{{ $belongsToRelations[$column['name']]['name'] }}->present()->url ｝｝" class="img-thumbnail" width="50" height="50">
＠else
                <img src="｛｛ \URLHelper::asset('images/no-image.png', 'common') ｝｝" class="img-thumbnail" width="50" height="50">
＠endif
@elseif( $column['type'] === 'file' )
＠if( $model->{{ $belongsToRelations[$column['name']]['name'] }} )
                <a href="｛｛ ${{ $variableName }}->{{ $belongsToRelations[$column['name']]['name'] }}->present()->url ｝｝">｛!! \FileHelper::getFileIconHTML($model->{{ $belongsToRelations[$column['name']]['name'] }}->mime_type) !!｝｛｛ $model->{{ $belongsToRelations[$column['name']]['name'] }}->present()->toString() ｝｝</a>
＠endif
@else
                ｛｛ ${{ $variableName }}->{{ $belongsToRelations[$column['name']]['name'] }} ? $model->{{ $belongsToRelations[$column['name']]['name'] }}->present()->toString() : '' ｝｝
@endif
                ｛｛ ${{ $variableName }}->{{ $belongsToRelations[$column['name']]['name'] }} ? ${{ $variableName }}->{{ $belongsToRelations[$column['name']]['name'] }}->present()->toString() : '' ｝｝
@else
                ｛｛ ${{ $variableName }}->{{ $column['name'] }} ｝｝
@endif
                </td>
            <tr>
        </tr>
@endforeach
        </table>
    </div>
</div>
@foreach( $relations as $relation )
@if( array_get($relation, 'type') == 'hasMany')
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">{{ array_get($relation, 'name') }}</h3>
        </div>
        <div class="box-body">
            <table class="table">
＠foreach( ${{ $variableName }}->{{ array_get($relation, 'name') }}  as $relationModel )

＠endforeach
            </table>
        </div>
    </div>
@endif
@endforeach
＠stop
