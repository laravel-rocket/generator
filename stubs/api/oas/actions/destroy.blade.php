/**
* PATH: {{ $action->getHttpMethod() }} {{ $action->getPath() }}
@foreach( $action->getParams() as $param )
    * @param {{ $param }}
@endforeach
* @param {{ $action->getRequest()->getName() }} $request
*
* @return \Illuminate\Http\JsonResponse
* @throws \App\Exceptions\Api\{{ $versionNamespace }}\APIErrorException
*/
public function {{ $action->getMethod() }}({{ implode(',', $action->getParams() ) }}{{ count($action->getParams()) > 0 ? ', ' : '' }}{{ $action->getRequest()->getName() }} $request)
{
    $model = $this->{{ lcfirst($action->getResponse()->getModelName()) }}Repository->find($id);
    if (empty($model) ) {
        throw new APIErrorException('notFound', 'Not found');
    }

    $model = $this->{{ lcfirst($action->getResponse()->getModelName()) }}Repository->delete($model);

    return Status::ok()->response();
}
