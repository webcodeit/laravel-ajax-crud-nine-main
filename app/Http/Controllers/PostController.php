<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /**
         * use hellper funcation
         */

        // $res = app('common')->statusByColor('Pending'); // helper

        // =======================

       /* 
       * use  operation Repository
       * 
       $re_param = $request->all();
        $perPage = $request->input('per_page') ?? config('constants.PER_PAGE_ADMIN');
        $sortType = $request->input('sort_type') ?? 'DESC';
        $sortColumn = $request->input('sort_column',) ?? 'id';
        $column_names = $request->input('column_names') ?? [];
        
        $data = app('operation')->getAll($re_param);  // operation Repository
        */

        // $posts = Post::simplePaginate(1);


        $posts = Post::get();
        return view('post', ['posts' => $posts]);
    }

    public function list()
    {
       

        return view('list');
    }
    public function ajaxPostList(Request $request)
    {
        $re_param = $request->all();
        $perPage = $request->input('per_page') ??  2;
        $sortType = $request->input('sort_type') ?? 'DESC';
        $sortColumn = $request->input('sort_column',) ?? 'id';
        $column_names = $request->input('column_names') ?? [];
        
        $pagination = true;

        $posts = Post::
        when($re_param, function($qry) use($re_param) {
            if(isset($re_param['search']) && !empty($re_param['search'])) {
                $qry->where('title', 'like', '%' . $re_param['search'] . '%');
            }
        })
        ->when($re_param, function ($query) use ($re_param) {
            if(isset($re_param['sort_column']) && isset($re_param['sort_type']) ){
                return $query->orderBy($re_param['sort_column'], $re_param['sort_type']);
            } else {
                return $query->orderBy('id', 'desc');
            }
        })
        ->when($pagination, function ($query) use($re_param) {
            if(isset($re_param['per_page']) && $re_param['per_page']!=''){
                return $query->paginate($re_param['per_page']);
            } else {
                // return $query->paginate(config('constants.PER_PAGE_ADMIN'));
                return $query->paginate(2);
            }
        }, function ($query) {
            return $query->get();
        });
       

        return view('ajax-post', ['posts' => $posts, 'sortType' => $sortType, 'sortColumn' => $sortColumn, 'perPage' => $perPage, 'column_names' => $column_names]);
    }

    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'mobile' => 'required|string|max:15', // Adjust max length as needed
            'gender' => 'required|in:male,female,other', // Change options as needed
            'hobbies' => 'required|array', // Assuming hobbies is an array
            'hobbies.*' => 'string', // Validate each hobby as a string
            'profile_file' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:2048', // File validation
            // 'profile_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048', // File validation
        ]);
        
        $input = $request->all();
        $default_val = null;
        \DB::beginTransaction();
        try {
            $post = new Post();
            $post->title = $input['title'];
            $post->description = $input['description'];
            $post->mobile = $input['mobile'];
            $post->gender = $input['gender'];
            $post->hobbies = json_encode($input['hobbies']);

            if($input['hobbies']) {
                $post->skill = implode(',', $input['hobbies']);
            }

            if ($request->hasFile('profile_file') && $request->file('profile_file')->isValid()) {

                $uploadedFile = $request->file('profile_file');
            
                // Get file details
                $fileName = $uploadedFile->getClientOriginalName();
                $fileSize = $uploadedFile->getSize();
                $extension = $uploadedFile->extension();
                $fileLastModified = $uploadedFile->getMTime();

                $upload_path = "uploads/profile_file";

                // \Storage::put($upload_path, $fileName); // true upload thy
                //  $path = $uploadedFile->store('uploads'); // true upload thy

                $path = $uploadedFile->storeAs($upload_path, $fileName);

                $post->file_name = $fileName;
                $post->file_size = $fileSize;
                $post->file_last_modified = $fileLastModified;
                $post->file_path = $path;
            } 

            $post->save();
            
            \DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Save submitted successfully!',
                    'redirect_url' => route('post.index') // Redirect URL after successful submission
                ]);
            } else {

                if($request->is('api/*')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Save submitted successfully!',
                        'redirect_url' => route('post.index') // Redirect URL after successful submission
                    ]);
                }
                
                return redirect()->route('post.index')->with('success','Save successfully.');
            }

        } catch (\Throwable $th) {
            \DB::rollBack();
            $response = [
                'success' => false,
                'success' => 0,
                'message' => $th->getMessage() . 'Line No. ' . $th->getLine(),
            ];
            return response()->json($response);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        return view('create',['edit' => $post]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'mobile' => 'required|string|max:15', // Adjust max length as needed
            'gender' => 'required|in:male,female,other', // Change options as needed
            'hobbies' => 'required|array', // Assuming hobbies is an array
            'hobbies.*' => 'string', // Validate each hobby as a string
            'profile_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048', // File validation
        ]);
        
        $input = $request->all();
        \DB::beginTransaction();
        try {
            // $post = new Post();
            $post->title = $input['title'];
            $post->description = $input['description'];
            $post->mobile = $input['mobile'];
            $post->gender = $input['gender'];
            $post->hobbies = json_encode($input['hobbies']);

            if($input['hobbies']) {
                $post->skill = implode(',', $input['hobbies']);
            }

            $default_val = null;
            if ($request->hasFile('profile_file')) {

                 /* delete file */
                 if (isset($post->file_path) && \Storage::exists($post->file_path)) {
                    \Storage::delete($post->file_path);
                }
                
                $uploadedFile = $request->file('profile_file');
                $fileName = $uploadedFile->getClientOriginalName();
                $fileSize = $uploadedFile->getSize();
                $extension = $uploadedFile->extension();
                $fileLastModified = $uploadedFile->getMTime();

                $upload_path = "uploads/profile_file";

                // \Storage::put($upload_path, $fileName); // true upload thy
                //  $path = $uploadedFile->store('uploads'); // true upload thy

                $path = $uploadedFile->storeAs($upload_path, $fileName);
                
               

                $post->file_name = $fileName;
                $post->file_size = $fileSize;
                $post->file_last_modified = $fileLastModified;
                $post->file_path = $path;
            }

            $post->save();
            
            \DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Update submitted successfully!',
                    'redirect_url' => route('post.index') // Redirect URL after successful submission
                ]);
            } else {
                return redirect()->route('post.index')->with('success','Save successfully.');
            }


        } catch (\Throwable $th) {
            \DB::rollBack();
            $response = [
                'success' => 0,
                'message' => $th->getMessage() . 'Line No. ' . $th->getLine(),
            ];
            return response()->json($response);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {

    }

    public function delete(Request $request, $id)
    {
        
        $post = Post::where('id', $id)->first();

        if(isset($post)) {

            if (isset($post->file_path) && \Storage::exists($post->file_path)) {
                \Storage::delete($post->file_path);
            }

            $is_delete = $post->delete(); 

            if ($request->ajax()) {
                if($is_delete) {
                    $response = [
                        'status' => true,
                        'message' => __(' deleted successfully'),
                        'data' => ''
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'message' => __('Associate module can not delete'),
                        'data' => ''
                    ];
                }
                return response()->json($response);
            } else {
                return redirect()->route('post.index')->with('success','delete successfully.');
            }
        } else {
            abort(404, 'File not found!');
        }
    }

    public function ajaxPostDeleteAll(Request $request)
    {
        $this->validate($request, [
            'ids' => ['required'],
        ]);

        try {
            
            $ids = $request->get('ids');

            if(is_array($ids)) {

                $posts = Post::whereIn('id', $ids)->get();

                if($posts) {
                    foreach ($posts as $key => $post) {
                        if (isset($post->file_path) && \Storage::exists($post->file_path)) {
                            \Storage::delete($post->file_path);
                        }
                        $post->delete(); 
                    }
                    $response = [
                        'status' => true,
                        'message' => __('Deleted successfully'),
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'message' => __('No deleted'),
                    ];
                }
            } else {
                $response = [
                    'status' => false,
                    'message' => __('No deleted'),
                ];
            }
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => $th->getMessage(),
            ];
        }
        return response()->json($response);
    }
}
