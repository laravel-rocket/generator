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
@foreach( $editableColumns as $column)
@if( $column['type'] === 'image')
    <script>
    $("#{{ $column['name'] }}").fileinput({
        overwriteInitial: true,
        maxFileSize: 1500,
        showClose: false,
        showCaption: false,
        browseLabel: '',
        removeLabel: '',
        browseIcon: '<i class="glyphicon glyphicon-folder-open"></i>',
        removeIcon: '<i class="glyphicon glyphicon-remove"></i>',
        removeTitle: 'Cancel or reset changes',
        elErrorContainer: '#kv-avatar-errors-{{ $column['name'] }}',
        msgErrorClass: 'alert alert-block alert-danger',
        ＠if( !empty($user->{{ $column['relation']  }}) )
        defaultPreviewContent: '<img src="{!! $user->{{ $column['relation']  }}->getThumbnailUrl(200, 200) !!}" alt="Your Avatar" style="width:100px">',
        ＠else
        defaultPreviewContent: '<img src="{!! \URLHelper::asset('img/user.png', 'common') !!}" alt="Your Avatar" style="width:100px">',
        ＠endif
        layoutTemplates: {main2: '{preview} {remove} {browse}'},
        allowedFileExtensions: ["jpg", "png", "gif", "jpeg"]
    });
    </script>
@endif
@endforeach

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
@if( $column['type'] == 'textarea')
                <div class="form-group ＠if ($errors->has('{{ $column['name'] }}')) has-error ＠endif">
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')</label>
                    <textarea name="{{ $column['name'] }}" class="form-control" rows="5" placeholder="＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')">｛!!  old('{{ $column['name'] }}') ? old('{{ $column['name'] }}') : ${{ $variableName }}->{{ $column['name'] }} !!｝</textarea>
                </div>
@elseif( $column['type'] === 'boolean')
                <td>
                    ＠if( $model->{{ $column['name'] }} )
                    <span class="badge bg-green">＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')_true')</span>
                    ＠else
                    <span class="badge bg-red">＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')_false')</span>
                    ＠endif
                </td>
@elseif( $column['type'] === 'password')
                <div class="form-group ＠if ($errors->has('{{ $column['name'] }}')) has-error ＠endif">
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')</label>
                    <input type="password" class="form-control" id="{{ $column['name'] }}" name="{{ $column['name'] }}" value="">
                </div>
@elseif( $column['type'] === 'image')

                <div class="row">
                    <div class="col-md-12">

                        <div id="kv-avatar-errors-{{ $column['name'] }}" class="center-block" style="display:none;"></div>
                        <div class="kv-avatar center-block" style="width:160px">
                            <input id="{{ $column['name'] }}" name="{{ $column['name'] }}" type="file" class="file-loading">
                        </div>

                    </div>
                </div>

@elseif( $column['type'] === 'select')
                <div class="form-group ＠if ($errors->has('{{ $column['name'] }}')) has-error ＠endif">
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')</label>
                <select name="{{ $column['name'] }}" id="{{ $column['name'] }}" class="select2 form-control">
@foreach($column['options'] as $option)
                        <option value="{{ array_get($option, 'value') }}">＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}_options.{{ array_get($option, 'value') }}')</option>
@endforeach
                    </select>
                </div>
@elseif( $column['type'] == 'relation')
                <div class="form-group @if ($errors->has('type')) has-error @endif">
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')</label>
                    <select name="{{ $column['name'] }}" id="{{ $column['name'] }}" class="select2 form-control">
                    </select>
                </div>
@else
                <div class="form-group ＠if ($errors->has('{{ $column['name'] }}')) has-error ＠endif">
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}')</label>
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
