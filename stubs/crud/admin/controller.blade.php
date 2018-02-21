namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Repositories\{{ $modelName }}RepositoryInterface;
use App\Repositories\FileRepositoryInterface;
use App\Models\{{ $modelName }};
use App\Http\Requests\Admin\{{ $modelName }}Request;
use LaravelRocket\Foundation\Http\Requests\PaginationRequest;

class {{ $modelName }}Controller extends Controller
{

    /** @var \App\Repositories\{{ $modelName }}RepositoryInterface */
    protected ${{ $variableName }}Repository;

    /** @var \App\Repositories\FileRepositoryInterface */
    protected $fileRepository;

    public function __construct(
        {{ $modelName }}RepositoryInterface ${{ $variableName }}Repository,
        FileRepositoryInterface $fileRepository
    )
    {
        $this->{{ $variableName }}Repository = ${{ $variableName }}Repository;
        $this->$fileRepository = $fileRepository;
    }

    /**
    * Display a listing of the resource.
    *
    * @param  \LaravelRocket\Foundation\Http\Requests\PaginationRequest $request
    * @return \Response|\Illuminate\Http\RedirectResponse
    */
    public function index(PaginationRequest $request)
    {
        $offset = $request->offset();
        $limit = $request->limit();
        $count = $this->{{ $variableName }}Repository->count();
        ${{ $pluralVariableName }} = $this->{{ $variableName }}Repository->get('id', 'desc', $offset, $limit);

        return view('pages.admin.{{ $viewName }}.index', [
            'models'  => ${{ $pluralVariableName }},
            'count'   => $count,
            'offset'  => $offset,
            'limit'   => $limit,
            'baseUrl' => action('Admin\{{ $modelName }}Controller@index'),
        ]);
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Response|\Illuminate\Http\RedirectResponse
    */
    public function create()
    {
        return view('pages.admin.{{ $viewName }}.edit', [
            'isNew'     => true,
            '{{ $variableName }}' => $this->{{ $variableName }}Repository->getBlankModel(),
        ]);
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  $request
    * @return \Response|\Illuminate\Http\RedirectResponse
    */
    public function store({{ $modelName }}Request $request)
    {
        $input = $request->only([
@foreach( $fillableColumns as $column )
            '{{ $column }}',
@endforeach
        ]);
@if( count($booleanColumns) > 0)

@foreach( $booleanColumns as $booleanColumn )
        $input['{{ $booleanColumn }}'] = $request->get('{{ $booleanColumn }}', 0);
@endforeach
@endif
@if( count($timestampColumns) > 0)

        $timestampColumns     = [
@foreach( $timestampColumns as $timestampColumn )
            '{{ $timestampColumn }}',
@endforeach
        ];
        foreach ($timestampColumns as $timestampColumn) {
            if (array_key_exists($timestampColumn, $input) && !empty($input[$timestampColumn])) {
                $input[$timestampColumn] = \DateTimeHelper::convertToStorageDateTime($input[$timestampColumn]);
            } else {
                $input[$timestampColumn] = null;
            }
        }
@endif
@if( count($unixTimestampColumns) > 0)
        $unixTimestampColumns     = [
@foreach( $unixTimestampColumns as $unixTimestampColumn )
            '{{ $unixTimestampColumn }}',
@endforeach
        ];
        foreach ($unixTimestampColumns as $unixTimestampColumn) {
            if (array_key_exists($unixTimestampColumn, $input) && !empty($input[$unixTimestampColumn])) {
                $input[$unixTimestampColumn] = (\DateTimeHelper::convertToStorageDateTime($input[$unixTimestampColumn]))->timestamp;
            } else {
                $input[$unixTimestampColumn] = 0;
            }
        }
@endif

@if( count($fileColumns) > 0)
    @foreach( $fileColumns as $fileColumn)
        if ($request->hasFile('{{ $fileColumn }}')) {
            $file                     = $request->file('{{ $fileColumn }}');
            $mediaType                = $file->getClientMimeType();
            $path                     = $file->getPathname();
            $file                     = $this->fileService->upload('default-file', $path, $mediaType, []);
            if( !empty($file) ){
                $input[{{ $fileColumn }}] = $file->id;
            }
        }
    @endforeach
@endif

@if( count($imageColumns) > 0)
    @foreach( $imageColumns as $imageColumn)
        if ($request->hasFile('{{ $imageColumn }}')) {
            $file                     = $request->file('{{ $imageColumn }}');
            $mediaType                = $file->getClientMimeType();
            $path                     = $file->getPathname();
            $file                     = $this->fileService->upload('default-image', $path, $mediaType, []);
            if( !empty($file) ){
                $input['{{ $imageColumn }}'] = $file->id;
            }
        }
    @endforeach
@endif

        ${{ $variableName }} = $this->{{ $variableName }}Repository->create($input);

        if (empty( ${{ $variableName }} )) {
            return redirect()->back()->withErrors(trans('admin.errors.general.save_failed'));
        }

        return redirect()->action('Admin\{{ $modelName }}Controller@index')
            ->with('message-success', trans('admin.messages.general.create_success'));
    }

    /**
    * Display the specified resource.
    *
    * @param  int $id
    * @return \Response|\Illuminate\Http\RedirectResponse
    */
    public function show($id)
    {
        ${{ $variableName }} = $this->{{ $variableName }}Repository->find($id);
        if (empty( ${{ $variableName }} )) {
            abort(404);
        }

        return view('pages.admin.{{ $viewName }}.edit', [
            'isNew' => false,
            '{{ $variableName }}' => ${{ $variableName }},
        ]);
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int $id
    * @return \Response|\Illuminate\Http\RedirectResponse
    */
    public function edit($id)
    {
        return redirect()->action('Admin\{{ $modelName }}Controller＠show', [$id]);
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  int $id
    * @param      $request
    * @return \Response|\Illuminate\Http\RedirectResponse
    */
    public function update($id, {{ $modelName }}Request $request)
    {
        ${{ $variableName }} = $this->{{ $variableName }}Repository->find($id);
        if (empty( ${{ $variableName }} )) {
            abort(404);
        }

        $input = $request->only([
@foreach( $fillableColumns as $column )
            '{{ $column }}',
@endforeach
        ]);
@if( count($booleanColumns) > 0)

@foreach( $booleanColumns as $booleanColumn )
        $input['{{ $booleanColumn }}'] = $request->get('{{ $booleanColumn }}', 0);
@endforeach
@endif
@if( count($timestampColumns) > 0)

        $timestampColumns     = [
@foreach( $timestampColumns as $timestampColumn )
            '{{ $timestampColumn }}',
@endforeach
        ];
        foreach ($timestampColumns as $timestampColumn) {
            if (array_key_exists($timestampColumn, $input) && !empty($input[$timestampColumn])) {
                $input[$timestampColumn] = \DateTimeHelper::convertToStorageDateTime($input[$timestampColumn]);
            } else {
                $input[$timestampColumn] = null;
            }
        }
@endif
@if( count($unixTimestampColumns) > 0)
        $unixTimestampColumns     = [
@foreach( $unixTimestampColumns as $unixTimestampColumn )
            '{{ $unixTimestampColumn }}',
@endforeach
        ];
        foreach ($unixTimestampColumns as $unixTimestampColumn) {
            if (array_key_exists($unixTimestampColumn, $input) && !empty($input[$unixTimestampColumn])) {
                $input[$unixTimestampColumn] = (\DateTimeHelper::convertToStorageDateTime($input[$unixTimestampColumn]))->timestamp;
            } else {
                $input[$unixTimestampColumn] = 0;
            }
        }
@endif

@if( count($fileColumns) > 0)
@foreach( $fileColumns as $fileColumn)
        if ($request->hasFile('{{ $fileColumn }}')) {
            $file                     = $request->file('{{ $fileColumn }}');
            $mediaType                = $file->getClientMimeType();
            $path                     = $file->getPathname();
            $file                     = $this->fileService->upload('default-file', $path, $mediaType, []);
            if( !empty($file) ){
                $oldFile = $this->fileRepository->find(${{ $variableName }}->{{ $fileColumn }});
                if (!empty($oldFile)) {
                    $this->fileService->delete($oldFile);
                }
                $updates['{{ $fileColumn }}'] = $file->id;
            }
        }
@endforeach
@endif

@if( count($imageColumns) > 0)
@foreach( $imageColumns as $imageColumn)
        if ($request->hasFile('{{ $imageColumn }}')) {
            $file                     = $request->file('{{ $imageColumn }}');
            $mediaType                = $file->getClientMimeType();
            $path                     = $file->getPathname();
            $file                     = $this->fileService->upload('default-image', $path, $mediaType, []);
            if( !empty($file) ){
                $oldFile = $this->fileRepository->find(${{ $variableName }}->{{ $imageColumn }});
                if (!empty($oldFile)) {
                    $this->fileService->delete($oldFile);
                }
                $updates['{{ $imageColumn }}'] = $file->id;
            }
        }
@endforeach
@endif

        $this->{{ $variableName }}Repository->update(${{ $variableName }}, $input);

        return redirect()->action('Admin\{{ $modelName }}Controller＠show', [$id])
            ->with('message-success', trans('admin.messages.general.update_success'));
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int $id
    * @return \Response|\Illuminate\Http\RedirectResponse
    */
    public function destroy($id)
    {
        ${{ $variableName }} = $this->{{ $variableName }}Repository->find($id);
        if (empty( ${{ $variableName }} )) {
            abort(404);
        }

@if( count($fileColumns) > 0)
@foreach( $fileColumns as $fileColumn)
        $file = $this->fileRepository->find(${{ $variableName }}->{{ $fileColumn }});
        if (!empty($file)) {
            $this->fileService->delete($file);
        }

@endforeach
@endif
@if( count($imageColumns) > 0)
@foreach( $imageColumns as $imageColumn)
        $file = $this->fileRepository->find(${{ $variableName }}->{{ $imageColumn }});
        if (!empty($file)) {
            $this->fileService->delete($file);
        }

@endforeach
@endif
        $this->{{ $variableName }}Repository->delete(${{ $variableName }});

        return redirect()->action('Admin\{{ $modelName }}Controller@index')
            ->with('message-success', trans('admin.messages.general.delete_success'));
    }

}
