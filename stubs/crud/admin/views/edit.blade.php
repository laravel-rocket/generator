＠extends('layouts.admin.application', ['menu' => '{{ $viewName }}'] )

＠section('metadata')
＠stop

＠section('styles')
＠stop

＠section('scripts')
    <script src="｛｛ \URLHelper::asset('libs/moment/moment.min.js', 'admin') ｝｝"></script>
    <script src="｛｛ \URLHelper::asset('libs/datetimepicker/js/bootstrap-datetimepicker.min.js', 'admin') ｝｝"></script>
    <script>
        $('.datetime-field').datetimepicker(｛'format': 'YYYY-MM-DD HH:mm:ss'｝);
    </script>
＠stop

＠section('title')
＠stop

＠section('header')
    {{ $modelName }}
＠stop

＠section('breadcrumb')
    <li><a href="｛!! action('Admin\{{ $modelName }}Controller＠index') !!｝"><i class="fa fa-files-o"></i> {{ $modelName }}</a></li>
    ＠if( $isNew )
        <li class="active">New</li>
    ＠else
        <li class="active">｛｛ ${{ $variableName }}->id ｝｝</li>
    ＠endif
＠stop

＠section('content')
＠if (count($errors) > 0)
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <ul>
＠foreach ($errors->all() as $error)
                    <li>｛｛ $error ｝｝</li>
＠endforeach
            </ul>
        </div>
＠endif

＠if( $isNew )
        <form action="｛!! action('Admin\{{ $modelName }}Controller＠store') !!｝" method="POST" enctype="multipart/form-data">
＠else
        <form action="｛!! action('Admin\{{ $modelName }}Controller＠update', [${{ $variableName }}->id]) !!｝" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_method" value="PUT">
＠endif
        ｛!! csrf_field() !!｝
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"></h3>
            </div>
            <div class="box-body">
@foreach( $editableColumns as $column)
            <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
@if( $column['type'] == 'int' || $column['type'] == 'int')
                <div class="form-group ＠if ($errors->has('{{ $column['name'] }}')) has-error ＠endif">
                    <label for="{{ $column['name'] }}">＠lang(＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')')</label>
                    <input type="text" class="form-control" id="{{ $column['name'] }}" name="{{ $column['name'] }}" value="｛｛ old('{{ $column['name'] }}') ? old('{{ $column['name'] }}') : ${{ $variableName }}->{{ $column['name'] }} ｝｝">
                </div>
@elseif( $column['type'] == 'text' || $column['type'] == 'mediumText' || $column['type'] == 'longText')
                <div class="form-group ＠if ($errors->has('{{ $column['name'] }}')) has-error ＠endif">
                    <label for="{{ $column['name'] }}">＠lang(＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')')</label>
                    <textarea name="{{ $column['name'] }}" class="form-control" rows="5" placeholder="＠lang(＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')')">｛!!  old('{{ $column['name'] }}') ? old('{{ $column['name'] }}') : ${{ $variableName }}->{{ $column['name'] }} !!｝</textarea>
                </div>

@elseif( $column['type'] == 'boolean')
                <td>
                    ＠if( $model->{{ $column['name'] }} )
                    <span class="badge bg-green">＠lang(＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')_true')</span>
                    ＠else
                    <span class="badge bg-red">＠lang(＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')_false')</span>
                    ＠endif
                </td>
@elseif( $column['type'] == 'relation')
                <div class="form-group @if ($errors->has('type')) has-error @endif">
                    <label for="{{ $column['name'] }}">＠lang(＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')')</label>
                    <select name="{{ $column['name'] }}" id="{{ $column['name'] }}" class="select2 form-control">
                    </select>
                </div>
@else
                <div class="form-group ＠if ($errors->has('{{ $column['name'] }}')) has-error ＠endif">
                    <label for="{{ $column['name'] }}">＠lang(＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')')</label>
                    <input type="text" class="form-control" id="{{ $column['name'] }}" name="{{ $column['name'] }}" value="｛｛ old('{{ $column['name'] }}') ? old('{{ $column['name'] }}') : ${{ $variableName }}->{{ $column['name'] }} ｝｝">
                </div>
@endif
            </div>
            </div>
@endforeach
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">＠lang('admin.pages.common.buttons.save')</button>
            </div>
        </div>
    </form>
＠stop
