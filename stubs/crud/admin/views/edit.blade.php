＠extends('layouts.admin.application', ['menu' => '{{ $viewName }}'] )

＠section('metadata')
＠stop

＠section('styles')
＠stop

＠section('scripts')
    <script src="｛｛ \URLHelper::asset('libs/moment/moment.min.js', 'admin') ｝｝"></script>
    <script src="｛｛ \URLHelper::asset('libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js', 'admin') ｝｝"></script>
    <script src="｛｛ \URLHelper::asset('libs/datetimepicker/js/bootstrap-datetimepicker.min.js', 'admin') ｝｝"></script>
    <script>
        $('.datetime-field').datetimepicker(｛'format': 'YYYY-MM-DD HH:mm:ss'｝);
        $('.date-field').datepicker(｛'format': 'YYYY-MM-DD'｝);
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
        ＠if( !empty(${{ $variableName }}->{{ $column['relation']  }}) )
        defaultPreviewContent: '<img src="｛!! ${{ $variableName }}->{{ $column['relation']  }}->getThumbnailUrl(200, 200) !!｝" alt="Your Avatar" style="width:100px">',
        ＠else
        defaultPreviewContent: '<img src="｛!! \URLHelper::asset('images/user.png', 'common') !!｝" alt="Your Avatar" style="width:100px">',
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
            <div class="row" data-column-name="{{ $column['name'] }}">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="form-group ＠if ($errors->has('{{ $column['name'] }}')) has-error ＠endif">
@if( $column['type'] == 'textarea')
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</label>
                    <textarea name="{{ $column['name'] }}" class="form-control" rows="5" placeholder="＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}.name')">｛!!  old('{{ $column['name'] }}') ? old('{{ $column['name'] }}') : ${{ $variableName }}->{{ $column['name'] }} !!｝</textarea>
@elseif( $column['type'] === 'boolean' || $column['type'] === 'json')
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</label><br/>
                    <input type="radio" name="{{ $column['name'] }}" value="0" ＠if( ${{ $variableName }}->{{ $column['name'] }} == 0 ) checked ＠endif> ＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}.booleans.false')
                    <input type="radio" name="{{ $column['name'] }}" value="1" ＠if( ${{ $variableName }}->{{ $column['name'] }} == 1 ) checked ＠endif> ＠lang('tables/{{ $viewName }}/columns.{{ $column['name'] }}.booleans.true')
@elseif( $column['type'] === 'password')
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</label>
                    <input type="password" class="form-control" id="{{ $column['name'] }}" name="{{ $column['name'] }}" value="">
@elseif( $column['type'] === 'date')
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</label>
                    <input type="text" class="datepicker form-control" id="{{ $column['name'] }}" name="{{ $column['name'] }}" value="">
@elseif( $column['type'] === 'image')
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</label><br/>
                    <div id="kv-avatar-errors-{{ $column['name'] }}" class="center-block" style="display:none;"></div>
                    <div class="kv-avatar center-block" style="width:160px">
                        <input id="{{ $column['name'] }}" name="{{ $column['name'] }}" type="file" class="file-loading">
                    </div>
@elseif( $column['type'] === 'file')
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</label><br/>
                    <input id="{{ $column['name'] }}" name="{{ $column['name'] }}" type="file">
@elseif( $column['type'] === 'select')
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</label>
                    <select name="{{ $column['name'] }}" id="{{ $column['name'] }}" class="select2 form-control">
＠foreach(\TypeHelper::getColumnTypes('{{ $tableName }}', '{{ $column['name'] }}') as $value => $name) as $option)
                        <option value="｛｛ $value ｝｝" ＠if( ( old('{{ $column['name'] }}') && old('{{ $column['name'] }}') == '｛｛ $value ｝｝') ||  ( !old('{{ $column['name'] }}') &&  ${{ $variableName }}->{{ $column['name'] }} == '｛｛ $value ｝｝' )) selected ＠endif >＠lang($name)</option>
＠endforeach
                    </select>
@elseif( $column['type'] === 'country')
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</label>
                    <select name="{{ $column['name'] }}" id="{{ $column['name'] }}" class="select2 form-control">
＠foreach(config('data.data.countries.country_codes.3digits', []) as $code => $key)
                        <option value="｛｛ $code ｝｝" ＠if( ( old('{{ $column['name'] }}') && old('{{ $column['name'] }}') == $code) ||  ( !old('{{ $column['name'] }}') &&  ${{ $variableName }}->{{ $column['name'] }} == $code) ) selected ＠endif >｛｛ \DataHelper::getCountryName($code, $code) ｝｝</option>
＠endforeach
                    </select>
@elseif( $column['type'] === 'currency')
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</label>
                    <select name="{{ $column['name'] }}" id="{{ $column['name'] }}" class="select2 form-control">
＠foreach(config('data.data.currencies.currency_codes', []) as $code => $key)
                        <option value="｛｛ $code ｝｝" ＠if( ( old('{{ $column['name'] }}') && old('{{ $column['name'] }}') == $code) ||  ( !old('{{ $column['name'] }}') &&  ${{ $variableName }}->{{ $column['name'] }} == $code ) ) selected ＠endif >｛｛ \DataHelper::getCurrencyName($code, $code) ｝｝</option>
＠endforeach
                    </select>
@elseif( $column['type'] == 'relation')
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</label>
                    <select name="{{ $column['name'] }}" id="{{ $column['name'] }}" class="select2 form-control">
                    </select>
@else
                    <label for="{{ $column['name'] }}">＠lang('tables/{{ $tableName }}/columns.{{ $column['name'] }}.name')</label>
                    <input type="text" class="form-control" id="{{ $column['name'] }}" name="{{ $column['name'] }}" value="｛｛ old('{{ $column['name'] }}') ? old('{{ $column['name'] }}') : ${{ $variableName }}->{{ $column['name'] }} ｝｝">
@endif
                </div>
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
