namespace App\Presenters;

use LaravelRocket\Foundation\Presenters\BasePresenter;

/**
 *
 * @property \App\Models\{{ $modelName }} $entity
@foreach($columns as $name => $type)
 * @property {{ $type }} ${{ $name }}
@endforeach
 */

class {{ $modelName }}Presenter extends BasePresenter
{

    protected $multilingualFields = [
@foreach( $multilingualFields as $multilingualField )
    '{{ $multilingualField }}',
@endforeach
    ];

    protected $imageFields = [
@foreach( $imageColumns as $imageColumn )
    '{{ $imageColumn }}',
@endforeach
    ];

}
