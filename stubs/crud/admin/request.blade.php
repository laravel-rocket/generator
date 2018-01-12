namespace App\Http\Requests\Admin;

use LaravelRocket\Foundation\Http\Requests\Request;
use App\Repositories\{{ $modelName }}RepositoryInterface;

class {{ $modelName }}Request extends Request
{

    /** @var \App\Repositories\{{ $modelName }}RepositoryInterface */
    protected ${{ $variableName }}Repository;

    public function __construct({{ $modelName }}RepositoryInterface ${{ $variableName }}Repository)
    {
        parent::__construct();
        $this->{{ $variableName }}Repository = ${{ $variableName }}Repository;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->{{ $variableName }}Repository->rules();
    }

    public function messages()
    {
        return $this->{{ $variableName }}Repository->messages();
    }

}
