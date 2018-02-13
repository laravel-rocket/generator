use Illuminate\Database\Schema\Blueprint;
use LaravelRocket\Foundation\Database\Migration;

class {!! $className !!} extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('{{ $tableName }}', function($table) {
            /** @var $table \Illuminate\Database\Schema\Blueprint */
@foreach( $upMigrations['indexes']['drop'] as $index )
            {!! $index !!}
@endforeach
@foreach( $upMigrations['columns']['drop'] as $column )
            {!! $column !!}
@endforeach
@foreach( $upMigrations['columns']['add'] as $column )
            {!! $column !!}
@endforeach
@foreach( $upMigrations['indexes']['add'] as $index )
            {!! $index !!}
@endforeach
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('{{ $tableName }}', function($table) {
            /** @var $table \Illuminate\Database\Schema\Blueprint */
@foreach( $downMigrations['indexes']['drop'] as $index )
            {!! $index !!}
@endforeach
@foreach( $downMigrations['columns']['drop'] as $column )
            {!! $column !!}
@endforeach
@foreach( $downMigrations['columns']['add'] as $column )
            {!! $column !!}
@endforeach
@foreach( $downMigrations['indexes']['add'] as $index )
            {!! $index !!}
@endforeach
        });
    }
}
