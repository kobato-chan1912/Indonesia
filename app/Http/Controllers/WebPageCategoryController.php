<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Song;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WebPageCategoryController extends Controller
{
    //
    public $page;
    public $url;
    public function __construct(Request $request)
    {
        $this->page = $request->get('page');
        $this->url = "?page=";
    }

    public function loadView($songs, $title, $ogTitle, $ogDes){
        return view("webpage.categories.index",
            ["songs" => $songs, "page" => $this->page, "url" => $this->url,
                "og_title" => $ogTitle, "og_des" => $ogDes, "title" => $title]);
    }

    public function newestSongs()
    {
        $songs = Song::orderBy("id", "desc")->where("display", 1)->paginate(10);
        return $this->loadView($songs,
            "Nada dering terbaik",
            "Nada Dering Terbaik ".Carbon::now()->year." - Download Nada Dering gratis",
            "Download Nada Dering Terbaik to your phone, high quality mp3, m4r ringtones. Free ringtones NadaderingTelepon.Com for Phone");
    }
    public function popularSongs()
    {
        $songs = Song::orderBy("listeners", "desc")->where("display", 1)->paginate(10);
        return $this->loadView($songs,
            "Nada dering baru",
            "Nada Dering Baru - Latest Ringtones for WA and iPhone",
            "Nada Dering Baru in mp3 and m4r format for Wa and Iphones. We allow you to download this Ringtone for free On NadaderingTelepon.Com");
    }

    public function categorySongs($slug){
        // Slug Solve //
        $category = Category::where("category_slug", $slug)->where("display",1)->first();
        $song = Song::where("slug", $slug)->where("display",1)->first();
        $post = Post::where("slug", $slug)->where("display",1)->first();

        if ($category != null){ // has category

            $songs = Song::where("category_id", $category->id)->where("display", 1)->paginate(10);
            $title = "Download Ringtone Nada Dering $category->category_name Gratis";
            $metaDes = "Download Ringtone Nada Dering Telepon $category->category_name Gratis, Download Ringtone iPhone iphone, android mp3 m4r free ";
            return $this->loadView($songs, $title, $title, $metaDes);

            // return view
        } elseif ($song!= null){ // has Song

            $similarSongs = Song::where("category_id", $song->category_id)
                ->where("display", 1)
                ->where("id", "!=", $song->id)
                ->limit(12)->get();
            $currentListener = $song->listeners;
            Song::where("id", $song->id)->update(["listeners" => $currentListener+1]);
            return view("webpage.song.index",
                ["song" => $song, "similarSongs" => $similarSongs, "og_title" => $song->meta_title,
                    "og_des" => $song->meta_description]);

        } elseif ($post != null){ // has Post

            return view("webpage.post.index", ["post" => $post]);
        }
        else {
            abort("404");
        }
    }

    public function losMejores(){
        $songs  = Song::orderBy("downloads", "desc")->where("display", 1)->paginate(10);
        return $this->loadView($songs,
            "Nada dering teratas",
            "Nada Dering Teratas ".Carbon::now()->year." - 10,000+ Top Ringtones free",
            "Download Nada Dering Teratas is available for your mobile phone. 500,000+ high quality iPhone Ringtones, free mp3 Ringtones latest for Phone");
    }
    public function search(Request $request, $search){
        $songs = Song::where('title', 'LIKE', "%$search%")->paginate(10);
        return $this->loadView($songs, "Search Results: $search", "You searched for $search",
            "");
    }
}
