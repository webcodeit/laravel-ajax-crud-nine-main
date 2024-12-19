@php
    function getSortingClassBySortColumn($sortType, $column, $reqColumn = '')
    {
        if ($reqColumn && $reqColumn == $column && $sortType) {
            return $sortType == 'asc' ? 'sorting_asc' : 'sorting_desc';
        } else {
            return null;
        }
    }
@endphp
<div>
<table class="table table-bordered table-hover dt-responsive nowrap" id="data-table-list">
    <thead>
      <tr>
        <th>
            <div class="form-check">
                <input class="form-check-input" value="1"  type="checkbox" id="evt_select_all_chk_box_delete">
                <label class="form-check-label" for="evt_select_all_chk_box_delete">
                    {{-- {{ __('Select All')}} --}}
                </label>
            </div>
        </th>
        <th class="sorting {{ getSortingClassBySortColumn($sortType, 'title', $sortColumn) }}" data-column-name="title">Title</th>
        <th>Image</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($posts as $post)
        <tr>
            <td>
                <div class="form-check">
                    <input class="form-check-input evt_single_chk_box_delete" type="checkbox" name="chk_box_delete[]" value="{{$post->id}}" id="operation_{{$post->id}}">
                </div>
            </td>
            <td>{{ $post->title }}</td>

              <td>
                @php
                   $file_ext = strtolower(pathinfo($post->file_name, PATHINFO_EXTENSION));
                @endphp
                 @if($file_ext != 'pdf')
                  <img width="100" src="{{ $post->file_path ? route('secure-image', Crypt::encryptString($post->file_path)) : '#' }}">
                 @else
                  <a href="{{ $post->file_path ? route('secure-pdf', Crypt::encryptString($post->file_path)) : '#' }}" target="_blank">
                      <img width="100" src="{{ asset('images/pdf.svg') }}" alt="document" class="img-fluid img-thumbnail rounded mt-2">
                  </a>
                 @endif
            </td>
            <td>
                <a href="{{route('post.edit', $post) }}" class="btn btn-info" role="button" onclick="return confirm('Are you sure you want to edit this item?');">Edit </a>
                <a href="javascript:;" data-href="{{ route('post.delete', $post->id) }}" onclick="permanentDeleteRecord(this)" data-slug="{{$post->id}}" class="text-white btn btn-sm btn-danger">{{ __('Delete')}}</a>
            </td>
         </tr>
        @endforeach
    </tbody>
  </table>

</div>

<div class="pt-1">
    <div class="d-flex justify-content-center justify-content-sm-between flex-wrap flex-sm-nowrap align-items-center">
        <div class="text-center text-sm-left">{{ $posts->links() }}</div>
        @if($posts->isNotEmpty())
            <div class="mt-2 mt-sm-0 text-center text-sm-right">
                <span>{{ __('Entries per page') }}:&nbsp;</span>
                <select name="per_page" id="per-page" form="form-filter-user">
                    <option value="2" @if($perPage == 2) selected @endif>2</option>
                    <option value="5" @if($perPage == 5) selected @endif>5</option>
                    <option value="15" @if($perPage == 15) selected @endif>15</option>
                    <option value="25" @if($perPage == 25) selected @endif>25</option>
                    <option value="50" @if($perPage == 50) selected @endif>50</option>
                    <option value="100" @if($perPage == 100) selected @endif>100</option>
                </select>
            </div>
        @endif
    </div>
</div>