<!-- permissions and roles -->
<div class="col-md-12 checklist_dependency"  data-entity ="{{ $field['field_unique_name'] }}" @include('crud::inc.field_wrapper_attributes')>

    @if (!empty($field['label']))
        <label>{!! $field['label'] !!}</label>
    @endif

    @include('crud::inc.field_translatable_icon')

    <?php
    // Sanitizes the field value
    $field['value'] = isset($field['value']) && is_array($field['value']) ? $field['value'] : null;

    $fieldRole = $field['subfields']['primary'];
    $fieldPermission = $field['subfields']['secondary'];

    // Gets the roles with their permissions
    $roles = $fieldRole['model']::with($fieldRole['entity_secondary'])->get();

    // Builds a simple matrix of roles and permissions (role id as key and array of permission ids as value)
    $rolesPermissions = $roles->pluck($fieldRole['entity_secondary'], 'id')->map(function($permissions) {
        return $permissions->pluck('id');
    });

    // Gets the entity roles and permissions
    $entityRoles = collect(is_array($field['value']) && isset($field['value'][0]) ? $field['value'][0] : []);
    $entityPermissions = collect(is_array($field['value']) && isset($field['value'][1]) ? $field['value'][0] : []);

    // Gets the permissions granted by the entity roles (update form only)
    $entityRolesPermissions = collect();

    // Gets the entity with roles and permissions
    $entity = ($crud->getModel())->with($fieldRole['entity'])
        ->with($fieldRole['entity'].'.'.$fieldRole['entity_secondary'])
        ->find($id);

    // Gets the permissions of each role related to the entity
    $oldRoles = old($fieldRole['name']);
    if ($oldRoles) {
        // ...from previous input (validation error)
        $selectedRoles = $roles->filter(function($role) use ($oldRoles) {
            return in_array($role->id, $oldRoles);
        });
    } else {
        // ...from current item
        $selectedRoles = $entity->{$fieldRole['entity']};
    }

    // Converts to a flat list
    $entityRolesPermissions = $selectedRoles->map(function($role) use ($fieldPermission) {
        return $role->{$fieldPermission['entity']}->pluck('id');
    })->flatten(1);

    // Groups permissions by prefix
    $permissionsByPrefix = $fieldPermission['model']::all()
        ->sortBy(function($permission) {
            return $permission->prefix() ?: PHP_INT_MAX; // Use PHP_INT_MAX as a little trick for sorting permissions without prefix at the end
        })
        ->groupBy(function($permission) {
            return $permission->prefix();
        });

    // Checks if there is at least one permission with a prefix
    $permissionWithPrefixExists = $permissionsByPrefix->keys()->filter()->isNotEmpty();

    ?>
    <script>
        var  {{ $field['field_unique_name'] }} = {!! $rolesPermissions->toJson() !!};
    </script>

    <div class="row form-group">

        <div class="col-xs-12">
            <label>{!! $fieldRole['label'] !!}</label>
        </div>

        <div class="hidden_fields_primary" data-name = "{{ $fieldRole['name'] }}">
            @if (isset($field['value']))
                @if (old($fieldRole['name']))
                    @foreach(old($fieldRole['name']) as $item)
                        <input type="hidden" class="primary_hidden" name="{{ $fieldRole['name'] }}[]" value="{{ $item }}">
                    @endforeach
                @else
                    @foreach($field['value'][0]->pluck('id', 'id')->toArray() as $item)
                        <input type="hidden" class="primary_hidden" name="{{ $fieldRole['name'] }}[]" value="{{ $item }}">
                    @endforeach
                @endif
            @endif
        </div>

        <?php $roleColumns = array_get($fieldRole, 'columns') ?>

        @if (is_bool($roleColumns))
            <div class="col-sm-12">
        @endif

        @foreach ($fieldRole['model']::all() as $role)
            @if (is_int($roleColumns))
                <div class="col-sm-{{ is_int($roleColumns) ? intval(12 / $roleColumns) : '12' }}">
            @endif
                <div class="checkbox {{ $roleColumns === true ? 'inline' : '' }}">
                    <label>
                        <input
                            type="checkbox"
                            data-id = "{{ $role->id }}"
                            class="primary_list"
                            @foreach ($fieldRole as $attribute => $value)
                                @if (is_string($attribute) && $attribute != 'value')
                                    @if ($attribute=='name')
                                        {{ $attribute }}="{{ $value }}_show[]"
                                    @else
                                        {{ $attribute }}="{{ $value }}"
                                    @endif
                                @endif
                            @endforeach
                            value="{{ $role->id }}"
                            @if ((is_array($field['value']) && ($field['value'][0]->pluck('id')->contains($role->id))) || (old($fieldRole["name"]) && in_array($role->id, old($fieldRole["name"]))))
                                checked = "checked"
                            @endif >
                            @if (is_callable($fieldRole['attribute']))
                                {{ $fieldRole['attribute']($role) }}
                            @elseif (is_string($fieldRole['attribute']))
                                {{ $role->{$fieldRole['attribute']} }}
                            @else
                                {{ $role->name }}
                            @endif
                    </label>
                    {{ $roleColumns === true ? '&nbsp;' : '' }}
                </div>
            @if (is_int($roleColumns))
            </div>
            @endif
        @endforeach

        @if (is_bool($roleColumns))
            </div>
        @endif

    </div>

    <div class="row form-group">
        <div class="col-xs-10">
            <label>{!! $fieldPermission['label'] !!}</label>
        </div>
        <div class="col-sm-2">
            <div class="pull-right">
                <button class="btn btn-default btn-xs uncheck-all" title="Uncheck all">
                    <i class="fa fa-square-o"></i>&nbsp; None
                </button>
                &nbsp;
                <button href="" class="btn btn-default btn-xs check-all" title="Check all">
                    <i class="fa fa-check-square-o"></i>&nbsp; All
                </button>
            </div>
        </div>

        <div class="hidden_fields_secondary" data-name="{{ $fieldPermission['name'] }}">
            @if (isset($field['value']))
                @if (old($fieldPermission['name']))
                    @foreach(old($fieldPermission['name']) as $item)
                        <input type="hidden" class="secondary_hidden" name="{{ $fieldPermission['name'] }}[]" value="{{ $item }}">
                    @endforeach
                @else
                    @foreach($field['value'][1]->pluck('id')->toArray() as $item)
                        <input type="hidden" class="secondary_hidden" name="{{ $fieldPermission['name'] }}[]" value="{{ $item }}">
                    @endforeach
                @endif
            @endif
        </div>

        <div class="col-sm-12">
            @foreach ($permissionsByPrefix as $prefix => $permissions)
                <hr/>
                <div class="row">
                    @if ($permissionWithPrefixExists)
                        <div class="col-sm-3">
                            <label class="no-margin">
                                <strong>{{ $prefix }}</strong>
                            </label>
                        </div>
                    @endif
                    <div class="col-sm-{{ $permissionWithPrefixExists ? 7 : 12 }}">
                        @foreach ($permissions as $permission)
                            <?php
                            $value = array_get($field, 'value');
                            $hasPermissionViaUser = ($value[1]->pluck('id')->contains($permission->id)) || (old($fieldPermission['name']) && in_array($permission->id, old($fieldPermission['name'])));
                            $hasPermissionViaRole = $entityRolesPermissions->contains($permission->id);
                            ?>
                            <div class="checkbox inline no-margin">
                                <label>
                                    <input
                                        type="checkbox"
                                        class="secondary_list"
                                        data-id="{{ $permission->id }}"
                                        @foreach ($fieldPermission as $attribute => $value)
                                            @if (is_string($attribute) && $attribute != 'value' && !is_callable($value))
                                                @if ($attribute=='name')
                                                    {{ $attribute }}="{{ $value }}_show[]"
                                                @else
                                                    {{ $attribute }}="{{ $value }}"
                                                @endif
                                            @endif
                                        @endforeach
                                        value="{{ $permission->id }}"
                                        @if ($hasPermissionViaUser || $hasPermissionViaRole)
                                            checked = "checked"
                                        @endif
                                        @if ($hasPermissionViaRole)
                                            disabled = disabled
                                        @endif
                                    >
                                    @if (is_callable($fieldPermission['attribute']))
                                        {{ $fieldPermission['attribute']($permission) }}
                                    @elseif (is_string($fieldPermission['attribute']))
                                        {{ $permission->{$fieldPermission['attribute']} }}
                                    @else
                                        {{ $permission->item() }}
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @if ($permissionWithPrefixExists)
                        <div class="col-sm-2">
                            <div class="pull-right">
                                <button href="" class="btn btn-default btn-xs uncheck-row" title="Uncheck all" class="">
                                    <i class="fa fa-square-o"></i>&nbsp; None
                                </button>
                                &nbsp;
                                <button href="" class="btn btn-default btn-xs check-row" title="Check all">
                                    <i class="fa fa-check-square-o"></i>&nbsp; All
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

  </div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include checklist_dependency js-->
    <script>
        jQuery(document).ready(function($) {

            $('.checklist_dependency').each(function(index, item) {
                var $field = $(this);

                var unique_name = $field.data('entity');

                // Gets the permissions granted by each role
                var rolesPermissions = window[unique_name];

                /**
                 * Check/uncheck all
                 */
                $field.find('.check-row').on('click', function(event) {
                    event.preventDefault();
                    $(this).closest('.row').find('.secondary_list').each(function() {
                        var $input = $(this);
                        $input.prop('checked', true);
                        addInputHidden($input);
                    });
                    return false;
                });
                $field.find('.uncheck-row').on('click', function(event) {
                    event.preventDefault();
                    $(this).closest('.row').find('.secondary_list').each(function() {
                        var $input = $(this);
                        if (!$input.is(':disabled')) {
                            $input.prop('checked', false);
                        }
                        removeInputHidden($input);
                    });
                    return false;
                });
                $field.find('.check-all').on('click', function(event) {
                    event.preventDefault();
                    $field.find('.secondary_list').each(function() {
                        var $input = $(this);
                        $input.prop('checked', true);
                        addInputHidden($input);
                    });
                    return false;
                });
                $field.find('.uncheck-all').on('click', function(event) {
                    event.preventDefault();
                    $field.find('.secondary_list').each(function() {
                        var $input = $(this);
                        if (!$input.is(':disabled')) {
                            $input.prop('checked', false);
                        }
                        removeInputHidden($input);
                    });
                    return false;
                });

                /**
                 * Roles
                 */
                $field.find('.primary_list').each(function() {
                    var $input = $(this);

                    // Handles click on a role
                    $input.change(function() {
                        var $input = $(this);
                        var roleId = $input.data('id');

                        // Check
                        if ($input.is(':checked')) {
                            // Adds hidden field with this value
                            var nameInput = $field.find('.hidden_fields_primary').data('name');
                            var inputToAdd = $('<input type="hidden" class="primary_hidden" name="'+nameInput+'[]" value="'+roleId+'">');
                            $field.find('.hidden_fields_primary').append(inputToAdd);

                            if ($.isArray(rolesPermissions[roleId])) {
                                $.each(rolesPermissions[roleId], function (key, permissionId) {
                                    // Checks and disable secondaries checkboxes
                                    $field.find('input.secondary_list[value="' + permissionId + '"]').prop("checked", true).prop("disabled", true);
                                });
                            }
                        }
                        // Uncheck
                        else {
                            // Removes hidden field with this value
                            $field.find('input.primary_hidden[value="'+roleId+'"]').remove();

                            // Unchecks and activates secondary checkboxes if are not in other selected primary.
                            var selectedRoles = [];
                            $field.find('input.primary_hidden').each(function(index, input) {
                                selectedRoles.push($(this).val());
                            });

                            if ($.isArray(rolesPermissions[roleId])) {
                                $.each(rolesPermissions[roleId], function (index, permissionId) {

                                    // Checks if the permission is granted by another selected role
                                    var inOtherRoles = $.grep(selectedRoles, function (otherRoleId) {
                                        return $.isArray(rolesPermissions[otherRoleId]) && rolesPermissions[otherRoleId].indexOf(permissionId) !== -1;
                                    });

                                    // If not granted by another role, removes the disabled state and resets to the last checked state
                                    if (inOtherRoles.length === 0) {
                                        var $input = $field.find('input.secondary_list[value="' + permissionId + '"]');
                                        $input.prop('checked', hasInputHidden($input)).prop('disabled', false);
                                    }
                                });
                            }
                        }
                    });
                });

                /**
                 * Checks if input has a hidden field
                 */
                function hasInputHidden($input)
                {
                    return $field.find('.hidden_fields_secondary input[value="'+$input.data('id')+'"]').length > 0;
                }

                /**
                 * Permissions
                 */
                $field.find('.secondary_list').each(function() {
                    var $input = $(this);

                    // Handles click on a permission
                    $input.on('click update', function () {
                        if ($input.is(':checked')) {
                            addInputHidden($input);
                        } else {
                            removeInputHidden($input);
                        }
                    })
                });

                /**
                 * Adds the value in a hidden field
                 *
                 * @param $input
                 */
                function addInputHidden($input)
                {
                    if (!hasInputHidden($input)) {
                        var nameInput = $field.find('.hidden_fields_secondary').data('name');
                        var inputToAdd = $('<input type="hidden" class="secondary_hidden" name="' + nameInput + '[]" value="' + $input.data('id') + '">');
                        $field.find('.hidden_fields_secondary').append(inputToAdd);
                    }
                }

                /**
                 * Removes the hidden field
                 *
                 * @param $input
                 */
                function removeInputHidden($input)
                {
                    $field.find('.hidden_fields_secondary input.secondary_hidden[value="' + $input.data('id') + '"]').remove();
                }
            });
        });
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
