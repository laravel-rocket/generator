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
@foreach( $upColumns as $column )
    {!! $column !!}
@endforeach
@foreach( $upIndexes as $index )
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
@foreach( $downIndexes as $index )
    {!! $index !!}
@endforeach
@foreach( $downColumns as $column )
    {!! $column !!}
@endforeach
        });
    }
}
