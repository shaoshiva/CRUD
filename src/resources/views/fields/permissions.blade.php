<!-- permissions -->
<div class="form-group col-md-12 checklist" @include('crud::inc.field_wrapper_attributes') >

    @if (!empty($field['label']))
        <label>{!! $field['label'] !!}</label>
    @endif

    @include('crud::inc.field_translatable_icon')

    <div class="row">
        <div class="col-sm-12">
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
    </div>

    @foreach ($field['model']::all()->groupBy(function($permission) { return $permission->prefix(); }) as $prefix => $permissions)
        <hr/>
        <div class="row">
                <div class="col-sm-3">
                    <label class="no-margin">
                        <strong>{{ $prefix }}</strong>
                    </label>
                </div>
                <div class="col-sm-7">
                    @foreach ($permissions as $permission)
                        <div class="checkbox inline no-margin">
                            <label>
                                <input
                                    type="checkbox"
                                    name="{{ $field['name'] }}[]"
                                    value="{{ $permission->getKey() }}"
                                    @if( ( old( $field["name"] ) && in_array($permission->getKey(), old( $field["name"])) ) || (isset($field['value']) && in_array($permission->getKey(), $field['value']->pluck($permission->getKeyName(), $permission->getKeyName())->toArray())))
                                        checked = "checked"
                                    @endif > {!! $permission->item() !!} &nbsp;
                            </label>
                        </div>
                    @endforeach
                </div>
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
        </div>
    @endforeach

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
    <!-- include checklist js-->
    <script>
        jQuery(document).ready(function($) {

            $('.checklist').each(function(index, item) {
                var $field = $(this);

                /**
                 * Check/uncheck all
                 */
                $field.find('.check-row').on('click', function(event) {
                    event.preventDefault();
                    $(this).closest('.row').find('.checkbox input').prop('checked', true);
                    return false;
                });
                $field.find('.uncheck-row').on('click', function(event) {
                    event.preventDefault();
                    $(this).closest('.row').find('.checkbox input').prop('checked', false);
                    return false;
                });
                $field.find('.check-all').on('click', function(event) {
                    event.preventDefault();
                    $field.find('.checkbox input').prop('checked', true);
                    return false;
                });
                $field.find('.uncheck-all').on('click', function(event) {
                    event.preventDefault();
                    $field.find('.checkbox input').prop('checked', false);
                    return false;
                });
            });
        });
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
