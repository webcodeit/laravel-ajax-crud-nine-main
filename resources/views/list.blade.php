<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" translate="no">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google" content="notranslate">
    <!-- Favicon icon -->

    <title>{{ config('app.name', 'ajax') }} </title>

    <link href="{{ asset('plugins/DataTables/DataTables-1.12.1/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/DataTables/Responsive-2.3.0/css/responsive.bootstrap5.min.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

</head>

<body class="font-sans antialiased">
    <div class="container">
        <div class="card-body">
            <div class="card-header">
                <form action="#" method="POST" id="form-filter-user" class="filter-form">
                    @csrf
                    <input type="hidden" name="sort_column" id="sort-column" value="">
                    <input type="hidden" name="sort_type" id="sort-type" value="">
                    <div class="row">
                        <div class="col-md-3">
                            <input class="form-control" placeholder="{{ __('Search...') }}" type="search" name="search">
                        </div>
                    </div>
                    <br>
                    <div class="row pt-2">
                        <button type="submit" class="btn btn-success" id="btn-filter-submit">
                        Submit
                        </button>
                        <button type="reset" class="btn btn-info" id="btn-filter-reset">Reset</button>
                        {{-- <button type="button" class="btn waves-effect waves-light btn-outline-dark rounded-md btn-sm" id="btn-filter-export">Export</button> --}}
                    </div>
                </form>
                <br>
                <div class="card-header pt-2">
                    <button type="button" role="button" data-href="{{ route('ajax-delete-all')}}" class="btn btn-danger evt_btn_delete_all" disabled>{{ __('Delete All')}}</button>
                </div>
            </div>
        </div>
        
        <br>
        <div class="card-header">
            <div id="load-ajax"></div>
        </div>

</div>
<script src="{{ asset('plugins/jquery-3.3.1.min.js') }}"></script>

<script>
    const ajaxDataTableUrl = "{{ route('ajaxPostList') }}";
    $(document).ready(function () {
        initDataTable();
    });
</script>
    <script src="{{ asset('plugins/DataTables/DataTables-1.12.1/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/DataTables/DataTables-1.12.1/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('plugins/DataTables/Responsive-2.3.0/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/DataTables/Responsive-2.3.0/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/custom-datatable.js') }}"></script>
</body>
</html>