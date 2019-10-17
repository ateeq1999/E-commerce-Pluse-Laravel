<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class categoryController extends Controller
{
    private $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = $this->category->whereIsRoot()->paginate(7);
        return view('admin.category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $allCategories = $this->category->select(['category_id','category_name','parent_id'])->get();
        return view('admin.category.create', compact('allCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'category_name' => 'required|string',
            'category_slug' => 'required|string',
        ]);
        $input = $request->except('_token');
        if ($input['parent_id'] != null) {
            $category = Category::findOrFail($input['parent_id']);
            $category->parent()->create($input);
        }else{
            $category = $this->category->create($input);
        }
        if (env('APP_AJAX')){
            return response()->json(['success' => $category]);
        }
        return redirect()->route('category.create')->with(['success' => 'new category has been created']);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!ctype_digit($id)){
            return response()->json(['error' => 'id is not valid']);
        }
        $category = $this->category->findOrFail($id);
        $category->products()->detach();
        $category->delete();
        if ($category) {
            return response()->json(['success' => $category]);
        }
        return response()->json(['error' => 'error']);
    }
}