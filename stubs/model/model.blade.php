namespace App\Models;

@if( $authenticatable )
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use LaravelRocket\Foundation\Models\AuthenticatableBase;
@else
use LaravelRocket\Foundation\Models\Base;
@endif
@if( $softDelete )
use Illuminate\Database\Eloquent\SoftDeletes;
@endif

/**
 * App\Models\{{ $className }}.
 *
 * @@method \App\Presenters\{{ $className }}Presenter present()
 *
 */

class {{ $className }} extends {{ $authenticatable ? 'AuthenticatableBase' : 'Base' }}
{

@if( $softDelete )
    use SoftDeletes;
@endif
@if( $authenticatable )
    use HasApiTokens, Notifiable;
@endif
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = '{{ $tableName }}';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
@foreach( $fillables as $fillable)
        '{{ $fillable }}',
@endforeach
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $dates  = [
@foreach( $timestamps as $timestamp)
    '{{ $timestamp }}',
@endforeach
    ];

    protected $presenter = \App\Presenters\{{ $className }}Presenter::class;

    // Relations
@foreach( $relations as $relation)
    @if( $relation['type'] === 'belongsTo')
    public function {{  $relation['name'] }}()
    {
        return $this->belongsTo(\App\Models\{{ $relation['referenceModel'] }}::class, '{{ $relation['referenceColumn']->getName() }}', '{{ $relation['column']->getName() }}');
    }
    @elseif( $relation['type'] === 'hasMany')
    public function {{  $relation['name'] }}()
    {
        return $this->hasMany(\App\Models\{{ $relation['referenceModel'] }}::class, '{{ $relation['referenceColumn']->getName() }}', '{{ $relation['column']->getName() }}');
    }
    @elseif( $relation['type'] === 'hasOne')
    public function {{  $relation['name'] }}()
    {
        return $this->hasOne(\App\Models\{{ $relation['referenceModel'] }}::class, '{{ $relation['referenceColumn']->getName() }}', '{{ $relation['column']->getName() }}');
    }
    @elseif( $relation['type'] === 'belongsToMany')
    public function {{  $relation['name'] }}()
    {
        return $this->belongsToMany(\App\Models\{{ $relation['referenceModel'] }}::class, '{{ $relation['relationTable'] }}', '{{ $relation['referenceColumn']->getName() }}', '{{ $relation['column']->getName() }}');
    }
    @endif
@endforeach

    // Utility Functions

}
