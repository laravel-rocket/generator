return [
    'roles' => [
@foreach( $roles as $role )
        '{{ \Illuminate\Support\Arr::get($role, 'name', '') }}' => [
            'name'      => 'admin.roles.{{ \Illuminate\Support\Arr::get($role, 'name', '') }}',
            'sub_roles' => [
@foreach( \Illuminate\Support\Arr::get($role, 'subRoles', []) as $subRole )
                '{{ $subRole }}',
@endforeach
            ],
        ],
@endforeach
    ],
];
