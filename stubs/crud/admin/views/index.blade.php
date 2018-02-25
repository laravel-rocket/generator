＠extends('layouts.admin.application', ['menu' => '{{ $viewName }}'] )

＠section('metadata')
＠stop

＠section('styles')
＠stop

＠section('scripts')
    <script src="｛!! \URLHelper::asset('js/delete_item.js', 'admin') !!｝"></script>
＠stop

＠section('title')
＠stop

＠section('header')
{{ $modelName }}
＠stop

＠section('breadcrumb')
    <li class="active">{{ $modelName }}</li>
＠stop

＠section('content')
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">
                <p class="text-right">
                    <a href="｛!! action('Admin\{{ $modelName }}Controller＠create') !!｝" class="btn btn-block btn-primary btn-sm">＠lang('admin.pages.common.buttons.create')</a>
                </p>
            </h3>
            ｛!! \PaginationHelper::render($offset, $limit, $count, $baseUrl, []) !!｝
        </div>
        <div class="box-body scroll">
            <table class="table table-bordered">
                <tr>
                    <th style="width: 10px">ID</th>
@foreach( $listColumns as $column)
                    <th>＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</th>
@endforeach
                    <th style="width: 40px">&nbsp;</th>
                </tr>
                ＠foreach( $models as $model )
                <tr>
                    <td>｛｛ $model->id ｝｝</td>
@foreach( $listColumns as $column)
                    <td>
@if( array_key_exists($column['name'], $belongsToRelations) )
@if( $column['type'] === 'image' )
＠if( $model->{{ $belongsToRelations[$column['name']]['name'] }} )
                                <img src="｛｛ $model->{{ $belongsToRelations[$column['name']]['name'] }}->present()->url ｝｝" class="img-thumbnail" width="50" height="50">
＠else
                                <img src="｛｛ \URLHelper::asset('images/no-image.png', 'common') ｝｝" class="img-thumbnail" width="50" height="50">
＠endif
@elseif( $column['type'] === 'file' )
＠if( $model->{{ $belongsToRelations[$column['name']]['name'] }} )
                                <a href="｛｛ $model->{{ $belongsToRelations[$column['name']]['name'] }}->present()->url ｝｝">｛!! \FileHelper::getFileIconHTML($model->{{ $belongsToRelations[$column['name']]['name'] }}->mime_type) !!｝｛｛ $model->{{ $belongsToRelations[$column['name']]['name'] }}->present()->toString() ｝｝</a>
＠endif
@else
                                ｛｛ $model->{{ $belongsToRelations[$column['name']]['name'] }} ? $model->{{ $belongsToRelations[$column['name']]['name'] }}->present()->toString() : '' ｝｝
@endif
@elseif( $column['type'] == 'int' || $column['type'] == 'int')
                                ｛｛ $model->present()->{{ $column['name'] }} ｝｝
@elseif( $column['type'] == 'boolean')
                                    ＠if( $model->{{ $column['name'] }} )
                                    <span class="badge bg-green">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.booleans.true')</span>
                                    ＠else
                                    <span class="badge bg-red">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.booleans.false')</span>
                                    ＠endif
@elseif( $column['type'] == 'relation')
                                    ｛｛ $model->{{ $column['relation'] }}->present()->{{ $column['name'] }} ｝｝
@else
                                    ｛｛ $model->present()->{{ $column['name'] }} ｝｝
@endif
                    </td>
@endforeach
                        <td>
                            <a href="｛!! action('Admin\{{ $modelName }}Controller＠show', $model->id) !!｝" class="btn btn-block btn-primary btn-sm"><i class="far fa-file-alt"></i> ＠lang('admin.pages.common.buttons.show')</a>
                            <a href="｛!! action('Admin\{{ $modelName }}Controller＠edit', $model->id) !!｝" class="btn btn-block btn-primary btn-sm"><i class="fas fa-edit"></i> ＠lang('admin.pages.common.buttons.edit')</a>
                            <a href="#" class="btn btn-block btn-danger btn-sm delete-button" data-delete-url="｛!! action('Admin\{{ $modelName }}Controller＠destroy', $model->id) !!｝"><i class="fas fa-trash-alt"></i> ＠lang('admin.pages.common.buttons.delete')</a>
                        </td>
                    </tr>
                ＠endforeach
            </table>
        </div>
        <div class="box-footer">
            ｛!! \PaginationHelper::render($offset, $limit, $count, $baseUrl, []) !!｝
        </div>
    </div>
＠stop
