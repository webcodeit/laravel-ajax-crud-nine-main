<!DOCTYPE html>
<html lang="en">

<head>
    <title>Register</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>

    <div class="container">
        <a href="{{ route('post.index') }}" class="btn btn-info" role="button">Back </a>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div id="error-list" style="display:none;" class="alert alert-danger"></div>


        <h2> form</h2>
        @if (isset($edit) && $edit->id > 0)
            <form class="form-horizontal" id="reg_form" method="POST" action="{{ route('post.update', $edit->id) }}"
                enctype="multipart/form-data">
                @method('PUT')
            @else
                <form class="form-horizontal" id="reg_form" method="POST" action="{{ route('post.store') }}"
                    enctype="multipart/form-data">
        @endif

        {{-- @csrf --}}
        <div class="form-group">
            <label class="control-label col-sm-2" for="title">Title:</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" id="title" name="title"
                    value="{{ old('title', $edit->title ?? '') }}">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="description">Description:</label>
            <div class="col-sm-8">
                <textarea name="description" id="description" cols="30" rows="10"class="form-control">{{ old('description', $edit->description ?? '') }}</textarea>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="mobile">Mobile:</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" id="mobile" name="mobile"
                    value="{{ old('mobile', $edit->mobile ?? '') }}">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="mobile">File:</label>
            <div class="col-sm-8">
                <input type="file" class="form-control" id="file" name="profile_file">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="mobile">Gender:</label>
            <div class="col-sm-8">
                <input type="radio" id="male" name="gender" value="male"
                    {{ old('gender', $edit->gender ?? '') == 'male' ? 'checked' : '' }}>
                <label for="male">Male</label><br>
                <input type="radio" id="female" name="gender" value="female"
                    {{ old('gender', $edit->gender ?? '') == 'female' ? 'checked' : '' }}>
                <label for="female">Female</label><br>
                <input type="radio" id="other" name="gender" value="other"
                    {{ old('gender', $edit->gender ?? '') == 'other' ? 'checked' : '' }}>
                <label for="other">Other</label>

            </div>
        </div>
        @php
            if (isset($edit->hobbies)) {
                $hobbies = json_decode($edit->hobbies, true) ?? [];
                $skills = explode(',' , $edit->skill) ?? [];
            }
        @endphp
       
        <div class="form-group">
            <label class="control-label col-sm-2" for="mobile">Hobbies:</label>
            <div class="col-sm-8">
                <input type="checkbox" id="vehicle1" name="hobbies[]" value="Bike"
                    {{ is_array(old('hobbies', $hobbies ?? [])) && in_array('Bike', old('hobbies', $hobbies ?? [])) ? 'checked' : '' }}>
                <label for="vehicle1">Bike</label><br>

                <input type="checkbox" id="vehicle2" name="hobbies[]" value="Car"
                    {{ is_array(old('hobbies', $hobbies ?? [])) && in_array('Car', old('hobbies', $hobbies ?? [])) ? 'checked' : '' }}>
                <label for="vehicle2">Car</label><br>

                <input type="checkbox" id="vehicle3" name="hobbies[]" value="Boat"
                    {{ is_array(old('hobbies', $hobbies ?? [])) && in_array('Boat', old('hobbies', $hobbies ?? [])) ? 'checked' : '' }}>
                <label for="vehicle3">Boat</label><br>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-2" for="mobile">Skill:</label>
            <div class="col-sm-8">
                <input type="checkbox" id="vehicle1" name="skills[]" value="Bike"
                    {{ is_array(old('hobbies', $skills ?? [])) && in_array('Bike', old('hobbies', $skills ?? [])) ? 'checked' : '' }}>
                <label for="vehicle1">Bike</label><br>

                <input type="checkbox" id="vehicle2" name="skills[]" value="Car"
                    {{ is_array(old('hobbies', $skills ?? [])) && in_array('Car', old('hobbies', $skills ?? [])) ? 'checked' : '' }}>
                <label for="vehicle2">Car</label><br>

                <input type="checkbox" id="vehicle3" name="skills[]" value="Boat"
                    {{ is_array(old('hobbies', $skills ?? [])) && in_array('Boat', old('hobbies', $skills ?? [])) ? 'checked' : '' }}>
                <label for="vehicle3">Boat</label><br>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-8">
                <button type="submit" class="btn btn-default">Submit</button>
            </div>
        </div>
        </form>
    </div>

</body>

</html>

<script type="text/javascript">
    $(document).ready(function() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#reg_form').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res.success) {
                        $('.text-danger').remove();  
                        $('#error-list').html('').hide();
                        alert(res.message);
                        $('#reg_form')[0].reset();
                        // window.location.href = response.redirect_url;  // Redirect to another page after success
                    } else {
                        alert(res.message);
                    }
                },
                error: function(xhr, status, errors) {
                    ajaxErrorMsg(xhr);
                }
            });
        });
    });

    function ajaxErrorMsg(xhr) {
        var errorMessages = '';
        $('span.text-danger').remove();
        if (xhr.status === 422) {
            $.each(xhr.responseJSON.errors, function(key, val) {
                var inputField = $('input[name="' + key + '"], textarea[name="' + key + '"], select[name="' +
                    key + '"]');
                if (key.includes('hobbies')) {
                    var checkboxGroup = $('input[name="' + key + '[]"]').closest('.form-group');
                    checkboxGroup.append('<span class="text-danger">' + val + '</span>');
                } else if (key === 'gender') {
                    var genderGroup = $('input[name="gender"]').closest('.form-group');
                    genderGroup.append('<span class="text-danger">' + val + '</span>');
                } else {
                    inputField.after('<span class="text-danger">' + val + '</span>');
                }
                errorMessages += '<li>' + val + '</li>';
            });
        } else {
            errorMessages += '<li>' + statusText + '</li>';
        }
        $('#error-list').html('<ul>' + errorMessages + '</ul>').show();
    }
</script>
