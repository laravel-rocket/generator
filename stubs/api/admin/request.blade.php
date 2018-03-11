namespace App\Http\Requests\Api\Admin\{{ $requestNameSpace }};

use App\Http\Requests\Api\Admin\{{ $baseClass }};

class {{ $className }} extends {{ $baseClass }}
{
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
        return [
        ];
    }

    public function messages()
    {
        return [
        ];
    }
}
