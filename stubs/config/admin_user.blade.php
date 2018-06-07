return [
    'roles' => [
@foreach( $roles as $role )
        '{{ array_get($role, 'name', '') }}' => [
            'name'      => 'admin.roles.{{ array_get($role, 'name', '') }}',
            'sub_roles' => [
@foreach( array_get($role, 'subRoles', []) as $subRole )
                '{{ $subRole }}',
@endforeach
            ],
        ],
@endforeach
    ],
];
