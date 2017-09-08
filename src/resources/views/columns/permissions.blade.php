{{-- relationships with pivot table (n-n) --}}
<td>
    <?php

    // Gets the attribute to display the permission name
    $attribute = array_get($column, 'attribute');

    $permissions = $entry->{$column['entity']};

    // Groups permissions by prefix, sorted alphabetically
    $permissionsByPrefix = collect($permissions)
        ->sortBy(function($permission) {
            return $permission->prefix() ?: PHP_INT_MAX; // Use PHP_INT_MAX as a little trick for sorting permissions without prefix at the end
        })
        ->groupBy(function($permission) {
            return $permission->prefix();
        });
    ?>

    @if ($permissionsByPrefix->isNotEmpty())

        @foreach ($permissionsByPrefix as $prefix => $permissions)

            @if (!$loop->first)
                @if (array_get($column, 'inline'))
                    ,
                @else
                    <br />
                @endif
            @endif

            {{ $prefix }}

            <em>
                ({{ $permissions->map(function($permission) use ($attribute) {
                    if (is_callable($attribute)) {
                        return $attribute($permission);
                    } elseif (is_string($attribute)) {
                        return $permission->$attribute;
                    } else {
                        return $permission->item();
                    }
                })->implode(', ') }})
            </em>

        @endforeach

    @else
        -
    @endif
</td>
