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
		Schema::create('{{ $tableName }}', function(Blueprint $table) {
			$table->bigIncrements('id');

@foreach( $columns as $column )
			{!! $column !!}
@endforeach

@if( $hasSoftDelete )
            $table->softDeletes();
@endif
@if( $hasRememberToken )
            $table->rememberToken();
@endif
            $table->timestamps();

@foreach( $indexes as $index )
			{!! $index !!}
@endforeach

		});

		$this->updateTimestampDefaultValue('{{ $tableName }}', ['updated_at'], ['created_at']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('{!! $tableName !!}');
	}
}
