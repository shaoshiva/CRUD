<!-- select2 -->
<div @include('crud::inc.field_wrapper_attributes') >

    @if (!empty($field['label']))
    <label>{!! $field['label'] !!}</label>
    @endif

    @include('crud::inc.field_translatable_icon')

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
                                <input type="checkbox"
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
                        <a href="" class="btn btn-default btn-xs" title="Uncheck all">
                            <i class="fa fa-square-o"></i>&nbsp; None
                        </a>
                        &nbsp;
                        <a href="" class="btn btn-default btn-xs" title="Check all">
                            <i class="fa fa-check-square-o"></i>&nbsp; All
                        </a>
                    </div>
                </div>
        </div>
    @endforeach

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>
