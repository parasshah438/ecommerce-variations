<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use App\Models\Care;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;
use Response;
use Image;
use File;

class SliderController extends Controller
{
    public function index()
    {
        $category = Slider::get();
        return view('admin.sliders.index', compact('category'));
    }

    public function view_sliders(Request $request)
    {
        $id = $request->get('id');
        $data = Slider::where('id', $id)->get();
        return response()->json($data);
    }

    public function manage_sliders()
    {
        $user = Slider::latest()->get();
        $data = array();
        foreach ($user as $result) {
            $row = array();
            $row[] = $result['id'];
            if ($result['image'] != "") {
                $image = '<img src=../public/sliders/' . $result['image'] . ' style="width:100px;">';
            } else {
                $image = '<img src="../public/front_assets/img/no-image.png" height="50px">';
            }
            $row[] = $image;
            $row[] = $result['link'];

            $action_column = '
  <a href="javascript:void(0);" 
     class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center delete" 
     id="' . $result['id'] . '">
    <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
  </a>';

            $action_column .= '
  <a href="javascript:void(0);" 
     class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center edit" 
     id="' . $result['id'] . '">
    <iconify-icon icon="lucide:edit"></iconify-icon>
  </a>';




            $row[] = $action_column;
            $data[] = $row;
        }
        $results = [
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "data" => $data
        ];
        return response()->json($results);
    }

    public function add_edit_sliders(Request $request)
    {

        $id = $request->get("id");
        $image = request()->file('image');


        if ($id != "") {

            $validator = Validator::make(
                $request->all(),
                [
                    'link' => 'required',
                    'image' => 'image|mimes:jpeg,png,jpg',
                ]
            );

            if ($validator->fails()) {
                $this->response['status'] = 'error';
                $this->response['message'] = $validator->getMessageBag()->toArray();
                return response()->json($this->response);
            }

            //dd($request->link);	
            $data = Slider::find($id);
            $data->link = $request->link;
            $image = $request->file('image');



            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $completeFileName = $request->file('image')->getClientOriginalName();
                $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
                $extension = $request->file('image')->getClientOriginalExtension();
                $compPic = str_replace(' ', '_', $fileNameOnly) . '-' . rand() . '_' . time() . '.' . $extension;
                $destinationPath = public_path('sliders');
                $image->move($destinationPath, $compPic);
                $data->image = $compPic;
            }


            $data->save();

            $this->response['status'] = 'success';
            $this->response['message'] = "Our value details update Successfully";
            return response()->json($this->response);

        } else {

            $validator = Validator::make(
                $request->all(),
                [
                    'image' => 'required|image|mimes:jpeg,png,jpg',
                ]
            );

            if ($validator->fails()) {
                $this->response['status'] = 'error';
                $this->response['message'] = $validator->getMessageBag()->toArray();
                return response()->json($this->response);
            }

            $data = new Slider;
            $image = $request->file('image');
            $data->link = $request->link;


            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $completeFileName = $request->file('image')->getClientOriginalName();
                $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
                $extension = $request->file('image')->getClientOriginalExtension();
                $compPic = str_replace(' ', '_', $fileNameOnly) . '-' . rand() . '_' . time() . '.' . $extension;
                $destinationPath = public_path('sliders');
                $image->move($destinationPath, $compPic);
                $data->image = $compPic;
            }


            if ($request->hasFile('mainimage')) {
                $photo = $request->file('mainimage');
                $imagename = time() . '.' . $photo->getClientOriginalExtension();

                $destinationPath = public_path('mainimage/');
                $thumb_img = Image::make($photo->getRealPath())->resize(291, 291);
                $thumb_img->save($destinationPath . '/' . $imagename, 80);
            }
            $data->mainimage = $imagename;

            $data->save();
            $this->response['status'] = 'success';
            $this->response['message'] = "Slider details added Successfully";
            return response()->json($this->response);
        }
    }

    public function delete_sliders(Request $request)
    {
        $id = $request->get("id");
        Slider::where('id', $id)->delete();
        return "Successfully record deleted";
    }
}