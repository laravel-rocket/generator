namespace App\Http\Requests\Api\{{ $versionNamespace }};

class {{ $className }} extends Request
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
@foreach( $request->getParameters as $parameter )
@if( $parameter->isInRequest() && $parameter->isRequired() )
            '{{ $parameter->getName() }}' => 'required',
@endif
@endforeach
        ];
    }

    public function messages()
    {
        return [
@foreach( $request->getParameters as $parameter )
@if( $parameter->isInRequest() && $parameter->isRequired() )
            '{{ $parameter->getName() }}.required' => trans('validation.required'),
@endif
@endforeach
        ];
    }
}
