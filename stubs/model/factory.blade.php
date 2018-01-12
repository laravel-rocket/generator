/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\{{ $modelName }}::class, function (Faker\Generator $faker) {
@if( $authenticatable )
    static $password;
@endif

    return [
@foreach( $columns as $name => $value )
        '{{ $name }}' => {!! $value !!},
@endforeach
    ];
});
