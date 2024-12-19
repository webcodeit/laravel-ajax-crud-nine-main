<!DOCTYPE html>
<html lang="en">

<head>
    <title>Bootstrap Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>

    <div class="container">
        <a href="{{route('post.index')}}" class="btn btn-info" role="button">Back </a>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <h2> form</h2>
        @if(isset($edit) && $edit->id > 0) 
            <form class="form-horizontal" method="POST" action="{{ route('post.update', $edit->id)}}" enctype="multipart/form-data">
            @method('PUT')
        @else
            <form class="form-horizontal" method="POST" action="{{ route('post.store')}}" enctype="multipart/form-data">
        @endif

        @csrf
            <div class="form-group">
                <label class="control-label col-sm-2" for="title">Title:</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title',  $edit->title ?? '') }}">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-2" for="description">Description:</label>
                <div class="col-sm-8">
                    <textarea name="description" id="description" cols="30" rows="10"class="form-control">{{  old('description', $edit->description ?? '') }}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-2" for="mobile">Mobile:</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $edit->mobile ?? '') }}">
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
                    <input type="radio" id="male" name="gender" {{ (isset($edit->gender)  && $edit->gender=="male")? "checked" : "" }}  value="male">
                    <label for="male">male</label><br>
                    <input type="radio" id="female" name="gender" {{ (isset($edit->gender) && $edit->gender=="female")? "checked" : "" }} value="female">
                    <label for="female">female</label><br>
                    <input type="radio" id="other" name="gender" {{ (isset($edit->gender)  && $edit->gender=="other")? "checked" : "" }} value="other">
                    <label for="other">other</label>
                </div>
            </div>
                @php
                    if(isset($edit->hobbies)) {
                        $hobbies = json_decode($edit->hobbies, true) ?? [];
                    }
                @endphp
            <div class="form-group">
                <label class="control-label col-sm-2" for="mobile">Hobbies:</label>
                <div class="col-sm-8">
                    <input type="checkbox" id="vehicle1" name="hobbies[]"  {{ (isset($hobbies) && in_array('Bike', $hobbies) ? 'checked' : '') }} value="Bike">
                    <label for="vehicle1"> bike</label><br>
                    <input type="checkbox" id="vehicle2" name="hobbies[]"  {{ (isset($hobbies) && in_array('Car', $hobbies) ? 'checked' : '') }} value="Car">
                    <label for="vehicle2"> car</label><br>
                    <input type="checkbox" id="vehicle3" name="hobbies[]" {{ (isset($hobbies) && in_array('Boat', $hobbies) ? 'checked' : '') }} value="Boat">
                    <label for="vehicle3"> boat</label><br><br>
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
