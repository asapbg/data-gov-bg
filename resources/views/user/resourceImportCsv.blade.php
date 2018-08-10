@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'dataset'])
    <div class="col-xs-12 m-t-lg">
        <form
            class="form-horizontal"
            method="POST"
            action="{{ route('resourceCreate', ['uri' => $uri]) }}"
        >
            {{ csrf_field() }}

            <table class="table">
                @foreach ($csvData as $index => $row)
                @if ($index == 0)
                @foreach ($row as $key => $value)
                <th>
                    <p>{{ $value }}</p>
                    <div class="js-check">
                        <label>
                            <input type="checkbox" name="keepcol[{{$key}}]" checked>
                        </label>
                    </div>
                </th>
                @endforeach
                @else
                <tr>
                    @foreach ($row as $key => $value)
                    <td>{{ $value }}</td>
                    @endforeach
                </tr>
                @endif
                @endforeach
            </table>

            <div class="form-group row">
                <div class="col-sm-12 text-right">
                    <button name="keep" type="submit" class="m-l-md btn btn-primary">{{ __('custom.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection