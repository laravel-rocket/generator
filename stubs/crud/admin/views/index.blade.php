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
        <div class="box-body">
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
@if( array_key_exists($column['name'], $belongsToRelations) )
                                <td>｛｛ $model->{{ $belongsToRelations[$column['name']]['name'] }}->present()->toString() ｝｝</td>
@elseif( $column['type'] == 'int' || $column['type'] == 'int')
                                <td>｛｛ $model->present()->{{ $column['name'] }} ｝｝</td>
@elseif( $column['type'] == 'boolean')
                                <td>
                                    ＠if( $model->{{ $column['name'] }} )
                                    <span class="badge bg-green">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.booleans.true')</span>
                                    ＠else
                                    <span class="badge bg-red">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.booleans.false')</span>
                                    ＠endif
                                </td>
@elseif( $column['type'] == 'relation')
                                <td>｛｛ $model->{{ $column['relation'] }}->present()->{{ $column['name'] }} ｝｝</td>
@else
                                <td>｛｛ $model->present()->{{ $column['name'] }} ｝｝</td>
@endif
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
