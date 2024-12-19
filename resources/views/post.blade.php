<!DOCTYPE html>
<html lang="en">
<head>
  <title>List</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>

<div class="container">
  @if(session('success'))
  <div class="alert alert-success">
      {{ session('success') }}
  </div>
@endif

  <h2> <a href="{{route('post.create')}}" class="btn btn-info" role="button">Add </a></h2>
  <table class="table dataTable" id="users-table">
    <thead>
      <tr>
        <th class="no-sort">Title</th>
        <th>Image</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($posts as $post)
        <tr>
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
                {{-- <a href="{{route('post.destroy', $post) }}" class="btn btn-info" role="button">Delete </a> --}}
                <a href="{{ route('post.delete', $post->id) }}" class="btn btn-danger delete-button" role="button">Delete </a>
            </td>
         </tr>
        @endforeach
    </tbody>
  </table>


  {{-- {{ $posts->appends(request()->query())->links() }} --}}

</div>

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</html>
{{--
 code working
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Select all delete buttons
        const deleteButtons = document.querySelectorAll('.delete-button');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                // Show confirmation dialog
                const confirmed = confirm("Are you sure you want to delete this item?");
                
                // If user clicks "Cancel," prevent the action
                if (!confirmed) {
                    event.preventDefault();
                }
            });
        });
    });
</script> --}}

<script>
  $(document).ready(function() {

    if ($(".dataTable").length) {
        $(".dataTable").DataTable({
            "responsive": true,
            "autoWidth": false,
            columnDefs: [
                {
                    orderable: false,
                    targets: "no-sort"
                },
            ],
        });
    }

      // Select all delete buttons with jQuery
      $('.delete-button').on('click', function(event) {
          // Show confirmation dialog
          const confirmed = confirm("Are you sure you want to delete this item?");
          
          // If user clicks "Cancel," prevent the action
          if (!confirmed) {
              event.preventDefault();
          }
      });
  });
</script>
