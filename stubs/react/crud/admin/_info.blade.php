export default {
  path: '/{{ $pathName }}',
  title: '{{ $title }}',
  exportable: {{ $table->isExportable() ? 'true' : 'false' }},
}
