public function upload()
    {
        return view('upload');
    }

    /*##
    Store function will gather the data from the "upload" view and store it to the database
    ##*/

    public function store(Request $request)
    {
        // assure necessary data is input correctly
       $data = request()->validate([
            'postTitle' => 'required',
            'postDescription' => 'required',
            'postTags' => 'required',
            'postImage.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg,',
            'thumbnail.*' => '',
        ]);

        //grabbing the image(s) from the request
        if($request->file('postImage')){
            //foreach through each image in the request
            foreach($request->file('postImage') as $postImage) {
                //generate a random name for each image to avoid duplicate names in the database
                $basename = Str::random();
                //set the random name for each image in the original variable. this will be the full size image
                $original = $basename . '.' . $postImage->getClientOriginalExtension();
                //set the random name for each thumbnail. it will be the same as the corrisponding original image but with "_thumb" in the name
                $thumbnail = $basename . '_thumb.' . $postImage->getClientOriginalExtension();
                //save all images to the originalImageArray array
                $originalImageArray[] = $original;
                //save all the thumbnails to the thumbnailArray
                $thumbnailArray[] = $thumbnail;

                //create the thumbnails with intervention image and store them in the "photos" folder
                Image::make($postImage)
                    ->fit(250,200)
                    ->save(public_path('/storage/photos/' . $thumbnail));
                //save the original images in the "photos" folder
                $postImage->move(public_path("/storage/photos/"), $original);
            }
        }
        
        //create the post with all the data
        auth()->user()->post()->create(array_merge([
            'postTitle' => ucfirst($data['postTitle']),
            'postDescription' => $data['postDescription'],
            'postTags' => strtolower($data['postTags']),
            'postImage' => json_encode($originalImageArray),
            'thumbnail' => json_encode($thumbnailArray),
        ]));

        return redirect('/user/'. auth()->user()->id)->with('success', 'Post Created');
    }