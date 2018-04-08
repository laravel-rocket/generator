
Route::group(['prefix' => 'api', 'as' => 'api.', 'namespace' => 'Api'], function() {
    Route::group(['prefix' => '{{ strtolower($versionNamespace) }}', 'as' => '{{ strtolower($versionNamespace) }}.', 'namespace' => '{{ $versionNamespace }}'], function() {

    });
});
